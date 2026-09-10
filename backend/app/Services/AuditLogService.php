<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    public function log(
        User $user,
        string $action,
        Model $entity,
        ?array $before = null,
        ?array $extra = null,
    ): AuditLog {
        $after = $entity->toArray();

        return AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'entity_type' => $entity->getTable(),
            'entity_id' => (string) $entity->getKey(),
            'before_data' => $before,
            'after_data' => $extra ? array_merge($after, $extra) : $after,
            'ip_address' => app(Request::class)->ip(),
        ]);
    }
}
