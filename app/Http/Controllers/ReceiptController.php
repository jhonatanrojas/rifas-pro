<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReceiptController extends Controller
{
    public function verify(Request $request, Order $order)
    {
        $order->load(['user', 'raffle', 'tickets', 'payment']);

        $metadata = $order->metadata ?? [];
        $receiptPath = $metadata['receipt_path'] ?? null;
        $receiptUrl = $receiptPath ? Storage::disk('public')->url($receiptPath) : ($metadata['receipt_url'] ?? null);

        return view('receipts.verify', [
            'order' => $order,
            'receiptUrl' => $receiptUrl,
            'receiptHash' => $metadata['receipt_hash'] ?? hash_hmac('sha256', $order->id . '|' . $order->created_at?->timestamp, (string) config('app.key')),
            'verifiedAt' => now(),
        ]);
    }
}
