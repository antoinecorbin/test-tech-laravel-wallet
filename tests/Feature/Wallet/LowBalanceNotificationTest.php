<?php

use App\Models\User;
use App\Notifications\LowBalanceNotification;
use Illuminate\Support\Facades\Notification;

test('notify users when their wallet falls below 10 euros', function (){
    Notification::fake();

    $user = User::factory()->create();
    $user->wallet->update(['balance' => 900]);

    $user->notifyBalanceIsLow();

    Notification::assertSentTo($user, LowBalanceNotification::class);
});
