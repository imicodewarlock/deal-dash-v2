<?php

namespace App\Listeners;

use App\Events\RegisteredUser;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendVerificationEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RegisteredUser $event)
    {
        $user = $event->user;

        // Send email verification notification
        $user->notify(new VerifyEmailNotification($user->verification_token));
    }
}
