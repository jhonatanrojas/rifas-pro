<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ReviewPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\BulkReviewPaymentsRequest;
use App\Http\Requests\ReviewPaymentRequest;
use App\Models\Raffle;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'order.raffle'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->method, fn ($q) => $q->where('method', $request->method))
            ->when($request->raffle_id, fn ($q) => $q->whereHas('order', fn ($o) => $o->where('raffle_id', $request->raffle_id)))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to));

        if ($request->input('export') === 'csv') {
            $payments = $query->latest()->get();

            return Response::streamDownload(function () use ($payments) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['ID', 'Usuario', 'Rifa', 'Metodo', 'Monto', 'Moneda', 'Estado', 'Referencia', 'Notas', 'Creado']);

                foreach ($payments as $payment) {
                    fputcsv($handle, [
                        $payment->id,
                        $payment->user?->name,
                        $payment->order?->raffle?->title,
                        $payment->method,
                        $payment->amount,
                        $payment->currency,
                        $payment->status,
                        $payment->reference_number,
                        $payment->notes,
                        optional($payment->created_at)->toDateTimeString(),
                    ]);
                }

                fclose($handle);
            }, 'payments.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename=payments.csv',
            ]);
        }

        $payments = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'filters'  => $request->only(['status', 'method', 'raffle_id', 'date_from', 'date_to']),
            'raffles'  => Raffle::select('id', 'title')->orderBy('title')->get(),
        ]);
    }

    public function show(Payment $payment)
    {
        $payment->load(['user', 'order.raffle', 'order.tickets', 'reviewer']);
        
        return Inertia::render('Admin/Payments/Show', [
            'payment' => $payment
        ]);
    }

    public function review(ReviewPaymentRequest $request, Payment $payment, ReviewPaymentAction $action)
    {
        $action->execute($payment, $request->status, $request->notes, $request->user()?->id);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Pago revisado correctamente.');
    }

    public function bulkReview(BulkReviewPaymentsRequest $request, ReviewPaymentAction $action)
    {
        $payments = Payment::with(['order.user', 'order.raffle', 'order.tickets'])
            ->whereIn('id', $request->payment_ids)
            ->get();

        foreach ($payments as $payment) {
            $action->execute($payment, $request->status, null, $request->user()?->id);
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Pagos procesados en lote correctamente.');
    }
}
