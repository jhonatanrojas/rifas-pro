<?php

namespace App\Services\Notifications\Providers;

use App\Models\Order;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class CallMeBotWhatsAppService extends AbstractWhatsAppService
{
    public function __construct(
        protected ?string $apiKey = null,
        protected ?string $phone = null,
    ) {
        $this->apiKey = $this->apiKey ?: (string) config('services.whatsapp.callmebot.apikey', '');
        $this->phone = $this->phone ?: (string) config('services.whatsapp.callmebot.phone', '');
    }

    public function sendTicket(User $user, Order $order, string $receiptImageUrl): bool
    {
        $message = implode("\n", array_filter([
            'TICKET CONFIRMED',
            'Raffle: ' . $order->raffle->title,
            'Numbers: ' . $this->formatTickets($order),
            'Amount: ' . $order->total . ' ' . $order->currency,
            'Draw: ' . optional($order->raffle->draw_date)->format('d/m/Y'),
            'Verify: ' . $this->verificationUrl($order),
            $receiptImageUrl ? 'Receipt: ' . $receiptImageUrl : null,
        ]));

        $success = $this->sendMessage($user->phone ?: $this->phone, $message);

        $this->record($user, 'ticket', [
            'order_id' => $order->id,
            'receipt_url' => $receiptImageUrl,
        ], $success ? 'sent' : 'failed');

        return $success;
    }

    public function sendDrawReminder(Raffle $raffle): int
    {
        $users = Ticket::query()
            ->where('raffle_id', $raffle->id)
            ->whereNotNull('user_id')
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id');

        $sent = 0;

        foreach ($users as $user) {
            $phone = $user->phone ?: $this->phone;

            if (! $phone || ! $this->apiKey) {
                $this->record($user, 'draw_reminder', ['raffle_id' => $raffle->id], 'failed');
                continue;
            }

            $success = $this->sendMessage($phone, "Reminder: {$raffle->title} draw is coming up.");

            $this->record($user, 'draw_reminder', [
                'raffle_id' => $raffle->id,
            ], $success ? 'sent' : 'failed');

            if ($success) {
                $sent++;
            }
        }

        return $sent;
    }

    public function sendWinnerNotification(User $user, Raffle $raffle): bool
    {
        $message = implode("\n", array_filter([
            'WINNER ALERT',
            "Hello {$user->name},",
            "Your ticket won in {$raffle->title}.",
            'Prize: ' . ($raffle->prize_description ?? 'Main Prize'),
            'Details: ' . route('raffles.show', $raffle->slug),
        ]));

        $success = $this->sendMessage($user->phone ?: $this->phone, $message);

        $this->record($user, 'winner', [
            'raffle_id' => $raffle->id,
        ], $success ? 'sent' : 'failed');

        return $success;
    }

    protected function sendMessage(string $phone, string $text): bool
    {
        if (! $this->apiKey || ! $phone) {
            return false;
        }

        $response = Http::get('https://api.callmebot.com/whatsapp.php', [
            'phone' => $phone,
            'text' => $text,
            'apikey' => $this->apiKey,
        ]);

        return $response->successful();
    }
}
