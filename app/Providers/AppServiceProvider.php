<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use App\Services\Notifications\Providers\CallMeBotWhatsAppService;
use App\Services\Notifications\Providers\TwilioWhatsAppService;
use App\Services\Notifications\WhatsAppServiceInterface;
use App\Services\OCR\MockOCRService;
use App\Services\OCR\OCRServiceInterface;
use App\Services\OCR\TesseractOCRService;
use App\Services\SystemSettingsService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OCRServiceInterface::class, function () {
            return config('app.env') === 'testing'
                ? new MockOCRService()
                : new TesseractOCRService();
        });

        $this->app->bind(WhatsAppServiceInterface::class, function () {
            $provider = app(SystemSettingsService::class)->get('whatsapp_provider', config('services.whatsapp.provider', 'callmebot'));

            return match ($provider) {
                'twilio' => new TwilioWhatsAppService(),
                default => new CallMeBotWhatsAppService(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $redirect = session('auth.redirect', route('purchases.index', absolute: false));

            if (!is_string($redirect) || !str_starts_with($redirect, '/')) {
                $redirect = route('purchases.index', absolute: false);
            }

            return route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
                'redirect' => $redirect,
            ], false);
        });

        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
