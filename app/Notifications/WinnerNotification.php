<?php

namespace App\Notifications;

use App\Models\Raffle;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class WinnerNotification extends Notification
{
    use Queueable;

    protected $raffle;

    public function __construct(Raffle $raffle)
    {
        $this->raffle = $raffle;
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('🏆 ¡ERES EL GANADOR!')
            ->icon('/pwa-192x192.png')
            ->badge('/pwa-192x192.png')
            ->body("¡Felicidades! Tu ticket ha sido el ganador en el sorteo: {$this->raffle->title}. Contacta con el administrador.")
            ->action('Ver detalles', 'view_raffle')
            ->data(['url' => route('purchases.index')]);
    }
}
