<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'sender_id', 
        'recipient_id', 
        'subject', 
        'body', 
        'status'
    ];

    protected $casts = [
        'body' => 'encrypted',
    ];
    
}
