<?php

namespace App\Providers;

use App\Services\Notifications\Providers\CallMeBotWhatsAppService;
use App\Services\Notifications\Providers\TwilioWhatsAppService;
use App\Services\Notifications\WhatsAppServiceInterface;
use App\Services\OCR\MockOCRService;
use App\Services\OCR\OCRServiceInterface;
use App\Services\OCR\TesseractOCRService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
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
            return match (config('services.whatsapp.provider', 'callmebot')) {
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
        Vite::prefetch(concurrency: 3);

        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
