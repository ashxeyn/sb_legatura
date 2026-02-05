<?php
namespace App\Models\admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class disputeClass
{
    public static function fetchDisputes($filters = [])
    {
        $query = DB::table('disputes')
            ->leftJoin('users as reporter', 'disputes.raised_by_user_id', '=', 'reporter.user_id')
            ->join('projects', 'disputes.project_id', '=', 'projects.project_id')
            ->select(
                'disputes.dispute_id',
                'disputes.raised_by_user_id as complainant_id',
                DB::raw('disputes.title as subject'),
                'disputes.dispute_type',
                DB::raw('disputes.dispute_status as status'),
                'disputes.created_at',
                'reporter.username as reporter_first_name',
                DB::raw('NULL as reporter_last_name'),
                'reporter.profile_pic as reporter_profile_pic',
                'projects.project_title'
            )
            ->orderBy('disputes.created_at', 'desc');

        // Basic filters (optional)
        // status filter mapping
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $status = $filters['status'];
            if ($status === 'pending') {
                // some databases use 'open' while others use 'pending' — accept both
                $query->whereIn('disputes.dispute_status', ['pending', 'open']);
            } elseif ($status === 'disputes') {
                $query->whereIn('disputes.dispute_status', ['under_review', 'escalated']);
            } elseif ($status === 'resolved') {
                $query->where('disputes.dispute_status', 'resolved');
            }
        }

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('disputes.dispute_id', 'like', "%{$s}%")
                  ->orWhere('disputes.title', 'like', "%{$s}%")
                  ->orWhere('disputes.dispute_desc', 'like', "%{$s}%")
                  ->orWhere('reporter.username', 'like', "%{$s}%");
            });
        }

        // date filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('disputes.created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('disputes.created_at', '<=', $filters['date_to']);
        }

        // sorting
        if (!empty($filters['sort'])) {
            if ($filters['sort'] === 'date') {
                $query->orderBy('disputes.created_at', 'desc');
            } elseif ($filters['sort'] === 'status') {
                $query->orderBy('disputes.dispute_status', 'asc');
            }
        }

        return $query->paginate(10);
    }

    public static function getCounts()
    {
        $total = DB::table('disputes')->count();
        // Count pending as either 'open' or 'pending' to be compatible with different seeds
        $pending = DB::table('disputes')->whereIn('dispute_status', ['open', 'pending'])->count();
        $active = DB::table('disputes')->whereIn('dispute_status', ['under_review', 'escalated'])->count();
        $resolved = DB::table('disputes')->where('dispute_status', 'resolved')->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'active' => $active,
            'resolved' => $resolved
        ];
    }

    public static function getById($id)
    {
        return DB::table('disputes')
            ->leftJoin('users as reporter', 'disputes.raised_by_user_id', '=', 'reporter.user_id')
            ->leftJoin('projects', 'disputes.project_id', '=', 'projects.project_id')
            ->select('disputes.*', 'reporter.username as reporter_username', 'reporter.profile_pic as reporter_profile_pic', 'projects.project_title')
            ->where('disputes.dispute_id', $id)
            ->first();
    }

    /**
     * Get open halt-type disputes for a specific project
     * Used when admin is halting a project - must select which dispute triggered the halt
     *
     * @param int $projectId
     * @return \Illuminate\Support\Collection
     */
    public static function getOpenHaltDisputesForProject($projectId)
    {
        return DB::table('disputes')
            ->leftJoin('users as reporter', 'disputes.raised_by_user_id', '=', 'reporter.user_id')
            ->leftJoin('users as against', 'disputes.against_user_id', '=', 'against.user_id')
            ->select(
                'disputes.dispute_id',
                'disputes.title',
                'disputes.dispute_desc',
                'disputes.dispute_type',
                'disputes.created_at',
                'reporter.username as reporter_username',
                'against.username as against_username'
            )
            ->where('disputes.project_id', $projectId)
            ->where('disputes.dispute_type', 'Halt')
            ->where('disputes.dispute_status', 'open')
            ->orderBy('disputes.created_at', 'desc')
            ->get();
    }

    public static function getEvidence($id)
    {
        if (!Schema::hasTable('dispute_files')) return collect();
        return DB::table('dispute_files')->where('dispute_id', $id)->get();
    }

    public static function getMessages($id)
    {
        if (!Schema::hasTable('dispute_messages')) return collect();
        return DB::table('dispute_messages')->where('dispute_id', $id)->orderBy('created_at')->get();
    }

    public static function paginateAll($perPage = 20)
    {
        return DB::table('disputes')
            ->leftJoin('projects','disputes.project_id','=','projects.project_id')
            ->select('disputes.*','projects.project_title')
            ->orderBy('disputes.created_at','desc')
            ->paginate($perPage);
    }

    public static function resolveDispute($id, $notes = null)
    {
        return self::updateStatus($id, 'resolved', $notes);
    }

    public static function updateStatus($disputeId, $status, $adminResponse = null)
    {
        return DB::table('disputes')->where('dispute_id', $disputeId)->update([
            'dispute_status' => $status,
            'admin_response' => $adminResponse,
            'resolved_at' => ($status === 'resolved' || $status === 'closed') ? now() : null
        ]);
    }

    public static function getWeeklyChange()
    {
        $now = now();
        $thisWeekStart = $now->copy()->subDays(7);
        $lastWeekStart = $now->copy()->subDays(14);

        $thisWeekCount = DB::table('disputes')->where('created_at', '>=', $thisWeekStart)->count();
        $lastWeekCount = DB::table('disputes')->whereBetween('created_at', [$lastWeekStart, $thisWeekStart])->count();

        if ($lastWeekCount == 0) {
            $percent = $thisWeekCount > 0 ? round((($thisWeekCount - $lastWeekCount) / max(1, $thisWeekCount)) * 100, 1) : 0;
        } else {
            $percent = round((($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100, 1);
        }

        return [
            'thisWeek' => $thisWeekCount,
            'lastWeek' => $lastWeekCount,
            'percent' => $percent
        ];
    }

    public static function getDisputeDetails($id)
    {
        // fetch dispute with reporter and against user and project
        $dispute = DB::table('disputes')
            ->leftJoin('users as reporter', 'disputes.raised_by_user_id', '=', 'reporter.user_id')
            ->leftJoin('users as accused', 'disputes.against_user_id', '=', 'accused.user_id')
            ->leftJoin('projects', 'disputes.project_id', '=', 'projects.project_id')
            ->select('disputes.*',
                'reporter.username as reporter_username',
                'reporter.profile_pic as reporter_profile_pic',
                'accused.username as against_username',
                'projects.project_title')
            ->where('disputes.dispute_id', $id)
            ->first();

        if (!$dispute) return null;

        // initial proofs from dispute_files (include uploaded_at for display)
        $initialProofs = collect();
        if (Schema::hasTable('dispute_files')) {
            $initialProofs = DB::table('dispute_files')
                ->where('dispute_id', $id)
                ->select('storage_path', DB::raw('original_name as file_name'), 'uploaded_at')
                ->get()
                ->map(function ($r) {
                    $path = $r->storage_path ?? ($r->file_path ?? null);
                    // Normalize storage path for frontend: strip possible prefixes and ensure relative path under storage/
                    if ($path) {
                        $path = preg_replace('#^\\/?storage/app/public/#i', '', $path);
                        $path = preg_replace('#^\\/?public/#i', '', $path);
                        $path = preg_replace('#^\\/?storage/#i', '', $path);
                        $path = ltrim($path, "/\\");
                    }
                    return (object) [
                        'file_name' => $r->file_name ?? null,
                        'file_path' => $path,
                        'uploaded_at' => $r->uploaded_at ?? null
                    ];
                });
        }

        // messages between parties (if table exists)
        $messages = collect();
        if (Schema::hasTable('dispute_messages')) {
            $messages = DB::table('dispute_messages')
                ->where('dispute_id', $id)
                ->orderBy('created_at')
                ->get();
        }

        // Resubmissions / progress entries linked by milestone_item_id -> progress.milestone_item_id
        $resubmissions = collect();
        if (!empty($dispute->milestone_item_id) && Schema::hasTable('progress')) {
            $progressRows = DB::table('progress')
                ->where('milestone_item_id', $dispute->milestone_item_id)
                ->orderBy('submitted_at', 'asc')
                ->get();

            foreach ($progressRows as $p) {
                $files = collect();
                if (Schema::hasTable('progress_files')) {
                    $files = DB::table('progress_files')
                        ->where('progress_id', $p->progress_id)
                        ->select('file_path', DB::raw('original_name as file_name'))
                        ->get()
                        ->map(function ($f) {
                            $path = $f->file_path ?? null;
                            if ($path) {
                                $path = preg_replace('#^\\/?storage/app/public/#i', '', $path);
                                $path = preg_replace('#^\\/?public/#i', '', $path);
                                $path = preg_replace('#^\\/?storage/#i', '', $path);
                                $path = ltrim($path, "/\\");
                            }
                            return (object) [
                                'file_name' => $f->file_name ?? null,
                                'file_path' => $path
                            ];
                        });
                }

                // derive project_id via milestone_item -> milestone -> project
                $projectId = null;
                if (Schema::hasTable('milestone_items')) {
                    $milestoneId = DB::table('milestone_items')->where('item_id', $p->milestone_item_id)->value('milestone_id');
                    if ($milestoneId && Schema::hasTable('milestones')) {
                        $projectId = DB::table('milestones')->where('milestone_id', $milestoneId)->value('project_id');
                    }
                }

                $resubmissions->push([
                    'progress_id' => $p->progress_id,
                    'progress_status' => $p->progress_status ?? null,
                    'submitted_at' => $p->submitted_at ?? null,
                    'milestone_item_id' => $p->milestone_item_id ?? null,
                    'project_id' => $projectId,
                    'files' => $files
                ]);
            }
        }

        // Determine latest resubmission status/date (if any)
        $latest_status = null;
        $latest_date = null;
        $latest_project_id = null;
        if (!empty($dispute->milestone_item_id) && Schema::hasTable('progress')) {
            $latest = DB::table('progress')
                ->where('milestone_item_id', $dispute->milestone_item_id)
                ->orderByDesc('updated_at')
                ->orderByDesc('submitted_at')
                ->first();
            if ($latest) {
                $latest_status = $latest->progress_status ?? null;
                $latest_date = $latest->updated_at ?? $latest->submitted_at ?? null;
                // resolve project id via milestone_item -> milestone -> project
                if (!empty($latest->milestone_item_id) && Schema::hasTable('milestone_items')) {
                    $milestoneId = DB::table('milestone_items')->where('item_id', $latest->milestone_item_id)->value('milestone_id');
                    if ($milestoneId && Schema::hasTable('milestones')) {
                        $latest_project_id = DB::table('milestones')->where('milestone_id', $milestoneId)->value('project_id');
                    }
                }
            }
        }

        $resolution = [
            'admin_response' => $dispute->admin_response ?? null,
            'resolved_at' => $dispute->resolved_at ?? null
        ];

        // Map subject/requested_action: some schemas may have 'title' or 'subject'
        $subject = $dispute->title ?? ($dispute->subject ?? null);

        return [
            // compatibility: include original dispute and common keys expected by existing frontend
            'dispute' => $dispute,
            'evidence' => $initialProofs,
            'progressReports' => $resubmissions,
            'header' => [
                'reporter_name' => $dispute->reporter_username ?? null,
                'against_name' => $dispute->against_username ?? null,
                'dispute_type' => $dispute->dispute_type ?? null,
                'date_submitted' => $dispute->created_at ?? null,
                'dispute_status' => $dispute->dispute_status ?? null,
                'project_title' => $dispute->project_title ?? null
            ],
            'content' => [
                'subject' => $subject,
                'dispute_desc' => $dispute->dispute_desc ?? null,
                'requested_action' => $dispute->reason ?? null
            ],
            'initial_proofs' => $initialProofs,
            'resubmissions' => $resubmissions,
            // top-level convenience keys for frontend mapping
            'reporter_name' => $dispute->reporter_username ?? null,
            'latest_resubmission_status' => $latest_status,
            'latest_resubmission_date' => $latest_date,
            'latest_resubmission_project_id' => $latest_project_id,
            'messages' => $messages,
            'resolution' => $resolution
        ];
    }
}
