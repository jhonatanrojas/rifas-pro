<?php

namespace App\Http\Controllers\Api;

use App\Actions\ConfirmPaymentAction;
use App\Exceptions\Domain\InsufficientTicketsException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ComboResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\RaffleResource;
use App\Http\Resources\TicketResource;
use App\Models\Raffle;
use App\Services\TicketReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class RaffleApiController extends Controller
{
    public function index()
    {
        return RaffleResource::collection(Raffle::active()->with(['combos', 'prizes'])->get());
    }

    public function show($id)
    {
        $raffle = Raffle::with(['combos', 'prizes'])->findOrFail($id);
        $raffle->combos->each->setRelation('raffle', $raffle);

        return response()->json([
            'raffle' => (new RaffleResource($raffle))->resolve(request()),
            'tickets' => TicketResource::collection($raffle->tickets()->with('raffle')->orderBy('number', 'asc')->get())->resolve(request()),
            'combos' => ComboResource::collection($raffle->combos)->resolve(request()),
        ]);
    }

    public function stats(Raffle $raffle)
    {
        return response()->json([
            'sold' => $raffle->sold_count,
            'available' => $raffle->total_tickets - $raffle->sold_count,
            'percentage' => $raffle->total_tickets > 0 ? round(($raffle->sold_count / $raffle->total_tickets) * 100, 2) : 0,
            'draw_date' => $raffle->draw_date,
        ]);
    }

    public function searchTickets(Request $request, Raffle $raffle)
    {
        $q = $request->query('q');
        $status = $request->query('status');

        $tickets = $raffle->tickets()
            ->when($q, function ($query) use ($q) {
                return $query->where('number', 'like', "%{$q}%");
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->limit(100)
            ->get();

        return TicketResource::collection($tickets);
    }

    public function purchase(
        Request $request,
        $id,
        TicketReservationService $reservationService,
        ConfirmPaymentAction $paymentAction
    ) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'reference' => 'required|string',
            'proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $raffle = Raffle::findOrFail($id);
        $user = $request->user();

        try {
            DB::beginTransaction();

            if (!$user) {
                $user = \App\Models\User::firstOrCreate(
                    ['email' => $request->email],
                    [
                        'name' => $request->name,
                        'phone' => $request->phone,
                        'password' => Hash::make(Str::random(16)),
                    ]
                );
            } else {
                if (empty($user->phone) && !empty($request->phone)) {
                    $user->phone = $request->phone;
                    $user->save();
                }
            }

            $quantity = (int) $request->quantity;
            $manualTickets = null;
            if ($request->has('manual_tickets') && $request->manual_tickets) {
                $numbers = json_decode($request->manual_tickets, true);
                if (is_array($numbers) && count($numbers) > 0) {
                    $manualTickets = $numbers;
                    $quantity = count($numbers);
                }
            }

            $reserveDto = new \App\DTOs\ReserveTicketsDTO(
                raffleId: $raffle->id,
                userId: $user->id,
                quantity: $quantity,
                manualTickets: $manualTickets
            );

            $order = $reservationService->reserve($reserveDto);

            $paymentDto = new \App\DTOs\ConfirmPaymentDTO(
                orderId: $order->id,
                method: $request->payment_method,
                amount: (float) $order->total,
                currency: $order->currency,
                referenceNumber: $request->reference,
                receiptImage: $request->file('proof')
            );

            $paymentAction->execute($paymentDto);

            $order->loadMissing(['raffle', 'tickets', 'payment']);
            $verificationUrl = URL::temporarySignedRoute('receipts.verify', now()->addDays(30), [
                'order' => $order->id,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Tu pago fue recibido y se encuentra en revision.',
                'verification_url' => $verificationUrl,
                'tickets_url' => $request->user() ? route('purchases.index') : null,
                'order' => (new OrderResource($order))->resolve($request),
            ]);
        } catch (InsufficientTicketsException $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al procesar el pago: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function winners($id)
    {
        return response()->json(['winners' => []]);
    }
}
