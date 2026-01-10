<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentCategoryResource;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;

class DocumentCategoryPublicController extends Controller
{
    /**
     * GET /v1/document-categories
     * Option: ?parent_only=1
     */
    public function index(Request $request)
    {
        $q = DocumentCategory::query()->orderBy('name');

        if ($request->boolean('parent_only')) {
            $q->whereNull('parent_id');
        }

        $per = min((int) $request->get('per_page', 100), 200);

        return DocumentCategoryResource::collection($q->paginate($per));
    }
}
