<?php

namespace Tests\Unit;

use App\DTOs\ReserveTicketsDTO;
use App\Exceptions\Domain\InsufficientTicketsException;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    private TicketReservationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TicketReservationService();
    }

    public function test_reserves_random_tickets_for_user(): void
    {
        $user   = User::factory()->create();
        $raffle = Raffle::factory()->create(['ticket_price' => 10, 'total_tickets' => 10, 'sold_count' => 0]);

        Ticket::factory()->count(10)->sequence(fn ($seq) => [
            'number'    => $seq->index + 1,
            'raffle_id' => $raffle->id,
            'status'    => 'available',
        ])->create();

        $dto   = new ReserveTicketsDTO($raffle->id, $user->id, 3);
        $order = $this->service->reserve($dto);

        $this->assertSame('pending', $order->status);
        $this->assertCount(3, $order->tickets);
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'raffle_id' => $raffle->id]);
    }

    public function test_throws_when_not_enough_tickets_available(): void
    {
        $this->expectException(InsufficientTicketsException::class);

        $user   = User::factory()->create();
        $raffle = Raffle::factory()->create(['ticket_price' => 10, 'total_tickets' => 2, 'sold_count' => 0]);

        Ticket::factory()->count(2)->sequence(fn ($seq) => [
            'number'    => $seq->index + 1,
            'raffle_id' => $raffle->id,
            'status'    => 'available',
        ])->create();

        $dto = new ReserveTicketsDTO($raffle->id, $user->id, 5);
        $this->service->reserve($dto);
    }

    public function test_reserves_specific_manual_tickets(): void
    {
        $user   = User::factory()->create();
        $raffle = Raffle::factory()->create(['ticket_price' => 10, 'total_tickets' => 10, 'sold_count' => 0]);

        Ticket::factory()->count(5)->sequence(fn ($seq) => [
            'number'    => $seq->index + 1,
            'raffle_id' => $raffle->id,
            'status'    => 'available',
        ])->create();

        $dto   = new ReserveTicketsDTO($raffle->id, $user->id, 2, [1, 3]);
        $order = $this->service->reserve($dto);

        $reservedNumbers = $order->tickets->pluck('number')->sort()->values()->toArray();
        $this->assertSame([1, 3], $reservedNumbers);
    }

    public function test_throws_when_manual_ticket_already_taken(): void
    {
        $this->expectException(InsufficientTicketsException::class);

        $user   = User::factory()->create();
        $raffle = Raffle::factory()->create(['ticket_price' => 10, 'total_tickets' => 5, 'sold_count' => 1]);

        Ticket::factory()->create(['number' => 1, 'raffle_id' => $raffle->id, 'status' => 'sold']);
        Ticket::factory()->create(['number' => 2, 'raffle_id' => $raffle->id, 'status' => 'available']);

        $dto = new ReserveTicketsDTO($raffle->id, $user->id, 2, [1, 2]);
        $this->service->reserve($dto);
    }

    public function test_reserved_tickets_have_expiry(): void
    {
        $user   = User::factory()->create();
        $raffle = Raffle::factory()->create(['ticket_price' => 10, 'total_tickets' => 5, 'sold_count' => 0]);

        Ticket::factory()->count(5)->sequence(fn ($seq) => [
            'number'    => $seq->index + 1,
            'raffle_id' => $raffle->id,
            'status'    => 'available',
        ])->create();

        $dto   = new ReserveTicketsDTO($raffle->id, $user->id, 1);
        $order = $this->service->reserve($dto);

        $ticket = $order->tickets->first();
        $this->assertNotNull($ticket->reserved_until);
        $this->assertTrue($ticket->reserved_until->isFuture());
    }
}
