<?php

namespace Tests\Feature;

use App\Models\Raffle;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set up base Raffle
        $this->raffle = Raffle::create([
            'title' => 'Test Raffle',
            'description' => 'Test Desc',
            'ticket_price' => 10,
            'currency' => 'USD',
            'total_tickets' => 100,
            'status' => 'active',
            'draw_date' => now()->addDays(7),
        ]);

        // Generate tickets
        for ($i = 1; $i <= 10; $i++) {
            Ticket::create([
                'raffle_id' => $this->raffle->id,
                'number' => str_pad($i, 3, '0', STR_PAD_LEFT),
                'status' => 'available',
            ]);
        }
    }

    public function test_guest_purchase_checkout_flow()
    {
        Storage::fake('public');

        $proofImage = UploadedFile::fake()->image('proof.jpg');

        $payload = [
            'name' => 'John Doe Test',
            'email' => 'john.test@example.com',
            'phone' => '+1234567890',
            'quantity' => 2,
            'amount' => 20,
            'payment_method' => 'zelle',
            'reference' => 'ZEL123456',
            'proof' => $proofImage,
        ];

        $response = $this->postJson("/api/raffles/{$this->raffle->id}/purchase", $payload);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        // Check if user was created
        $this->assertDatabaseHas('users', [
            'email' => 'john.test@example.com',
            'name' => 'John Doe Test'
        ]);

        // Check if order was created
        $user = User::where('email', 'john.test@example.com')->first();
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'raffle_id' => $this->raffle->id,
            'total' => 20,
        ]);

        // Check if tickets were reserved
        $reservedTicketsCount = Ticket::where('status', 'reserved')->where('user_id', $user->id)->count();
        $this->assertEquals(2, $reservedTicketsCount);

        // Check if image was stored
        $payment = $user->orders->first()->payment;
        Storage::disk('public')->assertExists($payment->receipt_image_path);
    }

    public function test_manual_tickets_selection_flow()
    {
        Storage::fake('public');
        $proofImage = UploadedFile::fake()->image('proof2.jpg');

        $payload = [
            'name' => 'Jane Manual',
            'email' => 'jane.manual@example.com',
            'phone' => '+1234567890',
            'quantity' => 3,
            'amount' => 30,
            'payment_method' => 'pago_movil',
            'reference' => 'PM9999',
            'proof' => $proofImage,
            'manual_tickets' => json_encode(['002', '005', '008']),
        ];

        $response = $this->postJson("/api/raffles/{$this->raffle->id}/purchase", $payload);

        $response->assertStatus(200);

        // Verify specific tickets are reserved
        foreach (['002', '005', '008'] as $number) {
            $this->assertDatabaseHas('tickets', [
                'raffle_id' => $this->raffle->id,
                'number' => $number,
                'status' => 'reserved'
            ]);
        }
    }
}
