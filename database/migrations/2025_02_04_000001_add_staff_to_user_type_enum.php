<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds 'staff' to the user_type enum to support contractor team members.
     * Staff members are users who belong to a contractor organization but are not the owner.
     */
    public function up(): void
    {
        // Alter the enum to include 'staff' option
        // MySQL requires recreating the column definition for enum changes
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `user_type` ENUM('contractor', 'property_owner', 'both', 'staff') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This will fail if there are records with user_type='staff'
        // You may need to manually update or delete those records first
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `user_type` ENUM('contractor', 'property_owner', 'both') NOT NULL");
    }
};
