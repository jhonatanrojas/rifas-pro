<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Raffle;
use App\Models\Combo;
use App\Models\Ticket;
use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Usuarios Bases
        $admin = User::firstOrCreate(
            ['email' => 'admin@rifasonline.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'phone' => '+1234567890',
                'country' => 'VE',
                'email_verified_at' => now(),
            ]
        );

        $affiliate = User::firstOrCreate(
            ['email' => 'afiliado@rifasonline.com'],
            [
                'name' => 'Socio Afiliado',
                'password' => Hash::make('12345678'),
                'role' => 'affiliate',
                'phone' => '+1987654321',
                'country' => 'CO',
                'email_verified_at' => now(),
            ]
        );

        $affiliate->affiliate()->firstOrCreate([
            'code' => 'DEMO-AFIL',
            'commission_rate' => 0.10,
        ]);

        $user1 = User::firstOrCreate(
            ['email' => 'juan@example.com'],
            [
                'name' => 'Juan Perez',
                'password' => Hash::make('12345678'),
                'role' => 'user',
                'phone' => '+584141234567',
                'email_verified_at' => now(),
            ]
        );

        // 2. Demo Raffles
        $this->command->info('Creating BMW M4 Raffle...');
        $raffleCar = Raffle::firstOrCreate(
            ['slug' => 'bmw-m4-competition-2024'],
            [
                'owner_id' => $admin->id,
                'title' => 'BMW M4 Competition 2024',
                'description' => 'Gana un espectacular BMW M4 Competition 0km. El auto de tus sueños a tan solo un ticket de distancia.',
                'cover_image' => 'raffles/car_demo.png',
                'total_tickets' => 1000,
                'ticket_price' => 50.00,
                'currency' => 'USD',
                'draw_type' => 'internal_random',
                'draw_date' => now()->addDays(30),
                'status' => 'active',
                'is_featured' => true,
            ]
        );

        Combo::firstOrCreate(
            ['raffle_id' => $raffleCar->id, 'quantity' => 3],
            ['price' => 140.00, 'label' => 'Pack Básico (Ahorra $10)']
        );

        $this->command->info('Creating iPhone 15 Pro Raffle...');
        $rafflePhone = Raffle::firstOrCreate(
            ['slug' => 'iphone-15-pro-max'],
            [
                'owner_id' => $admin->id,
                'title' => 'iPhone 15 Pro Max',
                'description' => 'Se sortea el teléfono más potente del mercado. Dos ganadores, color Titanio Natual o Negro Espacial.',
                'cover_image' => 'raffles/iphone_demo.png',
                'total_tickets' => 500,
                'ticket_price' => 10.00,
                'currency' => 'USD',
                'draw_type' => 'external_lottery',
                'draw_date' => now()->addDays(15),
                'status' => 'active',
                'is_featured' => true,
            ]
        );

        $this->command->info('Creating PS5 Pro Raffle...');
        $rafflePS5 = Raffle::firstOrCreate(
            ['slug' => 'playstation-5-pro-combo'],
            [
                'owner_id' => $admin->id,
                'title' => 'PlayStation 5 Pro + DualSense Edge',
                'description' => 'Tu setup de gaming al siguiente nivel. Sorprende a tus amigos con la nueva PS5 Pro.',
                'cover_image' => 'raffles/ps5_demo.png',
                'total_tickets' => 300,
                'ticket_price' => 5.00,
                'currency' => 'USD',
                'draw_type' => 'internal_random',
                'draw_date' => now()->addDays(10),
                'status' => 'active',
                'is_featured' => false,
            ]
        );

        $raffles = [$raffleCar, $rafflePhone, $rafflePS5];

        foreach ($raffles as $raffle) {
            $this->command->info("Generating {$raffle->total_tickets} tickets for '{$raffle->title}'...");
            $ticketsCount = Ticket::where('raffle_id', $raffle->id)->count();
            if ($ticketsCount === 0) {
                $ticketData = [];
                for ($i = 1; $i <= $raffle->total_tickets; $i++) {
                    $ticketData[] = [
                        'raffle_id' => $raffle->id,
                        'number' => $i,
                        'status' => 'available',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if ($i % 500 === 0) {
                        Ticket::insert($ticketData);
                        $ticketData = [];
                    }
                }
                if (count($ticketData) > 0) {
                    Ticket::insert($ticketData);
                }
                
                // Simular alguos tickets vendidos al azar para mostrar que hay actividad
                $soldNumbers = [7, 13, 100, 255];
                // Filtrar por si la cantidad total es menor
                $soldNumbers = array_filter($soldNumbers, fn($n) => $n <= $raffle->total_tickets);
                
                if (!empty($soldNumbers)) {
                    Ticket::where('raffle_id', $raffle->id)
                        ->whereIn('number', $soldNumbers)
                        ->update([
                            'status' => 'sold',
                            'user_id' => $user1->id,
                        ]);
                    $raffle->increment('sold_count', count($soldNumbers));
                }
            }
        }

        // 4. Coupons
        Coupon::firstOrCreate(
            ['code' => 'BIENVENIDA20'],
            [
                'type' => 'percent',
                'value' => 20.00,
                'max_uses' => 100,
                'used_count' => 0,
                'valid_until' => now()->addMonths(1),
            ]
        );
        
        $this->command->info('Database seeding completed successfully.');
    }
}
