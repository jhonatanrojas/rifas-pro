<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PurchasesController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = Order::with(['raffle', 'tickets', 'payment'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('MyTickets/Index', [
            'orders' => $orders,
        ]);
    }
}
