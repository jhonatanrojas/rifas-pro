<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmPaymentAction;
use App\DTOs\ConfirmPaymentDTO;
use App\Http\Requests\ConfirmPaymentRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function store(ConfirmPaymentRequest $request, ConfirmPaymentAction $confirmAction)
    {
        $order = Order::findOrFail($request->order_id);
        
        $dto = new ConfirmPaymentDTO(
            orderId: $request->input('order_id'),
            method: $request->input('method'),
            amount: (float) $order->total,
            currency: $order->currency,
            referenceNumber: $request->input('reference_number'),
            receiptImage: $request->file('receipt_image')
        );

        $confirmAction->execute($dto);

        return redirect()->back()->with('success', 'Tu comprobante ha sido enviado. Esperando verificación.');
    }

    public function stripeWebhook(Request $request)
    {
        // WIP: Verify signature
        Log::info('Stripe Webhook Received', $request->all());
        return response()->json(['status' => 'received']);
    }

    public function paypalWebhook(Request $request)
    {
        // WIP: Verify signature
        Log::info('PayPal Webhook Received', $request->all());
        return response()->json(['status' => 'received']);
    }
}
