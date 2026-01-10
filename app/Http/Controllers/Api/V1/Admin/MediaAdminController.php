<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MediaAdminController extends Controller
{
    public function index(Request $request)
    {
        $per = min((int) $request->get('per_page', 50), 100);
        $q = Media::query()->latest();

        if ($type = $request->get('type')) {
            $q->where('mime', 'like', $type . '%');
        }

        return MediaResource::collection($q->paginate($per));
    }

    public function show(int $id)
    {
        $media = Media::findOrFail($id);
        return new MediaResource($media);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required','file','max:10240'],
            'alt' => ['nullable','string','max:255'],
            'disk' => ['nullable','in:public,private'],
        ]);

        $disk = $validated['disk'] ?? 'public';
        $file = $validated['file'];

        $folder = 'uploads/' . now()->format('Y/m');
        $filename = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, $disk);

        $media = Media::create([
            'disk' => $disk,
            'path' => $path,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt' => $validated['alt'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return new MediaResource($media);
    }

    public function destroy(int $id)
    {
        $media = Media::findOrFail($id);
        $media->delete();

        return response()->json(['data' => true]);
    }
}
