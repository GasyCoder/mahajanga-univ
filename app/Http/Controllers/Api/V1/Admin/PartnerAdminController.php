<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePartnerRequest;
use App\Http\Requests\Admin\UpdatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = Partner::query()->with('logo');

        if ($request->filled('type')) $q->where('type', $request->string('type'));

        $per = min((int) $request->get('per_page', 20), 50);
        return PartnerResource::collection($q->orderBy('name')->paginate($per));
    }

    public function store(StorePartnerRequest $request)
    {
        $partner = Partner::create($request->validated());
        return new PartnerResource($partner->load('logo'));
    }

    public function show(int $id)
    {
        $partner = Partner::with('logo')->findOrFail($id);
        return new PartnerResource($partner);
    }

    public function update(UpdatePartnerRequest $request, int $id)
    {
        $partner = Partner::findOrFail($id);
        $partner->fill($request->validated())->save();

        return new PartnerResource($partner->load('logo'));
    }

    public function destroy(int $id)
    {
        $partner = Partner::findOrFail($id);
        $partner->delete();
        return response()->json(['data' => true]);
    }
}
