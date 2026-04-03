<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raffles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('total_tickets');
            $table->decimal('ticket_price', 10, 2);
            $table->enum('currency', ['USD', 'VES', 'COP'])->default('USD');
            $table->decimal('exchange_rate', 15, 4)->nullable();
            $table->enum('draw_type', ['internal_random', 'external_lottery'])->default('internal_random');
            $table->timestamp('draw_date')->nullable();
            $table->enum('status', ['draft', 'active', 'paused', 'drawn', 'cancelled'])->default('draft');
            $table->unsignedInteger('sold_count')->default(0);
            $table->decimal('goal_amount', 15, 2)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('is_featured');
            $table->index(['status', 'is_featured']);
            $table->index('draw_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raffles');
    }
};
