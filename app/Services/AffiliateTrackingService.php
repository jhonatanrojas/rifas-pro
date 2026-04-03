<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Order;
use App\Models\ReferralConversion;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AffiliateTrackingService
{
    private const COOKIE_NAME  = 'affiliate_code';
    private const CACHE_PREFIX = 'affiliate_click_';
    private const CACHE_TTL    = 86400 * 30; // 30 days

    public function trackClick(string $code, Request $request): void
    {
        $affiliate = Affiliate::where('code', $code)->first();

        if (! $affiliate) {
            return;
        }

        $key = self::CACHE_PREFIX . $code . '_' . ($request->user()?->id ?? $request->ip());
        Cache::put($key, ['code' => $code, 'tracked_at' => now()->toIso8601String()], self::CACHE_TTL);
    }

    public function resolveCodeFromRequest(Request $request): ?string
    {
        // Priority: query param → cookie → cache (IP-based)
        if ($request->has('ref')) {
            return $request->query('ref');
        }

        if ($request->hasCookie(self::COOKIE_NAME)) {
            return $request->cookie(self::COOKIE_NAME);
        }

        return null;
    }

    public function convertReferral(string $code, User $user, Order $order): ?ReferralConversion
    {
        $affiliate = Affiliate::where('code', $code)->first();

        if (! $affiliate) {
            return null;
        }

        // Avoid duplicate conversions for the same order
        if (ReferralConversion::where('order_id', $order->id)->exists()) {
            return null;
        }

        $commission = round((float) $order->total * (float) $affiliate->commission_rate, 4);

        return ReferralConversion::create([
            'affiliate_id'      => $affiliate->id,
            'referred_user_id'  => $user->id,
            'order_id'          => $order->id,
            'commission_amount' => $commission,
            'status'            => 'pending',
        ]);
    }

    public function creditCommission(ReferralConversion $conversion): WalletTransaction
    {
        return DB::transaction(function () use ($conversion): WalletTransaction {
            $affiliate = $conversion->affiliate;

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $affiliate->user_id],
                ['balance' => 0, 'currency' => 'USD'],
            );

            $wallet->increment('balance', $conversion->commission_amount);
            $affiliate->increment('total_earned', $conversion->commission_amount);
            $conversion->update(['status' => 'paid']);

            return WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'type'           => 'credit',
                'amount'         => $conversion->commission_amount,
                'description'    => "Comisión por referido — Orden #{$conversion->order_id}",
                'reference_type' => ReferralConversion::class,
                'reference_id'   => $conversion->id,
            ]);
        });
    }
}
