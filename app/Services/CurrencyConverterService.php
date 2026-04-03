<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyConverterService
{
    private const CACHE_TTL = 3600; // 1 hour

    public function convert(float $amount, string $from, string $to, ?float $rate = null): float
    {
        if ($from === $to) {
            return $amount;
        }

        $rate ??= $this->getRate($from, $to);

        return round($amount * $rate, 4);
    }

    public function getRate(string $from, string $to): float
    {
        $cacheKey = "exchange_rate_{$from}_{$to}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($from, $to) {
            return $this->fetchRateFromApi($from, $to);
        });
    }

    private function fetchRateFromApi(string $from, string $to): float
    {
        $apiUrl = config('currency.api_url');
        $apiKey = config('currency.api_key');

        if (! $apiUrl || ! $apiKey) {
            Log::warning("CurrencyConverterService: no API configured for {$from}->{$to}, returning 1.0");
            return 1.0;
        }

        try {
            $response = Http::timeout(5)->get($apiUrl, [
                'base'    => $from,
                'symbols' => $to,
                'apikey'  => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return (float) ($data['rates'][$to] ?? 1.0);
            }
        } catch (\Throwable $e) {
            Log::error("CurrencyConverterService: error fetching rate {$from}->{$to}: " . $e->getMessage());
        }

        return 1.0;
    }
}
