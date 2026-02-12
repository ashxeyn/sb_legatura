<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Expand the type enum to cover all notification categories
        DB::statement("ALTER TABLE `notifications` MODIFY `type` ENUM(
            'Milestone Update',
            'Bid Status',
            'Payment Reminder',
            'Project Alert',
            'Progress Update',
            'Dispute Update',
            'Team Update',
            'Payment Status'
        ) NOT NULL");

        Schema::table('notifications', function (Blueprint $table) {
            $table->string('title', 255)->nullable()->after('message');
            $table->enum('priority', ['critical', 'high', 'normal'])->default('normal')->after('delivery_method');
            $table->string('reference_type', 50)->nullable()->after('priority');
            $table->unsignedInteger('reference_id')->nullable()->after('reference_type');
            $table->string('dedup_key', 100)->nullable()->after('reference_id');

            $table->index(['user_id', 'is_read'], 'idx_user_read');
            $table->index(['user_id', 'created_at'], 'idx_user_created');
            $table->unique(['user_id', 'dedup_key'], 'idx_dedup');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropUnique('idx_dedup');
            $table->dropIndex('idx_user_created');
            $table->dropIndex('idx_user_read');
            $table->dropColumn(['title', 'priority', 'reference_type', 'reference_id', 'dedup_key']);
        });

        DB::statement("ALTER TABLE `notifications` MODIFY `type` ENUM(
            'Milestone Update',
            'Bid Status',
            'Payment Reminder',
            'Project Alert'
        ) NOT NULL");
    }
};
