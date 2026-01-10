<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Support\Slugger;
use Illuminate\Http\Request;

class TagAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureRole($request);

        $type = $request->string('type', 'posts')->toString();

        $q = Tag::query()
            ->where('type', $type)
            ->withCount('posts')
            ->orderBy('name');

        $per = min((int) $request->get('per_page', 50), 200);

        return TagResource::collection($q->paginate($per));
    }

    public function store(StoreTagRequest $request)
    {
        $data = $request->validated();

        $type = $data['type'] ?? 'posts';

        $tag = Tag::create([
            'name' => $data['name'],
            'slug' => Slugger::uniqueSlug(Tag::class, $data['name']),
            'type' => $type,
            'color' => $data['color'] ?? null,
        ]);

        return new TagResource($tag);
    }

    public function update(Request $request, int $id)
    {
        $this->ensureRole($request);

        $tag = Tag::findOrFail($id);

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'type' => ['nullable','string','max:50'],
            'color' => ['nullable','string','max:7'],
        ]);

        $tag->fill([
            'name' => $data['name'],
            'slug' => Slugger::uniqueSlugForUpdate(Tag::class, $tag->id, $data['name']),
            'type' => $data['type'] ?? $tag->type,
            'color' => $data['color'] ?? $tag->color,
        ])->save();

        return new TagResource($tag);
    }

    public function destroy(Request $request, int $id)
    {
        $this->ensureRole($request);

        $tag = Tag::withCount('posts')->findOrFail($id);

        if ($tag->posts_count > 0) {
            return response()->json([
                'message' => 'Tag is used by posts and cannot be deleted.',
                'code' => 'TAG_IN_USE',
            ], 409);
        }

        $tag->delete();

        return response()->json(['data' => true]);
    }

    private function ensureRole(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['SuperAdmin','Validateur']), 403);
    }
}
