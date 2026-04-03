<?php

namespace App\Actions;

use App\DTOs\ApplyCouponDTO;
use App\Exceptions\Domain\CouponNotApplicableException;
use App\Models\Coupon;
use App\Models\Order;
use App\Services\CouponValidationService;
use Illuminate\Support\Facades\DB;

class ApplyCouponToOrderAction
{
    public function __construct(
        private readonly CouponValidationService $couponService,
    ) {}

    /**
     * @throws CouponNotApplicableException
     */
    public function execute(ApplyCouponDTO $dto): Order
    {
        $result = $this->couponService->validate($dto->couponCode, $dto->raffleId);

        if (! $result['valid']) {
            throw new CouponNotApplicableException($result['message']);
        }

        return DB::transaction(function () use ($dto, $result): Order {
            $order  = Order::findOrFail($dto->orderId);
            $coupon = Coupon::where('code', $dto->couponCode)->firstOrFail();

            $discount = $result['discount_type'] === 'percent'
                ? round((float) $order->subtotal * ($result['discount_value'] / 100), 2)
                : (float) $result['discount_value'];

            $order->update([
                'coupon_id' => $coupon->id,
                'discount'  => $discount,
                'total'     => max(0, (float) $order->subtotal - $discount),
            ]);

            $coupon->increment('used_count');

            return $order->fresh();
        });
    }
}
