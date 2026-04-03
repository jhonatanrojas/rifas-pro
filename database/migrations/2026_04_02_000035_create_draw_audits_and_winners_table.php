<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('draw_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raffle_id')->constrained()->cascadeOnDelete();
            $table->string('participants_hash', 64); // SHA-256
            $table->json('participants_snapshot');
            $table->string('algorithm_version', 20)->default('v1');
            $table->string('seed', 255)->nullable();
            $table->timestamp('drawn_at');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique('raffle_id'); // Solo un sorteo por rifa
        });

        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raffle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('prize_description');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->text('testimony')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->index('raffle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winners');
        Schema::dropIfExists('draw_audits');
    }
};
