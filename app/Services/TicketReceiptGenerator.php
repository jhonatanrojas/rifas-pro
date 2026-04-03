<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class TicketReceiptGenerator
{
    public function generatePDF(Order $order): string
    {
        $order->load(['user', 'raffle', 'tickets']);
        
        $pdf = Pdf::loadView('receipts.ticket', ['order' => $order]);
        
        $path = "receipts/" . now()->format('Y/m') . "/{$order->id}.pdf";
        
        Storage::put("public/{$path}", $pdf->output());
        
        return $path;
    }
}
