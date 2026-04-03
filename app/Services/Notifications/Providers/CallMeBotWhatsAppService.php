<?php

namespace App\Services\Notifications\Providers;

use App\Models\User;
use App\Models\Order;
use App\Models\Raffle;
use App\Services\Notifications\WhatsAppServiceInterface;
use Illuminate\Support\Facades\Http;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CallMeBotWhatsAppService implements WhatsAppServiceInterface
{
    protected string $apiKey;
    protected string $phone;

    public function __construct()
    {
        $this->apiKey = config('services.whatsapp.callmebot.apikey');
        $this->phone = config('services.whatsapp.callmebot.phone');
    }

    public function sendTicket(User $user, Order $order, string $receiptImageUrl): bool
    {
        $message = "🎟️ *¡Tu ticket está confirmado!*\n";
        $message .= "Rifa: " . $order->raffle->title . "\n";
        $message .= "Números: " . $order->tickets->pluck('number')->implode(', ') . "\n";
        $message .= "Total pagado: " . $order->total . " " . $order->currency . "\n";
        $message .= "📅 Sorteo: " . $order->raffle->draw_date->format('d/m/Y') . "\n";
        $message .= "🔗 Verifica aquí: " . route('raffles.show', $order->raffle->slug);

        return $this->sendMessage($user->phone ?? $this->phone, $message);
    }

    public function sendDrawReminder(Raffle $raffle): int
    {
         // Implementation for multiple users?
         return 0;
    }

    protected function sendMessage(string $phone, string $text): bool
    {
        $response = Http::get("https://api.callmebot.com/whatsapp.php", [
            'phone' => $phone,
            'text'  => $text,
            'apikey' => $this->apiKey,
        ]);

        return $response->successful();
    }
}
