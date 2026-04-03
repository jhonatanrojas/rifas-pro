<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCouponRequest;
use App\Services\CouponValidationService;
use Illuminate\Http\JsonResponse;

class CouponController extends Controller
{
    public function validate(
        ValidateCouponRequest $request, 
        CouponValidationService $couponService
    ): JsonResponse {
        $result = $couponService->validate(
            $request->coupon_code, 
            $request->raffle_id
        );

        return response()->json($result);
    }
}
