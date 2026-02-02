<?php

namespace App\Controllers\Api;

use Calvino\Core\Controller;
use Calvino\Core\Request;
use Calvino\Models\User;
use Calvino\Services\NotificationService;
use Calvino\Services\AuditService;
use Calvino\Core\Auth;
use Calvino\Models\ActivityLog;
use PDO;

class UserController extends Controller
{
    /**
     * Service de notification
     *
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Instance Auth
     *
     * @var Auth
     */
    protected $auth;

    /**
     * Instance Audit
     *
     * @var AuditService
     */
    protected $auditService;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->auth = new Auth();
        $this->auditService = new AuditService();
    }

    /**
     * Récupère tous les utilisateurs avec leur dernière connexion
     *
     * @return array
     */
    public function index(): array
    {
        $users = User::all();
        $formattedUsers = [];
        
        $pdo = (new \Calvino\Providers\DatabaseServiceProvider(app()))->getConnection();
        
        foreach ($users as $user) {
            $userId = (int)$user->id;
            
            $stmt = $pdo->prepare(
                "SELECT created_at FROM activity_logs 
                WHERE user_id = :user_id AND action = 'login' 
                ORDER BY created_at DESC LIMIT 1"
            );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $lastLogin = $stmt->fetchColumn();
            
            // Récupérer la date de création directement depuis la base de données
            $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $createdAt = $stmt->fetchColumn();
            
            // Formater les données selon le format demandé
            $formattedUsers[] = [
                'id' => $userId,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'is_active' => boolval($user->is_active),
                'lastLogin' => $lastLogin ?? 'Jamais connecté',
                'created_at' => $createdAt
            ];
        }
        
        return $this->success('Liste des utilisateurs récupérée avec succès', $formattedUsers);
    }

    /**
     * Récupère un utilisateur spécifique
     *
     * @param Request $request
     * @param string $id
     * @return array
     */
    public function show(Request $request, string $id): array
    {
        $userId = (int) $id;
        $user = User::find($userId);
        
        if (!$user) {
            return $this->error('Utilisateur non trouvé', 404);
        }
        
        $pdo = (new \Calvino\Providers\DatabaseServiceProvider(app()))->getConnection();
        
        $userId = (int)$user->id;
        
        $stmt = $pdo->prepare(
            "SELECT created_at FROM activity_logs 
            WHERE user_id = :user_id AND action = 'login' 
            ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $lastLogin = $stmt->fetchColumn();
        
        // Récupérer la date de création directement depuis la base de données
        $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $createdAt = $stmt->fetchColumn();
        
        $formattedUser = [
            'id' => $userId,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'is_active' => boolval($user->is_active),
            'lastLogin' => $lastLogin ?? 'Jamais connecté',
            'created_at' => $createdAt,
        
        ];
        
        return $this->success('Utilisateur récupéré avec succès', $formattedUser);
    }

    /**
     * Crée un nouvel utilisateur
     *
     * @param Request $request
     * @return array
     */
    public function store(Request $request): array
    {
        $errors = $this->validate([
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required|in:admin,manager,pharmacist,cashier',
            'phone' => 'required',
            'address' => 'required'
        ]);
        
        if (!empty($errors)) {
            return $this->error('Erreurs de validation', 422);
        }
        
        // Vérifier si l'email existe déjà
        $existingUser = User::findByEmail(request('email'));
        if ($existingUser) {
            return $this->error('Cet email est déjà utilisé', 422);
        }
        
        // Générer un mot de passe par défaut (toujours généré par le système)
        $randomChars = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4);
        $password = 'PHS' . $randomChars;
        
        // Créer l'utilisateur
        $user = new User();
        $user->name = request('name');
        $user->email = request('email');
        $user->password = User::hashPassword($password);
        $user->role = request('role');
        $user->phone = request('phone');
        $user->address = request('address');
        $user->is_active = 1;
        $user->save();
        
        // Récupérer l'administrateur qui crée l'utilisateur
        $currentUser = $this->auth->user();
        $currentUserId = $currentUser ? $currentUser->id : 1;
        
        // Journaliser l'action
        ActivityLog::log(
            $currentUserId,
            'user_created',
            'users',
            "Création d'un nouvel utilisateur {$user->email} avec le rôle {$user->role}"
        );
        
        // Envoyer une notification à l'utilisateur créé
        $this->notificationService->send(
            $user->id,
            'Bienvenue',
            'Bienvenue dans notre application de gestion de pharmacie. Votre mot de passe est: ' . $password,
            'info'
        );
        
        // Envoyer une notification à l'administrateur qui a créé l'utilisateur
        if ($currentUser) {
            $this->notificationService->send(
                $currentUser->id,
                'Nouvel utilisateur créé',
                'Vous avez créé un nouvel utilisateur ' . $user->name . ' (' . $user->email . '). Mot de passe généré: ' . $password,
                'success'
            );
        }
        
        // Inclure le mot de passe généré dans la réponse
        $responseData = [
            'user' => $user,
            'generated_password' => $password
        ];
        
        return $this->success('Utilisateur créé avec succès', $responseData);
    }

    /**
     * Met à jour un utilisateur existant
     *
     * @param Request $request
     * @param string $id
     * @return array
     */
    public function update(Request $request, string $id): array
    {
        $userId = (int) $id;
        $user = User::find($userId);
        
        if (!$user) {
            return $this->error('Utilisateur non trouvé', 404);
        }
        
        $errors = $this->validate([
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required|in:admin,manager,pharmacist,cashier',
            'phone' => 'required',
            'address' => 'required'
        ]);
        
        if (!empty($errors)) {
            return $this->error('Erreurs de validation', 422);
        }
        
        // Vérifier si l'email existe déjà pour un autre utilisateur
        $existingUser = User::findByEmail(request('email'));
        if ($existingUser && $existingUser->id !== $user->id) {
            return $this->error('Cet email est déjà utilisé', 422);
        }
        
        // Sauvegarder les anciennes valeurs pour l'audit
        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'address' => $user->address,
            'is_active' => $user->is_active
        ];
        
        // Mettre à jour l'utilisateur
        $user->name = request('name');
        $user->email = request('email');
        $user->role = request('role');
        $user->phone = request('phone');
        $user->address = request('address');
        
        // Gérer le statut actif si fourni
        if (request()->has('is_active')) {
            // Convertir explicitement en entier (0 ou 1)
            $user->is_active = request('is_active') ? 1 : 0;
        }
        
        // Mettre à jour le mot de passe si fourni
        if (request('password')) {
            $user->password = User::hashPassword(request('password'));
        }
        
        $user->save();
        
        // Journaliser l'action
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
     * Supprime un utilisateur
     *
     * @param Request $request
     * @param string $id
     * @return array
     */
    public function destroy(Request $request, string $id): array
    {
        $userId = (int) $id;
        $user = User::find($userId);
        
        if (!$user) {
            return $this->error('Utilisateur non trouvé', 404);
        }
        
        // Vérifier que l'utilisateur n'est pas en train de se supprimer lui-même
        $currentUser = $this->auth->user();
        if ($currentUser && $currentUser->id === $user->id) {
            return $this->error('Vous ne pouvez pas supprimer votre propre compte', 403);
        }
        
        // Sauvegarder les données de l'utilisateur avant suppression pour l'audit
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ];
        
        $user->delete();
        
        // Journaliser l'action
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
     * Active ou désactive un utilisateur
     *
     * @param Request $request
     * @param string $id
     * @return array
     */
    public function toggleStatus(Request $request, string $id): array
    {
        $userId = (int) $id;
        $user = User::find($userId);
        
        if (!$user) {
            return $this->error('Utilisateur non trouvé', 404);
        }
        
        // Vérifier que l'utilisateur n'est pas en train de se désactiver lui-même
        $currentUser = $this->auth->user();
        if ($currentUser && $currentUser->id === $user->id) {
            return $this->error('Vous ne pouvez pas modifier le statut de votre propre compte', 403);
        }
        
        $oldStatus = (int)$user->is_active;
        // Basculer entre 0 et 1 explicitement
        $user->is_active = $oldStatus ? 0 : 1;
        $user->save();
        
        $status = $user->is_active ? 'activé' : 'désactivé';
        
        // Journaliser l'action
        $currentUserId = $currentUser ? $currentUser->id : 0;
        ActivityLog::log(
            $currentUserId,
            'user_status_changed',
            'users',
            "Statut de l'utilisateur {$user->email} modifié de {$oldStatus} à " . (int)$user->is_active
        );
        
        return $this->success("Utilisateur {$status} avec succès", $user);
    }
}