<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Public channel for vessel position updates — no auth required
Broadcast::channel('vessel-positions', function () {
    return true;
});

// Public channel for port events — no auth required
Broadcast::channel('port-events', function () {
    return true;
});
