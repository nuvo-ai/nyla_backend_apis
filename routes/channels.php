<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    Log::info('Broadcast auth attempt', [
        'auth_user_id' => $user->id,
        'channel_id'   => $id,
    ]);

    return (int) $user->id === (int) $id;
});

