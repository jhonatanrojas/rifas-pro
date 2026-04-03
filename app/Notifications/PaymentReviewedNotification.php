<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Services\NotificationTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PaymentReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Payment $payment,
        protected string $status,
    ) {
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $approved = $this->status === 'approved';
        $template = app(NotificationTemplateService::class)->render('push', $approved ? 'payment_approved' : 'payment_rejected', [
            'nombre' => $notifiable->name ?? '',
            'rifa' => $this->payment->order->raffle->title,
            'numeros' => $this->payment->order->tickets->pluck('number')->implode(', '),
            'total' => $this->payment->amount . ' ' . $this->payment->currency,
            'motivo' => $this->payment->notes ?? '',
        ]);

        return (new WebPushMessage)
            ->title($template['title'])
            ->icon('/pwa-192x192.png')
            ->badge('/pwa-192x192.png')
            ->body($template['body'])
            ->action('Ver mis tickets', 'view_tickets')
            ->data([
                'url' => route('purchases.index'),
                'payment_id' => $this->payment->id,
                'status' => $this->status,
            ]);
    }
}
