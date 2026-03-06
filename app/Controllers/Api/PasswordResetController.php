<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use Calvino\Core\Controller;
use App\Models\User;
use Calvino\Services\NotificationService;
use Calvino\Services\AuditService;

/**
 * PasswordResetController
 * Gère la réinitialisation de mot de passe via token.
 *
 * Flux :
 *   1. POST /auth/forgot-password  → génère un token et le notifie à l'utilisateur
 *   2. POST /auth/reset-password   → valide le token et met à jour le mot de passe
 */
class PasswordResetController extends Controller
{
    /**
     * Durée de validité du token en secondes (1 heure)
     */
    private const TOKEN_EXPIRY = 3600;

    protected NotificationService $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->notificationService = new NotificationService();
    }

    /**
     * Génère un token de réinitialisation et l'envoie à l'utilisateur.
     *
     * POST /auth/forgot-password
     * Body : { "email": "user@example.com" }
     */
    public function forgotPassword(): array
    {
        $errors = $this->validate([
            'email' => 'required|email',
        ]);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => trans('api.validation_failed'),
                'errors'  => $errors,
                'status'  => 422,
            ];
        }

        $email = request('email');
        $user  = User::findByEmail($email);

        /*
         * Réponse volontairement identique qu'un compte existe ou non
         * pour éviter l'énumération d'emails.
         */
        if (!$user) {
            return [
                'success' => true,
                'message' => trans('api.password_reset.sent'),
                'status'  => 200,
            ];
        }

        if ($user->isBlocked()) {
            return [
                'success' => false,
                'message' => trans('api.account_blocked'),
                'status'  => 403,
            ];
        }

        // Invalider les anciens tokens de cet utilisateur
        $this->deleteExistingTokens($user->id);

        // Générer un token sécurisé
        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_EXPIRY);

        $this->storeToken($user->id, $token, $expiresAt);

        // Notifier l'utilisateur (notification interne + email si configuré)
        $resetLink = rtrim(env('APP_URL', 'http://localhost:8000'), '/') . '/reset-password?token=' . $token;

        $this->notificationService->send(
            $user->id,
            trans('api.password_reset.notification_title'),
            trans('api.password_reset.notification_body') . "\n\nLien : " . $resetLink . "\n(valable " . (self::TOKEN_EXPIRY / 60) . " minutes)",
            'warning'
        );

        AuditService::logAction(
            $user->id,
            'password_reset_requested',
            'auth',
            "Demande de réinitialisation de mot de passe pour {$user->email}"
        );

        return [
            'success' => true,
            'message' => trans('api.password_reset.sent'),
            'status'  => 200,
        ];
    }

    /**
     * Valide le token et met à jour le mot de passe.
     *
     * POST /auth/reset-password
     * Body : { "token": "...", "password": "...", "password_confirmation": "..." }
     */
    public function resetPassword(): array
    {
        $errors = $this->validate([
            'token'    => 'required',
            'password' => 'required|min:8',
        ]);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => trans('api.validation_failed'),
                'errors'  => $errors,
                'status'  => 422,
            ];
        }

        $token           = request('token');
        $newPassword     = request('password');
        $confirmation    = request('password_confirmation');

        // Vérifier la confirmation de mot de passe
        if ($confirmation !== null && $newPassword !== $confirmation) {
            return [
                'success' => false,
                'message' => trans('api.password_reset.confirmation_mismatch'),
                'status'  => 422,
            ];
        }

        // Vérifier que le nouveau mot de passe n'est pas un mot de passe par défaut
        if (User::isDefaultPassword($newPassword)) {
            return [
                'success' => false,
                'message' => trans('api.password_reset.no_default_password'),
                'status'  => 422,
            ];
        }

        $record = $this->findToken($token);

        if (!$record) {
            return [
                'success' => false,
                'message' => trans('api.password_reset.invalid_token'),
                'status'  => 422,
            ];
        }

        // Vérifier l'expiration
        if (strtotime($record['expires_at']) < time()) {
            $this->deleteToken($token);
            return [
                'success' => false,
                'message' => trans('api.password_reset.token_expired'),
                'status'  => 422,
            ];
        }

        $user = User::find((int) $record['user_id']);

        if (!$user) {
            return [
                'success' => false,
                'message' => trans('api.user.not_found'),
                'status'  => 404,
            ];
        }

        // Mettre à jour le mot de passe
        $user->password = User::hashPassword($newPassword);
        $user->save();

        // Supprimer le token utilisé
        $this->deleteToken($token);

        // Notification de confirmation
        $this->notificationService->send(
            $user->id,
            trans('api.password_reset.success_title'),
            trans('api.password_reset.success_body'),
            'success'
        );

        AuditService::logAction(
            $user->id,
            'password_reset_completed',
            'auth',
            "Mot de passe réinitialisé pour {$user->email}"
        );

        return [
            'success' => true,
            'message' => trans('api.password_reset.success'),
            'status'  => 200,
        ];
    }

    /**
     * Vérifie si un token est valide sans l'utiliser (utile côté front).
     *
     * GET /auth/reset-password/verify?token=...
     */
    public function verifyToken(): array
    {
        $token  = request('token');

        if (!$token) {
            return [
                'success' => false,
                'message' => trans('api.password_reset.invalid_token'),
                'status'  => 422,
            ];
        }

        $record = $this->findToken($token);

        if (!$record || strtotime($record['expires_at']) < time()) {
            return [
                'success' => false,
                'message' => $record ? trans('api.password_reset.token_expired') : trans('api.password_reset.invalid_token'),
                'status'  => 422,
            ];
        }

        return [
            'success'    => true,
            'message'    => trans('api.password_reset.token_valid'),
            'expires_at' => $record['expires_at'],
            'status'     => 200,
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers — accès à la table password_resets
    // -------------------------------------------------------------------------

    private function storeToken(int $userId, string $token, string $expiresAt): void
    {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare(
            'INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (:user_id, :token, :expires_at, NOW())'
        );
        $stmt->execute([
            ':user_id'    => $userId,
            ':token'      => hash('sha256', $token),
            ':expires_at' => $expiresAt,
        ]);

        // On met le token brut dans la session/réponse uniquement lors de la création
        // Ici on le retourne via la notification — le hash est stocké en base
    }

    private function findToken(string $token): ?array
    {
        $pdo  = self::getPdo();
        $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = :token LIMIT 1');
        $stmt->execute([':token' => hash('sha256', $token)]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function deleteToken(string $token): void
    {
        $pdo  = self::getPdo();
        $stmt = $pdo->prepare('DELETE FROM password_resets WHERE token = :token');
        $stmt->execute([':token' => hash('sha256', $token)]);
    }

    private function deleteExistingTokens(int $userId): void
    {
        $pdo  = self::getPdo();
        $stmt = $pdo->prepare('DELETE FROM password_resets WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
    }

    /**
     * Accès à la connexion PDO via le modèle User
     */
    private static function getPdo(): \PDO
    {
        return User::getPdo();
    }
}
