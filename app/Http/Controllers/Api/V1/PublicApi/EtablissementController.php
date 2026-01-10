<?php

namespace App\Http\Controllers\Api\V1\PublicApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\EtablissementResource;
use App\Models\Etablissement;
use Illuminate\Http\Request;

class EtablissementController extends Controller
{
    public function index(Request $request)
    {
        $q = Etablissement::query()
            ->with(['logo', 'coverImage'])
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $q->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('acronym', 'like', "%{$search}%");
            });
        }

        $per = min((int)$request->get('per_page', 20), 100);

        return EtablissementResource::collection($q->paginate($per));
    }

    public function show(string $slug)
    {
        $etablissement = Etablissement::with(['logo', 'coverImage'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return new EtablissementResource($etablissement);
    }
}
