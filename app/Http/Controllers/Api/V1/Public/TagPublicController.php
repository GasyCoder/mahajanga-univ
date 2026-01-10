<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagPublicController extends Controller
{
    /**
     * GET /v1/tags?type=posts
     */
    public function index(Request $request)
    {
        $type = $request->string('type', 'posts')->toString();

        $q = Tag::query()
            ->type($type)
            ->orderBy('name');

        $per = min((int) $request->get('per_page', 100), 200);

        return TagResource::collection($q->paginate($per));
    }
}
