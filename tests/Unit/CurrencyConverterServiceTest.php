<?php

namespace Tests\Unit;

use App\Services\CurrencyConverterService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CurrencyConverterServiceTest extends TestCase
{
    private CurrencyConverterService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CurrencyConverterService();
    }

    public function test_same_currency_returns_same_amount(): void
    {
        $result = $this->service->convert(100.0, 'USD', 'USD');

        $this->assertSame(100.0, $result);
    }

    public function test_convert_uses_provided_rate(): void
    {
        $result = $this->service->convert(100.0, 'USD', 'VES', 36.5);

        $this->assertEqualsWithDelta(3650.0, $result, 0.0001);
    }

    public function test_convert_fetches_rate_from_cache(): void
    {
        Cache::put('exchange_rate_USD_COP', 4000.0, 3600);

        $result = $this->service->convert(10.0, 'USD', 'COP');

        $this->assertEqualsWithDelta(40000.0, $result, 0.0001);
        Cache::forget('exchange_rate_USD_COP');
    }

    public function test_falls_back_to_1_when_api_not_configured(): void
    {
        config(['currency.api_url' => null, 'currency.api_key' => null]);
        Cache::forget('exchange_rate_USD_VES');

        $result = $this->service->convert(50.0, 'USD', 'VES');

        $this->assertSame(50.0, $result);
    }

    public function test_convert_rounds_to_4_decimal_places(): void
    {
        $result = $this->service->convert(1.0, 'USD', 'VES', 3.14159265);

        $this->assertSame(3.1416, $result);
    }

    public function test_falls_back_to_1_on_api_error(): void
    {
        config(['currency.api_url' => 'https://api.example.com/rates', 'currency.api_key' => 'test-key']);
        Cache::forget('exchange_rate_USD_VES');

        Http::fake(['*' => Http::response(null, 500)]);

        $result = $this->service->convert(100.0, 'USD', 'VES');

        $this->assertSame(100.0, $result);
    }
}
