<?php

namespace App\Services;

use App\Models\Coupon;

class CouponValidationService
{
    public function validate(string $code, int $raffleId): array
    {
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return ['valid' => false, 'message' => 'Cupón no encontrado.'];
        }

        $now = now();

        if ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            return ['valid' => false, 'message' => 'El cupón aún no es válido.'];
        }

        if ($coupon->valid_until && $now->gt($coupon->valid_until)) {
            return ['valid' => false, 'message' => 'El cupón ha expirado.'];
        }

        if ($coupon->used_count >= $coupon->max_uses) {
            return ['valid' => false, 'message' => 'Este cupón ha agotado sus usos permitidos.'];
        }

        if ($coupon->raffle_id && $coupon->raffle_id !== $raffleId) {
            return ['valid' => false, 'message' => 'Este cupón no aplica para esta rifa.'];
        }

        return [
            'valid'          => true,
            'discount_type'  => $coupon->type,
            'discount_value' => (float) $coupon->value,
            'message'        => 'Cupón aplicado correctamente.',
        ];
    }
}
