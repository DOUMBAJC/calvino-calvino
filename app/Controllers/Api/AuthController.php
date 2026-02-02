<?php

namespace App\Controllers\Api;

use Calvino\Core\Controller;
use App\Models\User;
use App\Models\UserSession;
use Calvino\Services\AuditService;
use Calvino\Services\NotificationService;
use Calvino\Services\GeoLocationService;
use Calvino\Core\Request;
/**
 * AuthController
 * Gère l'authentification API
 */
class AuthController extends Controller
{
    /**
     * Service de notification
     *
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Constructeur
     */
    public function __construct()
    {
        parent::__construct();
        $this->notificationService = new NotificationService();
    }

    /**
     * Connecte un utilisateur et génère un token JWT
     *
     * @return array
     */
    public function login(): array
    {
        try {
            $email = request('email');
            $password = request('password');

            // Validation des données
            if (!$email || !$password) {
                return [
                    'success' => false,
                    'message' => trans('api.validation_failed'),
                    'status' => 422
                ];
            }

            // Vérification des identifiants
            $user = User::findByEmail($email);
            
            if (!$user || !$user->verifyPassword($password)) {
                return [
                    'success' => false,
                    'message' => trans('api.invalid_credentials'),
                    'status' => 401
                ];
            }

            if($user->isBlocked()) {
                return [
                    'success' => false,
                    'message' => trans('api.account_blocked'),
                    'status' => 403
                    ];
                }

            // Vérifier le nombre de sessions actives de l'utilisateur
            $activeSessions = $user->getActiveSessions();
            $maxSessions = 3; // Limite maximale de sessions par utilisateur
            
            // Si l'utilisateur a déjà atteint la limite de sessions, désactiver la plus ancienne
            if (count($activeSessions) >= $maxSessions) {
                // Trier les sessions par date d'activité (la plus ancienne en premier)
                usort($activeSessions, function($a, $b) {
                    return strtotime($a->last_activity) - strtotime($b->last_activity);
                });
                
                // Désactiver la session la plus ancienne
                $oldestSession = $activeSessions[0];
                $oldestSession->deactivate();
                
                // Journal d'activité - Session automatiquement déconnectée
                AuditService::logAction($user->id, 'auto_logout_session', 'auth', 
                    "Session {$oldestSession->session_id} automatiquement déconnectée (limite de {$maxSessions} sessions atteinte)");
                
                // Notification de déconnexion automatique
                $this->notificationService->send(
                    $user->id,
                    'Session déconnectée automatiquement',
                    "Une de vos sessions a été déconnectée automatiquement car vous avez atteint la limite de {$maxSessions} sessions simultanées.",
                    'warning'
                );
            }

            // Générer un ID de session unique
            $sessionId = bin2hex(random_bytes(16));
                
            // Génération du token JWT avec l'ID de session
            $token = $user->createToken($sessionId);
            $refreshToken = $user->createRefreshToken($sessionId);
            
            // Obtenir l'adresse IP et la localisation
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? request()->ip();
            $location = GeoLocationService::getFormattedLocation($ipAddress);
            
            // Enregistrer la session avec l'ID de session généré et la localisation
            UserSession::createSession(
                $user->id, 
                $token, 
                $refreshToken, 
                $sessionId,
                [
                    'ip_address' => $ipAddress,
                    'location' => $location ?: 'Inconnue',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]
            );
            
            // Journal d'activité - Connexion
            AuditService::logLogin($user->id, $user->email);
            
            // Notification de connexion
            $this->notificationService->send(
                $user->id,
                'Nouvelle connexion',
                "Vous venez de vous connecter à votre compte. Si ce n'était pas vous, veuillez sécuriser votre compte immédiatement.",
                'info'
            );

            // Vérifier si l'utilisateur utilise un mot de passe par défaut
            $isDefaultPassword = User::isDefaultPassword($password);
            
            // Si c'est un mot de passe par défaut, envoyer une notification
            if ($isDefaultPassword) {
                $this->notificationService->send(
                    $user->id,
                    'Sécurité du compte',
                    "Vous utilisez un mot de passe par défaut. Pour votre sécurité, veuillez le changer dès que possible.",
                    'warning'
                );
            }

            // Réponse avec message traduit et variables remplacées
            return [
                'success' => true,
                'message' => trans('api.login_success'),
                'welcome' => trans('api.welcome_user', [
                    'name' => $user->name,
                    'role' => $user->role
                ]),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ],
                'token' => $token,
                'refresh_token' => $refreshToken,
                'session_id' => $sessionId,
                'token_type' => 'Bearer',
                'status' => 200,
                'default_password' => $isDefaultPassword
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la connexion',
                'error' => $e->getMessage(),
                'status' => 500
            ];
        }
    }
    
    
    /**
     * Récupère le profil de l'utilisateur connecté
     *
     * @return array
     */
    public function profile(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return $this->error('Non authentifié', 401);
        }
        
        return [
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'address' => $user->address,
                'created_at' => $user->created_at
            ],
            'status' => 200
        ];
    }
    
    /**
     * Déconnecte l'utilisateur
     *
     * @return array
     */
    public function logout(): array
    {
        $user = auth()->user();
        $session = auth()->currentSession();
        
        if ($user) {
            // Journal d'activité - Déconnexion
            AuditService::logLogout($user->id, $user->email);
            
            // Notification de déconnexion
            $this->notificationService->send(
                $user->id,
                'Déconnexion',
                "Vous avez été déconnecté avec succès.",
                'info'
            );
            
            // Désactiver la session courante
            if ($session) {
                $session->deactivate();
            }
            
            // Nettoyer la session PHP
            if (isset($_SESSION['user'])) {
                unset($_SESSION['user']);
            }
        }

        return [
            'success' => true,
            'message' => trans('api.logout_success'),
            'status' => 200
        ];
    }
    
    /**
     * Met à jour le profil de l'utilisateur connecté
     * 
     * @return array
     */
    public function updateProfile(): array
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return $this->error('Non authentifié', 401);
            }
            
            // Valider les données
            $errors = $this->validate([
                'name' => 'min:3',
                'email' => 'email',
                'phone' => 'min:9',
                'address' => 'min:5'
            ]);
            
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => trans('api.validation_failed'),
                    'errors' => $errors,
                    'status' => 422
                ];
            }
            
            $name = request('name');
            $email = request('email');
            $phone = request('phone');
            $address = request('address');
            
            // Vérifier si l'email existe déjà pour un autre utilisateur
            if ($email && $email !== $user->email) {
                $existingUser = User::findByEmail($email);
                
                if ($existingUser && $existingUser->id !== $user->id) {
                    return [
                        'success' => false,
                        'message' => trans('api.email_already_used'),
                        'status' => 422
                    ];
                }
            }
            
            // Mettre à jour les champs si fournis
            if ($name) $user->name = $name;
            if ($phone) $user->phone = $phone;
            if ($address) $user->address = $address;
            
            $user->save();
            
            return [
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'created_at' => $user->created_at
                ],
                'status' => 200
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => $e->getMessage(),
                'status' => 500
            ];
        }
    }
    
    /**
     * Change le mot de passe de l'utilisateur connecté
     * 
     * @return array
     */
    public function changePassword(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return $this->error('Non authentifié', 401);
        }
        
        // Valider les données
        $errors = $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8'
        ]);
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $errors,
                'status' => 422
            ];
        }
        
        $currentPassword = request('current_password');
        $newPassword = request('new_password');
        
        // Vérifier si le mot de passe actuel est correct
        if (!$user->verifyPassword($currentPassword)) {
            return $this->error('Mot de passe actuel incorrect', 422);
        }
        
        // Mettre à jour le mot de passe
        $user->password = User::hashPassword($newPassword);
        $user->save();
        
        // Notification de changement de mot de passe
        $this->notificationService->send(
            $user->id,
            'Mot de passe modifié',
            "Votre mot de passe a été modifié avec succès. Si vous n'êtes pas à l'origine de cette action, veuillez contacter l'administrateur.",
            'success'
        );
        
        return [
            'success' => true,
            'message' => 'Mot de passe modifié avec succès',
            'status' => 200
        ];
    }
    
    /**
     * Force le changement du mot de passe par défaut
     * 
     * @return array
     */
    public function forceChangeDefaultPassword(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return $this->error('Non authentifié', 401);
        }
        
        // Valider les données
        $errors = $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8'
        ]);
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $errors,
                'status' => 422
            ];
        }
        
        $currentPassword = request('current_password');
        $newPassword = request('new_password');
        
        // Vérifier si le mot de passe actuel est correct
        if (!$user->verifyPassword($currentPassword)) {
            return $this->error('Mot de passe actuel incorrect', 422);
        }
        
        // Vérifier si le mot de passe actuel est un mot de passe par défaut
        if (!User::isDefaultPassword($currentPassword)) {
            return $this->error('Cette opération est uniquement pour changer un mot de passe par défaut', 422);
        }
        
        // Vérifier que le nouveau mot de passe n'est pas un mot de passe par défaut
        if (User::isDefaultPassword($newPassword)) {
            return $this->error('Le nouveau mot de passe ne peut pas être un mot de passe par défaut', 422);
        }
        
        // Mettre à jour le mot de passe
        $user->password = User::hashPassword($newPassword);
        $user->save();
        
        // Journal d'activité - Changement de mot de passe par défaut
        AuditService::logAction($user->id, 'default_password_changed', 'auth', "Mot de passe par défaut changé pour {$user->email}");
        
        // Notification de changement de mot de passe par défaut
        $this->notificationService->send(
            $user->id,
            'Mot de passe par défaut modifié',
            "Votre mot de passe par défaut a été changé avec succès. Votre compte est maintenant plus sécurisé.",
            'success'
        );
        
        return [
            'success' => true,
            'message' => 'Mot de passe par défaut modifié avec succès',
            'status' => 200
        ];
    }

    /**
     * Obtient les informations de l'utilisateur
     * 
     * @return array
     */
    public function me(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => trans('api.unauthorized'),
                'status' => 401
            ];
        }

        return [
            'success' => true,
            'user' => $user,
            'status' => 200
        ];
    }

    /**
     * Renouvelle le token JWT d'un utilisateur
     *
     * @return array
     */
    public function refreshToken(): array
    {
        $refreshToken = request('refresh_token');
        
        if (!$refreshToken) {
            return [
                'success' => false,
                'message' => 'Refresh token non fourni',
                'status' => 401
            ];
        }
        
        // Décoder le token pour obtenir l'ID utilisateur
        $parts = explode('.', $refreshToken);
        if (count($parts) !== 3) {
            return [
                'success' => false,
                'message' => 'Format de token invalide',
                'status' => 401
            ];
        }
        
        try {
            $payload = json_decode(base64_decode($parts[1]), true);
            
            if (!isset($payload['sub']) || !isset($payload['type']) || $payload['type'] !== 'refresh') {
                return [
                    'success' => false,
                    'message' => 'Token invalide ou non reconnu comme refresh token',
                    'status' => 401
                ];
            }
            
            // Vérifier si le token a expiré
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return [
                    'success' => false,
                    'message' => 'Refresh token expiré',
                    'status' => 401
                ];
            }
            
            $userId = $payload['sub'];
            $user = User::find($userId);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Utilisateur non trouvé',
                    'status' => 401
                ];
            }
            
            // Récupérer l'ID de session s'il existe
            $sessionId = $payload['sid'] ?? null;
            
            // Si un ID de session est fourni, vérifier que la session existe et est active
            if ($sessionId) {
                $session = UserSession::findBySessionId($sessionId);
                
                if (!$session || !$session->is_active) {
                    return [
                        'success' => false,
                        'message' => trans('api.session.invalid'),
                        'status' => 401
                    ];
                }
                
                // Mettre à jour la session avec les nouveaux tokens
                $newToken = $user->createToken($sessionId);
                $newRefreshToken = $user->createRefreshToken($sessionId);
                
                // Mettre à jour la localisation si l'IP a changé
                $currentIp = $_SERVER['REMOTE_ADDR'] ?? request()->ip();
                if ($currentIp !== $session->ip_address) {
                    $session->ip_address = $currentIp;
                    $session->location = GeoLocationService::getFormattedLocation($currentIp) ?: 'Inconnue';
                }
                
                $session->token = $newToken;
                $session->refresh_token = $newRefreshToken;
                $session->last_activity = date('Y-m-d H:i:s');
                $session->save();
            } else {
                // Créer une nouvelle session
                $sessionId = bin2hex(random_bytes(16));
                $newToken = $user->createToken($sessionId);
                $newRefreshToken = $user->createRefreshToken($sessionId);
                
                // Obtenir l'adresse IP et la localisation
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? request()->ip();
                $location = GeoLocationService::getFormattedLocation($ipAddress);
                
                UserSession::createSession(
                    $user->id, 
                    $newToken, 
                    $newRefreshToken, 
                    $sessionId,
                    [
                        'ip_address' => $ipAddress,
                        'location' => $location ?: 'Inconnue',
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                    ]
                );
            }
            
            // Journal d'activité - Refresh Token
            AuditService::logAction($user->id, 'refresh_token', 'auth', "Token renouvelé pour {$user->email}");
            
            return [
                'success' => true,
                'message' => 'Token renouvelé avec succès',
                'token' => $newToken,
                'refresh_token' => $newRefreshToken,
                'session_id' => $sessionId,
                'token_type' => 'Bearer',
                'status' => 200
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du renouvellement du token: ' . $e->getMessage(),
                'status' => 500
            ];
        }
    }
    
    /**
     * Récupère toutes les sessions actives de l'utilisateur
     *
     * @return array
     */
    public function sessions(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => trans('api.unauthorized'),
                'status' => 401
            ];
        }
        
        $currentSession = auth()->currentSession();
        $currentSessionId = $currentSession ? $currentSession->session_id : null;
        
        $sessions = $user->getActiveSessions();
        $formattedSessions = [];
        
        foreach ($sessions as $session) {
            // Obtenir la localisation à partir de l'adresse IP
            $location = 'Inconnue';
            if ($session->ip_address) {
                $geoLocation = GeoLocationService::getFormattedLocation($session->ip_address);
                if ($geoLocation) {
                    $location = $geoLocation;
                }
            }

            $formattedSessions[] = [
                'id' => $session->id,
                'session_id' => $session->session_id,
                'device_name' => $session->device_name,
                'user_agent' => $session->user_agent,
                'device_type' => $session->device_type,
                'ip_address' => $session->ip_address,
                'location' => $location,
                'last_activity' => $session->last_activity,
                'is_current' => ($session->session_id === $currentSessionId)
            ];
        }
        
        return [
            'success' => true,
            'message' => trans('api.session.list_success'),
            'sessions' => $formattedSessions,
            'current_session_id' => $currentSessionId,
            'status' => 200
        ];
    }
    
    /**
     * Déconnecte une session spécifique
     *
     * @param string|null $sessionId ID de la session à déconnecter
     * @return array
     */
    public function logoutSession(Request $request, ?string $sessionId = null): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => trans('api.unauthorized'),
                'status' => 401
            ];
        }
        
        // Si aucun ID n'est fourni en paramètre, essayer de le récupérer depuis la requête
        $sessionId = $sessionId ?? request('session_id');
        
        if (!$sessionId) {
            return [
                'success' => false,
                'message' => 'ID de session non fourni',
                'status' => 422
            ];
        }
        
        $currentSession = auth()->currentSession();
        $currentSessionId = $currentSession ? $currentSession->session_id : null;
        
        // Vérifier si l'utilisateur essaie de déconnecter sa session actuelle
        if ($sessionId === $currentSessionId) {
            return $this->logout();
        }
        
        // Trouver la session
        $session = UserSession::findBySessionId($sessionId);
        
        if (!$session || $session->user_id !== $user->id) {
            return [
                'success' => false,
                'message' => trans('api.session.not_found'),
                'status' => 404
            ];
        }
        
        // Désactiver la session
        $session->deactivate();
        
        // Journal d'activité
        AuditService::logAction($user->id, 'logout_session', 'auth', "Session {$sessionId} déconnectée");
        
        // Notification de déconnexion de session
        $this->notificationService->send(
            $user->id,
            'Session déconnectée',
            "Une de vos sessions a été déconnectée. Si ce n'était pas vous, veuillez vérifier la sécurité de votre compte.",
            'info'
        );
        
        return [
            'success' => true,
            'message' => trans('api.session.logout_success'),
            'status' => 200
        ];
    }
    
    /**
     * Déconnecte toutes les autres sessions de l'utilisateur
     *
     * @return array
     */
    public function logoutOtherSessions(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => trans('api.unauthorized'),
                'status' => 401
            ];
        }
        
        $currentSession = auth()->currentSession();
        
        if (!$currentSession) {
            return [
                'success' => false,
                'message' => 'Session courante non trouvée',
                'status' => 500
            ];
        }
        
        $count = $user->logoutOtherSessions($currentSession->session_id);
        
        // Journal d'activité
        AuditService::logAction($user->id, 'logout_other_sessions', 'auth', "{$count} autres sessions déconnectées");
        
        // Notification de déconnexion de toutes les autres sessions
        if ($count > 0) {
            $this->notificationService->send(
                $user->id,
                'Sessions déconnectées',
                "Toutes vos autres sessions ({$count}) ont été déconnectées. Seule votre session actuelle reste active.",
                'warning'
            );
        }
        
        return [
            'success' => true,
            'message' => trans('api.session.logout_others_success'),
            'count' => $count,
            'status' => 200
        ];
    }
} 