<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Raffle;
use Illuminate\Http\Request;

class RaffleApiController extends Controller
{
    public function index()
    {
        return Raffle::active()->with('combos')->get();
    }

    public function show($id)
    {
        $raffle = Raffle::with(['combos'])->findOrFail($id);
        
        // Cargar los tickets y su estado
        $tickets = $raffle->tickets()
            ->select('id', 'number', 'status')
            ->orderBy('number', 'asc')
            ->get();
            
        return response()->json([
            'raffle' => $raffle,
            'tickets' => $tickets,
            'combos' => $raffle->combos,
        ]);
    }

    public function reserve(Request $request, $id)
    {
        // ... Logica de reserva que se desarrollará en el Checkout
        return response()->json(['message' => 'Reserve logic pending']);
    }

    public function purchase(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'reference' => 'required|string',
            'proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120' // max 5mb
        ]);

        $raffle = Raffle::findOrFail($id);
        $user = $request->user();

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // 1. User Management (Guest logic)
            if (!$user) {
                $user = \App\Models\User::firstOrCreate(
                    ['email' => $request->email],
                    [
                        'name' => $request->name,
                        'phone' => $request->phone,
                        'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16))
                    ]
                );
            } else {
                if (empty($user->phone) && !empty($request->phone)) {
                    $user->phone = $request->phone;
                    $user->save();
                }
            }

            // 2. Select Tickets
            $quantity = (int) $request->quantity;
            $tickets = collect();

            if ($request->has('manual_tickets') && $request->manual_tickets) {
                $numbers = json_decode($request->manual_tickets, true);
                if (is_array($numbers) && count($numbers) > 0) {
                    $tickets = $raffle->tickets()
                        ->whereIn('number', $numbers)
                        ->where('status', 'available')
                        ->lockForUpdate()
                        ->get();

                    if ($tickets->count() !== count($numbers)) {
                        return response()->json(['message' => 'Algunos tickets seleccionados ya no están disponibles.'], 422);
                    }
                    $quantity = count($numbers);
                }
            } else {
                $tickets = $raffle->tickets()
                    ->where('status', 'available')
                    ->inRandomOrder()
                    ->limit($quantity)
                    ->lockForUpdate()
                    ->get();

                if ($tickets->count() < $quantity) {
                    return response()->json(['message' => 'No hay suficientes tickets disponibles para la cantidad solicitada.'], 422);
                }
            }

            // 3. Security Check: Calculate real total
            $rafflePrice = $raffle->ticket_price;
            $subtotal = $quantity * $rafflePrice;
            $discount = 0;
            
            $combos = $raffle->combos()->orderByDesc('quantity')->get();
            foreach ($combos as $combo) {
                if ($quantity >= $combo->quantity) {
                    $comboSubtotal = $combo->quantity * $rafflePrice;
                    $discountPerCombo = $comboSubtotal - $combo->price;
                    $comboMultiplier = floor($quantity / $combo->quantity);
                    $discountC = $discountPerCombo * $comboMultiplier;
                    if ($discountC > $discount) {
                        $discount = $discountC;
                    }
                }
            }
            $calculatedTotal = $subtotal - $discount;

            // 4. Create Order
            $order = \App\Models\Order::create([
                'user_id' => $user->id,
                'raffle_id' => $raffle->id,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $calculatedTotal,
                'currency' => $raffle->currency,
            ]);

            // 5. Update and attach Tickets
            $ticketIds = $tickets->pluck('id')->toArray();
            $order->tickets()->attach($ticketIds);
            
            \App\Models\Ticket::whereIn('id', $ticketIds)->update([
                'status' => 'reserved',
                'user_id' => $user->id,
                'order_id' => $order->id,
                'reserved_at' => now(),
                'reserved_until' => now()->addHours(12) // User has 12 hours window before cancelation
            ]);

            // Update global stats (sold tracking approx, can be refined to 'reserved_count')
            //$raffle->increment('sold_count', $quantity); // Solo incrementar sold cuando se apruebe.

            // 6. Handle Image Proof
            $path = $request->file('proof')->store('receipts', 'public');

            // 7. Create Payment record attached to Order
            $order->payment()->create([
                'method' => strtolower($request->payment_method),
                'amount' => $calculatedTotal,
                'currency' => $raffle->currency,
                'reference_number' => $request->reference,
                'receipt_image_path' => $path,
                'status' => 'pending'
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Compra registrada y en verificación'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    public function winners($id)
    {
        return response()->json(['winners' => []]);
    }
}
