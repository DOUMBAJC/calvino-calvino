<?php

namespace App\Models;

use Calvino\Core\Model;
use Calvino\Auth\Authenticatable;
use Calvino\Traits\Notifiable;
// use Calvino\Traits\Auditable;

/**
 * Modèle User
 * Représente un utilisateur du système
 */
class User extends Model
{
    use Authenticatable, Notifiable;
    // use Auditable;

    /**
     * Table associée au modèle
     */
    protected string $table = 'users';
    
    /**
     * Champs remplissables
     */
    protected array $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'is_active'
    ];
    
    /**
     * Récupère les sessions de l'utilisateur
     */
    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }
    
    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
    
    /**
     * Trouve un utilisateur par son email
     */
    public static function findByEmail(string $email)
    {
        return self::where('email', $email)->first();
    }
    
    /**
     * Trouve tous les utilisateurs avec le rôle d'administrateur
     */
    public function findAdmins(): array
    {
        return self::where('role', 'admin')->where('is_active', 1)->get();
    }

    /**
     * Spécifie les données à sérialiser en JSON
     */
    public function jsonSerialize(): array
    {
        $attributes = $this->attributes;
        if (isset($attributes['password'])) {
            unset($attributes['password']);
        }
        return $attributes;
    }

    /**
     * Vérifie si l'utilisateur est actif
     */
    public function isBlocked(): bool
    {
        return !boolval($this->is_active);
    }
    
    /**
     * Récupère toutes les sessions actives de l'utilisateur
     */
    public function getActiveSessions(): array
    {
        return UserSession::getActiveSessions($this->id);
    }
    
    /**
     * Déconnecte toutes les sessions de l'utilisateur sauf la session courante
     */
    public function logoutOtherSessions(string $currentSessionId): int
    {
        $sessionModel = new UserSession();
        $table = $sessionModel->getTable();
        $pdo = self::getPdo();
        
        $stmt = $pdo->prepare("UPDATE {$table} SET is_active = 0 WHERE user_id = :user_id AND session_id != :session_id AND is_active = 1");
        $stmt->bindParam(':user_id', $this->id);
        $stmt->bindParam(':session_id', $currentSessionId);
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}
