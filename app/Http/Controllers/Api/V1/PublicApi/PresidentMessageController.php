<?php

namespace App\Http\Controllers\Api\V1\PublicApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\PresidentMessageResource;
use App\Models\PresidentMessage;

class PresidentMessageController extends Controller
{
    public function active()
    {
        $message = PresidentMessage::active();

        if (!$message) {
            return response()->json(['data' => null]);
        }

        return new PresidentMessageResource($message);
    }
}
