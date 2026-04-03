<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Resources\AffiliateStatsResource;
use Illuminate\Support\Str;

class AffiliateController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        if (!$user) {
             return redirect()->route('login');
        }

        $affiliate = $user->affiliate()->withCount(['clicks', 'conversions'])->withSum('conversions as commissions_sum_amount', 'commission_amount')->first();

        return Inertia::render('Affiliate/Dashboard', [
            'affiliate' => $affiliate ? new AffiliateStatsResource($affiliate) : null
        ]);
    }

    public function generateCode(Request $request)
    {
        $user = $request->user();
        
        if ($user->affiliate()->exists()) {
             return redirect()->back();
        }

        $user->affiliate()->create([
            'code' => Str::upper(Str::random(8)),
            'commission_rate' => config('affiliates.default_rate', 0.1) // 10%
        ]);

        return redirect()->back()->with('success', 'Tu código de afiliado ha sido generado.');
    }
}
