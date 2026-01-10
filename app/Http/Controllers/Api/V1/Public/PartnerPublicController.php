<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerPublicController extends Controller
{
    public function index(Request $request)
    {
        $q = Partner::query()->with('logo')->orderBy('name');

        if ($request->filled('type')) {
            $q->where('type', $request->string('type'));
        }

        if ($request->boolean('featured')) {
            $q->where('is_featured', true);
        }

        $per = min((int) $request->get('per_page', 50), 50);
        return PartnerResource::collection($q->paginate($per));
    }
}
