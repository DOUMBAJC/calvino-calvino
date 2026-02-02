<?php

namespace App\Controllers\Api;

use Calvino\Core\Controller;
use Calvino\Core\Request;
use Calvino\Models\ActivityLog;
use Calvino\Models\User;

class ActivityLogController extends Controller
{
    /**
     * Récupère la liste des journaux d'activités avec pagination et filtres
     * 
     * @param Request $request
     * @return array
     */
    public function index(Request $request): array
    {
        // Récupération des paramètres de filtrage
        $page = (int) request('page', 1);
        $limit = (int) request('limit', 10);
        $search = request('search', '');
        $module = request('module', '');
        $action = request('action', '');
        $startDate = request('start_date', '');
        $endDate = request('end_date', '');
        
        // Requête de base
        $logs = ActivityLog::all();
        $filteredLogs = [];
        
        // Application des filtres manuellement
        foreach ($logs as $log) {
            // Filtre de recherche
            if (!empty($search) && strpos((string)$log->description, $search) === false) {
                continue;
            }
            
            // Filtre par module
            if (!empty($module) && $log->module !== $module) {
                continue;
            }
            
            // Filtre par action
            if (!empty($action) && $log->action !== $action) {
                continue;
            }
            
            // Filtre par date de début
            if (!empty($startDate) && $log->created_at && strtotime($log->created_at) < strtotime($startDate . ' 00:00:00')) {
                continue;
            }
            
            // Filtre par date de fin
            if (!empty($endDate) && $log->created_at && strtotime($log->created_at) > strtotime($endDate . ' 23:59:59')) {
                continue;
            }
            
            $filteredLogs[] = $log;
        }
        
        // Tri par date décroissante
        usort($filteredLogs, function($a, $b) {
            $timeA = $a->created_at ? strtotime($a->created_at) : 0;
            $timeB = $b->created_at ? strtotime($b->created_at) : 0;
            return $timeB - $timeA;
        });
        
        // Pagination manuelle
        $total = count($filteredLogs);
        $offset = ($page - 1) * $limit;
        $paginatedLogs = array_slice($filteredLogs, $offset, $limit);
        
        // Enrichissement des données avec les informations utilisateur
        foreach ($paginatedLogs as &$log) {
            $user = User::find($log->user_id);
            if ($user) {
                $log->user = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => 'https://gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?d=mp' ?? null,
                    'role' => $user->role ?? 'Utilisateur'
                ];
            } else {
                $log->user = [
                    'name' => 'Utilisateur supprimé',
                    'avatar' => null,
                    'role' => 'Inconnu'
                ];
            }
        }
        
        // Construction de la réponse
        $response = [
            'data' => $paginatedLogs,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'start_record' => ($page - 1) * $limit + 1,
                'end_record' => min($page * $limit, $total)
            ]
        ];
        
        return $this->success('Journaux d\'activités récupérés avec succès', $response);
    }
    
    /**
     * Récupère les détails d'un journal d'activité spécifique
     * 
     * @param Request $request
     * @param int $id
     * @return array
     */
    public function show(Request $request, int $id): array
    {
        $log = ActivityLog::find($id);
        
        if (!$log) {
            return $this->error('Journal d\'activité non trouvé', 404);
        }
        
        // Récupération des informations utilisateur
        $user = User::find($log->user_id);
        if ($user) {
            $log->user = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => 'https://gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?d=mp' ?? null,
                'role' => $user->role ?? 'Utilisateur'
            ];
        } else {
            $log->user = [
                'name' => 'Utilisateur supprimé',
                'avatar' => null,
                'role' => 'Inconnu'
            ];
        }
        
        return $this->success('Journal d\'activité récupéré avec succès', $log);
    }
    
    /**
     * Récupère les statistiques des journaux d'activités
     * 
     * @param Request $request
     * @return array
     */
    public function stats(Request $request): array
    {
        $logs = ActivityLog::all();
        
        // Total des activités
        $totalActivities = count($logs);
        
        // Activités aujourd'hui
        $today = date('Y-m-d');
        $activitiesToday = 0;
        foreach ($logs as $log) {
            if ($log->created_at && strpos((string)$log->created_at, $today) === 0) {
                $activitiesToday++;
            }
        }
        
        // Activités hier
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $activitiesYesterday = 0;
        foreach ($logs as $log) {
            if ($log->created_at && strpos((string)$log->created_at, $yesterday) === 0) {
                $activitiesYesterday++;
            }
        }
        
        // Calcul du pourcentage de changement
        $percentChange = 0;
        if ($activitiesYesterday > 0) {
            $percentChange = round((($activitiesToday - $activitiesYesterday) / $activitiesYesterday) * 100);
        }
        
        // Utilisateurs actifs aujourd'hui (utilisateurs uniques ayant effectué des actions)
        $activeUserIds = [];
        foreach ($logs as $log) {
            if ($log->created_at && strpos((string)$log->created_at, $today) === 0) {
                $activeUserIds[$log->user_id] = true;
            }
        }
        $activeUsers = count($activeUserIds);
        
        // Alertes système (actions de type 'alert' ou 'error')
        $systemAlerts = 0;
        foreach ($logs as $log) {
            if ($log->created_at && strpos((string)$log->created_at, $today) === 0 && in_array($log->action, ['alert', 'error'])) {
                $systemAlerts++;
            }
        }
        
        $stats = [
            'total_activities' => $totalActivities,
            'activities_today' => $activitiesToday,
            'active_users' => $activeUsers,
            'system_alerts' => $systemAlerts,
            'percent_change' => $percentChange
        ];
        
        return $this->success('Statistiques récupérées avec succès', $stats);
    }
    
    /**
     * Exporte les journaux d'activités au format CSV
     * 
     * @param Request $request
     * @return array
     */
    public function export(Request $request): array
    {
        // Récupération des paramètres de filtrage
        $module = request('module', '');
        $action = request('action', '');
        $startDate = request('start_date', '');
        $endDate = request('end_date', '');
        
        // Récupération de tous les logs
        $logs = ActivityLog::all();
        $filteredLogs = [];
        
        // Application des filtres manuellement
        foreach ($logs as $log) {
            // Filtre par module
            if (!empty($module) && $log->module !== $module) {
                continue;
            }
            
            // Filtre par action
            if (!empty($action) && $log->action !== $action) {
                continue;
            }
            
            // Filtre par date de début
            if (!empty($startDate) && $log->created_at && strtotime($log->created_at) < strtotime($startDate . ' 00:00:00')) {
                continue;
            }
            
            // Filtre par date de fin
            if (!empty($endDate) && $log->created_at && strtotime($log->created_at) > strtotime($endDate . ' 23:59:59')) {
                continue;
            }
            
            $filteredLogs[] = $log;
        }
        
        // Tri par date décroissante
        usort($filteredLogs, function($a, $b) {
            $timeA = $a->created_at ? strtotime($a->created_at) : 0;
            $timeB = $b->created_at ? strtotime($b->created_at) : 0;
            return $timeB - $timeA;
        });
        
        // Nom du fichier d'export
        $filename = 'activity_logs_' . date('Y-m-d_His') . '.csv';
        $filepath = dirname(__DIR__, 3) . '/database/exports/' . $filename;
        
        // Création du fichier CSV
        $file = fopen($filepath, 'w');
        
        // En-têtes CSV
        fputcsv($file, [
            'ID', 
            'Utilisateur', 
            'Action', 
            'Module', 
            'Description', 
            'Date & Heure', 
            'Adresse IP', 
            'User Agent'
        ]);
        
        // Données CSV
        foreach ($filteredLogs as $log) {
            $user = User::find($log->user_id);
            $username = $user ? $user->name : 'Utilisateur supprimé';
            
            fputcsv($file, [
                $log->id,
                $username,
                $log->action,
                $log->module,
                $log->description,
                $log->created_at,
                $log->ip_address,
                $log->user_agent
            ]);
        }
        
        fclose($file);
        
        // Vérifier si le fichier a été créé avec succès
        if (!file_exists($filepath)) {
            return $this->error('Erreur lors de la création du fichier d\'export', 500);
        }
        
        // Retourner l'URL du fichier pour téléchargement
        $downloadUrl = '/api/activity-logs/download/' . basename($filepath);
        return $this->success('Fichier d\'export généré avec succès', [
            'download_url' => $downloadUrl,
            'filename' => $filename
        ]);
    }
    
    /**
     * Télécharge un fichier CSV exporté
     * 
     * @param Request $request
     * @param string $filename
     * @return void
     */
    public function download(Request $request, string $filename): void
    {
        $filepath = dirname(__DIR__, 3) . '/database/exports/' . $filename;
        
        if (!file_exists($filepath)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Fichier non trouvé',
                'code' => 404
            ]);
            exit;
        }
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
    
    /**
     * Récupère les options de filtrage disponibles
     * 
     * @param Request $request
     * @return array
     */
    public function filterOptions(Request $request): array
    {
        $logs = ActivityLog::all();
        
        // Récupération des modules distincts
        $modules = [];
        foreach ($logs as $log) {
            if (!in_array($log->module, $modules)) {
                $modules[] = $log->module;
            }
        }
        
        // Récupération des actions distinctes
        $actions = [];
        foreach ($logs as $log) {
            if (!in_array($log->action, $actions)) {
                $actions[] = $log->action;
            }
        }
        
        $options = [
            'modules' => $modules,
            'actions' => $actions
        ];
        
        return $this->success('Options de filtrage récupérées avec succès', $options);
    }
} 