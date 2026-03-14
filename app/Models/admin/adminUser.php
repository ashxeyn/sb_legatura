<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;

class adminUser extends Model
{
    protected $table = 'admin_users';
    protected $primaryKey = 'admin_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'username',
        'email',
        'password_hash',
        'last_name',
        'middle_name',
        'first_name',
        'is_active',
        'created_at',
        'profile_pic',
    ];
}
