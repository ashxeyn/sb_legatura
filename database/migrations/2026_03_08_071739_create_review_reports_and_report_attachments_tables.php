<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates:
     *   - review_reports      : separate reports table for reviews
     *   - report_attachments  : files attached to any report (post_reports or review_reports)
     */
    public function up(): void
    {
        // ── review_reports ────────────────────────────────────────────────────
        Schema::create('review_reports', function (Blueprint $table) {
            $table->bigIncrements('report_id');
            $table->unsignedBigInteger('reporter_user_id');
            $table->unsignedBigInteger('review_id');
            $table->string('reason', 120);
            $table->text('details')->nullable();
            $table->enum('status', ['pending', 'under_review', 'resolved', 'dismissed'])->default('pending');
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('reporter_user_id');
            $table->index('review_id');
            $table->index('status');
        });

        // ── report_attachments ────────────────────────────────────────────────
        // Polymorphic: attach files to either post_reports or review_reports
        Schema::create('report_attachments', function (Blueprint $table) {
            $table->bigIncrements('attachment_id');
            // 'post_report' or 'review_report'
            $table->string('report_type', 30);
            $table->unsignedBigInteger('report_id');
            $table->string('original_name', 255);
            $table->string('file_path', 500);   // relative path inside storage/app/public/
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size')->default(0); // bytes
            $table->timestamps();

            $table->index(['report_type', 'report_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_attachments');
        Schema::dropIfExists('review_reports');
    }
};
