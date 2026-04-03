<?php

namespace App\Notifications;

use App\Models\Raffle;
use App\Services\NotificationTemplateService;
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
        $prize = $this->raffle->winners()->latest()->value('prize_description') ?? 'Premio principal';
        $template = app(NotificationTemplateService::class)->render('push', 'winner', [
            'nombre' => $notifiable->name ?? '',
            'rifa' => $this->raffle->title,
            'premio' => $prize,
        ]);

        return (new WebPushMessage)
            ->title($template['title'])
            ->icon('/pwa-192x192.png')
            ->badge('/pwa-192x192.png')
            ->body($template['body'])
            ->action('Ver detalles', 'view_raffle')
            ->data(['url' => route('purchases.index')]);
    }
}
