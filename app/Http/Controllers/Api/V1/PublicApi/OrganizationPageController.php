<?php

namespace App\Http\Controllers\Api\V1\PublicApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationPageResource;
use App\Models\OrganizationPage;
use Illuminate\Http\Request;

class OrganizationPageController extends Controller
{
    public function index(Request $request)
    {
        $q = OrganizationPage::query()
            ->with(['featuredImage'])
            ->where('is_published', true)
            ->orderBy('page_type')
            ->orderBy('order');

        if ($request->filled('page_type')) {
            $q->where('page_type', $request->string('page_type'));
        }

        return OrganizationPageResource::collection($q->get());
    }

    public function show(string $slug)
    {
        $page = OrganizationPage::with(['featuredImage'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return new OrganizationPageResource($page);
    }

    public function byType(string $type)
    {
        $pages = OrganizationPage::with(['featuredImage'])
            ->where('page_type', $type)
            ->where('is_published', true)
            ->orderBy('order')
            ->get();

        return OrganizationPageResource::collection($pages);
    }
}
