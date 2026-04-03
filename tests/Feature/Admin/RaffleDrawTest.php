<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Notifications\WhatsAppServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

class RaffleDrawTest extends TestCase
{
    use RefreshDatabase;

    protected $whatsapp;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->whatsapp = Mockery::mock(WhatsAppServiceInterface::class);
        $this->app->instance(WhatsAppServiceInterface::class, $this->whatsapp);
    }

    public function test_admin_can_create_raffle_with_combos()
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->postJson(route('admin.raffles.store'), [
            'title' => 'Rifa de Prueba ' . uniqid(),
            'description' => 'Descripción de la rifa',
            'ticket_price' => 10,
            'total_tickets' => 100,
            'draw_date' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'currency' => 'USD',
            'combos' => [
                ['quantity' => 5, 'price' => 45],
                ['quantity' => 10, 'price' => 80],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('raffles', 1);
        $this->assertDatabaseCount('combos', 2);
    }

    public function test_admin_can_execute_draw_and_notify_winner()
    {
        $this->withoutExceptionHandling();
        Notification::fake();
        Http::fake();
        
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $raffle = Raffle::factory()->active()->create([
            'title' => 'Rifa para Sorteo',
            'draw_date' => now()->subDay(), // Debería haber pasado
        ]);

        // Crear tickets vendidos
        Ticket::factory()->count(5)->create([
            'raffle_id' => $raffle->id,
            'user_id' => $user->id,
            'status' => 'sold'
        ]);

        $this->whatsapp->shouldReceive('sendWinnerNotification')
            ->once()
            ->andReturn(true);

        $response = $this->actingAs($admin)->post(route('admin.draw.execute', $raffle), [
            'raffle_id' => $raffle->id,
            'prize_description' => 'Un Auto Nuevo',
            'confirm_draw' => true
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('winners', [
            'raffle_id' => $raffle->id,
            'user_id' => $user->id
        ]);

        $this->assertEquals('drawn', $raffle->fresh()->status);
        
        Notification::assertSentTo($user, \App\Notifications\WinnerNotification::class);
    }

    public function test_cannot_execute_draw_if_no_tickets_sold()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $raffle = Raffle::factory()->active()->create();

        $response = $this->actingAs($admin)->post(route('admin.draw.execute', $raffle), [
            'raffle_id' => $raffle->id,
            'prize_description' => 'Un Auto Nuevo',
            'confirm_draw' => true
        ]);

        $response->assertSessionHasErrors(['error']);
        $this->assertDatabaseCount('winners', 0);
    }
}
