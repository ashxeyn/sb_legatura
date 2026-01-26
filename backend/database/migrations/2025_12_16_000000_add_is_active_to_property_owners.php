<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('property_owners', 'is_active')) {
            Schema::table('property_owners', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('verification_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('property_owners', 'is_active')) {
            Schema::table('property_owners', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
