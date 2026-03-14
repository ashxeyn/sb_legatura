<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;

class adminActivityLog extends Model
{
    protected $table = 'admin_activity_logs';
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'action',
        'details',
        'ip_address',
        'created_at',
    ];
}
