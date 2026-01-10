<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

final class Audit
{
    public static function log(
        Request $request,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        array $meta = []
    ): void {
        ActivityLog::create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'meta' => $meta ?: null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}