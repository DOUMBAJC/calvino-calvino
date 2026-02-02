<?php

namespace App\Models;

use Calvino\Core\Model;
use Calvino\Traits\LoggableActivity;

/**
 * Modèle ActivityLog
 */
class ActivityLog extends Model
{
    use LoggableActivity;

    protected string $table = 'activity_logs';
    protected array $fillable = [
        'user_id', 'action', 'module', 'description', 
        'old_values', 'new_values', 'ip_address', 
        'user_agent', 'created_at'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
