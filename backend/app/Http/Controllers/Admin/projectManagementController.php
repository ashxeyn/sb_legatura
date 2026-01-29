<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\admin\disputeClass;
use App\Models\admin\projectClass;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\disputeVerifiedReporterRequest;
use App\Mail\disputeFiledRespondentRequest;

class projectManagementController extends Controller
{
    public function listOfProjects(Request $request)
    {
        $search = $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $verification = $request->query('verification');
        $progress = $request->query('progress');

        $projects = projectClass::getAllProjects($search, $dateFrom, $dateTo, $verification, $progress, 15);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.projectManagement.partials.projectTable', ['projects' => $projects])->render(),
            ]);
        }

        return view('admin.projectManagement.listOfProjects', [
            'projects' => $projects
        ]);
    }

    public function index()
    {
        $disputes = disputeClass::paginateAll(20);
        return view('admin.disputes.index', compact('disputes'));
    }

    public function show($id)
    {
        $dispute = disputeClass::getById($id);
        if (!$dispute) return redirect()->route('admin.disputes.index')->with('error','Dispute not found');

        $evidence = disputeClass::getEvidence($id);
        $messages = disputeClass::getMessages($id);

        return view('admin.disputes.show', compact('dispute','evidence','messages'));
    }

    public function resolve($id, Request $request)
    {
        $notes = $request->input('notes', null);
        disputeClass::resolveDispute($id, $notes);
        return back()->with('success', 'Dispute resolved.');
    }

    /**
     * Show disputes and reports with analytics (moved here from ProjectAdminController)
     */
    public function disputesReports(Request $request)
    {
        // use disputeClass to fetch paginated disputes
        $disputes = disputeClass::fetchDisputes($request->all());

        // Projects analytics / success rate / timeline (moved to projectClass)
        $projectsAnalytics = ['data' => projectClass::analytics()];
        $projectSuccessRate = ['data' => projectClass::successRate()];
        $projectsTimeline = projectClass::timeline(12);

        // compute simple dispute stats expected by the view via disputeClass
        $counts = disputeClass::getCounts();
        $totalReports = $counts['total'];
        $pendingCount = $counts['pending'];
        $activeCount = $counts['active'];
        $resolvedCount = $counts['resolved'];

        // compute percentages
        $pendingPercent = $totalReports > 0 ? round(($pendingCount / $totalReports) * 100, 1) : 0;
        $activePercent = $totalReports > 0 ? round(($activeCount / $totalReports) * 100, 1) : 0;
        $resolvedPercent = $totalReports > 0 ? round(($resolvedCount / $totalReports) * 100, 1) : 0;

        // total reports change vs last week (use disputeClass helper)
        $weekly = disputeClass::getWeeklyChange();
        $totalChangePercent = $weekly['percent'] ?? 0;

        // if AJAX request, return partial HTML for the table and pagination
        if ($request->ajax()) {
            $table = view('admin.projectManagement.partials.disputeTable', compact('disputes'))->render();
            $links = $disputes->links()->render();
            return response()->json(['table' => $table, 'links' => $links]);
        }

        return view('admin.projectManagement.disputesReports', [
            'disputes' => $disputes,
            'projectsAnalytics' => $projectsAnalytics,
            'projectSuccessRate' => $projectSuccessRate,
            'projectsTimeline' => $projectsTimeline,
            'totalReports' => $totalReports,
            'pendingCount' => $pendingCount,
            'activeCount' => $activeCount,
            'resolvedCount' => $resolvedCount
        ]);
    }

    public function getDisputeDetails($id)
    {
        $details = disputeClass::getDisputeDetails($id);
        if (!$details) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        // Normalize/flatten resubmissions: convert progress entries + files into a flat files array
        $flat = [];
        if (!empty($details['resubmissions'])) {
            foreach ($details['resubmissions'] as $progressEntry) {
                $status = $progressEntry['progress_status'] ?? ($progressEntry->progress_status ?? null);
                $submitted_at = $progressEntry['submitted_at'] ?? ($progressEntry->submitted_at ?? null);
                $progress_id = $progressEntry['progress_id'] ?? ($progressEntry->progress_id ?? null);
                $files = [];
                // progressEntry may be an array with 'files' or an object
                if (is_array($progressEntry) && isset($progressEntry['files'])) {
                    $files = $progressEntry['files'];
                } elseif (is_object($progressEntry) && isset($progressEntry->files)) {
                    $files = $progressEntry->files;
                }

                foreach ($files as $f) {
                    // file may be object or array
                    $original = is_array($f) ? ($f['file_name'] ?? $f['original_name'] ?? null) : ($f->file_name ?? $f->original_name ?? null);
                    $path = is_array($f) ? ($f['file_path'] ?? $f['path'] ?? null) : ($f->file_path ?? $f->path ?? null);
                    $project_id = $progressEntry['project_id'] ?? ($progressEntry->project_id ?? null);
                    $flat[] = [
                        'progress_id' => $progress_id,
                        'project_id' => $project_id,
                        'original_name' => $original,
                        'file_path' => $path,
                        'progress_status' => $status,
                        'submitted_at' => $submitted_at
                    ];
                }
            }
        }

        // overwrite/add normalized key
        $details['resubmissions'] = $flat;

        return response()->json(['success' => true, 'data' => $details]);
    }

    // Approve initial dispute: move from 'open' -> 'under_review' and notify both parties
    public function approveForReview($id, Request $request)
    {
        // update status without setting admin_response
        disputeClass::updateStatus($id, 'under_review', null);

        // fetch emails for reporter and accused
        $row = DB::table('disputes')
            ->leftJoin('users as reporter', 'disputes.raised_by_user_id', '=', 'reporter.user_id')
            ->leftJoin('users as accused', 'disputes.against_user_id', '=', 'accused.user_id')
            ->select('reporter.email as reporter_email', 'accused.email as accused_email', 'reporter.username as reporter_name', 'accused.username as accused_name', 'disputes.title')
            ->where('disputes.dispute_id', $id)
            ->first();

        try {
            // use the centralized dispute payload to populate the email templates
            $details = disputeClass::getDisputeDetails($id);
            $mailData = [
                'title' => $details['content']['subject'] ?? ($details['dispute']->title ?? $row->title ?? null),
                'subject' => $details['content']['subject'] ?? ($details['dispute']->title ?? $row->title ?? null),
                'description' => $details['content']['dispute_desc'] ?? ($details['dispute']->dispute_desc ?? null),
                'dispute_desc' => $details['content']['dispute_desc'] ?? ($details['dispute']->dispute_desc ?? null),
                'requested_action' => $details['content']['requested_action'] ?? ($details['dispute']->reason ?? null),
                'reason' => $details['content']['requested_action'] ?? ($details['dispute']->reason ?? null),
                'project_title' => $details['header']['project_title'] ?? null,
                'dispute_type' => $details['header']['dispute_type'] ?? null,
            ];

            if ($row && $row->reporter_email) {
                Mail::to($row->reporter_email)->send(new disputeVerifiedReporterRequest($mailData));
            }
            if ($row && $row->accused_email) {
                Mail::to($row->accused_email)->send(new disputeFiledRespondentRequest($mailData));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send approve emails: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    // Reject/cancel dispute: status -> cancelled. Emails only sent to reporter (do not persist admin_response here)
    public function rejectDispute($id, Request $request)
    {
        $reason = $request->input('reason', null);
        disputeClass::updateStatus($id, 'cancelled', null);

        // fetch reporter email
        $row = DB::table('disputes')
            ->leftJoin('users as reporter', 'disputes.raised_by_user_id', '=', 'reporter.user_id')
            ->select('reporter.email as reporter_email', 'reporter.username as reporter_name', 'disputes.title')
            ->where('disputes.dispute_id', $id)
            ->first();

        try {
            if ($row && $row->reporter_email) {
                $subject = "Dispute Rejected";
                $body = "Your dispute has been rejected by the admin." . ($reason ? "\n\nReason: {$reason}" : "");
                Mail::raw($body, function ($m) use ($row, $subject) {
                    $m->to($row->reporter_email)->subject($subject);
                });
            }
        } catch (\Exception $e) {
            Log::error('Failed to send reject email: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    // Finalize resolution: only here do we save admin_response and mark resolved; notify both parties
    public function finalizeResolution($id, Request $request)
    {
        $notes = $request->input('notes', null);
        disputeClass::updateStatus($id, 'resolved', $notes);

        // fetch reporter and accused emails
        $row = DB::table('disputes')
            ->leftJoin('users as reporter', 'disputes.raised_by_user_id', '=', 'reporter.user_id')
            ->leftJoin('users as accused', 'disputes.against_user_id', '=', 'accused.user_id')
            ->select('reporter.email as reporter_email', 'accused.email as accused_email', 'reporter.username as reporter_name', 'accused.username as accused_name', 'disputes.title')
            ->where('disputes.dispute_id', $id)
            ->first();

        try {
            $subject = "Dispute Resolved";
            $body = "The dispute has been resolved by admin.\n\nResolution notes:\n" . ($notes ?: "(no notes provided)");
            if ($row && $row->reporter_email) {
                Mail::raw($body, function ($m) use ($row, $subject) {
                    $m->to($row->reporter_email)->subject($subject);
                });
            }
            if ($row && $row->accused_email) {
                Mail::raw($body, function ($m) use ($row, $subject) {
                    $m->to($row->accused_email)->subject($subject);
                });
            }
        } catch (\Exception $e) {
            Log::error('Failed to send finalize emails: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    /**
     * Fetch complete project details for view modals
     */
    public function getProjectDetails(Request $request, $id)
    {
        try {
            $projectModel = new projectClass();
            $projectData = $projectModel->fetchProjectFullDetails($id);

            if (!$projectData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'status' => $projectData['projectStatus'],
                'data' => $projectData
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching project details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get completed project details with rendered HTML
     */
    public function getCompletedDetails($id)
    {
        try {
            $projectModel = new projectClass();
            $project = $projectModel->fetchCompletedProjectDetails($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            $html = view('admin.projectManagement.partials.completedModalContent', compact('project'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching completed project details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get completion details modal with reviews and feedback
     */
    public function getCompletionDetails($id)
    {
        try {
            $projectModel = new projectClass();
            $completionData = $projectModel->getCompletionDetails($id);

            if (!$completionData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            $html = view('admin.projectManagement.partials.completionDetailsModal', compact('completionData'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching completion details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ongoing project details with rendered HTML
     */
    public function getOngoingDetails($id)
    {
        try {
            $projectModel = new projectClass();
            $project = $projectModel->fetchOngoingProjectDetails($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            $html = view('admin.projectManagement.partials.ongoingProjectModal', compact('project'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching ongoing project details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOpenDetails($id)
    {
        try {
            $projectModel = new projectClass();
            $project = $projectModel->fetchOpenProjectDetails($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            $html = view('admin.projectManagement.partials.biddingDetailsModal', compact('project'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching open project details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getBidDetails($bidId)
    {
        try {
            $projectModel = new \App\Models\admin\projectClass();
            $bid = $projectModel->fetchSpecificBidDetails($bidId);

            if (!$bid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bid not found'
                ], 404);
            }

            $html = view('admin.projectManagement.partials.bidStatusModal', compact('bid'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching bid details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load bid details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAcceptBidSummary($bidId)
    {
        try {
            $projectModel = new \App\Models\admin\projectClass();
            $bid = $projectModel->fetchAcceptBidSummary($bidId);

            if (!$bid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bid not found'
                ], 404);
            }

            $html = view('admin.projectManagement.partials.acceptBidModal', compact('bid'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching accept bid summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load bid summary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function acceptBid(Request $request, $bidId)
    {
        try {
            $projectModel = new \App\Models\admin\projectClass();
            $result = $projectModel->acceptBid($bidId);

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error('Error accepting bid: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept bid: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRejectBidSummary($bidId)
    {
        try {
            $projectModel = new \App\Models\admin\projectClass();
            $bid = $projectModel->fetchRejectBidSummary($bidId);

            if (!$bid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bid not found'
                ], 404);
            }

            $html = view('admin.projectManagement.partials.rejectBidModal', compact('bid'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching reject bid summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load bid summary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rejectBid(Request $request, $bidId)
    {
        try {
            $reason = $request->input('reason');
            $projectModel = new \App\Models\admin\projectClass();
            $result = $projectModel->rejectBid($bidId, $reason);

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error('Error rejecting bid: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject bid: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTerminatedDetails($id)
    {
        try {
            $projectModel = new \App\Models\admin\projectClass();
            $project = $projectModel->fetchTerminatedProjectDetails($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            $html = view('admin.projectManagement.partials.cancelledProjectModal', compact('project'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching terminated project details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load project details: ' . $e->getMessage()
            ], 500);
        }
    }
}
