<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;

class adminNotificationPreference extends Model
{
    protected $table = 'admin_notification_preferences';
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'setting_key',
        'is_enabled',
        'updated_at',
    ];
}
