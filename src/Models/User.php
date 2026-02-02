<?php

namespace Calvino\Models;

use Calvino\Core\Model;

/**
 * Modèle User
 * Représente un utilisateur du système
 */
class User extends Model
{
    /**
     * Table associée au modèle
     *
     * @var string
     */
    protected string $table = 'users';
    
    /**
     * Clé primaire
     *
     * @var string
     */
    protected string $primaryKey = 'id';
    
    /**
     * Champs remplissables
     *
     * @var array
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
     * Relations
     */
    
    /**
     * Récupère les sessions de l'utilisateur
     *
     * @return array
     */
    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }
    
    /**
     * Vérifie si le mot de passe est correct
     *
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
    
    /**
     * Hash le mot de passe avant de l'enregistrer
     *
     * @param string $password
     * @return string
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Crée un token JWT avec un identifiant de session
     *
     * @param string|null $sessionId Identifiant de session
     * @return string
     */
    public function createToken(?string $sessionId = null): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $header = $this->base64UrlEncode($header);
        
        $payload = json_encode([
            'sub' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'sid' => $sessionId, // Identifiant de session
            'iat' => time(),
            'exp' => time() + 60 * 60 * 24 // 24 heures
        ]);
        $payload = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', "$header.$payload", env('JWT_SECRET', 'default_secret'), true);
        $signature = $this->base64UrlEncode($signature);
        
        return "$header.$payload.$signature";
    }
    
    /**
     * Crée un refresh token JWT avec une durée de validité plus longue
     *
     * @param string|null $sessionId Identifiant de session
     * @return string
     */
    public function createRefreshToken(?string $sessionId = null): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $header = $this->base64UrlEncode($header);
        
        $payload = json_encode([
            'sub' => $this->id,
            'type' => 'refresh',
            'sid' => $sessionId, // Identifiant de session
            'iat' => time(),
            'exp' => time() + 60 * 60 * 24  // 24 heures
        ]);
        $payload = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', "$header.$payload", env('JWT_SECRET', 'default_secret') . '_refresh', true);
        $signature = $this->base64UrlEncode($signature);
        
        return "$header.$payload.$signature";
    }
    
    /**
     * Encode en base64url compatible avec les standards JWT
     * 
     * @param string $data
     * @return string
     */
    private function base64UrlEncode($data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
    
    /**
     * Trouve un utilisateur par son email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail(string $email)
    {
        $pdo = self::getPdo();
        $table = (new static())->getTable();
        
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $record = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$record) {
            return null;
        }
        
        return new static($record);
    }
    
    /**
     * Trouve tous les utilisateurs avec le rôle d'administrateur
     *
     * @return array
     */
    public function findAdmins(): array
    {
        $pdo = self::getPdo();
        $table = $this->getTable();
        
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE role = 'admin' AND is_active = 1");
        $stmt->execute();
        
        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $admins = [];
        foreach ($records as $record) {
            $admins[] = new User($record);
        }
        
        return $admins;
    }

    /**
     * Spécifie les données à sérialiser en JSON en excluant les données sensibles
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $attributes = $this->attributes;
        
        // Supprimer le mot de passe des données sérialisées
        if (isset($attributes['password'])) {
            unset($attributes['password']);
        }
        
        return $attributes;
    }

    /**
     * Vérifie si l'utilisateur est actif
     *
     * @return bool
     */
    public function isBlocked(): bool
    {
        return !boolval($this->is_active);
    }
    
    /**
     * Vérifie si le mot de passe fourni est un mot de passe par défaut
     *
     * @param string $password
     * @return bool
     */
    public static function isDefaultPassword(string $password): bool
    {
        // Simple heuristic - can be improved
        return strpos($password, 'PHS') === 0 && strlen($password) === 7;
    }
    
    /**
     * Récupère toutes les sessions actives de l'utilisateur
     *
     * @return array
     */
    public function getActiveSessions(): array
    {
        return UserSession::getActiveSessions($this->id);
    }
    
    /**
     * Déconnecte toutes les sessions de l'utilisateur sauf la session courante
     *
     * @param string $currentSessionId
     * @return int Nombre de sessions déconnectées
     */
    public function logoutOtherSessions(string $currentSessionId): int
    {
        $pdo = self::getPdo();
        $table = (new UserSession())->getTable();
        
        // Récupérer l'ID utilisateur directement depuis les attributs
        $userId = $this->attributes[$this->primaryKey];
        
        $stmt = $pdo->prepare("UPDATE {$table} SET is_active = 0 WHERE user_id = :user_id AND session_id != :session_id AND is_active = 1");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':session_id', $currentSessionId);
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}
