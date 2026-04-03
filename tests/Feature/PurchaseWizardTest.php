<?php

namespace Tests\Feature;

use App\Models\Raffle;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseWizardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Raffle $raffle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->raffle = Raffle::create([
            'title' => 'Wizard Raffle',
            'slug' => 'wizard-raffle',
            'description' => 'Desc',
            'prize_description' => 'Prize',
            'ticket_price' => 50,
            'currency' => 'USD',
            'total_tickets' => 10,
            'status' => 'active',
            'draw_date' => now()->addDays(1),
            'owner_id' => $this->admin->id,
        ]);
    }

    public function test_can_view_raffle_numbers_page()
    {
        $response = $this->get(route('raffles.show', $this->raffle));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Raffle/Show')
            ->has('raffle')
        );
    }
}
