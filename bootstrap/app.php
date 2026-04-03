<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'affiliate' => \App\Http\Middleware\TrackAffiliateReferral::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\App\Exceptions\Domain\TicketAlreadySoldException $e, \Illuminate\Http\Request $request) {
            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422)
                : back()->withErrors(['message' => $e->getMessage()]);
        });

        $exceptions->render(function (\App\Exceptions\Domain\InsufficientTicketsException $e, \Illuminate\Http\Request $request) {
            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422)
                : back()->withErrors(['message' => $e->getMessage()]);
        });

        $exceptions->render(function (\App\Exceptions\Domain\RaffleNotActiveException $e, \Illuminate\Http\Request $request) {
            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422)
                : back()->withErrors(['message' => $e->getMessage()]);
        });

        $exceptions->render(function (\App\Exceptions\Domain\CouponExpiredException $e, \Illuminate\Http\Request $request) {
            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422)
                : back()->withErrors(['message' => $e->getMessage()]);
        });

        $exceptions->render(function (\App\Exceptions\Domain\CouponMaxUsesReachedException $e, \Illuminate\Http\Request $request) {
            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422)
                : back()->withErrors(['message' => $e->getMessage()]);
        });

        $exceptions->render(function (\App\Exceptions\Domain\CouponNotApplicableException $e, \Illuminate\Http\Request $request) {
            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422)
                : back()->withErrors(['message' => $e->getMessage()]);
        });

        $exceptions->render(function (\App\Exceptions\Domain\DrawAlreadyExecutedException $e, \Illuminate\Http\Request $request) {
            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422)
                : back()->withErrors(['message' => $e->getMessage()]);
        });

        $exceptions->render(function (\App\Exceptions\Domain\PaymentAlreadyReviewedException $e, \Illuminate\Http\Request $request) {
            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422)
                : back()->withErrors(['message' => $e->getMessage()]);
        });

        $exceptions->render(function (\App\Exceptions\Domain\TicketReservationExpiredException $e, \Illuminate\Http\Request $request) {
            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422)
                : back()->withErrors(['message' => $e->getMessage()]);
        });
    })->create();
