<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class TicketReceiptGenerator
{
    public function generate(Order $order): array
    {
        $path = "receipts/" . now()->format('Y/m') . "/{$order->id}.pdf";
        $order->loadMissing(['user', 'raffle', 'tickets']);
        $verificationUrl = URL::temporarySignedRoute('receipts.verify', now()->addDays(30), [
            'order' => $order->id,
        ]);
        $receiptHash = hash_hmac('sha256', $order->id . '|' . $order->created_at?->timestamp, (string) config('app.key'));

        $pdf = Pdf::loadView('receipts.ticket', [
            'order' => $order,
            'verificationUrl' => $verificationUrl,
            'receiptHash' => $receiptHash,
        ]);

        Storage::disk('public')->put($path, $pdf->output());

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'verification_url' => $verificationUrl,
            'hash' => $receiptHash,
        ];
    }

    public function generatePDF(Order $order): string
    {
        return $this->generate($order)['path'];
    }
}
