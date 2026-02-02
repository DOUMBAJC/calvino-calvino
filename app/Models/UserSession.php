<?php

namespace App\Models;

use Calvino\Core\Model;
use Calvino\Traits\ManageSessions;

/**
 * Modèle UserSession
 */
class UserSession extends Model
{
    use ManageSessions;

    protected string $table = 'user_sessions';
    protected array $fillable = [
        'user_id', 'session_id', 'token', 'refresh_token', 
        'ip_address', 'user_agent', 'device_name', 
        'device_type', 'location', 'last_activity', 'is_active'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
