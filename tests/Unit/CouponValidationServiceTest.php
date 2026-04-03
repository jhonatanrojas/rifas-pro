<?php

namespace Tests\Unit;

use App\Models\Coupon;
use App\Models\Raffle;
use App\Services\CouponValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private CouponValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CouponValidationService();
    }

    public function test_returns_invalid_for_unknown_code(): void
    {
        $raffle = Raffle::factory()->create();
        $result = $this->service->validate('NONEXISTENT', $raffle->id);

        $this->assertFalse($result['valid']);
    }

    public function test_returns_invalid_for_expired_coupon(): void
    {
        $raffle = Raffle::factory()->create();
        $coupon = Coupon::factory()->create([
            'raffle_id'   => null,
            'valid_until' => now()->subDay(),
        ]);

        $result = $this->service->validate($coupon->code, $raffle->id);

        $this->assertFalse($result['valid']);
    }

    public function test_returns_invalid_when_max_uses_reached(): void
    {
        $raffle = Raffle::factory()->create();
        $coupon = Coupon::factory()->create([
            'raffle_id'  => null,
            'max_uses'   => 10,
            'used_count' => 10,
        ]);

        $result = $this->service->validate($coupon->code, $raffle->id);

        $this->assertFalse($result['valid']);
    }

    public function test_returns_invalid_when_not_applicable_to_raffle(): void
    {
        $raffle      = Raffle::factory()->create();
        $otherRaffle = Raffle::factory()->create();
        $coupon      = Coupon::factory()->create(['raffle_id' => $otherRaffle->id]);

        $result = $this->service->validate($coupon->code, $raffle->id);

        $this->assertFalse($result['valid']);
    }

    public function test_returns_valid_for_active_global_coupon(): void
    {
        $raffle = Raffle::factory()->create();
        $coupon = Coupon::factory()->create([
            'raffle_id'   => null,
            'valid_from'  => now()->subDay(),
            'valid_until' => now()->addDay(),
            'max_uses'    => 100,
            'used_count'  => 0,
            'type'        => 'percent',
            'value'       => 10,
        ]);

        $result = $this->service->validate($coupon->code, $raffle->id);

        $this->assertTrue($result['valid']);
        $this->assertSame('percent', $result['discount_type']);
        $this->assertSame(10.0, $result['discount_value']);
    }

    public function test_returns_invalid_when_coupon_not_yet_valid(): void
    {
        $raffle = Raffle::factory()->create();
        $coupon = Coupon::factory()->create([
            'raffle_id'  => null,
            'valid_from' => now()->addDay(),
        ]);

        $result = $this->service->validate($coupon->code, $raffle->id);

        $this->assertFalse($result['valid']);
    }
}
