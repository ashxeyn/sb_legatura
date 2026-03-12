<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `contractor_staff` MODIFY `company_role` ENUM('manager','engineer','others','architect','representative','owner') DEFAULT NULL");
    }

    public function down(): void
    {
        // Move any 'owner' rows to 'representative' before removing the value
        DB::statement("UPDATE `contractor_staff` SET `company_role` = 'representative' WHERE `company_role` = 'owner'");
        DB::statement("ALTER TABLE `contractor_staff` MODIFY `company_role` ENUM('manager','engineer','others','architect','representative') DEFAULT NULL");
    }
};
