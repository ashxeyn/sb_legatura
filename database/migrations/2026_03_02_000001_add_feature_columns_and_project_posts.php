<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Profile, Post Project, Star Review, Highlight Set features
 *
 * Changes:
 *  1. reviews — add UNIQUE(reviewer_user_id, project_id) constraint
 *  2. projects — add is_highlighted, highlighted_at columns
 *  3. project_posts — new table for Facebook-style social posts
 *  4. project_post_images — related images for project_posts
 *  5. Add indexes for feed and review performance
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. Reviews: unique constraint (one review per user per project) ───
        // Check if the unique index already exists before adding
        $existingIndexes = DB::select("SHOW INDEX FROM reviews WHERE Key_name = 'reviews_reviewer_project_unique'");
        if (empty($existingIndexes)) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->unique(['reviewer_user_id', 'project_id'], 'reviews_reviewer_project_unique');
            });
        }

        // ─── 2. Projects: highlight columns ────────────────────────────────────
        if (!Schema::hasColumn('projects', 'is_highlighted')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->boolean('is_highlighted')->default(false)->after('selected_contractor_id');
                $table->timestamp('highlighted_at')->nullable()->after('is_highlighted');
            });
        }

        // ─── 3. project_posts: showcase / social posts ─────────────────────
        if (!Schema::hasTable('project_posts')) {
            Schema::create('project_posts', function (Blueprint $table) {
                $table->id('post_id');
                $table->integer('user_id');
                $table->string('title', 255)->nullable();
                $table->text('content');
                $table->integer('tagged_user_id')->nullable();             // tag a contractor or owner
                $table->integer('linked_project_id')->nullable();          // link to actual projects table
                $table->string('location', 500)->nullable();               // optional location text
                $table->enum('status', ['open', 'closed', 'deleted'])->default('open');
                $table->boolean('is_highlighted')->default(false);
                $table->timestamp('highlighted_at')->nullable();
                $table->string('boost_tier', 20)->nullable();              // gold / silver / none
                $table->timestamp('boost_expiration')->nullable();
                $table->timestamps();

                // Indexes
                $table->index('user_id', 'pp_user_id_idx');
                $table->index('status', 'pp_status_idx');
                $table->index('created_at', 'pp_created_at_idx');
                $table->index(['is_highlighted', 'highlighted_at'], 'pp_highlight_idx');
                $table->index('linked_project_id', 'pp_linked_project_idx');
                $table->index('tagged_user_id', 'pp_tagged_user_idx');

                // Foreign keys
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
                $table->foreign('tagged_user_id')->references('user_id')->on('users')->onDelete('set null');
                $table->foreign('linked_project_id')->references('project_id')->on('projects')->onDelete('set null');
            });
        }

        // ─── 4. project_post_images: related images ────────────────────────────
        if (!Schema::hasTable('project_post_images')) {
            Schema::create('project_post_images', function (Blueprint $table) {
                $table->id('image_id');
                $table->unsignedBigInteger('post_id');
                $table->string('file_path', 500);
                $table->string('original_name', 255)->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('post_id')->references('post_id')->on('project_posts')->onDelete('cascade');
                $table->index('post_id', 'ppi_post_id_idx');
            });
        }

        // ─── 5. Additional indexes for existing tables ─────────────────────────
        // projects — for feed and highlight queries
        $projIndexes = collect(DB::select("SHOW INDEX FROM projects"))->pluck('Key_name')->unique();
        if (!$projIndexes->contains('projects_is_highlighted_idx')) {
            try {
                DB::statement('ALTER TABLE projects ADD INDEX projects_is_highlighted_idx (is_highlighted, highlighted_at)');
            } catch (\Throwable $e) {
                // Column may not exist on older schemas — silently skip
            }
        }
        if (!$projIndexes->contains('projects_status_idx')) {
            try {
                DB::statement('ALTER TABLE projects ADD INDEX projects_status_idx (project_status)');
            } catch (\Throwable $e) {}
        }
        if (!$projIndexes->contains('projects_type_id_idx')) {
            try {
                DB::statement('ALTER TABLE projects ADD INDEX projects_type_id_idx (type_id)');
            } catch (\Throwable $e) {}
        }

        // reviews — additional indexes
        $revIndexes = collect(DB::select("SHOW INDEX FROM reviews"))->pluck('Key_name')->unique();
        if (!$revIndexes->contains('reviews_reviewee_rating_idx')) {
            try {
                DB::statement('ALTER TABLE reviews ADD INDEX reviews_reviewee_rating_idx (reviewee_user_id, rating)');
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_post_images');
        Schema::dropIfExists('project_posts');

        if (Schema::hasColumn('projects', 'is_highlighted')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn(['is_highlighted', 'highlighted_at']);
            });
        }

        // Remove unique constraint from reviews
        try {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropUnique('reviews_reviewer_project_unique');
            });
        } catch (\Throwable $e) {}
    }
};
