<?php

namespace App\Providers;

use App\Services\OCR\MockOCRService;
use App\Services\OCR\OCRServiceInterface;
use App\Services\OCR\TesseractOCRService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

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

        $this->app->bind(\App\Services\Notifications\WhatsAppServiceInterface::class, function () {
             $provider = config('services.whatsapp.provider');
             if ($provider === 'callmebot') {
                 return new \App\Services\Notifications\Providers\CallMeBotWhatsAppService();
             }
             // Fallback for production?
             return new \App\Services\Notifications\Providers\CallMeBotWhatsAppService();
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
