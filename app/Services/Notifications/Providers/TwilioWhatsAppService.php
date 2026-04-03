<?php

namespace App\Services\Notifications\Providers;

use App\Models\Order;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class TwilioWhatsAppService extends AbstractWhatsAppService
{
    public function __construct(
        protected ?string $sid = null,
        protected ?string $token = null,
        protected ?string $from = null,
    ) {
        $this->sid = $this->sid ?: config('services.whatsapp.twilio.sid');
        $this->token = $this->token ?: config('services.whatsapp.twilio.token');
        $this->from = $this->from ?: config('services.whatsapp.twilio.from');
    }

    public function sendTicket(User $user, Order $order, string $receiptImageUrl): bool
    {
        $to = $user->phone ?: config('services.whatsapp.twilio.default_to');

        if (! $this->sid || ! $this->token || ! $this->from || ! $to) {
            $this->record($user, 'ticket', [
                'order_id' => $order->id,
                'reason' => 'missing_twilio_credentials',
            ], 'failed');

            return false;
        }

        $response = Http::asForm()
            ->withBasicAuth($this->sid, $this->token)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json", [
                'From' => $this->from,
                'To' => $to,
                'Body' => $this->buildTicketMessage($order, $receiptImageUrl),
            ]);

        $this->record($user, 'ticket', [
            'order_id' => $order->id,
            'receipt_url' => $receiptImageUrl,
            'response_status' => $response->status(),
        ], $response->successful() ? 'sent' : 'failed');

        return $response->successful();
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
            $phone = $user->phone ?: config('services.whatsapp.twilio.default_to');

            if (! $phone || ! $this->sid || ! $this->token || ! $this->from) {
                $this->record($user, 'draw_reminder', ['raffle_id' => $raffle->id], 'failed');
                continue;
            }

            $response = Http::asForm()
                ->withBasicAuth($this->sid, $this->token)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json", [
                    'From' => $this->from,
                    'To' => $phone,
                    'Body' => "Reminder: {$raffle->title} draw is coming up.",
                ]);

            $this->record($user, 'draw_reminder', [
                'raffle_id' => $raffle->id,
                'response_status' => $response->status(),
            ], $response->successful() ? 'sent' : 'failed');

            if ($response->successful()) {
                $sent++;
            }
        }

        return $sent;
    }

    public function sendWinnerNotification(User $user, Raffle $raffle): bool
    {
        $to = $user->phone ?: config('services.whatsapp.twilio.default_to');
        $prize = $raffle->winners()->latest()->value('prize_description') ?? 'Premio principal';
        $template = $this->template('winner', [
            'nombre' => $user->name,
            'rifa' => $raffle->title,
            'premio' => $prize,
        ]);

        if (! $this->sid || ! $this->token || ! $this->from || ! $to) {
            $this->record($user, 'winner', [
                'raffle_id' => $raffle->id,
                'reason' => 'missing_twilio_credentials',
            ], 'failed');

            return false;
        }

        $response = Http::asForm()
            ->withBasicAuth($this->sid, $this->token)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json", [
                'From' => $this->from,
                'To' => $to,
                'Body' => implode("\n", array_filter([
                    $template['title'] ?? 'WINNER ALERT',
                    $template['body'] ?? "Winner alert: {$user->name} won {$raffle->title}.",
                    'Prize: ' . $prize,
                ])),
            ]);

        $this->record($user, 'winner', [
            'raffle_id' => $raffle->id,
            'response_status' => $response->status(),
        ], $response->successful() ? 'sent' : 'failed');

        return $response->successful();
    }

    protected function buildTicketMessage(Order $order, string $receiptImageUrl): string
    {
        $template = $this->template('payment_approved', [
            'nombre' => $order->user->name,
            'rifa' => $order->raffle->title,
            'numeros' => $this->formatTickets($order),
            'total' => $order->total . ' ' . $order->currency,
        ]);

        return implode("\n", array_filter([
            $template['title'] ?? 'TICKET CONFIRMED',
            $template['body'] ?? null,
            'Draw: ' . optional($order->raffle->draw_date)->format('d/m/Y'),
            'Verify: ' . $this->verificationUrl($order),
            $receiptImageUrl ? 'Receipt: ' . $receiptImageUrl : null,
        ]));
    }
}
