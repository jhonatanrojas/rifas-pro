<?php

namespace App\Actions;

use App\DTOs\ReserveTicketsDTO;
use App\Exceptions\Domain\RaffleNotActiveException;
use App\Models\Order;
use App\Models\Raffle;
use App\Services\CouponValidationService;
use App\Services\CurrencyConverterService;
use App\Services\TicketReservationService;
use Illuminate\Support\Facades\DB;

class ReserveTicketsAction
{
    public function __construct(
        private readonly TicketReservationService $reservationService,
        private readonly CouponValidationService  $couponService,
        private readonly CurrencyConverterService $currencyService,
    ) {}

    /**
     * @throws RaffleNotActiveException
     */
    public function execute(ReserveTicketsDTO $dto): Order
    {
        $raffle = Raffle::findOrFail($dto->raffleId);

        if ($raffle->status !== 'active') {
            throw new RaffleNotActiveException();
        }

        return DB::transaction(function () use ($dto, $raffle): Order {
            $order = $this->reservationService->reserve($dto);

            if ($dto->couponCode) {
                $result = $this->couponService->validate($dto->couponCode, $dto->raffleId);

                if ($result['valid']) {
                    $discount = $result['discount_type'] === 'percent'
                        ? round((float) $order->subtotal * ($result['discount_value'] / 100), 2)
                        : (float) $result['discount_value'];

                    $order->update([
                        'discount' => $discount,
                        'total'    => max(0, (float) $order->subtotal - $discount),
                    ]);
                }
            }

            if ($raffle->exchange_rate) {
                $order->update([
                    'exchange_rate_snapshot' => $raffle->exchange_rate,
                ]);
            }

            return $order->load(['tickets', 'raffle', 'coupon']);
        });
    }
}
