<?php

namespace Tests\Feature\Receipts;

use App\Jobs\GenerateTicketReceiptJob;
use App\Models\Order;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ReceiptVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_receipt_verification_page_loads(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create();
        $raffle = Raffle::factory()->active()->create([
            'owner_id' => $admin->id,
            'title' => 'Receipt Raffle',
            'ticket_price' => 30,
            'total_tickets' => 100,
            'currency' => 'USD',
        ]);

        $tickets = Ticket::factory()->count(2)->create([
            'raffle_id' => $raffle->id,
            'user_id' => $buyer->id,
            'status' => 'sold',
        ]);

        $order = Order::create([
            'user_id' => $buyer->id,
            'raffle_id' => $raffle->id,
            'status' => 'paid',
            'subtotal' => 60,
            'discount' => 0,
            'total' => 60,
            'currency' => 'USD',
            'payment_method' => 'zelle',
            'paid_at' => now(),
            'metadata' => [],
        ]);

        $order->tickets()->attach($tickets->pluck('id'));

        (new GenerateTicketReceiptJob($order->id))->handle();

        $order->refresh();
        $this->assertNotEmpty($order->metadata['receipt_path'] ?? null);
        Storage::disk('public')->assertExists($order->metadata['receipt_path']);

        $response = $this->get(URL::temporarySignedRoute('receipts.verify', now()->addMinutes(10), [
            'order' => $order->id,
        ]));

        $response->assertOk();
        $response->assertSee('Firma válida');
        $response->assertSee($order->raffle->title);
        $response->assertSee((string) $order->id);
    }
}
