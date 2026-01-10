<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;

class HealthPublicController extends Controller
{
    public function __invoke()
    {
        return response()->json(['data' => ['status' => 'ok']]);
    }
}
