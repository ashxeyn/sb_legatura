<?php
namespace App\Models\admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class reportManagementClass
{
    private static array $userNameCache = [];

    private static function formatUserDisplayName($user): string
    {
        if (!$user) {
            return 'Unknown';
        }

        $firstName = trim((string) ($user->first_name ?? ''));
        $lastName = trim((string) ($user->last_name ?? ''));
        $username = trim((string) ($user->username ?? ''));
        $email = trim((string) ($user->email ?? ''));
        $fullName = trim($firstName . ' ' . $lastName);

        if ($fullName !== '' && $username !== '') {
            return $fullName . ' (@' . $username . ')';
        }

        if ($fullName !== '') {
            return $fullName;
        }

        if ($username !== '') {
            return '@' . $username;
        }

        return $email !== '' ? $email : 'Unknown';
    }

    private static function normalizeStoragePath($path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = preg_replace('#^\\/?storage/app/public/#i', '', (string) $path);
        $path = preg_replace('#^\\/?public/#i', '', $path);
        $path = preg_replace('#^\\/?storage/#i', '', $path);

        return ltrim($path, "/\\");
    }

    private static function getUserDisplayNameById($userId): string
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return 'Unknown';
        }

        if (array_key_exists($userId, self::$userNameCache)) {
            return self::$userNameCache[$userId];
        }

        $user = DB::table('users')
            ->where('user_id', $userId)
            ->select('first_name', 'last_name', 'username', 'email')
            ->first();

        $name = self::formatUserDisplayName($user);
        self::$userNameCache[$userId] = $name;

        return $name;
    }

    private static function resolveReporterDisplayName($row): string
    {
        $display = self::formatUserDisplayName($row);
        if ($display !== 'Unknown') {
            return $display;
        }

        $reporterUserId = (int) ($row->reporter_user_id ?? 0);
        if ($reporterUserId > 0) {
            return self::getUserDisplayNameById($reporterUserId);
        }

        return 'Unknown';
    }

    public static function resolveReportedUserIdForCase($source, $reportId): ?int
    {
        $detail = self::getReportWithEvidence($source, $reportId);
        $candidate = (int) ($detail['reported_user_id'] ?? 0);

        return $candidate > 0 ? $candidate : null;
    }

    /**
     * Unified moderation feed for reports + disputes with normalized fields.
     */
    public static function getAllModerationCases($filters = [], $page = 1, $perPage = 15)
    {
        $mappedFilters = [
            'status' => $filters['status'] ?? 'all',
            'source' => 'all',
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
        ];

        $rows = self::getActiveReports($mappedFilters)->map(function ($row) {
            $isDispute = ($row->report_source ?? null) === 'dispute';
            $sourceType = match ($row->report_source ?? null) {
                'dispute' => 'dispute',
                'review' => 'review',
                default => strtolower((string) ($row->content_type ?? '')),
            };

            return (object) [
                'case_id' => ($isDispute ? 'D-' : 'R-') . $row->report_id,
                'case_ref_id' => (int) $row->report_id,
                'case_type' => $isDispute ? 'dispute' : 'report',
                'source_type' => strtolower((string) $sourceType),
                'source' => $row->report_source,
                'reporter' => self::resolveReporterDisplayName($row),
                'target' => self::resolveCaseTarget($row),
                'reason' => $row->reason ?? '-',
                'status' => $row->status ?? 'pending',
                'admin_action' => $row->admin_action ?? null,
                'created_at' => $row->created_at,
            ];
        });

        // Apply search after mapping so reporter, target, and reason are all resolved
        if (!empty($filters['search'])) {
            $s = strtolower((string) $filters['search']);
            $rows = $rows->filter(function ($row) use ($s) {
                return str_contains(strtolower((string) ($row->reporter ?? '')), $s)
                    || str_contains(strtolower((string) ($row->target ?? '')), $s)
                    || str_contains(strtolower((string) ($row->reason ?? '')), $s)
                    || str_contains(strtolower((string) ($row->case_id ?? '')), $s);
            })->values();
        }

        if (!empty($filters['case_type']) && $filters['case_type'] !== 'all') {
            $rows = $rows->where('case_type', strtolower((string) $filters['case_type']));
        }

        if (!empty($filters['source_type']) && $filters['source_type'] !== 'all') {
            $wanted = strtolower((string) $filters['source_type']);
            $rows = $rows->where('source_type', $wanted);
        }

        $rows = $rows->sortByDesc('created_at')->values();

        $total = $rows->count();
        $page = max(1, (int) $page);
        $perPage = max(1, (int) $perPage);
        $offset = ($page - 1) * $perPage;

        $paged = $rows->slice($offset, $perPage)->values();

        return [
            'data' => $paged,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => (int) max(1, ceil($total / $perPage)),
            ],
        ];
    }

    /**
     * Fetch all reports from supported moderation sources
     * unified into a single collection for the Moderation Hub.
     */
    public static function getActiveReports($filters = [])
    {
        $reports = collect();

        // Post Reports
        if (Schema::hasTable('post_reports')) {
            $postAdminActionSelect = Schema::hasColumn('post_reports', 'admin_action')
                ? DB::raw('post_reports.admin_action as admin_action')
                : DB::raw('NULL as admin_action');

            $postReports = DB::table('post_reports')
                ->leftJoin('users as reporter', 'post_reports.reporter_user_id', '=', 'reporter.user_id')
                ->select(
                    'post_reports.report_id',
                    DB::raw("'post' as report_source"),
                    'post_reports.post_type as content_type',
                    'post_reports.post_id as content_id',
                    'post_reports.reason',
                    'post_reports.details',
                    'post_reports.status',
                    $postAdminActionSelect,
                    'post_reports.admin_notes',
                    'post_reports.reviewed_at',
                    'post_reports.created_at',
                    'reporter.first_name',
                    'reporter.last_name',
                    'reporter.username as reporter_username',
                    'reporter.email as reporter_email',
                    'reporter.user_id as reporter_user_id'
                )
                ->get();
            $reports = $reports->merge($postReports);
        }

        // Review Reports
        if (Schema::hasTable('review_reports')) {
            $reviewAdminActionSelect = Schema::hasColumn('review_reports', 'admin_action')
                ? DB::raw('review_reports.admin_action as admin_action')
                : DB::raw('NULL as admin_action');

            $reviewReports = DB::table('review_reports')
                ->leftJoin('users as reporter', 'review_reports.reporter_user_id', '=', 'reporter.user_id')
                ->leftJoin('reviews', 'review_reports.review_id', '=', 'reviews.review_id')
                ->select(
                    'review_reports.report_id',
                    DB::raw("'review' as report_source"),
                    DB::raw("'review' as content_type"),
                    'review_reports.review_id as content_id',
                    'review_reports.reason',
                    'review_reports.details',
                    'review_reports.status',
                    $reviewAdminActionSelect,
                    'review_reports.admin_notes',
                    'review_reports.reviewed_at',
                    'review_reports.created_at',
                    'reporter.first_name',
                    'reporter.last_name',
                    'reporter.username as reporter_username',
                    'reporter.email as reporter_email',
                    'reporter.user_id as reporter_user_id'
                )
                ->get();
            $reports = $reports->merge($reviewReports);
        }

        // Content Reports
        if (Schema::hasTable('content_reports')) {
            $contentAdminActionSelect = Schema::hasColumn('content_reports', 'admin_action')
                ? DB::raw('content_reports.admin_action as admin_action')
                : DB::raw('NULL as admin_action');

            $contentReports = DB::table('content_reports')
                ->leftJoin('users as reporter', 'content_reports.reporter_user_id', '=', 'reporter.user_id')
                ->select(
                    'content_reports.report_id',
                    DB::raw("'content' as report_source"),
                    'content_reports.content_type',
                    'content_reports.content_id',
                    'content_reports.reason',
                    'content_reports.details',
                    'content_reports.status',
                    $contentAdminActionSelect,
                    'content_reports.admin_notes',
                    'content_reports.reviewed_at',
                    'content_reports.created_at',
                    'reporter.first_name',
                    'reporter.last_name',
                    'reporter.username as reporter_username',
                    'reporter.email as reporter_email',
                    'reporter.user_id as reporter_user_id'
                )
                ->get();
            $reports = $reports->merge($contentReports);
        }

        // User-to-user Reports
        if (Schema::hasTable('user_reports')) {
            $userAdminActionSelect = Schema::hasColumn('user_reports', 'admin_action')
                ? DB::raw('user_reports.admin_action as admin_action')
                : DB::raw('NULL as admin_action');

            $userReports = DB::table('user_reports')
                ->leftJoin('users as reporter', 'user_reports.reporter_user_id', '=', 'reporter.user_id')
                ->select(
                    'user_reports.report_id',
                    DB::raw("'user' as report_source"),
                    DB::raw("'user' as content_type"),
                    'user_reports.reported_user_id as content_id',
                    'user_reports.reason',
                    'user_reports.description as details',
                    'user_reports.status',
                    $userAdminActionSelect,
                    DB::raw('NULL as admin_notes'),
                    DB::raw('NULL as reviewed_at'),
                    'user_reports.created_at',
                    'reporter.first_name',
                    'reporter.last_name',
                    'reporter.username as reporter_username',
                    'reporter.email as reporter_email',
                    'reporter.user_id as reporter_user_id'
                )
                ->get();
            $reports = $reports->merge($userReports);
        }

        // Disputes (user-level reports)
        if (Schema::hasTable('disputes')) {
            $disputes = DB::table('disputes')
                ->leftJoin('users as reporter', 'disputes.raised_by_user_id', '=', 'reporter.user_id')
                ->leftJoin('users as accused', 'disputes.against_user_id', '=', 'accused.user_id')
                ->select(
                    'disputes.dispute_id as report_id',
                    DB::raw("'dispute' as report_source"),
                    DB::raw("'dispute' as content_type"),
                    'disputes.against_user_id as content_id',
                    DB::raw('disputes.dispute_type as reason'),
                    'disputes.dispute_desc as details',
                    DB::raw("CASE 
                        WHEN disputes.dispute_status = 'open' THEN 'pending'
                        WHEN disputes.dispute_status = 'under_review' THEN 'under_review'
                        WHEN disputes.dispute_status = 'resolved' THEN 'resolved'
                        WHEN disputes.dispute_status = 'cancelled' THEN 'dismissed'
                        ELSE disputes.dispute_status
                    END as status"),
                    (Schema::hasColumn('disputes', 'admin_action')
                        ? DB::raw('disputes.admin_action as admin_action')
                        : DB::raw('NULL as admin_action')),
                    'disputes.admin_response as admin_notes',
                    'disputes.resolved_at as reviewed_at',
                    'disputes.created_at',
                    'reporter.first_name',
                    'reporter.last_name',
                    'reporter.username as reporter_username',
                    'reporter.email as reporter_email',
                    'reporter.user_id as reporter_user_id'
                )
                ->get();
            $reports = $reports->merge($disputes);
        }

        // Apply filters
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $reports = $reports->where('status', $filters['status']);
        }
        if (!empty($filters['source']) && $filters['source'] !== 'all') {
            $reports = $reports->where('report_source', $filters['source']);
        }
        if (!empty($filters['search'])) {
            $s = strtolower($filters['search']);
            $reports = $reports->filter(function ($r) use ($s) {
                return str_contains(strtolower(self::formatUserDisplayName($r)), $s)
                    || str_contains(strtolower($r->reason ?? ''), $s)
                    || str_contains(strtolower($r->details ?? ''), $s);
            });
        }
        if (!empty($filters['date_from'])) {
            $reports = $reports->where('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $reports = $reports->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        // Sort newest first, with report_id as a stable tie-breaker.
        $reports = $reports->sort(function ($a, $b) {
            $aTime = strtotime((string) ($a->created_at ?? '')) ?: 0;
            $bTime = strtotime((string) ($b->created_at ?? '')) ?: 0;

            if ($aTime === $bTime) {
                return (int) ($b->report_id ?? 0) <=> (int) ($a->report_id ?? 0);
            }

            return $bTime <=> $aTime;
        })->values();

        return $reports;
    }

    /**
     * Get active reports with in-memory pagination.
     */
    public static function getActiveReportsPaginated($filters = [], $page = 1, $perPage = 10)
    {
        $page = max(1, (int) $page);
        $perPage = max(1, (int) $perPage);

        $allReports = self::getActiveReports($filters);
        $total = $allReports->count();
        $lastPage = max(1, (int) ceil($total / $perPage));

        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $data = $allReports->forPage($page, $perPage)->values();

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => $lastPage,
        ];
    }

    /**
     * Get counts for stat cards
     */
    public static function getCounts()
    {
        $all = self::getActiveReports();

        return [
            'total' => $all->count(),
            'pending' => $all->where('status', 'pending')->count(),
            'under_review' => $all->where('status', 'under_review')->count(),
            'resolved' => $all->where('status', 'resolved')->count() + $all->where('status', 'dismissed')->count(),
        ];
    }

    /**
     * Get reporter statistics for Report History tab
     */
    public static function getReporterStats()
    {
        $all = self::getActiveReports();

        $grouped = $all->groupBy('reporter_user_id');

        $stats = $grouped->map(function ($reports, $userId) {
            $first = $reports->first();
            $totalReports = $reports->count();
            $dismissed = $reports->where('status', 'dismissed')->count();
            $resolved = $reports->where('status', 'resolved')->count();
            $pending = $reports->where('status', 'pending')->count();
            $underReview = $reports->where('status', 'under_review')->count();

            $dismissRate = $totalReports > 0 ? round(($dismissed / $totalReports) * 100, 1) : 0;

            return (object)[
                'reporter_user_id' => $userId,
                'reporter_username' => self::resolveReporterDisplayName($first),
                'total_reports' => $totalReports,
                'dismissed_count' => $dismissed,
                'resolved_count' => $resolved,
                'pending_count' => $pending,
                'under_review_count' => $underReview,
                'dismiss_rate' => $dismissRate,
                'is_super_reporter' => $totalReports >= 10,
                'is_potential_abuser' => $dismissRate >= 50 && $totalReports >= 5,
                'latest_report' => $reports->sortByDesc('created_at')->first()->created_at ?? null,
                'sources' => $reports->pluck('report_source')->unique()->values()->toArray(),
            ];
        })->sortByDesc('total_reports')->values();

        return $stats;
    }

    /**
     * Update report status (for moderation actions)
     */
    public static function updateReportStatus($source, $reportId, $status, $adminNotes = null, $adminUserId = null, $adminAction = null)
    {
        $table = self::getTableForSource($source);
        if (!$table) return false;

        $idCol = $source === 'dispute' ? 'dispute_id' : 'report_id';

        if ($source === 'dispute') {
            // Disputes use different column names
            $disputeStatus = match ($status) {
                'pending' => 'open',
                'under_review' => 'under_review',
                'resolved' => 'resolved',
                'dismissed' => 'cancelled',
                default => $status,
            };
            $data = ['dispute_status' => $disputeStatus];
            if ($adminNotes !== null) $data['admin_response'] = $adminNotes;
            if (in_array($status, ['resolved', 'dismissed'])) {
                $data['resolved_at'] = now();
            }
            if ($adminAction !== null && Schema::hasColumn('disputes', 'admin_action')) {
                $data['admin_action'] = $adminAction;
            }
        } elseif ($source === 'user') {
            // user_reports schema only tracks status and core report fields.
            $data = ['status' => $status];
            if ($adminAction !== null && Schema::hasColumn('user_reports', 'admin_action')) {
                $data['admin_action'] = $adminAction;
            }
        } else {
            $data = ['status' => $status];
            if ($adminNotes !== null) $data['admin_notes'] = $adminNotes;
            if ($adminUserId !== null) $data['reviewed_by_user_id'] = $adminUserId;
            if (in_array($status, ['resolved', 'dismissed'])) {
                $data['reviewed_at'] = now();
            }
            if ($adminAction !== null && Schema::hasColumn($table, 'admin_action')) {
                $data['admin_action'] = $adminAction;
            }
            $data['updated_at'] = now();
        }

        return DB::table($table)->where($idCol, $reportId)->update($data);
    }

    /**
     * Get table name from source type
     */
    private static function getTableForSource($source)
    {
        return match ($source) {
            'post' => 'post_reports',
            'review' => 'review_reports',
            'content' => 'content_reports',
            'user' => 'user_reports',
            'dispute' => 'disputes',
            default => null,
        };
    }

    /**
     * Get detail of a specific report with evidence (the reported content)
     */
    public static function getReportWithEvidence($source, $reportId)
    {
        $table = self::getTableForSource($source);
        if (!$table) return null;

        $idCol = $source === 'dispute' ? 'dispute_id' : 'report_id';

        $report = DB::table($table)->where($idCol, $reportId)->first();
        if (!$report) return null;

        $result = [
            'report_id'   => $reportId,
            'source'      => $source,
            'reporter'    => null,
            'reported_user_id' => null,
            'evidence'    => null,
        ];

        // Get reporter info
        $reporterUserId = match ($source) {
            'dispute' => $report->raised_by_user_id ?? null,
            default   => $report->reporter_user_id ?? null,
        };
        if ($reporterUserId) {
            $result['reporter'] = DB::table('users')
                ->where('user_id', $reporterUserId)
                ->select('user_id', 'first_name', 'last_name', 'username', 'email', 'user_type')
                ->first();
        }

        // Build report meta
        if ($source === 'post') {
            $result['reason']       = $report->reason;
            $result['details']      = $report->details;
            $result['status']       = $report->status;
            $result['content_type'] = $report->post_type;
            $result['admin_notes']  = $report->admin_notes ?? null;
            $result['created_at']   = $report->created_at;

            // Evidence: fetch the actual post
            if ($report->post_type === 'showcase') {
                $showcasePostId = (int) ($report->post_id ?? 0);
                $post = DB::table('showcases')
                    ->leftJoin('users', 'showcases.user_id', '=', 'users.user_id')
                    ->leftJoin('projects as linked_project', 'showcases.linked_project_id', '=', 'linked_project.project_id')
                    ->where('showcases.post_id', $showcasePostId)
                    ->select(
                        'showcases.*',
                        'users.first_name',
                        'users.last_name',
                        'users.username',
                        'users.user_id as author_user_id',
                        'linked_project.project_title as linked_project_title'
                    )
                    ->first();
                $images = collect();
                if ($post && Schema::hasTable('showcase_images')) {
                    $images = DB::table('showcase_images')
                        ->where('post_id', $showcasePostId)
                        ->orderBy('sort_order')
                        ->select('original_name', 'file_path', 'sort_order')
                        ->get()
                        ->map(function ($image) {
                            return (object) [
                                'original_name' => $image->original_name,
                                'file_path' => self::normalizeStoragePath($image->file_path),
                                'sort_order' => $image->sort_order,
                            ];
                        });
                }
                $result['evidence'] = $post ? ['type' => 'showcase', 'data' => $post, 'images' => $images] : null;
                $result['reported_user_id'] = (int) ($post->author_user_id ?? 0) ?: null;
            } elseif ($report->post_type === 'project') {
                $post = DB::table('projects')
                    ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                    ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
                    ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id')
                    ->leftJoin('contractor_types', 'projects.type_id', '=', 'contractor_types.type_id')
                    ->where('projects.project_id', $report->post_id)
                    ->select(
                        'projects.*',
                        'project_relationships.created_at as relationship_created_at',
                        'users.first_name',
                        'users.last_name',
                        'users.username',
                        'users.user_id as author_user_id',
                        'contractor_types.type_name as category_name'
                    )
                    ->first();
                $files = collect();
                if ($post && Schema::hasTable('project_files')) {
                    $files = DB::table('project_files')
                        ->where('project_id', $report->post_id)
                        ->orderBy('uploaded_at')
                        ->select('file_type', 'file_path', 'uploaded_at')
                        ->get()
                        ->map(function ($file) {
                            return (object) [
                                'file_type' => $file->file_type,
                                'file_path' => self::normalizeStoragePath($file->file_path),
                                'uploaded_at' => $file->uploaded_at,
                            ];
                        });
                }
                $result['evidence'] = $post ? ['type' => 'project', 'data' => $post, 'files' => $files] : null;
                $result['reported_user_id'] = (int) ($post->author_user_id ?? 0) ?: null;
            }

        } elseif ($source === 'review') {
            $result['reason']       = $report->reason;
            $result['details']      = $report->details;
            $result['status']       = $report->status;
            $result['content_type'] = 'review';
            $result['admin_notes']  = $report->admin_notes ?? null;
            $result['created_at']   = $report->created_at;

            // Evidence: fetch the review
            $review = DB::table('reviews')
                ->leftJoin('users as reviewer', 'reviews.reviewer_user_id', '=', 'reviewer.user_id')
                ->leftJoin('users as reviewee', 'reviews.reviewee_user_id', '=', 'reviewee.user_id')
                ->leftJoin('projects', 'reviews.project_id', '=', 'projects.project_id')
                ->where('reviews.review_id', $report->review_id)
                ->select(
                    'reviews.*',
                    'reviewer.first_name',
                    'reviewer.last_name',
                    'reviewer.username',
                    'reviewer.user_id as reviewer_account_user_id',
                    'reviewee.user_id as reviewee_account_user_id',
                    'reviewee.first_name as reviewee_first_name',
                    'reviewee.last_name as reviewee_last_name',
                    'reviewee.username as reviewee_username',
                    'projects.project_title'
                )
                ->first();
            $result['evidence'] = $review ? ['type' => 'review', 'data' => $review] : null;
            // Review reports should target the reviewer account (author of the reported review).
            $result['reported_user_id'] = (int) ($review->reviewer_account_user_id ?? $review->reviewer_user_id ?? 0) ?: null;

        } elseif ($source === 'content') {
            $result['reason']       = $report->reason;
            $result['details']      = $report->details;
            $result['status']       = $report->status;
            $result['content_type'] = $report->content_type;
            $result['admin_notes']  = $report->admin_notes ?? null;
            $result['created_at']   = $report->created_at;

            if ($report->content_type === 'showcase') {
                $showcasePostId = (int) ($report->content_id ?? 0);
                $post = DB::table('showcases')
                    ->leftJoin('users', 'showcases.user_id', '=', 'users.user_id')
                    ->leftJoin('projects as linked_project', 'showcases.linked_project_id', '=', 'linked_project.project_id')
                    ->where('showcases.post_id', $showcasePostId)
                    ->select(
                        'showcases.*',
                        'users.first_name',
                        'users.last_name',
                        'users.username',
                        'users.user_id as author_user_id',
                        'linked_project.project_title as linked_project_title'
                    )
                    ->first();
                $images = collect();
                if ($post && Schema::hasTable('showcase_images')) {
                    $images = DB::table('showcase_images')
                        ->where('post_id', $showcasePostId)
                        ->orderBy('sort_order')
                        ->select('original_name', 'file_path', 'sort_order')
                        ->get()
                        ->map(function ($image) {
                            return (object) [
                                'original_name' => $image->original_name,
                                'file_path' => self::normalizeStoragePath($image->file_path),
                                'sort_order' => $image->sort_order,
                            ];
                        });
                }
                $result['evidence'] = $post ? ['type' => 'showcase', 'data' => $post, 'images' => $images] : null;
                $result['reported_user_id'] = (int) ($post->author_user_id ?? 0) ?: null;
            } elseif ($report->content_type === 'project') {
                $post = DB::table('projects')
                    ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                    ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
                    ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id')
                    ->leftJoin('contractor_types', 'projects.type_id', '=', 'contractor_types.type_id')
                    ->where('projects.project_id', $report->content_id)
                    ->select(
                        'projects.*',
                        'project_relationships.created_at as relationship_created_at',
                        'users.first_name',
                        'users.last_name',
                        'users.username',
                        'users.user_id as author_user_id',
                        'contractor_types.type_name as category_name'
                    )
                    ->first();
                $files = collect();
                if ($post && Schema::hasTable('project_files')) {
                    $files = DB::table('project_files')
                        ->where('project_id', $report->content_id)
                        ->orderBy('uploaded_at')
                        ->select('file_type', 'file_path', 'uploaded_at')
                        ->get()
                        ->map(function ($file) {
                            return (object) [
                                'file_type' => $file->file_type,
                                'file_path' => self::normalizeStoragePath($file->file_path),
                                'uploaded_at' => $file->uploaded_at,
                            ];
                        });
                }
                $result['evidence'] = $post ? ['type' => 'project', 'data' => $post, 'files' => $files] : null;
                $result['reported_user_id'] = (int) ($post->author_user_id ?? 0) ?: null;
            }

        } elseif ($source === 'user') {
            $result['reason']       = $report->reason;
            $result['details']      = $report->description;
            $result['status']       = $report->status;
            $result['content_type'] = 'user';
            $result['admin_notes']  = null;
            $result['created_at']   = $report->created_at;
            $result['reported_user_id'] = (int) ($report->reported_user_id ?? 0) ?: null;

            $reportedUser = DB::table('users')
                ->where('user_id', $report->reported_user_id)
                ->select('user_id', 'first_name', 'last_name', 'username', 'email', 'user_type')
                ->first();

            $result['evidence'] = [
                'type' => 'user_report',
                'data' => (object) [
                    'reported_user' => $reportedUser,
                    'description' => $report->description,
                ],
            ];

        } elseif ($source === 'dispute') {
            $result['reason']       = $report->dispute_type;
            $result['details']      = $report->dispute_desc;
            $result['content_type'] = 'dispute';
            $result['admin_notes']  = $report->admin_response ?? null;
            $result['created_at']   = $report->created_at;
            $result['reported_user_id'] = $report->against_user_id;

            // Map dispute_status to unified status
            $result['status'] = match ($report->dispute_status) {
                'open' => 'pending',
                'under_review' => 'under_review',
                'resolved' => 'resolved',
                'cancelled' => 'dismissed',
                default => $report->dispute_status,
            };

            // Evidence: info about the accused user
            $accused = DB::table('users')
                ->where('user_id', $report->against_user_id)
                ->select('user_id', 'first_name', 'last_name', 'username', 'email', 'user_type')
                ->first();

            // Evidence about the related project if applicable
            $project = DB::table('projects')
                ->where('project_id', $report->project_id ?? 0)
                ->select('project_id', 'project_title', 'project_status')
                ->first();

            $result['evidence'] = [
                'type' => 'dispute',
                'data' => (object)[
                    'accused' => $accused,
                    'project' => $project,
                    'dispute_type' => $report->dispute_type,
                    'dispute_desc' => $report->dispute_desc,
                ],
            ];

            // Pull richer dispute context from the existing dispute model.
            $detail = disputeClass::getDisputeDetails($reportId);
            if (!empty($detail)) {
                $result['dispute_subject'] = $detail['content']['subject'] ?? null;
                $result['requested_action'] = $detail['content']['requested_action'] ?? null;
                $result['complainant'] = $detail['header']['reporter_name'] ?? null;
                $result['respondent'] = $detail['header']['against_name'] ?? null;
                $result['project_title'] = $detail['header']['project_title'] ?? null;
                $result['evidence_files'] = $detail['initial_proofs'] ?? [];
                $result['resubmissions'] = $detail['resubmissions'] ?? [];
            } else {
                $result['evidence_files'] = [];
                $result['resubmissions'] = [];
            }
        }

        // Hard fallback for edge-cases where joined evidence record is already removed.
        if (empty($result['reported_user_id'])) {
            $result['reported_user_id'] = self::resolveReportedUserIdFromReport($source, $report);
        }

        if (!empty($result['reported_user_id'])) {
            $result['reported_user'] = DB::table('users')
                ->where('user_id', $result['reported_user_id'])
                ->select('user_id', 'first_name', 'last_name', 'username', 'email', 'user_type')
                ->first();
        }

        return $result;
    }

    private static function resolveReportedUserIdFromReport($source, $report): ?int
    {
        if ($source === 'dispute') {
            $id = (int) ($report->against_user_id ?? 0);
            return $id > 0 ? $id : null;
        }

        if ($source === 'review') {
            $id = (int) DB::table('reviews')
                ->where('review_id', $report->review_id ?? 0)
                ->value('reviewee_user_id');
            return $id > 0 ? $id : null;
        }

        if ($source === 'post' || $source === 'content') {
            $contentType = strtolower((string) (($source === 'post' ? ($report->post_type ?? null) : ($report->content_type ?? null)) ?? ''));
            $contentId = (int) ($source === 'post' ? ($report->post_id ?? 0) : ($report->content_id ?? 0));

            if ($contentType === 'showcase' && $contentId > 0) {
                $id = (int) DB::table('showcases')->where('post_id', $contentId)->value('user_id');
                return $id > 0 ? $id : null;
            }

            if ($contentType === 'project' && $contentId > 0) {
                $id = (int) DB::table('projects')
                    ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                    ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
                    ->where('projects.project_id', $contentId)
                    ->value('property_owners.user_id');
                return $id > 0 ? $id : null;
            }
        }

        if ($source === 'user') {
            $id = (int) ($report->reported_user_id ?? 0);
            return $id > 0 ? $id : null;
        }

        return null;
    }

    /**
     * Get user profile card data for the suspension modal
     */
    public static function getUserProfileCard($userId)
    {
        $user = DB::table('users')
            ->where('user_id', $userId)
            ->select('user_id', 'first_name', 'last_name', 'username', 'email', 'user_type')
            ->first();

        if (!$user) return null;

        $profile = [
            'user_id'    => $user->user_id,
            'name'       => $user->first_name . ' ' . $user->last_name,
            'username'   => $user->username,
            'email'      => $user->email,
            'user_type'  => $user->user_type,
            'profile_pic' => null,
            'completed_projects' => 0,
            'ongoing_projects'   => 0,
        ];

        if ($user->user_type === 'property_owner') {
            $owner = DB::table('property_owners')
                ->where('user_id', $userId)
                ->select('owner_id', 'profile_pic')
                ->first();
            if ($owner) {
                $profile['profile_pic'] = $owner->profile_pic;

                $relIds = DB::table('project_relationships')
                    ->where('owner_id', $owner->owner_id)
                    ->pluck('rel_id');

                if ($relIds->isNotEmpty()) {
                    $profile['completed_projects'] = DB::table('projects')
                        ->whereIn('relationship_id', $relIds)
                        ->where('project_status', 'completed')
                        ->count();
                    $profile['ongoing_projects'] = DB::table('projects')
                        ->whereIn('relationship_id', $relIds)
                        ->whereIn('project_status', ['open', 'in_progress'])
                        ->count();
                }
            }
        }

        if ($user->user_type === 'contractor' || $user->user_type === 'both') {
            $contractor = DB::table('contractors')
                ->leftJoin('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                ->where('property_owners.user_id', $userId)
                ->select('contractors.contractor_id', 'contractors.completed_projects', 'property_owners.profile_pic')
                ->first();
            if ($contractor) {
                $profile['profile_pic'] = $profile['profile_pic'] ?: $contractor->profile_pic;
                $profile['completed_projects'] = max((int) $profile['completed_projects'], (int) ($contractor->completed_projects ?? 0));

                // Count ongoing projects via bids
                $profile['ongoing_projects'] = max((int) $profile['ongoing_projects'], (int) DB::table('bids')
                    ->join('projects', 'bids.project_id', '=', 'projects.project_id')
                    ->where('bids.contractor_id', $contractor->contractor_id)
                    ->where('bids.bid_status', 'approved')
                    ->whereIn('projects.project_status', ['in_progress'])
                    ->count());
            }
        }

        return $profile;
    }

    /**
     * Hide/remove offending content based on source type
     */
    public static function hideContent($source, $reportId)
    {
        $table = self::getTableForSource($source);
        if (!$table) return false;
        $idCol = $source === 'dispute' ? 'dispute_id' : 'report_id';
        $report = DB::table($table)->where($idCol, $reportId)->first();
        if (!$report) return false;

        if ($source === 'review') {
            return DB::table('reviews')
                ->where('review_id', $report->review_id)
                ->update([
                    'is_deleted' => 1,
                    'deletion_reason' => 'Report confirmed by admin',
                ]);
        } elseif ($source === 'post') {
            if ($report->post_type === 'showcase') {
                return DB::table('showcases')
                    ->where('post_id', $report->post_id)
                    ->update(['status' => 'deleted']);
            } elseif ($report->post_type === 'project') {
                return DB::table('projects')
                    ->where('project_id', $report->post_id)
                    ->update(['project_status' => 'deleted_post']);
            }
        } elseif ($source === 'content') {
            if ($report->content_type === 'showcase') {
                return DB::table('showcases')
                    ->where('post_id', $report->content_id)
                    ->update(['status' => 'deleted']);
            } elseif ($report->content_type === 'project') {
                return DB::table('projects')
                    ->where('project_id', $report->content_id)
                    ->update(['project_status' => 'deleted_post']);
            }
        }

        return true; // disputes don't have separate content to hide
    }

    /**
     * Search users for Direct Admin Action tab (with pagination, default shows all)
     */
    public static function searchUsers($query = '', $page = 1, $perPage = 15)
    {
        $builder = DB::table('users')
            ->leftJoin('property_owners', 'users.user_id', '=', 'property_owners.user_id')
            ->leftJoin('contractors', 'property_owners.owner_id', '=', 'contractors.owner_id')
            ->whereIn('users.user_type', ['property_owner', 'contractor', 'both']);

        if (!empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('users.first_name', 'like', "%{$query}%")
                  ->orWhere('users.last_name', 'like', "%{$query}%")
                  ->orWhere('users.username', 'like', "%{$query}%")
                  ->orWhere('users.email', 'like', "%{$query}%");
            });
        }

        $total = $builder->count();

        $results = $builder->select(
                'users.user_id',
                'users.first_name',
                'users.last_name',
                'users.username',
                'users.email',
                'users.user_type',
                'property_owners.is_active as owner_is_active',
                'property_owners.suspension_reason',
                'property_owners.suspension_until',
                'property_owners.profile_pic',
                'contractors.contractor_id',
                'contractors.company_name',
                'contractors.is_active as contractor_is_active'
            )
            ->orderBy('users.first_name')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return ['data' => $results, 'total' => $total, 'page' => $page, 'per_page' => $perPage, 'last_page' => (int) ceil($total / $perPage)];
    }

    /**
     * Search showcase posts for Direct Admin Action tab (with pagination, default shows all)
     */
    public static function searchPosts($query = '', $page = 1, $perPage = 15)
    {
        $builder = DB::table('showcases')
            ->leftJoin('users', 'showcases.user_id', '=', 'users.user_id');

        if (!empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('showcases.title', 'like', "%{$query}%")
                  ->orWhere('showcases.content', 'like', "%{$query}%")
                  ->orWhere('users.username', 'like', "%{$query}%");
            });
        }

        $total = (clone $builder)
            ->distinct()
            ->count('showcases.post_id');

        $results = $builder->select(
                'showcases.post_id',
                'showcases.title',
                'showcases.content',
                'showcases.status',
                'showcases.created_at',
                'users.user_id',
                'users.first_name',
                'users.last_name',
                'users.username'
            )
            ->distinct()
            ->orderByDesc('showcases.created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return ['data' => $results, 'total' => $total, 'page' => $page, 'per_page' => $perPage, 'last_page' => (int) ceil($total / $perPage)];
    }

    /**
     * Search project posts for Direct Admin Action tab (with pagination, default shows all)
     */
    public static function searchProjects($query = '', $page = 1, $perPage = 15)
    {
        $builder = DB::table('projects')
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id');

        if (!empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('projects.project_title', 'like', "%{$query}%")
                  ->orWhere('projects.project_description', 'like', "%{$query}%")
                  ->orWhere('users.username', 'like', "%{$query}%");
            });
        }

        $total = $builder->count();

        $results = $builder->select(
                'projects.project_id',
                'projects.project_title',
                'projects.project_description',
                'projects.project_status',
                'projects.project_location',
                'project_relationships.created_at as created_at',
                'users.user_id',
                'users.first_name',
                'users.last_name',
                'users.username'
            )
            ->orderByDesc('project_relationships.created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return ['data' => $results, 'total' => $total, 'page' => $page, 'per_page' => $perPage, 'last_page' => (int) ceil($total / $perPage)];
    }

    /**
     * Search reviews for Direct Admin Action tab (with pagination, default shows all)
     */
    public static function searchReviews($query = '', $page = 1, $perPage = 15)
    {
        $builder = DB::table('reviews')
            ->leftJoin('users as reviewer', 'reviews.reviewer_user_id', '=', 'reviewer.user_id')
            ->leftJoin('users as reviewee', 'reviews.reviewee_user_id', '=', 'reviewee.user_id')
            ->leftJoin('projects', 'reviews.project_id', '=', 'projects.project_id');

        if (!empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('reviews.comment', 'like', "%{$query}%")
                  ->orWhere('reviewer.username', 'like', "%{$query}%")
                  ->orWhere('reviewee.username', 'like', "%{$query}%")
                  ->orWhere('projects.project_title', 'like', "%{$query}%");
            });
        }

        $total = $builder->count();

        $results = $builder->select(
                'reviews.review_id',
                'reviews.rating',
                'reviews.comment',
                'reviews.is_deleted',
                'reviews.created_at',
                'reviews.project_id',
                'projects.project_title',
                'reviewer.user_id as reviewer_user_id',
                'reviewer.first_name as reviewer_first_name',
                'reviewer.last_name as reviewer_last_name',
                'reviewer.username as reviewer_username',
                'reviewee.user_id as reviewee_user_id',
                'reviewee.first_name as reviewee_first_name',
                'reviewee.last_name as reviewee_last_name',
                'reviewee.username as reviewee_username'
            )
            ->orderByDesc('reviews.created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return ['data' => $results, 'total' => $total, 'page' => $page, 'per_page' => $perPage, 'last_page' => (int) ceil($total / $perPage)];
    }


    /**
     * Admin-initiated content hiding (direct action, not from a report)
     */
    public static function adminHidePost($postId, $reason = 'Removed by admin (direct action)')
    {
        $updated = DB::table('showcases')
            ->where('post_id', $postId)
            ->where('status', '!=', 'deleted')
            ->update(['status' => 'deleted']);

        if (!$updated) {
            return false;
        }

        // Notify the post owner
        $post = DB::table('showcases')->where('post_id', $postId)->first();
        if ($post && $post->user_id) {
            self::notifyUser($post->user_id, 'Your Showcase Post Has Been Hidden',
                "Your showcase post (ID #{$postId}) has been hidden by an administrator. Reason: {$reason}",
                'Admin Announcement', 'showcase', $postId);
        }

        return true;
    }

    public static function adminHideReview($reviewId, $reason = 'Removed by admin (direct action)')
    {
        $updated = DB::table('reviews')
            ->where('review_id', $reviewId)
            ->where('is_deleted', '!=', 1)
            ->update([
                'is_deleted' => 1,
                'deletion_reason' => $reason,
            ]);

        if (!$updated) {
            return false;
        }

        // Notify the reviewer
        $review = DB::table('reviews')->where('review_id', $reviewId)->first();
        if ($review && $review->reviewer_user_id) {
            self::notifyUser($review->reviewer_user_id, 'Your Review Has Been Hidden',
                "Your review (ID #{$reviewId}) has been hidden by an administrator. Reason: {$reason}",
                'Admin Announcement', 'review', $reviewId);
        }

        return true;
    }

    public static function adminHideProject($projectId, $reason = 'Removed by admin (direct action)')
    {
        $updated = DB::table('projects')
            ->where('project_id', $projectId)
            ->where('project_status', '!=', 'deleted_post')
            ->update(['project_status' => 'deleted_post']);

        if (!$updated) {
            return false;
        }

        $ownerUserId = DB::table('projects')
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->where('projects.project_id', $projectId)
            ->value('property_owners.user_id');

        if ($ownerUserId) {
            self::notifyUser(
                $ownerUserId,
                'Your Project Post Has Been Hidden',
                "Your project post (ID #{$projectId}) has been hidden by an administrator. Reason: {$reason}",
                'Admin Announcement',
                'project',
                $projectId
            );
        }

        return true;
    }

    public static function adminUnhidePost($postId, $reason = 'Restored by admin (direct action)')
    {
        $updated = DB::table('showcases')
            ->where('post_id', $postId)
            ->where('status', 'deleted')
            ->update(['status' => 'approved']);

        if (!$updated) {
            return false;
        }

        $post = DB::table('showcases')->where('post_id', $postId)->first();
        if ($post && $post->user_id) {
            self::notifyUser(
                $post->user_id,
                'Your Showcase Post Has Been Restored',
                "Your showcase post (ID #{$postId}) is visible again after admin review. Note: {$reason}",
                'Admin Announcement',
                'showcase',
                $postId
            );
        }

        return true;
    }

    public static function adminUnhideProject($projectId, $reason = 'Restored by admin (direct action)')
    {
        $updated = DB::table('projects')
            ->where('project_id', $projectId)
            ->where('project_status', 'deleted_post')
            ->update(['project_status' => 'open']);

        if (!$updated) {
            return false;
        }

        $ownerUserId = DB::table('projects')
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->where('projects.project_id', $projectId)
            ->value('property_owners.user_id');

        if ($ownerUserId) {
            self::notifyUser(
                $ownerUserId,
                'Your Project Post Has Been Restored',
                "Your project post (ID #{$projectId}) is visible again after admin review. Note: {$reason}",
                'Admin Announcement',
                'project',
                $projectId
            );
        }

        return true;
    }

    public static function adminUnhideReview($reviewId, $reason = 'Restored by admin (direct action)')
    {
        $updated = DB::table('reviews')
            ->where('review_id', $reviewId)
            ->where('is_deleted', 1)
            ->update([
                'is_deleted' => 0,
                'deletion_reason' => null,
            ]);

        if (!$updated) {
            return false;
        }

        $review = DB::table('reviews')->where('review_id', $reviewId)->first();
        if ($review && $review->reviewer_user_id) {
            self::notifyUser(
                $review->reviewer_user_id,
                'Your Review Has Been Restored',
                "Your review (ID #{$reviewId}) is visible again after admin review. Note: {$reason}",
                'Admin Announcement',
                'review',
                $reviewId
            );
        }

        return true;
    }

    /**
     * Notify a user about an admin action via the notifications table.
     */
    public static function notifyUser($userId, $title, $message, $type = 'Admin Announcement', $refType = null, $refId = null)
    {
        DB::table('notifications')->insert([
            'user_id'         => $userId,
            'title'           => $title,
            'message'         => $message,
            'type'            => $type,
            'is_read'         => 0,
            'delivery_method' => 'App',
            'priority'        => 'high',
            'reference_type'  => $refType,
            'reference_id'    => $refId,
            'created_at'      => now(),
        ]);
    }

    /**
     * Build displayable target for normalized moderation table rows.
     */
    private static function resolveCaseTarget($row)
    {
        $source = $row->report_source ?? null;

        if ($source === 'dispute') {
            $dispute = DB::table('disputes')
                ->leftJoin('users as accused', 'disputes.against_user_id', '=', 'accused.user_id')
                ->leftJoin('projects', 'disputes.project_id', '=', 'projects.project_id')
                ->where('disputes.dispute_id', $row->report_id)
                ->select('accused.username as accused_username', 'projects.project_title')
                ->first();

            if (!empty($dispute->project_title) && !empty($dispute->accused_username)) {
                return $dispute->accused_username . ' / ' . $dispute->project_title;
            }

            return $dispute->accused_username ?? $dispute->project_title ?? 'Dispute Case';
        }

        if ($source === 'review') {
            $review = DB::table('reviews')
                ->leftJoin('users as reviewee', 'reviews.reviewee_user_id', '=', 'reviewee.user_id')
                ->leftJoin('projects', 'reviews.project_id', '=', 'projects.project_id')
                ->where('reviews.review_id', $row->content_id)
                ->select('reviewee.username as reviewee_username', 'projects.project_title')
                ->first();

            return $review->reviewee_username
                ?? $review->project_title
                ?? ('Review #' . (string) $row->content_id);
        }

        if ($source === 'user') {
            $reportedUserId = (int) ($row->content_id ?? 0);
            if ($reportedUserId <= 0) {
                return 'Unknown User';
            }

            $user = DB::table('users')
                ->where('user_id', $reportedUserId)
                ->select('first_name', 'last_name', 'username', 'email')
                ->first();

            return self::formatUserDisplayName($user);
        }

        $contentType = strtolower((string) ($row->content_type ?? ''));

        if (in_array($contentType, ['project'], true)) {
            $project = DB::table('projects')
                ->where('project_id', $row->content_id)
                ->select('project_title')
                ->first();
            return $project->project_title ?? ('Project #' . (string) $row->content_id);
        }

        $showcasePostId = (int) ($row->content_id ?? 0);

        $showcase = DB::table('showcases')
            ->leftJoin('users', 'showcases.user_id', '=', 'users.user_id')
            ->where('showcases.post_id', $showcasePostId)
            ->select('showcases.title', 'users.username')
            ->first();

        if (!empty($showcase->title) && !empty($showcase->username)) {
            return $showcase->title . ' (@' . $showcase->username . ')';
        }

        return $showcase->title
            ?? $showcase->username
            ?? ucfirst($contentType ?: 'Content') . ' #' . (string) $row->content_id;
    }
}
