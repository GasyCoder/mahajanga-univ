<?php

namespace App\Http\Controllers\Api\V1\PublicApi;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingsPublicController extends Controller
{
    public function maintenanceStatus(): JsonResponse
    {
        $settings = Setting::byGroup('maintenance');
        $general = Setting::byGroup('general'); // Need site name/email for maintenance page
        
        $logoId = $general['logo_id'] ?? null;
        $logoUrl = null;
        
        if ($logoId) {
            $logo = \App\Models\Media::find($logoId);
            $logoUrl = $logo?->url;
        }

        return response()->json([
            'maintenance_mode' => filter_var($settings['maintenance_mode'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'maintenance_message' => $settings['maintenance_message'] ?? 'Le site est en maintenance.',
            'site_name' => $general['site_name'] ?? 'Université de Mahajanga',
            'site_email' => $general['site_email'] ?? '',
            'site_phone' => $general['site_phone'] ?? '',
            'logo_url' => $logoUrl,
        ]);
    }
}
