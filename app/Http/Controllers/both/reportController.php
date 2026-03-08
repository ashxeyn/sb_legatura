<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class reportController extends Controller
{
    public function store(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $validated = $request->validate([
            'post_type' => 'required|string|in:project,showcase',
            'post_id'   => 'required|integer|min:1',
            'reason'       => 'required|string|max:120',
            'details'      => 'nullable|string|max:3000',
        ]);

        // Basic content existence check.
        if (!$this->postExists($validated['post_type'], (int) $validated['post_id'])) {
            return response()->json(['success' => false, 'message' => 'Target content not found.'], 404);
        }

        // Multiple reports from the same reporter are allowed by product requirement.


        $reportId = DB::table('post_reports')->insertGetId([
            'reporter_user_id' => $userId,
            'post_type'        => $validated['post_type'],
            'post_id'          => $validated['post_id'],
            'reason'           => trim($validated['reason']),
            'details'          => isset($validated['details']) ? trim((string) $validated['details']) : null,
            'status'           => 'pending',
            'created_at'       => now(),
            'updated_at'       => now(),
        ], 'report_id');

        // Log project reported activity
        if ($validated['post_type'] === 'project') {
            \\App\Services\UserActivityLogger::projectReported($userId, $reportId, $validated['post_id']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully.',
            'data'    => ['report_id' => $reportId],
        ], 201);
    }

    public function mine(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $reports = DB::table('post_reports as cr')
            ->where('cr.reporter_user_id', $userId)
            ->orderByDesc('cr.created_at')
            ->select('cr.*')
            ->get();

        return response()->json(['success' => true, 'data' => $reports]);
    }

    public function adminIndex(Request $request)
    {
        [$adminUserId, $error] = $this->requireAdmin($request);
        if ($error) {
            return $error;
        }

        $status = $request->query('status', 'pending');
        $postType = $request->query('post_type');

        $query = DB::table('post_reports as cr')
            ->leftJoin('users as reporter', 'cr.reporter_user_id', '=', 'reporter.user_id')
            ->leftJoin('users as reviewer', 'cr.reviewed_by_user_id', '=', 'reviewer.user_id')
            ->select(
                'cr.*',
                'reporter.username as reporter_username',
                'reviewer.username as reviewer_username'
            )
            ->orderByDesc('cr.created_at');

        if ($status !== 'all') {
            $query->where('cr.status', $status);
        }
        if ($postType && in_array($postType, ['project', 'showcase'])) {
            $query->where('cr.post_type', $postType);
        }

        $reports = $query->get()->map(function ($report) {
            $report->content_preview = $this->getPostPreview($report->post_type, (int) $report->post_id);
            return $report;
        });

        return response()->json(['success' => true, 'data' => $reports]);
    }

    public function adminShow(Request $request, int $reportId)
    {
        [$adminUserId, $error] = $this->requireAdmin($request);
        if ($error) {
            return $error;
        }

        $report = DB::table('post_reports as cr')
            ->leftJoin('users as reporter', 'cr.reporter_user_id', '=', 'reporter.user_id')
            ->leftJoin('users as reviewer', 'cr.reviewed_by_user_id', '=', 'reviewer.user_id')
            ->where('cr.report_id', $reportId)
            ->select(
                'cr.*',
                'reporter.username as reporter_username',
                'reporter.email as reporter_email',
                'reviewer.username as reviewer_username'
            )
            ->first();

        if (!$report) {
            return response()->json(['success' => false, 'message' => 'Report not found.'], 404);
        }

        $report->content_preview = $this->getPostPreview($report->post_type, (int) $report->post_id);

        return response()->json(['success' => true, 'data' => $report]);
    }

    public function adminReview(Request $request, int $reportId)
    {
        [$adminUserId, $error] = $this->requireAdmin($request);
        if ($error) {
            return $error;
        }

        $validated = $request->validate([
            'status'        => 'required|string|in:under_review,resolved,dismissed',
            'admin_notes'   => 'nullable|string|max:3000',
            'hide_content'  => 'nullable|boolean',
        ]);

        $report = DB::table('post_reports')->where('report_id', $reportId)->first();
        if (!$report) {
            return response()->json(['success' => false, 'message' => 'Report not found.'], 404);
        }

        DB::beginTransaction();
        try {
            DB::table('post_reports')->where('report_id', $reportId)->update([
                'status'              => $validated['status'],
                'admin_notes'         => $validated['admin_notes'] ?? null,
                'reviewed_by_user_id' => $adminUserId,
                'reviewed_at'         => now(),
                'updated_at'          => now(),
            ]);

            if (!empty($validated['hide_content'])) {
                $this->hidePost($report->post_type, (int) $report->post_id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Report reviewed successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('reportController adminReview failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to review report.'], 500);
        }
    }

    private function postExists(string $type, int $id): bool
    {
        if ($type === 'project') {
            return DB::table('projects')->where('project_id', $id)->exists();
        }

        return DB::table('showcases')->where('post_id', $id)->exists();
    }

    private function getPostPreview(string $type, int $id): ?object
    {
        if ($type === 'project') {
            return DB::table('projects as p')
                ->leftJoin('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->select(
                    DB::raw("'project' as post_type"),
                    'p.project_id as id',
                    'p.project_title as title',
                    'p.project_description as content',
                    'p.project_status as status',
                    'pr.project_post_status as moderation_status',
                    'p.created_at'
                )
                ->where('p.project_id', $id)
                ->first();
        }

        return DB::table('showcases')
            ->select(
                DB::raw("'showcase' as post_type"),
                'post_id as id',
                'title',
                'content',
                'status',
                DB::raw('NULL as moderation_status'),
                'created_at'
            )
            ->where('post_id', $id)
            ->first();
    }

    private function hidePost(string $type, int $id): void
    {
        if ($type === 'project') {
            DB::table('project_relationships as pr')
                ->join('projects as p', 'pr.rel_id', '=', 'p.relationship_id')
                ->where('p.project_id', $id)
                ->update([
                    'pr.project_post_status' => 'deleted',
                    'pr.updated_at'          => now(),
                ]);

            return;
        }

        DB::table('showcases')->where('post_id', $id)->update([
            'status'     => 'deleted',
            'updated_at' => now(),
        ]);
    }

    private function requireAdmin(Request $request): array
    {
        $sessionAdmin = Session::get('admin');
        if ($sessionAdmin && isset($sessionAdmin->user_id)) {
            return [(int) $sessionAdmin->user_id, null];
        }

        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return [null, response()->json(['success' => false, 'message' => 'Authentication required.'], 401)];
        }

        $user = DB::table('users')->where('user_id', $userId)->first();
        $isAdmin = ($user && (($user->user_type ?? null) === 'admin'))
            || DB::table('admin_users')->where('user_id', $userId)->exists();

        if (!$isAdmin) {
            return [null, response()->json(['success' => false, 'message' => 'Admin access required.'], 403)];
        }

        return [$userId, null];
    }

    private function resolveUserId(Request $request): ?int
    {
        $user = Session::get('user') ?: $request->user();
        if (!$user && $request->bearerToken()) {
            try {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
                if ($token && $token->tokenable) {
                    $user = $token->tokenable;
                }
            } catch (\Throwable $e) {
                Log::warning('reportController bearer fallback: ' . $e->getMessage());
            }
        }

        if (!$user) {
            return null;
        }

        return is_object($user) ? ($user->user_id ?? $user->id ?? null) : ($user['user_id'] ?? null);
    }
}
