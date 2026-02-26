<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds budget-adjustment columns to project_extensions.
 *
 * New columns:
 *   current_budget      – snapshot of payment_plans.total_project_cost at request time
 *   proposed_budget     – contractor-requested new budget (nullable = no budget change)
 *   budget_change_type  – 'increase', 'decrease', or 'none'
 *   milestone_changes   – JSON blob: new items, edited items, deleted item IDs (nullable)
 *   allocation_mode     – 'percentage' or 'exact' (how contractor allocated item costs)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_extensions', function (Blueprint $table) {
            $table->decimal('current_budget', 12, 2)->nullable()->after('reason')
                  ->comment('Snapshot of total_project_cost at request time');

            $table->decimal('proposed_budget', 12, 2)->nullable()->after('current_budget')
                  ->comment('Proposed new total contract value (null = no budget change)');

            $table->enum('budget_change_type', ['none', 'increase', 'decrease'])->default('none')->after('proposed_budget')
                  ->comment('Auto-computed: none|increase|decrease');

            $table->json('milestone_changes')->nullable()->after('allocation_method')
                  ->comment('JSON: {new_items:[], edited_items:[], deleted_item_ids:[]}');

            $table->enum('allocation_mode', ['percentage', 'exact'])->nullable()->after('milestone_changes')
                  ->comment('How item costs were allocated in this request');
        });
    }

    public function down(): void
    {
        Schema::table('project_extensions', function (Blueprint $table) {
            $table->dropColumn([
                'current_budget',
                'proposed_budget',
                'budget_change_type',
                'milestone_changes',
                'allocation_mode',
            ]);
        });
    }
};
