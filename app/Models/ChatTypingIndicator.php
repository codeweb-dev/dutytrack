<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatTypingIndicator extends Model
{
    protected $fillable = [
        'chat_conversation_id',
        'user_id',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'chat_conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
