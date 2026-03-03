<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * HighlightService — Pinned Posts / Highlight Set logic.
 *
 * Rules:
 *   - Users can pin up to MAX_HIGHLIGHTS posts (configurable, default 3)
 *   - Posts must belong to the user
 *   - is_highlighted = 1, highlighted_at = timestamp
 *   - Sort: is_highlighted DESC, highlighted_at DESC, created_at DESC
 *
 * Works with both:
 *   - project_posts (Facebook-style social posts)
 *   - projects (traditional project posts — via projects.is_highlighted)
 */
class highlightService
{
    private const MAX_HIGHLIGHTS = 3;

    /* ──────────────────────────────────────────────────────────────────
     * PROJECT POSTS (project_posts table)
     * ────────────────────────────────────────────────────────────────── */

    /**
     * Highlight (pin) a project post.
     */
    public function highlightPost(int $userId, int $postId): array
    {
        $post = DB::table('project_posts')->where('post_id', $postId)->first();
        if (!$post) {
            return ['success' => false, 'message' => 'Post not found.'];
        }
        if ((int) $post->user_id !== $userId) {
            return ['success' => false, 'message' => 'You can only highlight your own posts.'];
        }
        if ($post->is_highlighted) {
            return ['success' => false, 'message' => 'This post is already highlighted.'];
        }

        // Check limit
        $currentCount = DB::table('project_posts')
            ->where('user_id', $userId)
            ->where('is_highlighted', true)
            ->count();

        if ($currentCount >= self::MAX_HIGHLIGHTS) {
            return [
                'success' => false,
                'message' => 'You can highlight up to ' . self::MAX_HIGHLIGHTS . ' posts. Remove a highlight first.',
            ];
        }

        DB::table('project_posts')->where('post_id', $postId)->update([
            'is_highlighted'  => true,
            'highlighted_at'  => now(),
            'updated_at'      => now(),
        ]);

        Log::info('HighlightService: Post highlighted', ['post_id' => $postId, 'user_id' => $userId]);

        return ['success' => true, 'message' => 'Post highlighted successfully.'];
    }

    /**
     * Remove highlight from a project post.
     */
    public function unhighlightPost(int $userId, int $postId): array
    {
        $post = DB::table('project_posts')->where('post_id', $postId)->first();
        if (!$post) {
            return ['success' => false, 'message' => 'Post not found.'];
        }
        if ((int) $post->user_id !== $userId) {
            return ['success' => false, 'message' => 'You can only manage your own posts.'];
        }
        if (!$post->is_highlighted) {
            return ['success' => false, 'message' => 'This post is not highlighted.'];
        }

        DB::table('project_posts')->where('post_id', $postId)->update([
            'is_highlighted'  => false,
            'highlighted_at'  => null,
            'updated_at'      => now(),
        ]);

        return ['success' => true, 'message' => 'Highlight removed.'];
    }

    /**
     * Get highlighted posts for a user.
     */
    public function getHighlightedPosts(int $userId): array
    {
        $posts = DB::table('project_posts')
            ->where('user_id', $userId)
            ->where('is_highlighted', true)
            ->where('status', '!=', 'deleted')
            ->orderBy('highlighted_at', 'desc')
            ->get();

        return [
            'highlights' => $posts,
            'count'      => $posts->count(),
            'max'        => self::MAX_HIGHLIGHTS,
        ];
    }

    /* ──────────────────────────────────────────────────────────────────
     * TRADITIONAL PROJECTS (projects table — for project listing pages)
     * ────────────────────────────────────────────────────────────────── */

    /**
     * Highlight a project (owned by user).
     */
    public function highlightProject(int $userId, int $projectId): array
    {
        // Verify ownership via project_relationships → property_owners
        $project = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->where('p.project_id', $projectId)
            ->where('po.user_id', $userId)
            ->select('p.project_id', 'p.is_highlighted')
            ->first();

        if (!$project) {
            return ['success' => false, 'message' => 'Project not found or not owned by you.'];
        }

        if ($project->is_highlighted) {
            return ['success' => false, 'message' => 'This project is already highlighted.'];
        }

        // Check limit
        $currentCount = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->where('po.user_id', $userId)
            ->where('p.is_highlighted', true)
            ->count();

        if ($currentCount >= self::MAX_HIGHLIGHTS) {
            return [
                'success' => false,
                'message' => 'You can highlight up to ' . self::MAX_HIGHLIGHTS . ' projects.',
            ];
        }

        DB::table('projects')->where('project_id', $projectId)->update([
            'is_highlighted' => true,
            'highlighted_at' => now(),
        ]);

        return ['success' => true, 'message' => 'Project highlighted successfully.'];
    }

    /**
     * Remove highlight from a project.
     */
    public function unhighlightProject(int $userId, int $projectId): array
    {
        $project = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->where('p.project_id', $projectId)
            ->where('po.user_id', $userId)
            ->select('p.project_id', 'p.is_highlighted')
            ->first();

        if (!$project) {
            return ['success' => false, 'message' => 'Project not found or not owned by you.'];
        }

        DB::table('projects')->where('project_id', $projectId)->update([
            'is_highlighted' => false,
            'highlighted_at' => null,
        ]);

        return ['success' => true, 'message' => 'Highlight removed.'];
    }
}
