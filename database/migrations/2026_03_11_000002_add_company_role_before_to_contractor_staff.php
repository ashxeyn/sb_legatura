<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contractor_staff', function (Blueprint $table) {
            $table->string('company_role_before', 50)->nullable()->after('role_if_others');
        });
    }

    public function down(): void
    {
        Schema::table('contractor_staff', function (Blueprint $table) {
            $table->dropColumn('company_role_before');
        });
    }
};
