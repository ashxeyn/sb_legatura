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
        Schema::table('contractors', function (Blueprint $table) {
            $table->tinyInteger('is_active')->default(1)->after('verification_date');
            $table->date('suspension_until')->nullable()->after('is_active');
            $table->text('suspension_reason')->nullable()->after('suspension_until');
            $table->text('deletion_reason')->nullable()->after('suspension_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'suspension_until', 'suspension_reason', 'deletion_reason']);
        });
    }
};
