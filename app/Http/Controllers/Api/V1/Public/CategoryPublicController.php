<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryPublicController extends Controller
{
    /**
     * GET /v1/categories?type=posts
     */
    public function index(Request $request)
    {
        $type = $request->string('type', 'posts')->toString();

        $q = Category::query()
            ->type($type)
            ->orderBy('name');

        // Option: parent only (useful if you want tree client-side)
        if ($request->boolean('parent_only')) {
            $q->whereNull('parent_id');
        }

        $per = min((int) $request->get('per_page', 100), 200);

        return CategoryResource::collection($q->paginate($per));
    }
}
