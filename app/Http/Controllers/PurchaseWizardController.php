<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Raffle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Resources\RaffleResource;
use App\Http\Resources\OrderResource;

class PurchaseWizardController extends Controller
{
    /**
     * Paso 1: Selección de números.
     * 
     * @param Raffle $raffle
     * @return \Inertia\Response
     * 
     * Props:
     * - raffle: RaffleResource
     * - step: number
     */
    public function showNumbers(Raffle $raffle)
    {
        return Inertia::render('Raffle/Show', [
            'raffle' => new RaffleResource($raffle),
            'step' => 1
        ]);
    }

    public function showPayment(Order $order)
    {
        $order->load(['raffle', 'tickets']);
        
        return Inertia::render('Checkout/Payment', [
            'order' => new OrderResource($order),
            'payment_methods' => config('payments.methods', [])
        ]);
    }

    public function showConfirmation(Order $order)
    {
        $order->load(['raffle', 'tickets', 'payment']);
        
        return Inertia::render('Checkout/Confirmation', [
            'order' => new OrderResource($order)
        ]);
    }
}
