<?php

namespace App\Listeners;

use App\Events\OfferCreated;
use App\Models\User;
use App\Notifications\OfferCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOfferCreatedNotification /*implements ShouldQueue*/
{
    // use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  \App\Events\OfferCreated  $event
     * @return void
     */
    public function handle(OfferCreated $event): void
    {
        $users = User::whereNotNull('fcm_token')->withoutTrashed()->get();
        // $users = User::all();
        // dd($users);

        foreach ($users as $user) {
            $user->notify(new OfferCreatedNotification($event->offer));
        }
    }
}
