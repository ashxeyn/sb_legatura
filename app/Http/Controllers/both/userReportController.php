<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class userReportController extends Controller
{
    // Allowed attachment MIME types
    private const ALLOWED_MIME = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /**
     * POST /api/user-reports
     * Create a new user report (report one user by another)
     */
    public function store(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $validated = $request->validate([
            'reported_user_id' => 'required|integer|min:1',
            'reason'            => 'required|string|max:120',
            'description'       => 'nullable|string|max:3000',
            'attachments'       => 'nullable|array|max:5',
            'attachments.*'     => 'file|max:10240|mimetypes:' . implode(',', self::ALLOWED_MIME),
        ]);

        // Ensure reported user exists
        if (!DB::table('users')->where('user_id', (int) $validated['reported_user_id'])->exists()) {
            return response()->json(['success' => false, 'message' => 'Reported user not found.'], 404);
        }

        // Prevent reporting oneself
        if ((int) $validated['reported_user_id'] === (int) $userId) {
            return response()->json(['success' => false, 'message' => 'You cannot report yourself.'], 400);
        }

        // Multiple reports are allowed; do not block duplicate pending reports.

        $reportId = DB::table('user_reports')->insertGetId([
            'reporter_user_id' => $userId,
            'reported_user_id' => (int) $validated['reported_user_id'],
            'reason'           => trim($validated['reason']),
            'description'      => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'status'           => 'pending',
            'created_at'       => now(),
        ], 'report_id');

        // Save any attachments under unified report_attachments
        $this->saveAttachments($request, 'user_report', $reportId);

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully.',
            'data'    => ['report_id' => $reportId],
        ], 201);
    }

    /**
     * GET /api/user-reports/mine
     * List reports submitted by the authenticated user
     */
    public function mine(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $reports = DB::table('user_reports')
            ->where('reporter_user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => $this->appendAttachments($r, 'user_report'));

        return response()->json(['success' => true, 'data' => $reports]);
    }

    /**
     * Save attachments for a report into report_attachments table.
     */
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

            $ext = $file->getClientOriginalExtension();
            $filename = Str::uuid() . ($ext ? '.' . $ext : '');
            $path = $file->storeAs($dir, $filename, 'public');

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

    /**
     * Attach storage URLs to a report object.
     */
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

    /**
     * Resolve user id from session, request user, or bearer token (Sanctum).
     */
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
                Log::warning('userReportController bearer fallback: ' . $e->getMessage());
            }
        }

        if (!$user) {
            return null;
        }

        return is_object($user) ? ($user->user_id ?? $user->id ?? null) : ($user['user_id'] ?? null);
    }
}
