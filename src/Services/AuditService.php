<?php

namespace Calvino\Services;

use Calvino\Models\ActivityLog;

class AuditService
{
    /**
     * Enregistre une connexion
     */
    /**
     * Enregistre une connexion
     */
    public static function logLogin(int|string $userId, string $email): void
    {
        ActivityLog::log($userId, 'LOGIN', 'AUTH', "Connexion de l'utilisateur {$email}");
    }
    
    /**
     * Enregistre une déconnexion
     */
    public static function logLogout(int|string $userId, string $email): void
    {
        ActivityLog::log($userId, 'LOGOUT', 'AUTH', "Déconnexion de l'utilisateur {$email}");
    }
    
    /**
     * Enregistre une action générique
     */
    public static function logAction(int|string $userId, string $action, string $module, string $description, ?array $oldValues = null, ?array $newValues = null): void
    {
        ActivityLog::log($userId, $action, $module, $description);
    }
}
