<?php

namespace Calvino\Core;

use Calvino\Models\User;
use Calvino\Models\UserSession;
use PDO;
use Calvino\Providers\DatabaseServiceProvider;

/**
 * Classe Auth
 * Gère l'authentification des utilisateurs
 */
class Auth
{
    /**
     * Utilisateur actuellement authentifié
     *
     * @var User|null
     */
    private ?User $user = null;
    
    /**
     * Session courante
     *
     * @var UserSession|null
     */
    private ?UserSession $currentSession = null;
    
    /**
     * Vérifie si un utilisateur est authentifié
     *
     * @return bool
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }
    
    /**
     * Retourne l'utilisateur authentifié
     *
     * @return User|null
     */
    public function user(): ?User
    {
        if ($this->user !== null) {
            return $this->user;
        }
        
        $token = $this->getBearerToken();
        
        if (!$token) {
            return null;
        }
        
        $payload = $this->decodeToken($token);
        
        if (!$payload || !isset($payload['sub'])) {
            return null;
        }
        
        $this->user = User::find($payload['sub']);
        
        // Si le token contient un ID de session, vérifier que la session est active
        if ($this->user && isset($payload['sid'])) {
            $sessionId = $payload['sid'];
            $session = UserSession::findBySessionId($sessionId);
            
            if (!$session || !$session->is_active) {
                // Session inactive ou inexistante
                $this->user = null;
                return null;
            }
            
            // Mettre à jour la dernière activité de la session
            $session->updateActivity();
            $this->currentSession = $session;
        }
        
        return $this->user;
    }
    
    /**
     * Retourne la session courante
     *
     * @return UserSession|null
     */
    public function currentSession(): ?UserSession
    {
        if ($this->currentSession !== null) {
            return $this->currentSession;
        }
        
        // Si l'utilisateur est défini mais pas la session, essayer de récupérer l'ID de session depuis le token
        if ($this->user !== null) {
            $token = $this->getBearerToken();
            
            if ($token) {
                $payload = $this->decodeToken($token);
                
                if ($payload && isset($payload['sid'])) {
                    $this->currentSession = UserSession::findBySessionId($payload['sid']);
                }
            }
        }
        
        return $this->currentSession;
    }
    
    /**
     * Décode un token JWT
     *
     * @param string $token
     * @return array|null
     */
    private function decodeToken(string $token): ?array
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            error_log("JWT: Format invalide - nombre de parties incorrect");
            return null;
        }
        
        list($header, $payload, $signature) = $parts;
        
        // Décoder le payload avec base64url
        $decodedPayload = json_decode($this->base64UrlDecode($payload), true);
        
        if (!$decodedPayload) {
            error_log("JWT: Payload invalide - impossible de décoder JSON");
            return null;
        }
        
        // Vérifier si le token a expiré
        if (isset($decodedPayload['exp']) && time() > $decodedPayload['exp']) {
            error_log("JWT: Token expiré");
            return null;
        }
        
        // Vérifier la signature du token
        $secret = env('JWT_SECRET', 'default_secret');
        $expectedSignature = $this->base64UrlEncode(hash_hmac('sha256', "$header.$payload", $secret, true));
        
        if (!hash_equals($expectedSignature, $signature)) {
            error_log("JWT: Signature invalide");
            return null;
        }
        
        return $decodedPayload;
    }
    
    /**
     * Encode en base64url
     * 
     * @param string $data
     * @return string
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Décode une chaîne base64url
     * 
     * @param string $data
     * @return string
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
    
    /**
     * Récupère le token Bearer de l'en-tête Authorization
     *
     * @return string|null
     */
    private function getBearerToken(): ?string
    {
        // Vérifier plusieurs sources possibles pour l'en-tête Authorization
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        // Si pas dans $_SERVER, vérifier les en-têtes apache
        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $authHeader = $headers['Authorization'] ?? '';
        }
        
        // Si toujours vide, vérifier les en-têtes manuellement
        if (empty($authHeader)) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        }
        
        // Log pour debug
        error_log("Auth Header: " . substr($authHeader, 0, 20) . (strlen($authHeader) > 20 ? '...' : ''));
        
        if (empty($authHeader)) {
            error_log("JWT: Aucun en-tête d'autorisation trouvé");
            return null;
        }
        
        // Match "Bearer token" et variations avec espaces ou majuscules
        if (preg_match('/[Bb]earer\s+(.+)/', $authHeader, $matches)) {
            $token = trim($matches[1]);
            error_log("JWT: Token extrait, longueur: " . strlen($token));
            return $token;
        }
        
        // Si l'en-tête ne contient que le token sans "Bearer"
        if (strpos($authHeader, '.') !== false && substr_count($authHeader, '.') === 2) {
            error_log("JWT: Token sans préfixe 'Bearer', utilisation directe");
            return trim($authHeader);
        }
        
        error_log("JWT: Format d'en-tête d'autorisation non reconnu");
        return null;
    }
    
    /**
     * Authentifie un utilisateur et retourne un token
     *
     * @param string $email
     * @param string $password
     * @return array
     */
    public function attempt(string $email, string $password): array
    {
        $user = $this->findUserByEmail($email);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => trans('api.invalid_credentials')
            ];
        }
        
        if (!$user->verifyPassword($password)) {
            return [
                'success' => false,
                'message' => trans('api.invalid_credentials')
            ];
        }
        
        $this->user = $user;
        
        // Créer une nouvelle session
        $sessionId = bin2hex(random_bytes(16));
        $token = $user->createToken($sessionId);
        $refreshToken = $user->createRefreshToken($sessionId);
        
        // Enregistrer la session
        $session = UserSession::createSession($user->id, $token, $refreshToken, $sessionId);
        $this->currentSession = $session;
        
        return [
            'success' => true,
            'token' => $token,
            'refresh_token' => $refreshToken,
            'session_id' => $sessionId,
            'user' => $user
        ];
    }
    
    /**
     * Trouve un utilisateur par son email
     *
     * @param string $email
     * @return User|null
     */
    private function findUserByEmail(string $email): ?User
    {
        // Commençons par chercher l'utilisateur par son email
        $dbProvider = new DatabaseServiceProvider(app());
        $pdo = $dbProvider->getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$userData) {
            return null;
        }
        
        return new User($userData);
    }
} 