<?php

namespace Calvino\Models;

use Calvino\Core\Model;

class Notification extends Model
{
    protected string $table = 'notifications';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_read',
        'data',
        'created_at'
    ];

    /**
     * Constructeur
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Récupérer les notifications pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param bool $unreadOnly Si vrai, ne récupère que les notifications non lues
     * @param int $limit Nombre max de notifications à récupérer
     * @return array Liste des notifications
     */
    public function getForUser($userId, $unreadOnly = false, $limit = 50): array
    {
        $pdo = self::getPdo();
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ";
        
        if ($unreadOnly) {
            $sql .= "AND is_read = 0 ";
        }
        
        $sql .= "ORDER BY created_at DESC LIMIT :limit";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Marquer une notification comme lue
     * 
     * @param int $id ID de la notification
     * @param int $userId ID de l'utilisateur (pour vérification)
     * @return bool Succès ou échec
     */
    public function markAsRead($id, $userId): bool
    {
        $pdo = self::getPdo();
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE id = :id AND user_id = :user_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Marquer toutes les notifications d'un utilisateur comme lues
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool Succès ou échec
     */
    public function markAllAsRead($userId): bool
    {
        $pdo = self::getPdo();
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE user_id = :user_id AND is_read = 0";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Créer une nouvelle notification
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $title Titre de la notification
     * @param string $message Message de la notification
     * @param string $type Type de notification (info, warning, error, success)
     * @param mixed $data Données supplémentaires (optionnel)
     * @return int|bool ID de la notification créée ou false en cas d'échec
     */
    public function createNotification($userId, $title, $message, $type = 'info', $data = null)
    {
        $notification = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => 0,
            'data' => $data ? json_encode($data) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $model = new static($notification);
        $success = $model->save();
        
        return $success ? $model->id : false;
    }

    /**
     * Supprimer une notification
     * 
     * @param int $id ID de la notification
     * @param int $userId ID de l'utilisateur (pour vérification)
     * @return bool Succès ou échec
     */
    public function deleteNotification($id, $userId): bool
    {
        $pdo = self::getPdo();
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Supprimer toutes les notifications d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool Succès ou échec
     */
    public function deleteAllNotifications($userId): bool
    {
        $pdo = self::getPdo();
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Compter les notifications non lues d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return int Nombre de notifications non lues
     */
    public function countUnread($userId): int
    {
        $pdo = self::getPdo();
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id AND is_read = 0";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return isset($result['count']) ? (int)$result['count'] : 0;
    }
}
