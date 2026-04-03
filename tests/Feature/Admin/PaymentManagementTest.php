<?php

namespace Tests\Feature\Admin;

use App\Actions\ReviewPaymentAction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\PaymentReviewedNotification;
use App\Services\Notifications\WhatsAppServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function seedPaymentFixture(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create();

        $raffle = Raffle::factory()->active()->create([
            'owner_id' => $admin->id,
            'title' => 'Phase 6 Raffle',
            'ticket_price' => 25,
            'total_tickets' => 100,
            'currency' => 'USD',
        ]);

        $tickets = Ticket::factory()->count(2)->create([
            'raffle_id' => $raffle->id,
            'user_id' => $buyer->id,
            'status' => 'reserved',
        ]);

        $order = Order::create([
            'user_id' => $buyer->id,
            'raffle_id' => $raffle->id,
            'status' => 'pending',
            'subtotal' => 50,
            'discount' => 0,
            'total' => 50,
            'currency' => 'USD',
            'payment_method' => 'zelle',
            'metadata' => [],
        ]);

        $order->tickets()->attach($tickets->pluck('id'));

        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => 'zelle',
            'amount' => 50,
            'currency' => 'USD',
            'reference_number' => 'REF-123',
            'receipt_image_path' => 'receipts/test-proof.png',
            'status' => 'pending',
        ]);

        return compact('admin', 'buyer', 'raffle', 'order', 'tickets', 'payment');
    }

    public function test_admin_can_review_payment_and_mark_tickets_sold(): void
    {
        Storage::fake('public');
        Notification::fake();
        $fixture = $this->seedPaymentFixture();
        Storage::disk('public')->put('receipts/test-proof.png', 'fake-image');

        $whatsapp = Mockery::mock(WhatsAppServiceInterface::class);
        $whatsapp->shouldReceive('sendTicket')->once()->andReturn(true);
        $this->app->instance(WhatsAppServiceInterface::class, $whatsapp);

        $response = $this->actingAs($fixture['admin'])->post(route('admin.payments.review', $fixture['payment']), [
            'status' => 'approved',
            'notes' => 'Looks good',
        ]);

        $response->assertRedirect(route('admin.payments.index'));

        $this->assertDatabaseHas('payments', [
            'id' => $fixture['payment']->id,
            'status' => 'approved',
            'notes' => 'Looks good',
            'reviewed_by' => $fixture['admin']->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $fixture['order']->id,
            'status' => 'paid',
        ]);

        $this->assertSame(2, $fixture['raffle']->fresh()->sold_count);
        $this->assertDatabaseHas('tickets', [
            'id' => $fixture['tickets'][0]->id,
            'status' => 'sold',
        ]);
        Notification::assertSentTo($fixture['buyer'], PaymentReviewedNotification::class);
    }

    public function test_admin_can_export_payments_csv(): void
    {
        $fixture = $this->seedPaymentFixture();

        $response = $this->actingAs($fixture['admin'])->get(route('admin.payments.index', [
            'export' => 'csv',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('content-disposition', 'attachment; filename=payments.csv');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('REF-123', $csv);
    }

    public function test_admin_can_bulk_review_payments(): void
    {
        Storage::fake('public');
        Notification::fake();

        $first = $this->seedPaymentFixture();
        $second = $this->seedPaymentFixture();

        $whatsapp = Mockery::mock(WhatsAppServiceInterface::class);
        $whatsapp->shouldReceive('sendTicket')->twice()->andReturn(true);
        $this->app->instance(WhatsAppServiceInterface::class, $whatsapp);

        $response = $this->actingAs($first['admin'])->post(route('admin.payments.bulk-review'), [
            'payment_ids' => [$first['payment']->id, $second['payment']->id],
            'status' => 'approved',
        ]);

        $response->assertRedirect(route('admin.payments.index'));

        $this->assertDatabaseHas('payments', [
            'id' => $first['payment']->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('payments', [
            'id' => $second['payment']->id,
            'status' => 'approved',
        ]);
    }
}
