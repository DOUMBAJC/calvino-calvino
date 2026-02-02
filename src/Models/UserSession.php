<?php

namespace Calvino\Models;

use Calvino\Core\Model;
use Calvino\Services\GeoLocationService;

/**
 * Modèle UserSession
 * Représente une session de connexion d'un utilisateur
 */
class UserSession extends Model
{
    /**
     * Table associée au modèle
     *
     * @var string
     */
    protected string $table = 'user_sessions';
    
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
        'user_id',
        'session_id',
        'token',
        'refresh_token',
        'ip_address',
        'user_agent',
        'device_name',
        'device_type',
        'location',
        'last_activity',
        'is_active'
    ];
    
    /**
     * Relation avec l'utilisateur
     *
     * @return User|null
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Crée une nouvelle session pour un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @param string $token Token JWT
     * @param string $refreshToken Token de rafraîchissement
     * @param string|null $sessionId ID de session existant (si null, un nouvel ID sera généré)
     * @param array $additionalData Données supplémentaires (ip_address, user_agent, location)
     * @return self
     */
    public static function createSession(
        int $userId, 
        string $token, 
        string $refreshToken, 
        ?string $sessionId = null,
        array $additionalData = []
    ): self {
        // Utiliser l'ID de session fourni ou en générer un nouveau
        $sessionId = $sessionId ?: self::generateSessionId();
        
        // Récupérer les informations sur l'appareil et la localisation
        $ipAddress = $additionalData['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $additionalData['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        // Déterminer le type d'appareil et son nom à partir du user agent
        $deviceInfo = self::parseUserAgent($userAgent);
        
        // Utiliser la localisation fournie ou obtenir les informations de localisation à partir de l'adresse IP
        $location = $additionalData['location'] ?? null;
        if (!$location && $ipAddress) {
            $location = GeoLocationService::getFormattedLocation($ipAddress);
        }
        
        return self::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'token' => $token,
            'refresh_token' => $refreshToken,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_name' => $deviceInfo['device_name'],
            'device_type' => $deviceInfo['device_type'],
            'location' => $location ?: 'Inconnue',
            'last_activity' => date('Y-m-d H:i:s'),
            'is_active' => 1
        ]);
    }
    
    /**
     * Met à jour l'activité de la session
     *
     * @return bool
     */
    public function updateActivity(): bool
    {
        $this->last_activity = date('Y-m-d H:i:s');
        return $this->save();
    }
    
    /**
     * Désactive la session
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        $this->is_active = 0;
        return $this->save();
    }
    
    /**
     * Génère un identifiant de session unique
     *
     * @return string
     */
    private static function generateSessionId(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Exception $e) {
            // Fallback pour les environnements sans random_bytes
            return bin2hex(openssl_random_pseudo_bytes(16));
        }
    }
    
    /**
     * Analyse le user agent pour déterminer le type d'appareil et son nom
     *
     * @param string|null $userAgent
     * @return array
     */
    private static function parseUserAgent(?string $userAgent): array
    {
        $deviceName = 'Unknown';
        $deviceType = 'unknown';
        
        if ($userAgent) {
            // Détection simple du type d'appareil
            if (strpos($userAgent, 'Mobile') !== false || strpos($userAgent, 'Android') !== false) {
                $deviceType = 'mobile';
                
                // Détection du modèle de téléphone
                if (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
                    $deviceName = 'iOS Device';
                } elseif (preg_match('/Android/i', $userAgent)) {
                    $deviceName = 'Android Device';
                }
            } elseif (strpos($userAgent, 'Tablet') !== false || strpos($userAgent, 'iPad') !== false) {
                $deviceType = 'tablet';
                $deviceName = 'Tablet';
            } else {
                $deviceType = 'desktop';
                
                // Détection du système d'exploitation
                if (strpos($userAgent, 'Windows') !== false) {
                    $deviceName = 'Windows PC';
                } elseif (strpos($userAgent, 'Macintosh') !== false) {
                    $deviceName = 'Mac';
                } elseif (strpos($userAgent, 'Linux') !== false) {
                    $deviceName = 'Linux';
                }
            }
        }
        
        return [
            'device_name' => $deviceName,
            'device_type' => $deviceType
        ];
    }
    
    /**
     * Trouve une session par son ID de session
     *
     * @param string $sessionId
     * @return self|null
     */
    public static function findBySessionId(string $sessionId): ?self
    {
        $pdo = self::getPdo();
        $table = (new self())->getTable();
        
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE session_id = :session_id LIMIT 1");
        $stmt->bindParam(':session_id', $sessionId);
        $stmt->execute();
        
        $record = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$record) {
            return null;
        }
        
        return new self($record);
    }
    
    /**
     * Récupère toutes les sessions actives d'un utilisateur
     *
     * @param int $userId
     * @return array
     */
    public static function getActiveSessions(int $userId): array
    {
        $pdo = self::getPdo();
        $table = (new self())->getTable();
        
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE user_id = :user_id AND is_active = 1 ORDER BY last_activity DESC");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $sessions = [];
        
        foreach ($records as $record) {
            $sessions[] = new self($record);
        }
        
        return $sessions;
    }
}
