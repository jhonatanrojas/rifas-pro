<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raffle_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->enum('status', ['available', 'reserved', 'sold', 'winner'])->default('available');
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('reserved_until')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('order_id')->nullable(); // FK se agrega después via alter
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            // Índice único compuesto crítico: evita duplicados de número por rifa
            $table->unique(['raffle_id', 'number']);

            // Índices de rendimiento
            $table->index(['raffle_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('reserved_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
