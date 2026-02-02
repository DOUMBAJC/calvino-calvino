<?php

namespace Calvino\Models;

use Calvino\Core\Model;

class ActivityLog extends Model
{
    protected string $table = 'activity_logs';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'ip_address',
        'user_agent',
        'created_at'
    ];
    
    // Désactiver les timestamps automatiques si la table ne les a pas (updated_at)
    // Mais ici on a created_at
    
    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Enregistre une nouvelle activité
     * 
     * @param int|null $userId ID de l'utilisateur (null si action système ou non connecté)
     * @param string $action Type d'action (create, update, delete, login, etc.)
     * @param string $module Module concerné (auth, products, users, etc.)
     * @param string $description Description détaillée
     * @return bool
     */
    public static function log(?int $userId, string $action, string $module, string $description): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => date('Y-m-d H:i:s')
        ]) !== null;
    }
}
