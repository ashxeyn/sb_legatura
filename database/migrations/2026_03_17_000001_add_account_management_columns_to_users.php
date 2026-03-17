<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // property_owners: add deletion_scheduled_at and deactivation_reason
        DB::statement("ALTER TABLE `property_owners` ADD COLUMN `deletion_scheduled_at` TIMESTAMP NULL DEFAULT NULL AFTER `deletion_reason`");
        DB::statement("ALTER TABLE `property_owners` ADD COLUMN `deactivation_reason` TEXT NULL DEFAULT NULL AFTER `deletion_scheduled_at`");

        // contractors: add deletion_scheduled_at and deactivation_reason
        DB::statement("ALTER TABLE `contractors` ADD COLUMN `deletion_scheduled_at` TIMESTAMP NULL DEFAULT NULL AFTER `deletion_reason`");
        DB::statement("ALTER TABLE `contractors` ADD COLUMN `deactivation_reason` TEXT NULL DEFAULT NULL AFTER `deletion_scheduled_at`");

        // contractor_staff: add deletion_scheduled_at and deactivation_reason
        DB::statement("ALTER TABLE `contractor_staff` ADD COLUMN `deletion_scheduled_at` TIMESTAMP NULL DEFAULT NULL AFTER `deletion_reason`");
        DB::statement("ALTER TABLE `contractor_staff` ADD COLUMN `deactivation_reason` TEXT NULL DEFAULT NULL AFTER `deletion_scheduled_at`");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `property_owners` DROP COLUMN `deactivation_reason`");
        DB::statement("ALTER TABLE `property_owners` DROP COLUMN `deletion_scheduled_at`");

        DB::statement("ALTER TABLE `contractors` DROP COLUMN `deactivation_reason`");
        DB::statement("ALTER TABLE `contractors` DROP COLUMN `deletion_scheduled_at`");

        DB::statement("ALTER TABLE `contractor_staff` DROP COLUMN `deactivation_reason`");
        DB::statement("ALTER TABLE `contractor_staff` DROP COLUMN `deletion_scheduled_at`");
    }
};
