<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_adjustment_logs', function (Blueprint $table) {
            $table->unsignedInteger('payment_id')->nullable()->comment('The payment that triggered this adjustment (NULL for completion-triggered)')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_adjustment_logs', function (Blueprint $table) {
            $table->unsignedInteger('payment_id')->nullable(false)->comment('The payment that triggered this adjustment')->change();
        });
    }
};
