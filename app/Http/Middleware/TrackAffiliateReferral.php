<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cookie;

class TrackAffiliateReferral
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('ref')) {
            $code = $request->query('ref');
            
            // Si el código de referido existe en el sistema
            if (\App\Models\Affiliate::where('code', $code)->exists()) {
                // Almacenar en una cookie por 30 días
                Cookie::queue('affiliate_code', $code, 60 * 24 * 30);
            }
        }

        return $next($request);
    }
}
