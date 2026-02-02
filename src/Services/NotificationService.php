<?php

namespace Calvino\Services;

use Calvino\Models\Notification;

class NotificationService
{
    /**
     * Envoie une notification à un utilisateur
     * 
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param string $type
     * @param mixed $data
     * @return bool
     */
    public function send(int $userId, string $title, string $message, string $type = 'info', $data = null): bool
    {
        $notification = new Notification();
        return (bool) $notification->createNotification($userId, $title, $message, $type, $data);
    }
    
    /**
     * Envoie une notification système (admin)
     */
    public function sendSystemAlert(string $title, string $message, string $type = 'info'): void
    {
        // TODO: Implémenter la logique pour envoyer aux admins
        // Pour l'instant on ne fait rien ou on log
        error_log("SYSTEM ALERT: $title - $message");
    }
}
