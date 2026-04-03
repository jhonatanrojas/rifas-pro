<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('draw_audits', function (Blueprint $table) {
            $table->enum('execution_mode', ['automatic', 'manual_external'])->default('automatic')->after('seed');
            $table->string('external_reference')->nullable()->after('execution_mode');
            $table->unsignedInteger('winning_number')->nullable()->after('external_reference');
        });
    }

    public function down(): void
    {
        Schema::table('draw_audits', function (Blueprint $table) {
            $table->dropColumn(['execution_mode', 'external_reference', 'winning_number']);
        });
    }
};
