<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Raffle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminParticipantController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'raffle', 'tickets', 'payment'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->raffle_id, fn ($q) => $q->where('raffle_id', $request->raffle_id))
            ->when($request->search, function ($q) use ($request) {
                $search = trim((string) $request->search);
                $q->where(function ($inner) use ($search) {
                    $inner->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhereHas('payment', function ($paymentQuery) use ($search) {
                        $paymentQuery->where('reference_number', 'like', "%{$search}%");
                    })->orWhereHas('tickets', function ($ticketQuery) use ($search) {
                        $ticketQuery->whereRaw('CAST(number AS TEXT) LIKE ?', ["%{$search}%"]);
                    });
                });
            });

        $orders = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Participants/Index', [
            'orders' => OrderResource::collection($orders),
            'filters' => $request->only(['status', 'raffle_id', 'search']),
            'raffles' => Raffle::select('id', 'title')->orderBy('title')->get(),
        ]);
    }
}
