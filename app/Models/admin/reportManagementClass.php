<?php
namespace App\Models\admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class reportManagementClass
{
    /**
     * Fetch all reports from post_reports, review_reports, and content_reports
     * unified into a single collection for the Moderation Hub.
     */
    public static function getActiveReports($filters = [])
    {
        $reports = collect();

        // Post Reports
        if (Schema::hasTable('post_reports')) {
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
                    'post_reports.admin_notes',
                    'post_reports.reviewed_at',
                    'post_reports.created_at',
                    'reporter.username as reporter_username',
                    'reporter.user_id as reporter_user_id'
                )
                ->get();
            $reports = $reports->merge($postReports);
        }

        // Review Reports
        if (Schema::hasTable('review_reports')) {
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
                    'review_reports.admin_notes',
                    'review_reports.reviewed_at',
                    'review_reports.created_at',
                    'reporter.username as reporter_username',
                    'reporter.user_id as reporter_user_id'
                )
                ->get();
            $reports = $reports->merge($reviewReports);
        }

        // Content Reports
        if (Schema::hasTable('content_reports')) {
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
                    'content_reports.admin_notes',
                    'content_reports.reviewed_at',
                    'content_reports.created_at',
                    'reporter.username as reporter_username',
                    'reporter.user_id as reporter_user_id'
                )
                ->get();
            $reports = $reports->merge($contentReports);
        }

        // Disputes (user-level reports)
        if (Schema::hasTable('disputes')) {
            $disputes = DB::table('disputes')
                ->leftJoin('users as reporter', 'disputes.raised_by_user_id', '=', 'reporter.user_id')
                ->leftJoin('users as accused', 'disputes.against_user_id', '=', 'accused.user_id')
                ->select(
                    'disputes.dispute_id as report_id',
                    DB::raw("'dispute' as report_source"),
                    DB::raw("'user' as content_type"),
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
                    'disputes.admin_response as admin_notes',
                    'disputes.resolved_at as reviewed_at',
                    'disputes.created_at',
                    'reporter.username as reporter_username',
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
                return str_contains(strtolower($r->reporter_username ?? ''), $s)
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

        // Sort by created_at desc
        $reports = $reports->sortByDesc('created_at')->values();

        return $reports;
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
                'reporter_username' => $first->reporter_username ?? 'Unknown',
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
    public static function updateReportStatus($source, $reportId, $status, $adminNotes = null, $adminUserId = null)
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
        } else {
            $data = ['status' => $status];
            if ($adminNotes !== null) $data['admin_notes'] = $adminNotes;
            if ($adminUserId !== null) $data['reviewed_by_user_id'] = $adminUserId;
            if (in_array($status, ['resolved', 'dismissed'])) {
                $data['reviewed_at'] = now();
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
                $post = DB::table('showcases')
                    ->leftJoin('users', 'showcases.user_id', '=', 'users.user_id')
                    ->where('post_id', $report->post_id)
                    ->select('showcases.*', 'users.first_name', 'users.last_name', 'users.username', 'users.user_id as author_user_id')
                    ->first();
                $result['evidence'] = $post ? ['type' => 'showcase', 'data' => $post] : null;
                $result['reported_user_id'] = $post->author_user_id ?? null;
            } elseif ($report->post_type === 'project') {
                $post = DB::table('projects')
                    ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                    ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
                    ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id')
                    ->where('projects.project_id', $report->post_id)
                    ->select('projects.*', 'users.first_name', 'users.last_name', 'users.username', 'users.user_id as author_user_id')
                    ->first();
                $result['evidence'] = $post ? ['type' => 'project', 'data' => $post] : null;
                $result['reported_user_id'] = $post->author_user_id ?? null;
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
                ->leftJoin('projects', 'reviews.project_id', '=', 'projects.project_id')
                ->where('reviews.review_id', $report->review_id)
                ->select('reviews.*', 'reviewer.first_name', 'reviewer.last_name', 'reviewer.username',
                    'projects.project_title')
                ->first();
            $result['evidence'] = $review ? ['type' => 'review', 'data' => $review] : null;
            $result['reported_user_id'] = $review->reviewer_user_id ?? null;

        } elseif ($source === 'content') {
            $result['reason']       = $report->reason;
            $result['details']      = $report->details;
            $result['status']       = $report->status;
            $result['content_type'] = $report->content_type;
            $result['admin_notes']  = $report->admin_notes ?? null;
            $result['created_at']   = $report->created_at;

            if ($report->content_type === 'showcase') {
                $post = DB::table('showcases')
                    ->leftJoin('users', 'showcases.user_id', '=', 'users.user_id')
                    ->where('post_id', $report->content_id)
                    ->select('showcases.*', 'users.first_name', 'users.last_name', 'users.username', 'users.user_id as author_user_id')
                    ->first();
                $result['evidence'] = $post ? ['type' => 'showcase', 'data' => $post] : null;
                $result['reported_user_id'] = $post->author_user_id ?? null;
            } elseif ($report->content_type === 'project') {
                $post = DB::table('projects')
                    ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                    ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
                    ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id')
                    ->where('projects.project_id', $report->content_id)
                    ->select('projects.*', 'users.first_name', 'users.last_name', 'users.username', 'users.user_id as author_user_id')
                    ->first();
                $result['evidence'] = $post ? ['type' => 'project', 'data' => $post] : null;
                $result['reported_user_id'] = $post->author_user_id ?? null;
            }

        } elseif ($source === 'dispute') {
            $result['reason']       = $report->dispute_type;
            $result['details']      = $report->dispute_desc;
            $result['content_type'] = 'user';
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
        }

        return $result;
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
        } elseif ($user->user_type === 'contractor') {
            $contractor = DB::table('contractors')
                ->leftJoin('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                ->where('property_owners.user_id', $userId)
                ->select('contractors.contractor_id', 'contractors.completed_projects', 'property_owners.profile_pic')
                ->first();
            if ($contractor) {
                $profile['profile_pic'] = $contractor->profile_pic;
                $profile['completed_projects'] = $contractor->completed_projects ?? 0;

                // Count ongoing projects via bids
                $profile['ongoing_projects'] = DB::table('bids')
                    ->join('projects', 'bids.project_id', '=', 'projects.project_id')
                    ->where('bids.contractor_id', $contractor->contractor_id)
                    ->where('bids.bid_status', 'approved')
                    ->whereIn('projects.project_status', ['in_progress'])
                    ->count();
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

        $total = $builder->count();

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
            ->orderByDesc('showcases.created_at')
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
        DB::table('showcases')
            ->where('post_id', $postId)
            ->update(['status' => 'deleted']);

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
        DB::table('reviews')
            ->where('review_id', $reviewId)
            ->update([
                'is_deleted' => 1,
                'deletion_reason' => $reason,
            ]);

        // Notify the reviewer
        $review = DB::table('reviews')->where('review_id', $reviewId)->first();
        if ($review && $review->reviewer_user_id) {
            self::notifyUser($review->reviewer_user_id, 'Your Review Has Been Hidden',
                "Your review (ID #{$reviewId}) has been hidden by an administrator. Reason: {$reason}",
                'Admin Announcement', 'review', $reviewId);
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
}
