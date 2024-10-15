<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class OfferCreatedNotification extends Notification /*implements ShouldQueue*/
{
    // use Queueable;

    protected Offer $offer;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toDatabase($notifiable)
    {
        return $this->offer->toArray();
    }

    public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification(
            title: $this->offer->name,
            body: $this->offer->about,
            image: $this->offer->image
        )))
        ->data($this->offer->toArray())
        ->custom([
            'android' => [
                'notification' => [
                    'color' => '#0A0A0A',
                    'sound' => 'default',
                ],
                'fcm_options' => [
                    'analytics_label' => 'analytics',
                ],
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => 'default'
                    ],
                ],
                'fcm_options' => [
                    'analytics_label' => 'analytics',
                ],
            ],
        ]);
    }
}
