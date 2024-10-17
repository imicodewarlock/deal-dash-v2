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
    public function via($notifiable)
    {
        return [FcmChannel::class, 'database'];
    }

    public function toArray($notifiable)
    {
        // return $this->offer->toArray();
        return [
            "id" => $this->offer->id,
            "name" => $this->offer->name,
            "store_id" => (int) $this->offer->store_id,
            "image" => $this->offer->image,
            "address" => $this->offer->address,
            "about" => $this->offer->about,
            "price" => $this->offer->price,
            "latitude" => $this->offer->latitude,
            "longitude" => $this->offer->longitude,
            "start_date" => $this->offer->start_date,
            "end_date" => $this->offer->end_date,
            "updated_at" => $this->offer->updated_at,
            "created_at" => $this->offer->created_at,
        ];
    }

    public function toFcm($notifiable)
    {
        return (new FcmMessage(notification: new FcmNotification(
            title: $this->offer->name,
            body: $this->offer->about,
            image: $this->offer->image
        )))
        ->data([
            "id" => $this->offer->id,
            "name" => $this->offer->name,
            "store_id" => (int) $this->offer->store_id,
            "image" => $this->offer->image,
            "address" => $this->offer->address,
            "about" => $this->offer->about,
            "price" => $this->offer->price,
            "latitude" => $this->offer->latitude,
            "longitude" => $this->offer->longitude,
            "start_date" => $this->offer->start_date,
            "end_date" => $this->offer->end_date,
            "updated_at" => $this->offer->updated_at,
            "created_at" => $this->offer->created_at,
        ])
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
