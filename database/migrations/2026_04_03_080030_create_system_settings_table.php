<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        DB::table('system_settings')->insert([
            ['key' => 'exchange_rate', 'value' => json_encode(['value' => 0]), 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'reservation_minutes', 'value' => json_encode(['value' => 15]), 'group' => 'sales', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'whatsapp_provider', 'value' => json_encode(['value' => 'callmebot']), 'group' => 'notifications', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'push_enabled', 'value' => json_encode(['value' => true]), 'group' => 'notifications', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
