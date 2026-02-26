<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remove the allocation_method column from project_extensions.
 *
 * This column is unnecessary — allocation is fully tracked via
 * milestone_changes JSON and allocation_mode. The column was only
 * used for backward-compat labeling (new_milestone|redistribute)
 * and is no longer populated or read by any code path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_extensions', function (Blueprint $table) {
            $table->dropColumn('allocation_method');
        });
    }

    public function down(): void
    {
        Schema::table('project_extensions', function (Blueprint $table) {
            $table->enum('allocation_method', ['new_milestone', 'redistribute'])
                ->nullable()
                ->after('additional_amount')
                ->comment('How extra cost is allocated when has_additional_cost = true');
        });
    }
};
