<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Models\Order;
use App\Models\Raffle;

interface WhatsAppServiceInterface
{
    public function sendTicket(User $user, Order $order, string $receiptImageUrl): bool;
    public function sendDrawReminder(Raffle $raffle): int;
}
