<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use Calvino\Core\Controller;
use Calvino\Core\Request;
use App\Models\User;
use Calvino\Services\NotificationService;
use Calvino\Services\AuditService;
use Calvino\Core\Auth;
use App\Models\ActivityLog;
use PDO;

class UserController extends Controller
{
    protected NotificationService $notificationService;
    protected Auth $auth;
    protected AuditService $auditService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->auth                = new Auth();
        $this->auditService        = new AuditService();
    }

    /**
     * Récupère tous les utilisateurs avec pagination et dernière connexion.
     *
     * Query params :
     *   - page     (int, défaut 1)
     *   - per_page (int, défaut 15, max 100)
     *   - search   (string, filtre sur name/email)
     *   - role     (string, filtre sur le rôle)
     *   - status   (int 0|1, filtre sur is_active)
     */
    public function index(): array
    {
        $page    = max(1, (int) request('page', 1));
        $perPage = min(100, max(1, (int) request('per_page', 15)));
        $search  = (string) request('search', '');
        $role    = (string) request('role', '');
        $status  = request('status', null);

        $users = User::all();

        // Filtres en mémoire (compatible avec l'ORM actuel)
        $filtered = array_filter($users, function ($user) use ($search, $role, $status) {
            if ($search !== '' && stripos($user->name . ' ' . $user->email, $search) === false) {
                return false;
            }
            if ($role !== '' && $user->role !== $role) {
                return false;
            }
            if ($status !== null && (string) $user->is_active !== (string) $status) {
                return false;
            }
            return true;
        });

        $filtered = array_values($filtered);
        $total    = count($filtered);
        $offset   = ($page - 1) * $perPage;
        $paginated = array_slice($filtered, $offset, $perPage);

        $pdo            = $this->getPdoConnection();
        $formattedUsers = [];

        foreach ($paginated as $user) {
            $userId = (int) $user->id;

            $stmt = $pdo->prepare(
                "SELECT created_at FROM activity_logs
                WHERE user_id = :user_id AND action = 'login'
                ORDER BY created_at DESC LIMIT 1"
            );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $lastLogin = $stmt->fetchColumn();

            $stmt = $pdo->prepare('SELECT created_at FROM users WHERE id = :user_id');
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $createdAt = $stmt->fetchColumn();

            $formattedUsers[] = [
                'id'        => $userId,
                'name'      => $user->name,
                'email'     => $user->email,
                'phone'     => $user->phone,
                'role'      => $user->role,
                'is_active' => boolval($user->is_active),
                'lastLogin' => $lastLogin ?: 'Jamais connecté',
                'created_at'=> $createdAt,
            ];
        }

        $data = [
            'users'      => $formattedUsers,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'total_pages'  => (int) ceil($total / $perPage),
                'from'         => $total > 0 ? $offset + 1 : 0,
                'to'           => min($offset + $perPage, $total),
            ],
        ];

        return $this->success('Liste des utilisateurs récupérée avec succès', $data);
    }

    /**
     * Récupère un utilisateur spécifique.
     */
    public function show(Request $request, string $id): array
    {
        $userId = (int) $id;
        $user   = User::find($userId);

        if (!$user) {
            return $this->error('Utilisateur non trouvé', 404);
        }

        $pdo = $this->getPdoConnection();

        $stmt = $pdo->prepare(
            "SELECT created_at FROM activity_logs
            WHERE user_id = :user_id AND action = 'login'
            ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $lastLogin = $stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT created_at FROM users WHERE id = :user_id');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $createdAt = $stmt->fetchColumn();

        $formattedUser = [
            'id'        => $userId,
            'name'      => $user->name,
            'email'     => $user->email,
            'phone'     => $user->phone,
            'role'      => $user->role,
            'is_active' => boolval($user->is_active),
            'lastLogin' => $lastLogin ?: 'Jamais connecté',
            'created_at'=> $createdAt,
        ];

        return $this->success('Utilisateur récupéré avec succès', $formattedUser);
    }

    /**
     * Crée un nouvel utilisateur.
     */
    public function store(Request $request): array
    {
        $errors = $this->validate([
            'name'    => 'required',
            'email'   => 'required|email',
            'role'    => 'required|in:admin,manager,pharmacist,cashier',
            'phone'   => 'required',
            'address' => 'required',
        ]);

        if (!empty($errors)) {
            return $this->error('Erreurs de validation', 422);
        }

        $existingUser = User::findByEmail(request('email'));
        if ($existingUser) {
            return $this->error('Cet email est déjà utilisé', 422);
        }

        $randomChars = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4);
        $password    = 'PHS' . $randomChars;

        $user           = new User();
        $user->name     = request('name');
        $user->email    = request('email');
        $user->password = User::hashPassword($password);
        $user->role     = request('role');
        $user->phone    = request('phone');
        $user->address  = request('address');
        $user->is_active = 1;
        $user->save();

        $currentUser   = $this->auth->user();
        $currentUserId = $currentUser ? $currentUser->id : 1;

        ActivityLog::log(
            $currentUserId,
            'user_created',
            'users',
            "Création d'un nouvel utilisateur {$user->email} avec le rôle {$user->role}"
        );

        $this->notificationService->send(
            $user->id,
            'Bienvenue',
            'Bienvenue dans notre application de gestion de pharmacie. Votre mot de passe est: ' . $password,
            'info'
        );

        if ($currentUser) {
            $this->notificationService->send(
                $currentUser->id,
                'Nouvel utilisateur créé',
                'Vous avez créé un nouvel utilisateur ' . $user->name . ' (' . $user->email . '). Mot de passe généré: ' . $password,
                'success'
            );
        }

        return $this->success('Utilisateur créé avec succès', [
            'user'               => $user,
            'generated_password' => $password,
        ]);
    }

    /**
     * Met à jour un utilisateur existant.
     */
    public function update(Request $request, string $id): array
    {
        $userId = (int) $id;
        $user   = User::find($userId);

        if (!$user) {
            return $this->error('Utilisateur non trouvé', 404);
        }

        $errors = $this->validate([
            'name'    => 'required',
            'email'   => 'required|email',
            'role'    => 'required|in:admin,manager,pharmacist,cashier',
            'phone'   => 'required',
            'address' => 'required',
        ]);

        if (!empty($errors)) {
            return $this->error('Erreurs de validation', 422);
        }

        $existingUser = User::findByEmail(request('email'));
        if ($existingUser && $existingUser->id !== $user->id) {
            return $this->error('Cet email est déjà utilisé', 422);
        }

        $user->name    = request('name');
        $user->email   = request('email');
        $user->role    = request('role');
        $user->phone   = request('phone');
        $user->address = request('address');

        if (request()->has('is_active')) {
            $user->is_active = request('is_active') ? 1 : 0;
        }

        if (request('password')) {
            $user->password = User::hashPassword(request('password'));
        }

        $user->save();

        $currentUserId = $this->auth->user() ? $this->auth->user()->id : 0;
        ActivityLog::log(
            $currentUserId,
            'user_updated',
            'users',
            "Mise à jour de l'utilisateur {$user->email} (ID: {$user->id})"
        );

        return $this->success('Utilisateur mis à jour avec succès', $user);
    }

    /**
     * Supprime un utilisateur.
     */
    public function destroy(Request $request, string $id): array
    {
        $userId = (int) $id;
        $user   = User::find($userId);

        if (!$user) {
            return $this->error('Utilisateur non trouvé', 404);
        }

        $currentUser = $this->auth->user();
        if ($currentUser && $currentUser->id === $user->id) {
            return $this->error('Vous ne pouvez pas supprimer votre propre compte', 403);
        }

        $user->delete();

        $currentUserId = $currentUser ? $currentUser->id : 0;
        ActivityLog::log(
            $currentUserId,
            'user_deleted',
            'users',
            "Suppression de l'utilisateur {$user->email} (ID: {$user->id})"
        );

        return $this->success('Utilisateur supprimé avec succès');
    }

    /**
     * Active ou désactive un utilisateur.
     */
    public function toggleStatus(Request $request, string $id): array
    {
        $userId = (int) $id;
        $user   = User::find($userId);

        if (!$user) {
            return $this->error('Utilisateur non trouvé', 404);
        }

        $currentUser = $this->auth->user();
        if ($currentUser && $currentUser->id === $user->id) {
            return $this->error('Vous ne pouvez pas modifier le statut de votre propre compte', 403);
        }

        $oldStatus       = (int) $user->is_active;
        $user->is_active = $oldStatus ? 0 : 1;
        $user->save();

        $status        = $user->is_active ? 'activé' : 'désactivé';
        $currentUserId = $currentUser ? $currentUser->id : 0;

        ActivityLog::log(
            $currentUserId,
            'user_status_changed',
            'users',
            "Statut de l'utilisateur {$user->email} modifié de {$oldStatus} à " . (int) $user->is_active
        );

        return $this->success("Utilisateur {$status} avec succès", $user);
    }

    /**
     * Retourne la connexion PDO partagée.
     */
    private function getPdoConnection(): \PDO
    {
        return (new \Calvino\Providers\DatabaseServiceProvider(app()))->getConnection();
    }
}
