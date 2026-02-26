<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds 'revision_requested' to the status enum and a 'revision_notes' column
 * on the project_extensions table. This supports the formal proposal approval
 * workflow where the owner can request changes before approving/rejecting.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Expand the status enum to include 'revision_requested'
        DB::statement("ALTER TABLE `project_extensions` MODIFY COLUMN `status` ENUM('pending','approved','rejected','withdrawn','revision_requested') NOT NULL DEFAULT 'pending'");

        // 2. Add revision_notes for owner feedback when requesting changes
        Schema::table('project_extensions', function (Blueprint $table) {
            $table->text('revision_notes')->nullable()->after('owner_response');
        });
    }

    public function down(): void
    {
        // Revert status enum
        DB::statement("ALTER TABLE `project_extensions` MODIFY COLUMN `status` ENUM('pending','approved','rejected','withdrawn') NOT NULL DEFAULT 'pending'");

        Schema::table('project_extensions', function (Blueprint $table) {
            $table->dropColumn('revision_notes');
        });
    }
};
