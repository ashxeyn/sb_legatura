<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class messageClass extends Model
{
    protected $primaryKey = 'message_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content',
        'sent_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime'
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }
}
