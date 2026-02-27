<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Normalize schema: separate milestone-level date proposals from project-level date updates.
 *
 * 1. Creates `milestone_item_updates` table for milestone-level proposed changes.
 * 2. Makes `proposed_end_date` and `current_end_date` nullable in `project_updates`
 *    so that milestone-only change requests don't require a project timeline extension.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Create milestone_item_updates ────────────────────────────
        Schema::create('milestone_item_updates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('milestone_item_id')->comment('FK → milestone_items.item_id');
            $table->unsignedInteger('project_update_id')->nullable()->comment('FK → project_updates.extension_id (nullable for standalone)');
            $table->date('proposed_start_date')->nullable();
            $table->date('proposed_end_date')->nullable();
            $table->decimal('proposed_cost', 12, 2)->nullable();
            $table->string('proposed_title', 255)->nullable();
            $table->date('previous_start_date')->nullable();
            $table->date('previous_end_date')->nullable();
            $table->decimal('previous_cost', 12, 2)->nullable();
            $table->string('previous_title', 255)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('milestone_item_id');
            $table->index('project_update_id');
            $table->index(['project_update_id', 'status']);
        });

        // ── 2. Make proposed_end_date & current_end_date nullable ───────
        DB::statement('ALTER TABLE `project_updates` MODIFY `proposed_end_date` DATE NULL COMMENT "Requested new project end date (nullable for milestone-only updates)"');
        DB::statement('ALTER TABLE `project_updates` MODIFY `current_end_date` DATE NULL COMMENT "Project end date at time of request (nullable for milestone-only updates)"');
    }

    public function down(): void
    {
        Schema::dropIfExists('milestone_item_updates');

        // Restore NOT NULL (set any NULLs to a safe default first)
        DB::statement('UPDATE `project_updates` SET `proposed_end_date` = CURDATE() WHERE `proposed_end_date` IS NULL');
        DB::statement('UPDATE `project_updates` SET `current_end_date` = CURDATE() WHERE `current_end_date` IS NULL');
        DB::statement('ALTER TABLE `project_updates` MODIFY `proposed_end_date` DATE NOT NULL COMMENT "Requested new project end date"');
        DB::statement('ALTER TABLE `project_updates` MODIFY `current_end_date` DATE NOT NULL COMMENT "Project end date at time of request"');
    }
};
