<?php

namespace Calvino\Traits;

/**
 * Trait LoggableActivity
 * Logique pour le modèle ActivityLog
 */
trait LoggableActivity
{
    /**
     * Enregistre une nouvelle activité
     */
    public static function log($userId, string $action, string $module, string $description, ?array $oldValues = null, ?array $newValues = null): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => date('Y-m-d H:i:s')
        ]) !== null;
    }
}
