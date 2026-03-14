<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;

class adminSentNotification extends Model
{
    protected $table = 'admin_sent_notifications';
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'title',
        'message',
        'delivery_method',
        'target_type',
        'target_user_ids',
        'recipient_count',
        'sent_at',
    ];
}
