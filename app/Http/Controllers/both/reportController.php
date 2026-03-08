<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class reportController extends Controller
{
    // Allowed MIME types for report attachments
    private const ALLOWED_MIME = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    // â”€â”€ POST /api/reports  (project / showcase reports) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function store(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $validated = $request->validate([
            'post_type'     => 'required|string|in:project,showcase',
            'post_id'       => 'required|integer|min:1',
            'reason'        => 'required|string|max:120',
            'details'       => 'nullable|string|max:3000',
            'attachments'   => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimetypes:' . implode(',', self::ALLOWED_MIME),
        ]);

        if (!$this->postExists($validated['post_type'], (int) $validated['post_id'])) {
            return response()->json(['success' => false, 'message' => 'Target content not found.'], 404);
        }

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
            \App\Services\UserActivityLogger::projectReported($userId, $reportId, $validated['post_id']);
        }

        $this->saveAttachments($request, 'post_report', $reportId);

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully.',
            'data'    => ['report_id' => $reportId],
        ], 201);
    }

    // â”€â”€ POST /api/review-reports  (review reports, separate table) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function storeReviewReport(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $validated = $request->validate([
            'review_id'     => 'required|integer|min:1',
            'reason'        => 'required|string|max:120',
            'details'       => 'nullable|string|max:3000',
            'attachments'   => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimetypes:' . implode(',', self::ALLOWED_MIME),
        ]);

        if (!DB::table('reviews')->where('review_id', (int) $validated['review_id'])->exists()) {
            return response()->json(['success' => false, 'message' => 'Review not found.'], 404);
        }

        $reportId = DB::table('review_reports')->insertGetId([
            'reporter_user_id' => $userId,
            'review_id'        => (int) $validated['review_id'],
            'reason'           => trim($validated['reason']),
            'details'          => isset($validated['details']) ? trim((string) $validated['details']) : null,
            'status'           => 'pending',
            'created_at'       => now(),
            'updated_at'       => now(),
        ], 'report_id');

        $this->saveAttachments($request, 'review_report', $reportId);

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
            ->get()
            ->map(fn($r) => $this->appendAttachments($r, 'post_report'));

        return response()->json(['success' => true, 'data' => $reports]);
    }

    public function myReviewReports(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $reports = DB::table('review_reports')
            ->where('reporter_user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => $this->appendAttachments($r, 'review_report'));

        return response()->json(['success' => true, 'data' => $reports]);
    }

    public function adminIndex(Request $request)
    {
        [$adminUserId, $error] = $this->requireAdmin($request);
        if ($error) {
            return $error;
        }

        $status   = $request->query('status', 'pending');
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
            return $this->appendAttachments($report, 'post_report');
        });

        return response()->json(['success' => true, 'data' => $reports]);
    }

    public function adminReviewReportsIndex(Request $request)
    {
        [$adminUserId, $error] = $this->requireAdmin($request);
        if ($error) {
            return $error;
        }

        $status = $request->query('status', 'pending');

        $query = DB::table('review_reports as rr')
            ->leftJoin('users as reporter', 'rr.reporter_user_id', '=', 'reporter.user_id')
            ->leftJoin('users as reviewer', 'rr.reviewed_by_user_id', '=', 'reviewer.user_id')
            ->leftJoin('reviews as rv', 'rr.review_id', '=', 'rv.review_id')
            ->leftJoin('users as reviewee', 'rv.reviewee_user_id', '=', 'reviewee.user_id')
            ->select(
                'rr.*',
                'reporter.username as reporter_username',
                'reviewer.username as reviewer_username',
                'rv.rating as review_rating',
                'rv.comment as review_comment',
                'reviewee.username as reviewee_username'
            )
            ->orderByDesc('rr.created_at');

        if ($status !== 'all') {
            $query->where('rr.status', $status);
        }

        $reports = $query->get()
            ->map(fn($r) => $this->appendAttachments($r, 'review_report'));

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

        return response()->json(['success' => true, 'data' => $this->appendAttachments($report, 'post_report')]);
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

            return response()->json(['success' => true, 'message' => 'Report reviewed successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('reportController adminReview failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to review report.'], 500);
        }
    }

    public function adminReviewReportAction(Request $request, int $reportId)
    {
        [$adminUserId, $error] = $this->requireAdmin($request);
        if ($error) {
            return $error;
        }

        $validated = $request->validate([
            'status'      => 'required|string|in:under_review,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:3000',
        ]);

        $report = DB::table('review_reports')->where('report_id', $reportId)->first();
        if (!$report) {
            return response()->json(['success' => false, 'message' => 'Report not found.'], 404);
        }

        DB::table('review_reports')->where('report_id', $reportId)->update([
            'status'              => $validated['status'],
            'admin_notes'         => $validated['admin_notes'] ?? null,
            'reviewed_by_user_id' => $adminUserId,
            'reviewed_at'         => now(),
            'updated_at'          => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Review report actioned successfully.']);
    }

    // â”€â”€ Attachment helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function saveAttachments(Request $request, string $reportType, int $reportId): void
    {
        if (!$request->hasFile('attachments')) {
            return;
        }

        $dir = 'report_attachments/' . $reportType . '/' . $reportId;

        foreach ($request->file('attachments') as $file) {
            if (!$file->isValid()) {
                continue;
            }

            $ext      = $file->getClientOriginalExtension();
            $filename = Str::uuid() . ($ext ? '.' . $ext : '');
            $path     = $file->storeAs($dir, $filename, 'public');

            DB::table('report_attachments')->insert([
                'report_type'   => $reportType,
                'report_id'     => $reportId,
                'original_name' => $file->getClientOriginalName(),
                'file_path'     => $path,
                'mime_type'     => $file->getMimeType() ?? $file->getClientMimeType(),
                'file_size'     => $file->getSize(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    private function appendAttachments(object $report, string $reportType): object
    {
        $report->attachments = DB::table('report_attachments')
            ->where('report_type', $reportType)
            ->where('report_id', $report->report_id)
            ->get()
            ->map(function ($a) {
                $a->url = Storage::disk('public')->url($a->file_path);
                return $a;
            });

        return $report;
    }

    // â”€â”€ Internal helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

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

        $user    = DB::table('users')->where('user_id', $userId)->first();
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
