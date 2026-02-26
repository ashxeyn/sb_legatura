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
        Schema::table('milestone_items', function (Blueprint $table) {
            $table->date('settlement_due_date')->nullable()->after('date_to_finish')
                  ->comment('Payment settlement deadline set by contractor');
            $table->date('extension_date')->nullable()->after('settlement_due_date')
                  ->comment('Optional extended deadline granted by contractor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('milestone_items', function (Blueprint $table) {
            $table->dropColumn(['settlement_due_date', 'extension_date']);
        });
    }
};
