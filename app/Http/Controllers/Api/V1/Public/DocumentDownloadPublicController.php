<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentDownloadPublicController extends Controller
{
    public function __invoke(int $id)
    {
        $doc = Document::with('file')->findOrFail($id);

        abort_unless($doc->status === 'published', 404);

        $doc->increment('download_count');

        $ext = pathinfo($doc->file->path, PATHINFO_EXTENSION);
        $filename = $doc->slug . ($ext ? '.'.$ext : '');

        return Storage::disk($doc->file->disk)->download($doc->file->path, $filename);
    }
}
