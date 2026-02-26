<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payment Allocation: add columns to milestone_items and create audit log table.
 *
 * milestone_items:
 *   - adjusted_cost        — adjusted required amount after underpayment carry-forward
 *                            (original amount stays in milestone_item_cost for history)
 *   - carry_forward_amount — shortfall carried FROM the previous item to this one
 *
 * payment_adjustment_logs:
 *   - full audit trail for every carry-forward / overpayment recording
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Add allocation columns to milestone_items ──
        Schema::table('milestone_items', function (Blueprint $table) {
            $table->decimal('adjusted_cost', 12, 2)->nullable()->after('milestone_item_cost')
                  ->comment('Required amount after underpayment carry-forward. NULL = no adjustment (use milestone_item_cost).');
            $table->decimal('carry_forward_amount', 12, 2)->default(0)->after('adjusted_cost')
                  ->comment('Shortfall amount carried forward FROM the previous item.');
        });

        // ── 2. Create payment adjustment audit log ──
        Schema::create('payment_adjustment_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('milestone_id');
            $table->unsignedInteger('source_item_id')->comment('Item that was over/under-paid');
            $table->unsignedInteger('target_item_id')->nullable()->comment('Next item that received carry-forward (NULL for overpayment)');
            $table->unsignedInteger('payment_id')->comment('The payment that triggered this adjustment');
            $table->enum('adjustment_type', ['overpayment', 'underpayment'])->comment('What kind of adjustment');
            $table->decimal('original_required', 12, 2)->comment('Original required amount of source item');
            $table->decimal('total_paid', 12, 2)->comment('Total approved payments on source item after this payment');
            $table->decimal('adjustment_amount', 12, 2)->comment('The excess (overpay) or shortfall (underpay) amount');
            $table->decimal('target_original_cost', 12, 2)->nullable()->comment('Target item original cost before adjustment');
            $table->decimal('target_adjusted_cost', 12, 2)->nullable()->comment('Target item cost after adjustment');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('project_id');
            $table->index('source_item_id');
            $table->index('target_item_id');
            $table->index('payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_adjustment_logs');

        Schema::table('milestone_items', function (Blueprint $table) {
            $table->dropColumn(['adjusted_cost', 'carry_forward_amount']);
        });
    }
};
