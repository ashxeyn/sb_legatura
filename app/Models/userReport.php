<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_reports';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'report_id';

    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Disable Eloquent timestamps (table uses created_at only).
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reporter_user_id',
        'reported_user_id',
        'reason',
        'description',
        'status',
        'created_at',
    ];

    /**
     * Reporter relationship (user who filed the report).
     */
    public function reporter()
    {
        return $this->belongsTo(user::class, 'reporter_user_id', 'user_id');
    }

    /**
     * Reported user relationship (user being reported).
     */
    public function reported()
    {
        return $this->belongsTo(user::class, 'reported_user_id', 'user_id');
    }
}
