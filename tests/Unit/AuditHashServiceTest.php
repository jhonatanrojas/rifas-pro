<?php

namespace Tests\Unit;

use App\Models\Raffle;
use App\Models\Ticket;
use App\Services\AuditHashService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditHashServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditHashService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuditHashService();
    }

    public function test_hash_is_deterministic(): void
    {
        $raffle = Raffle::factory()->create(['total_tickets' => 10, 'sold_count' => 3]);

        Ticket::factory()->count(3)->create([
            'raffle_id' => $raffle->id,
            'status'    => 'sold',
        ]);

        $hash1 = $this->service->generateParticipantsHash($raffle->id);
        $hash2 = $this->service->generateParticipantsHash($raffle->id);

        $this->assertSame($hash1, $hash2);
    }

    public function test_hash_is_sha256(): void
    {
        $raffle = Raffle::factory()->create();

        $hash = $this->service->generateParticipantsHash($raffle->id);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);
    }

    public function test_hash_changes_when_tickets_change(): void
    {
        $raffle = Raffle::factory()->create(['total_tickets' => 10, 'sold_count' => 2]);

        Ticket::factory()->create(['raffle_id' => $raffle->id, 'status' => 'sold', 'number' => 1]);
        $hash1 = $this->service->generateParticipantsHash($raffle->id);

        Ticket::factory()->create(['raffle_id' => $raffle->id, 'status' => 'sold', 'number' => 2]);
        $hash2 = $this->service->generateParticipantsHash($raffle->id);

        $this->assertNotSame($hash1, $hash2);
    }

    public function test_snapshot_is_ordered_by_number(): void
    {
        $raffle = Raffle::factory()->create(['total_tickets' => 10, 'sold_count' => 3]);

        Ticket::factory()->create(['raffle_id' => $raffle->id, 'status' => 'sold', 'number' => 5]);
        Ticket::factory()->create(['raffle_id' => $raffle->id, 'status' => 'sold', 'number' => 1]);
        Ticket::factory()->create(['raffle_id' => $raffle->id, 'status' => 'sold', 'number' => 3]);

        $snapshot = $this->service->buildParticipantsSnapshot($raffle->id);
        $numbers  = array_column($snapshot, 'number');

        $this->assertSame([1, 3, 5], $numbers);
    }
}
