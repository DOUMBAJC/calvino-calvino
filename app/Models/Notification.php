<?php

namespace App\Models;

use Calvino\Core\Model;
use Calvino\Traits\BaseNotification;

/**
 * Modèle Notification
 */
class Notification extends Model
{
    use BaseNotification;

    protected string $table = 'notifications';
    protected array $fillable = [
        'user_id', 'title', 'message', 'type', 
        'is_read', 'data', 'created_at'
    ];
}
