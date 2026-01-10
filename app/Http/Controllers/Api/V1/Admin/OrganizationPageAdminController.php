<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationPageResource;
use App\Models\OrganizationPage;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationPageAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureRole($request);

        $q = OrganizationPage::query()
            ->with(['featuredImage'])
            ->orderBy('page_type')
            ->orderBy('order');

        if ($request->filled('page_type')) {
            $q->where('page_type', $request->string('page_type'));
        }

        $per = min((int)$request->get('per_page', 20), 100);

        return OrganizationPageResource::collection($q->paginate($per));
    }

    public function show(Request $request, int $id)
    {
        $this->ensureRole($request);

        $page = OrganizationPage::with(['featuredImage'])->findOrFail($id);

        return new OrganizationPageResource($page);
    }

    public function store(Request $request)
    {
        $this->ensureRole($request);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'page_type' => 'required|string|in:historique,organisation,textes,organigramme',
            'order' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'featured_image_id' => 'nullable|exists:media,id',
        ]);

        $data['slug'] = Str::slug($data['title']);
        
        // Ensure unique slug
        $baseSlug = $data['slug'];
        $counter = 1;
        while (OrganizationPage::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $baseSlug . '-' . $counter++;
        }

        $page = OrganizationPage::create($data);

        Audit::log($request, 'organization_page.create', 'OrganizationPage', $page->id, [
            'title' => $page->title,
            'page_type' => $page->page_type,
        ]);

        return new OrganizationPageResource($page->load(['featuredImage']));
    }

    public function update(Request $request, int $id)
    {
        $this->ensureRole($request);

        $page = OrganizationPage::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'nullable|string',
            'page_type' => 'sometimes|required|string|in:historique,organisation,textes,organigramme',
            'order' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'featured_image_id' => 'nullable|exists:media,id',
        ]);

        // Update slug if title changed
        if (isset($data['title']) && $data['title'] !== $page->title) {
            $data['slug'] = Str::slug($data['title']);
            $baseSlug = $data['slug'];
            $counter = 1;
            while (OrganizationPage::where('slug', $data['slug'])->where('id', '!=', $id)->exists()) {
                $data['slug'] = $baseSlug . '-' . $counter++;
            }
        }

        $page->update($data);

        Audit::log($request, 'organization_page.update', 'OrganizationPage', $page->id, [
            'title' => $page->title,
        ]);

        return new OrganizationPageResource($page->load(['featuredImage']));
    }

    public function destroy(Request $request, int $id)
    {
        $this->ensureRole($request);

        $page = OrganizationPage::findOrFail($id);

        Audit::log($request, 'organization_page.delete', 'OrganizationPage', $page->id, [
            'title' => $page->title,
        ]);

        $page->delete();

        return response()->json(['data' => true]);
    }

    private function ensureRole(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['SuperAdmin', 'Validateur']), 403);
    }
}
