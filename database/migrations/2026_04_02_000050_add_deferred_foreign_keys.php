<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración de FKs diferidas para resolver dependencias circulares:
 * - coupons.raffle_id → raffles
 * - tickets.order_id → orders
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->foreign('raffle_id')
                ->references('id')
                ->on('raffles')
                ->nullOnDelete();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->nullOnDelete();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropIndex(['order_id']);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['raffle_id']);
        });
    }
};
