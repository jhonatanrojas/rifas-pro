<?php

namespace Tests\Unit;

use App\DTOs\ExecuteDrawDTO;
use App\Exceptions\Domain\DrawAlreadyExecutedException;
use App\Models\DrawAudit;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AuditHashService;
use App\Services\DrawService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DrawServiceTest extends TestCase
{
    use RefreshDatabase;

    private DrawService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->service = new DrawService(new AuditHashService());
    }

    public function test_throws_if_draw_already_executed(): void
    {
        $this->expectException(DrawAlreadyExecutedException::class);

        $admin  = User::factory()->create();
        $raffle = Raffle::factory()->create(['status' => 'active']);

        DrawAudit::factory()->create(['raffle_id' => $raffle->id, 'created_by' => $admin->id]);

        $dto = new ExecuteDrawDTO($raffle->id, $admin->id, 'Premio principal');
        $this->service->execute($dto);
    }

    public function test_returns_empty_collection_when_no_sold_tickets(): void
    {
        $admin  = User::factory()->create();
        $raffle = Raffle::factory()->create(['status' => 'active']);

        $dto     = new ExecuteDrawDTO($raffle->id, $admin->id, 'Premio');
        $winners = $this->service->execute($dto);

        $this->assertTrue($winners->isEmpty());
        $this->assertDatabaseHas('raffles', ['id' => $raffle->id, 'status' => 'drawn']);
    }

    public function test_selects_a_winner_from_sold_tickets(): void
    {
        $admin  = User::factory()->create();
        $buyer  = User::factory()->create();
        $raffle = Raffle::factory()->create(['status' => 'active', 'total_tickets' => 5, 'sold_count' => 5]);

        Ticket::factory()->count(5)->create([
            'raffle_id' => $raffle->id,
            'status'    => 'sold',
            'user_id'   => $buyer->id,
        ]);

        $dto     = new ExecuteDrawDTO($raffle->id, $admin->id, 'Premio principal');
        $winners = $this->service->execute($dto);

        $this->assertCount(1, $winners);
        $this->assertDatabaseHas('winners', ['raffle_id' => $raffle->id, 'user_id' => $buyer->id]);
        $this->assertDatabaseHas('raffles', ['id' => $raffle->id, 'status' => 'drawn']);
    }

    public function test_creates_draw_audit_with_hash(): void
    {
        $admin  = User::factory()->create();
        $buyer  = User::factory()->create();
        $raffle = Raffle::factory()->create(['status' => 'active', 'total_tickets' => 3, 'sold_count' => 3]);

        Ticket::factory()->count(3)->create([
            'raffle_id' => $raffle->id,
            'status'    => 'sold',
            'user_id'   => $buyer->id,
        ]);

        $dto = new ExecuteDrawDTO($raffle->id, $admin->id, 'Premio');
        $this->service->execute($dto);

        $audit = DrawAudit::where('raffle_id', $raffle->id)->first();
        $this->assertNotNull($audit);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $audit->participants_hash);
    }

    public function test_winner_ticket_status_is_set_to_winner(): void
    {
        $admin  = User::factory()->create();
        $buyer  = User::factory()->create();
        $raffle = Raffle::factory()->create(['status' => 'active', 'total_tickets' => 1, 'sold_count' => 1]);

        $ticket = Ticket::factory()->create([
            'raffle_id' => $raffle->id,
            'status'    => 'sold',
            'user_id'   => $buyer->id,
            'number'    => 1,
        ]);

        $dto = new ExecuteDrawDTO($raffle->id, $admin->id, 'Premio');
        $this->service->execute($dto);

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'winner']);
    }
}
