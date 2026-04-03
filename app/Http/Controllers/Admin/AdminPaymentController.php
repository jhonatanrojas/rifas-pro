<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ReviewPaymentAction;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['user', 'order.raffle'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->raffle_id, fn ($q) => $q->whereHas('order', fn ($o) => $o->where('raffle_id', $request->raffle_id)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'filters'  => $request->only(['status', 'raffle_id']),
        ]);
    }

    public function show(Payment $payment)
    {
        $payment->load(['user', 'order.raffle', 'order.tickets']);
        
        return Inertia::render('Admin/Payments/Show', [
            'payment' => $payment
        ]);
    }

    public function review(Request $request, Payment $payment, ReviewPaymentAction $action)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes'  => 'nullable|string'
        ]);

        $action->execute($payment, $request->status, $request->notes);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Pago revisado correctamente.');
    }
}
