<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class PostPublicController extends Controller
{
    public function index(Request $request)
    {
        $q = Post::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->with(['coverImage','categories','tags'])
            ->orderByDesc('published_at');

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $q->where(fn($w) => $w->where('title','like',"%$term%")->orWhere('excerpt','like',"%$term%"));
        }

        if ($request->filled('category')) {
            $slug = $request->string('category')->toString();
            $q->whereHas('categories', fn($c) => $c->where('slug', $slug));
        }

        if ($request->filled('tag')) {
            $slug = $request->string('tag')->toString();
            $q->whereHas('tags', fn($t) => $t->where('slug', $slug));
        }

        if ($request->boolean('featured')) {
            $q->where('is_featured', true);
        }

        $per = min((int) $request->get('per_page', 12), 50);
        return PostResource::collection($q->paginate($per));
    }

    public function show(string $slug)
    {
        $post = Post::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with(['coverImage','categories','tags','gallery','author'])
            ->firstOrFail();

        return new PostResource($post);
    }
}
