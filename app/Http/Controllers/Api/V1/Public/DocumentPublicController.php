<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentPublicController extends Controller
{
    public function index(Request $request)
    {
        $q = Document::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->with(['category','file'])
            ->orderByDesc('published_at');

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $q->where(fn($w) => $w->where('title','like',"%$term%")->orWhere('description','like',"%$term%"));
        }

        if ($request->filled('category')) {
            $slug = $request->string('category')->toString();
            $q->whereHas('category', fn($c) => $c->where('slug', $slug));
        }

        $per = min((int) $request->get('per_page', 12), 50);
        return DocumentResource::collection($q->paginate($per));
    }

    public function show(string $slug)
    {
        $doc = Document::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with(['category','file'])
            ->firstOrFail();

        return new DocumentResource($doc);
    }
}
