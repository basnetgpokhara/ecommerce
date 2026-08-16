<?php
namespace App\Models;

use App\Core\Database;

/** audit_logs — critical action log */
class AuditLog extends \App\Core\Model
{
    protected static string $table = 'audit_logs';

    public static function record(?int $userId, string $action, ?string $description = null): void
    {
        try {
            Database::insert('audit_logs', [
                'user_id'     => $userId,
                'action'      => $action,
                'description' => $description,
                'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            // Audit logging must never break the request.
        }
    }
}
