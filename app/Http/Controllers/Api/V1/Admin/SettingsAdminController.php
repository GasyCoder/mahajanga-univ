<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Audit;
use Illuminate\Http\Request;

class SettingsAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureRole($request);

        $settings = Setting::all()->map(fn($s) => [
            'key' => $s->key,
            'value' => $s->value,
            'type' => $s->type,
            'group' => $s->group,
        ]);

        return response()->json([
            'data' => $settings,
            'grouped' => Setting::allGrouped(),
        ]);
    }

    public function update(Request $request)
    {
        $this->ensureRole($request);

        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
        ]);

        foreach ($data['settings'] as $item) {
            Setting::set($item['key'], $item['value'] ?? '');
        }

        Audit::log($request, 'settings.update', 'Setting', 0, [
            'keys' => array_column($data['settings'], 'key'),
        ]);

        return response()->json([
            'data' => true,
            'message' => 'Paramètres mis à jour',
        ]);
    }

    public function show(Request $request, string $key)
    {
        $this->ensureRole($request);

        $setting = Setting::where('key', $key)->first();
        
        if (!$setting) {
            return response()->json(['message' => 'Setting not found'], 404);
        }

        return response()->json(['data' => $setting]);
    }

    private function ensureRole(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['SuperAdmin']), 403);
    }
}
