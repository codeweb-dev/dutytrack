<?php

use App\Models\ChatConversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('messages.user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('messages.conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = ChatConversation::query()->find($conversationId);

    if (! $conversation) {
        return false;
    }

    return (int) $conversation->student_id === (int) $user->id
        || (int) $conversation->participant_id === (int) $user->id;
});
