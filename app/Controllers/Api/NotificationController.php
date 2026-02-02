<?php

namespace App\Controllers\Api;

use Calvino\Core\Controller;
use Calvino\Core\Request;
use Calvino\Core\Response;
use App\Models\Notification;
use Calvino\Core\Auth;

class NotificationController extends Controller
{
    protected $notificationModel;
    protected $userId;
    protected $response;
    protected $auth;

    /**
     * Constructeur
     */
    public function __construct()
    {
        parent::__construct();
        $this->notificationModel = new Notification();
        $this->response = new Response();
        $this->auth = new Auth();
        $this->userId = $this->auth->user()->id;
    }

    /**
     * Récupérer toutes les notifications de l'utilisateur connecté
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $unreadOnly = $request->query('unread_only', false);
        $limit = $request->query('limit', 50);
        
        $notifications = $this->notificationModel->getForUser(
            $this->userId,
            $unreadOnly,
            $limit
        );
        
        $this->response->setStatusCode(200);
        $this->response->json([
            'status' => 'success',
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $this->notificationModel->countUnread($this->userId)
            ]
        ]);
        
        return $this->response;
    }

    /**
     * Récupérer une notification spécifique
     * 
     * @param Request $request
     * @param int $id ID de la notification
     * @return Response
     */
    public function show(Request $request, $id)
    {
        $notification = Notification::find($id);
        
        if (!$notification) {
            $this->response->setStatusCode(404);
            $this->response->json([
                'status' => 'error',
                'message' => 'Notification non trouvée'
            ]);
            return $this->response;
        }
        
        if ($notification->user_id != $this->userId) {
            $this->response->setStatusCode(403);
            $this->response->json([
                'status' => 'error',
                'message' => 'Vous n\'êtes pas autorisé à accéder à cette notification'
            ]);
            return $this->response;
        }
        
        // Si data est au format JSON, le décoder
        if (!empty($notification->data)) {
            $notification->data = json_decode($notification->data, true);
        }
        
        $this->response->setStatusCode(200);
        $this->response->json([
            'status' => 'success',
            'data' => $notification
        ]);
        
        return $this->response;
    }

    /**
     * Marquer une notification comme lue
     * 
     * @param Request $request
     * @param int $id ID de la notification
     * @return Response
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::find($id);
        
        if (!$notification) {
            $this->response->setStatusCode(404);
            $this->response->json([
                'status' => 'error',
                'message' => 'Notification non trouvée'
            ]);
            return $this->response;
        }
        
        if ($notification->user_id != $this->userId) {
            $this->response->setStatusCode(403);
            $this->response->json([
                'status' => 'error',
                'message' => 'Vous n\'êtes pas autorisé à modifier cette notification'
            ]);
            return $this->response;
        }
        
        $success = $this->notificationModel->markAsRead($id, $this->userId);
        
        if ($success) {
            $this->response->setStatusCode(200);
            $this->response->json([
                'status' => 'success',
                'message' => 'Notification marquée comme lue',
                'unread_count' => $this->notificationModel->countUnread($this->userId)
            ]);
        } else {
            $this->response->setStatusCode(500);
            $this->response->json([
                'status' => 'error',
                'message' => 'Impossible de marquer la notification comme lue'
            ]);
        }
        
        return $this->response;
    }

    /**
     * Marquer toutes les notifications comme lues
     * 
     * @param Request $request
     * @return Response
     */
    public function markAllAsRead(Request $request)
    {
        $success = $this->notificationModel->markAllAsRead($this->userId);
        
        $this->response->setStatusCode(200);
        $this->response->json([
            'status' => 'success',
            'message' => 'Toutes les notifications ont été marquées comme lues',
            'unread_count' => 0
        ]);
        
        return $this->response;
    }

    /**
     * Supprimer une notification
     * 
     * @param Request $request
     * @param int $id ID de la notification
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        $notification = Notification::find($id);
        
        if (!$notification) {
            $this->response->setStatusCode(404);
            $this->response->json([
                'status' => 'error',
                'message' => 'Notification non trouvée'
            ]);
            return $this->response;
        }
        
        if ($notification->user_id != $this->userId) {
            $this->response->setStatusCode(403);
            $this->response->json([
                'status' => 'error',
                'message' => 'Vous n\'êtes pas autorisé à supprimer cette notification'
            ]);
            return $this->response;
        }
        
        $success = $this->notificationModel->deleteNotification($id, $this->userId);
        
        if ($success) {
            $this->response->setStatusCode(200);
            $this->response->json([
                'status' => 'success',
                'message' => 'Notification supprimée avec succès'
            ]);
        } else {
            $this->response->setStatusCode(500);
            $this->response->json([
                'status' => 'error',
                'message' => 'Impossible de supprimer la notification'
            ]);
        }
        
        return $this->response;
    }

    /**
     * Supprimer toutes les notifications
     * 
     * @param Request $request
     * @return Response
     */
    public function destroyAll(Request $request)
    {
        $success = $this->notificationModel->deleteAllNotifications($this->userId);
        
        if ($success) {
            $this->response->setStatusCode(200);
            $this->response->json([
                'status' => 'success',
                'message' => 'Toutes les notifications ont été supprimées'
            ]);
        } else {
            $this->response->setStatusCode(500);
            $this->response->json([
                'status' => 'error',
                'message' => 'Impossible de supprimer les notifications'
            ]);
        }
        
        return $this->response;
    }
} 