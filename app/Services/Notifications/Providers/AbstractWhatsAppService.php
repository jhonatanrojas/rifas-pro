<?php

namespace App\Services\Notifications\Providers;

use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Raffle;
use App\Models\User;
use App\Services\Notifications\WhatsAppServiceInterface;
use Illuminate\Support\Facades\URL;

abstract class AbstractWhatsAppService implements WhatsAppServiceInterface
{
    protected function verificationUrl(Order $order): string
    {
        return URL::temporarySignedRoute('receipts.verify', now()->addDays(30), [
            'order' => $order->id,
        ]);
    }

    protected function record(User $user, string $type, array $payload, string $status = 'sent'): void
    {
        NotificationLog::create([
            'user_id' => $user->id,
            'channel' => 'whatsapp',
            'type' => $type,
            'payload' => $payload,
            'status' => $status,
            'sent_at' => $status === 'sent' ? now() : null,
        ]);
    }

    protected function formatTickets(Order $order): string
    {
        return $order->tickets
            ->pluck('number')
            ->map(fn ($number) => str_pad((string) $number, 4, '0', STR_PAD_LEFT))
            ->implode(', ');
    }

    abstract public function sendTicket(User $user, Order $order, string $receiptImageUrl): bool;

    abstract public function sendDrawReminder(Raffle $raffle): int;

    abstract public function sendWinnerNotification(User $user, Raffle $raffle): bool;
}
