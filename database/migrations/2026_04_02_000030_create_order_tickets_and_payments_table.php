<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['order_id', 'ticket_id']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['zelle', 'pago_movil', 'paypal', 'stripe'])->default('pago_movil');
            $table->decimal('amount', 15, 2);
            $table->enum('currency', ['USD', 'VES', 'COP'])->default('USD');
            $table->string('reference_number', 100)->nullable();
            $table->string('receipt_image_path')->nullable();
            $table->json('ocr_raw_data')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();

            $table->index(['status', 'method']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_tickets');
    }
};
