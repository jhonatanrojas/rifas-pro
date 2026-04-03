<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->enum('channel', ['whatsapp', 'email', 'push']);
            $table->string('key');
            $table->string('title')->nullable();
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['channel', 'key']);
        });

        $now = now();
        DB::table('notification_templates')->insert([
            ['channel' => 'whatsapp', 'key' => 'payment_approved', 'title' => 'Pago aprobado', 'body' => "Hola {nombre}, tu pago de la rifa {rifa} fue aprobado. Tus números son {numeros}. Total: {total}.", 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['channel' => 'whatsapp', 'key' => 'payment_rejected', 'title' => 'Pago rechazado', 'body' => "Hola {nombre}, tu pago de la rifa {rifa} fue rechazado. Revisa tus datos o intenta nuevamente.", 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['channel' => 'whatsapp', 'key' => 'winner', 'title' => 'Ganaste', 'body' => "¡Felicidades {nombre}! Tu ticket {numeros} ganó en {rifa}. Premio: {premio}.", 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['channel' => 'push', 'key' => 'payment_approved', 'title' => 'Pago aprobado', 'body' => 'Tu pago para {rifa} fue aprobado.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['channel' => 'push', 'key' => 'payment_rejected', 'title' => 'Pago rechazado', 'body' => 'Tu pago para {rifa} fue rechazado. Revisa los detalles.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['channel' => 'push', 'key' => 'winner', 'title' => '¡Eres el ganador!', 'body' => 'Tu ticket {numeros} ganó en {rifa}.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['channel' => 'email', 'key' => 'payment_approved', 'title' => 'Pago aprobado', 'body' => 'Tu pago para {rifa} fue aprobado. Número(s): {numeros}. Total: {total}.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['channel' => 'email', 'key' => 'payment_rejected', 'title' => 'Pago rechazado', 'body' => 'Tu pago para {rifa} fue rechazado.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['channel' => 'email', 'key' => 'winner', 'title' => '¡Felicidades! Has ganado', 'body' => 'Tu ticket {numeros} fue seleccionado como ganador en {rifa}. Premio: {premio}.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
