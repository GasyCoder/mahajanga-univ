<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\Slugger;
use Illuminate\Http\Request;

class CategoryAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureRole($request);

        $type = $request->string('type', 'posts')->toString();

        $q = Category::query()
            ->where('type', $type)
            ->withCount('posts')
            ->orderBy('name');

        $per = min((int) $request->get('per_page', 50), 200);

        return CategoryResource::collection($q->paginate($per));
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        $type = $data['type'] ?? 'posts';

        $category = Category::create([
            'name' => $data['name'],
            'slug' => Slugger::uniqueSlug(Category::class, $data['name']),
            'type' => $type,
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        return new CategoryResource($category);
    }

    public function update(Request $request, int $id)
    {
        $this->ensureRole($request);

        $category = Category::findOrFail($id);

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'type' => ['nullable','string','max:50'],
            'parent_id' => ['nullable','integer','exists:categories,id'],
        ]);

        $category->fill([
            'name' => $data['name'],
            'slug' => Slugger::uniqueSlugForUpdate(Category::class, $category->id, $data['name']),
            'type' => $data['type'] ?? $category->type,
            'parent_id' => $data['parent_id'] ?? null,
        ])->save();

        return new CategoryResource($category);
    }

    public function destroy(Request $request, int $id)
    {
        $this->ensureRole($request);

        $category = Category::withCount('posts')->findOrFail($id);

        if ($category->posts_count > 0) {
            return response()->json([
                'message' => 'Category is used by posts and cannot be deleted.',
                'code' => 'CATEGORY_IN_USE',
            ], 409);
        }

        $category->delete();

        return response()->json(['data' => true]);
    }

    private function ensureRole(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['SuperAdmin','Validateur']), 403);
    }
}
