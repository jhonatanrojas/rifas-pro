<?php

namespace App\Jobs;

use App\Models\Winner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppTicketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 60, 300];

    public function __construct(
        public readonly int $winnerId,
    ) {}

    public function handle(): void
    {
        $winner = Winner::with(['user', 'raffle', 'ticket'])->find($this->winnerId);

        if (! $winner) {
            Log::warning("SendWhatsAppTicketJob: winner #{$this->winnerId} not found.");
            return;
        }

        $phone = $winner->user?->phone;

        if (! $phone) {
            Log::warning("SendWhatsAppTicketJob: user has no phone for winner #{$this->winnerId}.");
            return;
        }

        // WhatsApp integration (Twilio / Meta API) to be configured via config/whatsapp.php
        // $receiptPath = $winner->raffle->orders()
        //     ->whereHas('tickets', fn ($q) => $q->where('id', $winner->ticket_id))
        //     ->value('metadata.receipt_path');

        Log::info("SendWhatsAppTicketJob: would send WhatsApp to {$phone} for winner #{$this->winnerId}.");

        $winner->update(['notified_at' => now()]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendWhatsAppTicketJob failed for winner #{$this->winnerId}: " . $exception->getMessage());
    }
}
