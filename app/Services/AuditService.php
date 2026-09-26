<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an audit event
     */
    public static function log(
        string $action,
        ?string $description = null,
        ?object $model = null,
        array $oldValues = [],
        array $newValues = []
    ): void {
        $user = Auth::user();

        AuditLog::create([
            'user_id'     => $user?->id,
            'user_name'   => $user?->display_name,
            'role'        => $user?->role,
            'branch_id'   => $user?->branch_id,
            'branch_name' => $user?->branch?->name,
            'action'      => $action,
            'model_type'  => $model ? get_class($model) : null,
            'model_id'    => $model?->id,
            'old_values'  => $oldValues ?: null,
            'new_values'  => $newValues ?: null,
            'description' => $description,
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::userAgent(),
            'created_at'  => now(),
        ]);
    }

    /**
     * Log login event
     */
    public static function logLogin(): void
    {
        static::log('auth.login', 'User logged in');
    }

    /**
     * Log logout event
     */
    public static function logLogout(): void
    {
        static::log('auth.logout', 'User logged out');
    }
}
