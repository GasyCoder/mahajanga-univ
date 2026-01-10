<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentCategoryResource;
use App\Models\DocumentCategory;
use App\Support\Slugger;
use Illuminate\Http\Request;

class DocumentCategoryAdminController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->hasAnyRole(['SuperAdmin','Validateur']), 403);

        $q = DocumentCategory::query()
            ->withCount('documents')
            ->orderBy('name');
        $per = min((int) $request->get('per_page', 50), 200);

        return DocumentCategoryResource::collection($q->paginate($per));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->hasAnyRole(['SuperAdmin','Validateur']), 403);

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'parent_id' => ['nullable','integer','exists:document_categories,id'],
        ]);

        $cat = DocumentCategory::create([
            'name' => $data['name'],
            'slug' => Slugger::uniqueSlug(DocumentCategory::class, $data['name']),
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        return new DocumentCategoryResource($cat);
    }

    public function update(Request $request, int $id)
    {
        abort_unless($request->user()?->hasAnyRole(['SuperAdmin','Validateur']), 403);

        $cat = DocumentCategory::findOrFail($id);

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'parent_id' => ['nullable','integer','exists:document_categories,id'],
        ]);

        $cat->fill([
            'name' => $data['name'],
            'slug' => Slugger::uniqueSlugForUpdate(DocumentCategory::class, $cat->id, $data['name']),
            'parent_id' => $data['parent_id'] ?? null,
        ])->save();

        return new DocumentCategoryResource($cat);
    }

    public function destroy(Request $request, int $id)
    {
        abort_unless($request->user()?->hasAnyRole(['SuperAdmin','Validateur']), 403);

        $cat = DocumentCategory::withCount('documents')->findOrFail($id);

        if ($cat->documents_count > 0) {
            return response()->json([
                'message' => 'Document category is used by documents and cannot be deleted.',
                'code' => 'DOCUMENT_CATEGORY_IN_USE',
            ], 409);
        }

        $cat->delete();

        return response()->json(['data' => true]);
    }
}
