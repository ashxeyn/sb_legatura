<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use App\Models\admin\disputeClass;
use App\Models\admin\projectClass;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\disputeVerifiedReporterRequest;
use App\Mail\disputeFiledRespondentRequest;
use App\Http\Requests\admin\editSubRequest;
use App\Http\Requests\admin\addSubRequest;
use App\Models\admin\subscriptionClass;
use App\Models\admin\showcaseClass;
use App\Models\admin\propertyOwnerClass;
use App\Models\admin\contractorClass;
use App\Services\AdminActivityLog;

class projectManagementController extends Controller
{
    public function listOfProjects(Request $request)
    {
        $search = $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $verification = $request->query('verification');
        $progress = $request->query('progress');

        $projects = projectClass::getAllProjects($search, $dateFrom, $dateTo, $verification, $progress, 10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.projectManagement.partials.projectTable', ['projects' => $projects])->render(),
            ]);
        }

        return view('admin.projectManagement.listOfprojects', [
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
        if (!$dispute)
            return redirect()->route('admin.projectManagement.disputesReports')->with('error', 'Dispute not found');

        $evidence = disputeClass::getEvidence($id);
        $messages = disputeClass::getMessages($id);

        return view('admin.disputes.show', compact('dispute', 'evidence', 'messages'));
    }

    public function resolve($id, Request $request)
    {
        $notes = $request->input('notes', null);
        disputeClass::resolveDispute($id, $notes);
        AdminActivityLog::log('dispute_resolved', ['dispute_id' => $id]);
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
            $links = $disputes->links()->toHtml();
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
            'resolvedCount' => $resolvedCount,
            'pendingPercent' => $pendingPercent,
            'activePercent' => $activePercent,
            'resolvedPercent' => $resolvedPercent,
            'totalChangePercent' => $totalChangePercent,
        ]);
    }

    public function getDisputeDetails($id)
    {
        $details = disputeClass::getDisputeDetails($id);
        if (!$details)
            return response()->json(['success' => false, 'message' => 'Not found'], 404);

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

        AdminActivityLog::log('dispute_approved_for_review', ['dispute_id' => $id]);
        return response()->json(['success' => true]);
    }

    // Reject/cancel dispute: status -> cancelled. Emails only sent to reporter (do not persist admin_response here)
    public function rejectDispute($id, Request $request)
    {
        $reason = $request->input('reason', null);
        disputeClass::updateStatus($id, 'cancelled', $reason);

        // Apply penalty if requested
        $penaltyApplied = false;
        if ($request->boolean('apply_penalty')) {
            $penaltyApplied = $this->applyDisputePenalty($id, $request, $reason);
        }

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

        AdminActivityLog::log('dispute_rejected', ['dispute_id' => $id, 'reason' => $reason, 'penalty_applied' => $penaltyApplied]);
        return response()->json(['success' => true, 'penalty_applied' => $penaltyApplied]);
    }

    // Finalize resolution: only here do we save admin_response and mark resolved; notify both parties
    public function finalizeResolution($id, Request $request)
    {
        $notes = $request->input('notes', null);
        disputeClass::updateStatus($id, 'resolved', $notes);

        // Apply penalty if requested
        $penaltyApplied = false;
        if ($request->boolean('apply_penalty')) {
            $penaltyApplied = $this->applyDisputePenalty($id, $request, 'Dispute resolution penalty: ' . ($notes ?: 'No notes'));
        }

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

        AdminActivityLog::log('dispute_finalized', ['dispute_id' => $id, 'penalty_applied' => $penaltyApplied]);
        return response()->json(['success' => true, 'penalty_applied' => $penaltyApplied]);
    }

    /**
     * Apply penalty (ban/terminate) to the reported user during dispute reject/resolve.
     */
    private function applyDisputePenalty($disputeId, Request $request, $reason)
    {
        $penaltyType = $request->input('penalty_type', 'temporary_ban');
        $banDuration = (int) $request->input('ban_duration', 30);

        // Get the dispute to find against_user_id and their user_type
        $dispute = DB::table('disputes')
            ->leftJoin('users', 'disputes.against_user_id', '=', 'users.user_id')
            ->select('disputes.against_user_id', 'users.user_type')
            ->where('disputes.dispute_id', $disputeId)
            ->first();

        if (!$dispute || !$dispute->against_user_id) {
            return false;
        }

        $suspensionUntil = $penaltyType === 'permanent_ban'
            ? '9999-12-31'
            : now()->addDays($banDuration)->toDateString();
        $duration = $penaltyType === 'permanent_ban' ? 'permanent' : $banDuration . ' days';
        $suspensionReason = 'Dispute #' . $disputeId . ' - ' . $reason;

        $userType = $dispute->user_type;
        $userId = $dispute->against_user_id;

        if ($userType === 'property_owner') {
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();
            if ($owner) {
                $model = new propertyOwnerClass();
                $model->suspendOwner($owner->owner_id, $suspensionReason, $duration, $suspensionUntil);
                return true;
            }
        }

        if ($userType === 'contractor' || $userType === 'both') {
            // A contractor user_id maps through property_owners -> contractors
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();
            if ($owner) {
                $contractor = DB::table('contractors')->where('owner_id', $owner->owner_id)->first();
                if ($contractor) {
                    $model = new contractorClass();
                    $model->suspendContractor($contractor->contractor_id, $suspensionReason, $duration, $suspensionUntil);
                    if ($userType === 'contractor') {
                        return true;
                    }
                }
            }
        }

        if ($userType === 'both') {
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();
            if ($owner) {
                $model = new propertyOwnerClass();
                $model->suspendOwner($owner->owner_id, $suspensionReason, $duration, $suspensionUntil);
                return true;
            }
        }

        return false;
    }

    /**
     * Show subscription management
     */
    public function subscriptions(Request $request)
    {
        $plans = subscriptionClass::getPlans();
        $stats = subscriptionClass::getStats();
        $activeSubscriptions = subscriptionClass::getSubscriptions('active');
        $expiredSubscriptions = subscriptionClass::getSubscriptions('expired');
        $cancelledSubscriptions = subscriptionClass::getSubscriptions('cancelled');

        return view('admin.projectManagement.subscriptions', compact(
            'plans',
            'stats',
            'activeSubscriptions',
            'expiredSubscriptions',
            'cancelledSubscriptions'
        ));
    }

    /**
     * Add a subscription plan.
     */
    public function addSubscriptionPlan(addSubRequest $request)
    {
        try {
            $data = [
                'name' => $request->subscription_name,
                'price' => $request->subscription_price,
                'billing_cycle' => $request->billing_cycle,
                'duration_days' => $request->duration_days,
                'plan_key' => $request->plan_key,
                'for_contractor' => $request->for_contractor,
                'benefits' => $request->benefits,
            ];

            subscriptionClass::addPlan($data);
            AdminActivityLog::log('subscription_plan_created', ['name' => $data['name']]);

            return response()->json([
                'status' => 'success',
                'message' => 'Subscription plan created successfully.',
                'data' => $data
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create subscription plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a subscription plan.
     */
    public function updateSubscriptionPlan(editSubRequest $request, $id)
    {
        try {
            $data = [
                'name' => $request->edit_subscription_name,
                'price' => $request->edit_subscription_price,
                'billing_cycle' => $request->edit_billing_cycle,
                'duration_days' => $request->edit_duration_days,
                'benefits' => $request->benefits,
            ];

            subscriptionClass::updatePlan($id, $data);
            AdminActivityLog::log('subscription_plan_updated', ['plan_id' => $id, 'name' => $data['name']]);

            return response()->json([
                'status' => 'success',
                'message' => 'Subscription plan updated successfully.',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update subscription plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a subscription plan.
     */
    public function deleteSubscriptionPlan(Request $request, $id)
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:255',
            ]);

            subscriptionClass::deletePlan($id, $request->reason);
            AdminActivityLog::log('subscription_plan_deleted', ['plan_id' => $id, 'reason' => $request->reason]);

            return response()->json([
                'status' => 'success',
                'message' => 'Subscription plan deleted successfully.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete subscription plan: ' . $e->getMessage()
            ], 500);
        }
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

            $completionData = $projectModel->getCompletionDetails($id);

            $html = view('admin.projectManagement.partials.completedModalContent', compact('project', 'completionData'))->render();

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
            AdminActivityLog::log('bid_accepted', ['bid_id' => $bidId]);

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
            AdminActivityLog::log('bid_rejected_by_admin', ['bid_id' => $bidId, 'reason' => $reason]);

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error('Error rejecting bid: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject bid: ' . $e->getMessage()
            ], 500);
        }
    }

    public function changeBidder(Request $request, $projectId)
    {
        try {
            $bidId = $request->input('bid_id');
            if (!$bidId) {
                return response()->json(['success' => false, 'message' => 'Bid ID is required.'], 422);
            }

            $projectModel = new \App\Models\admin\projectClass();
            $result = $projectModel->changeBidder($projectId, $bidId);

            if ($result['success']) {
                AdminActivityLog::log('bidder_changed', ['project_id' => $projectId, 'new_bid_id' => $bidId]);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error('Error changing bidder: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to change bidder: ' . $e->getMessage()
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

    public function getHaltedDetails($id)
    {
        try {
            $projectModel = new \App\Models\admin\projectClass();
            $project = $projectModel->fetchHaltedProjectDetails($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            $html = view('admin.projectManagement.partials.haltedProjectModal', compact('project'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching halted project details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load project details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getHaltDetails($id)
    {
        try {
            $projectModel = new \App\Models\admin\projectClass();
            $project = $projectModel->fetchHaltDetails($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            $html = view('admin.projectManagement.partials.haltDetailsModal', compact('project'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching halt details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load project details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelHalt(\App\Http\Requests\admin\cancelHaltedProjectsRequest $request, $id)
    {
        try {
            $projectModel = new \App\Models\admin\projectClass();
            $success = $projectModel->cancelHaltedProject($id, $request->remarks);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Project terminated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error terminating halted project: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to terminate project: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resumeHalt($id)
    {
        try {
            $projectModel = new \App\Models\admin\projectClass();
            $success = $projectModel->resumeHaltedProject($id);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Project resumed successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error resuming halted project: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to resume project: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEditProject($id)
    {
        try {
            $projectModel = new projectClass();
            $project = $projectModel->fetchProjectForEdit($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            // Debug log to check contractor data
            Log::info('Edit Project Data', [
                'project_id' => $project->project_id,
                'selected_contractor_id' => $project->selected_contractor_id,
                'company_name' => $project->company_name ?? 'NULL',
                'contractor_email' => $project->contractor_email ?? 'NULL'
            ]);

            $html = view('admin.projectManagement.partials.editProjectModal', compact('project'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching project for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDeleteSummary($id)
    {
        try {
            $projectModel = new projectClass();
            $project = $projectModel->fetchDeleteSummary($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            $html = view('admin.projectManagement.partials.deleteProjectModal', compact('project'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching delete project summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load project summary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteProject(\App\Http\Requests\admin\deleteProjectRequest $request, $id)
    {
        try {
            $reason = $request->input('reason');
            $projectModel = new projectClass();
            $result = $projectModel->deleteProject($id, $reason);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error deleting project: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete project: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRestoreSummary($id)
    {
        try {
            $projectModel = new projectClass();
            $project = $projectModel->fetchRestoreSummary($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            $html = view('admin.projectManagement.partials.restoreProjectModal', compact('project'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching restore project summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load project summary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restoreProject($id)
    {
        try {
            $projectModel = new projectClass();
            $result = $projectModel->restoreProject($id);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error restoring project: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore project: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getHaltSummary($id)
    {
        try {
            $projectModel = new projectClass();
            $data = $projectModel->fetchHaltSummary($id);

            if (!$data || !isset($data['project'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            $project = $data['project'];
            $disputes = $data['disputes'];

            $html = view('admin.projectManagement.partials.haltProjectModal', compact('project', 'disputes'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching halt project summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load project summary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function haltProject(\App\Http\Requests\admin\haltProjectRequest $request, $id)
    {
        try {
            $projectModel = new projectClass();
            $result = $projectModel->haltProject($id, [
                'dispute_id' => $request->dispute_id,
                'halt_reason' => $request->halt_reason,
                'project_remarks' => $request->project_remarks
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error halting project: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to halt project: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resumeProject($id)
    {
        try {
            $projectModel = new projectClass();
            $result = $projectModel->resumeProject($id);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error resuming project: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to resume project: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getMilestoneItemForEdit($itemId)
    {
        try {
            $projectModel = new projectClass();
            $item = $projectModel->fetchMilestoneItemForEdit($itemId);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Milestone item not found'
                ], 404);
            }

            $html = view('admin.projectManagement.partials.editMilestoneModal', compact('item'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching milestone item for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load milestone item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateMilestoneItem(\App\Http\Requests\admin\editMilestoneRequest $request, $itemId)
    {
        try {
            // Get admin user ID from session
            $adminUser = Session::get('admin');
            $adminUserId = $adminUser && isset($adminUser->admin_id) ? $adminUser->admin_id : 1;

            $projectModel = new projectClass();
            $result = $projectModel->updateMilestoneItem($itemId, [
                'milestone_item_title' => $request->milestone_item_title,
                'milestone_item_description' => $request->milestone_item_description,
                'date_to_finish' => $request->date_to_finish,
                'milestone_item_cost' => $request->milestone_item_cost,
                'item_status' => $request->item_status
            ], $adminUserId);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error updating milestone item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update milestone item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProject(\App\Http\Requests\admin\editProjectRequest $request, $id)
    {
        try {
            $projectModel = new projectClass();
            $currentProject = DB::table('projects')
                ->where('project_id', $id)
                ->first();

            if (!$currentProject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            // Determine the current (old) selected contractor id from project_relationships
            $oldContractorId = null;
            if ($currentProject) {
                // Prefer joining via relationship_id if present
                if (isset($currentProject->relationship_id) && $currentProject->relationship_id) {
                    $oldContractorId = DB::table('project_relationships')
                        ->where('rel_id', $currentProject->relationship_id)
                        ->value('selected_contractor_id');
                }

                // Fallback to finding by project_id (in case relationship_id isn't populated)
                if ($oldContractorId === null) {
                    $oldContractorId = DB::table('project_relationships')
                        ->where('project_id', $id)
                        ->value('selected_contractor_id');
                }
            }

            $result = $projectModel->updateProject($id, [
                'project_title' => $request->project_title,
                'project_description' => $request->project_description,
                'property_type' => $request->property_type,
                'lot_size' => $request->lot_size,
                'floor_area' => $request->floor_area,
                'project_location' => $request->project_location,
                'selected_contractor_id' => $request->selected_contractor_id,
                'old_contractor_id' => $oldContractorId
            ]);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error updating project: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate a subscription (platform payment).
     */
    public function deactivateSubscription(Request $request, $id)
    {
        try {
            $reason = $request->input('reason');
            if (empty($reason)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reason for deactivation is required'
                ], 422);
            }

            subscriptionClass::deactivate($id, $reason);
            AdminActivityLog::log('subscription_deactivated', ['subscription_id' => $id, 'reason' => $reason]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription deactivated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deactivating subscription: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reactivate a subscription (platform payment).
     */
    public function reactivateSubscription($id)
    {
        try {
            subscriptionClass::reactivate($id);
            AdminActivityLog::log('subscription_reactivated', ['subscription_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription reactivated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error reactivating subscription: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show showcase management page with real DB data.
     */
    public function showcaseManagement(Request $request)
    {
        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status', 'all'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $model = new showcaseClass();
        $showcases = $model->fetchShowcases($filters);
        $stats = $model->getStats();

        if ($request->ajax()) {
            return response()->json([
                'showcases_html' => view('admin.projectManagement.partials.showcaseTable', ['showcases' => $showcases])->render(),
                'pagination_html' => (string) $showcases->links()
            ]);
        }

        return view('admin.projectManagement.showcaseManagement', compact('showcases', 'stats'));
    }

    /**
     * Get showcase details (AJAX – returns rendered HTML partial)
     */
    public function getShowcaseDetails($id)
    {
        $model = new showcaseClass();
        $details = $model->getShowcaseDetails($id);

        if ($details) {
            $html = view('admin.projectManagement.partials.showcaseViewModal', ['showcase' => $details])->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'status' => $details['post']['status'],
                'title' => $details['post']['title'],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Showcase not found'], 404);
    }

    /**
     * Approve a showcase post
     */
    public function approveShowcase($id)
    {
        $model = new showcaseClass();
        $approved = $model->approveShowcase($id);

        if ($approved) {
            AdminActivityLog::log('showcase_approved', ['showcase_id' => $id]);
            return response()->json(['success' => true, 'message' => 'Showcase approved successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to approve showcase.'], 400);
    }

    /**
     * Reject a showcase post
     */
    public function rejectShowcase(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $model = new showcaseClass();
        $rejected = $model->rejectShowcase($id, $request->rejection_reason);

        if ($rejected) {
            AdminActivityLog::log('showcase_rejected', ['showcase_id' => $id, 'reason' => $request->rejection_reason]);
            return response()->json(['success' => true, 'message' => 'Showcase rejected successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to reject showcase.'], 400);
    }

    /**
     * Delete a showcase post
     */
    public function deleteShowcase(Request $request, $id)
    {
        $request->validate([
            'deletion_reason' => 'required|string|max:500'
        ]);

        $model = new showcaseClass();
        $deleted = $model->deleteShowcase($id, $request->deletion_reason);

        if ($deleted) {
            AdminActivityLog::log('showcase_deleted', ['showcase_id' => $id, 'reason' => $request->deletion_reason]);
            return response()->json(['success' => true, 'message' => 'Showcase deleted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to delete showcase.'], 400);
    }

    /**
     * Restore a deleted showcase post
     */
    public function restoreShowcase($id)
    {
        $model = new showcaseClass();
        $restored = $model->restoreShowcase($id);

        if ($restored) {
            AdminActivityLog::log('showcase_restored', ['showcase_id' => $id]);
            return response()->json(['success' => true, 'message' => 'Showcase restored successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to restore showcase.'], 400);
    }

    /**
     * Extend project timeline (admin)
     */
    public function extendTimeline(Request $request, $id)
    {
        $request->validate([
            'new_end_date' => 'required|date|after:today',
            'reason' => 'required|string|min:10|max:500',
            'extension_type' => 'required|in:admin_override,request_behalf'
        ]);

        $model = new projectClass();
        $adminUserId = auth()->id();

        $result = $model->adminExtendTimeline($id, [
            'new_end_date' => $request->new_end_date,
            'reason' => $request->reason,
            'extension_type' => $request->extension_type
        ], $adminUserId);

        if ($result['success']) {
            AdminActivityLog::log('timeline_extended', ['project_id' => $id, 'new_end_date' => $request->new_end_date, 'reason' => $request->reason]);
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Get affected milestones for timeline extension
     */
    public function getAffectedMilestones(Request $request, $id)
    {
        $request->validate([
            'new_end_date' => 'required|date'
        ]);

        $model = new projectClass();
        $result = $model->getAffectedMilestones($id, $request->new_end_date);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Get pending extension requests for a project
     */
    public function getPendingExtensions($id)
    {
        $model = new projectClass();
        $result = $model->getPendingExtensions($id);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Approve extension request
     */
    public function approveExtension(Request $request, $extensionId)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        $model = new projectClass();
        $adminUserId = auth()->id();

        $result = $model->adminApproveExtension($extensionId, $adminUserId, $request->notes);

        if ($result['success']) {
            AdminActivityLog::log('extension_approved', ['extension_id' => $extensionId]);
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Reject extension request
     */
    public function rejectExtension(Request $request, $extensionId)
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:500'
        ]);

        $model = new projectClass();
        $adminUserId = auth()->id();

        $result = $model->adminRejectExtension($extensionId, $adminUserId, $request->reason);

        if ($result['success']) {
            AdminActivityLog::log('extension_rejected', ['extension_id' => $extensionId, 'reason' => $request->reason]);
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Request revision on extension request
     */
    public function requestRevision(Request $request, $extensionId)
    {
        $request->validate([
            'feedback' => 'required|string|min:10|max:500'
        ]);

        $model = new projectClass();
        $adminUserId = auth()->id();

        $result = $model->adminRequestRevision($extensionId, $adminUserId, $request->feedback);

        if ($result['success']) {
            AdminActivityLog::log('extension_revision_requested', ['extension_id' => $extensionId]);
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Bulk adjust milestone dates
     */
    public function bulkAdjustDates(Request $request, $id)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'direction' => 'required|in:forward,backward',
            'reason' => 'required|string|min:10|max:500'
        ]);

        $model = new projectClass();

        // Get admin user ID from session
        $adminUser = Session::get('admin');
        $adminUserId = $adminUser && isset($adminUser->admin_id) ? $adminUser->admin_id : 1;

        $result = $model->bulkAdjustMilestoneDates(
            $id,
            $request->days,
            $request->direction,
            $request->reason,
            $adminUserId
        );

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Preview bulk date adjustment
     */
    public function previewBulkAdjustment(Request $request, $id)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'direction' => 'required|in:forward,backward'
        ]);

        $model = new projectClass();

        $result = $model->previewBulkAdjustment(
            $id,
            $request->days,
            $request->direction
        );

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Get full project summary (mirrors mobile projectSummary.tsx)
     * Used for in_progress and terminated project "View Details" modal
     */
    public function getProjectSummaryAdmin($id)
    {
        try {
            $summaryService = new \App\Services\SummaryService();
            $result = $summaryService->getProjectSummary((int) $id);

            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['message'] ?? 'Not found'], 404);
            }

            $summary = $result['data'];
            $html = view('admin.projectManagement.partials.projectSummaryContent', compact('summary'))->render();

            return response()->json(['success' => true, 'html' => $html]);

        } catch (\Exception $e) {
            Log::error('Error fetching admin project summary: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function getPaymentHistory($id)
    {
        try {
            \Log::info('Fetching payment history for project: ' . $id);

            $model = new projectClass();
            $result = $model->fetchPaymentHistory($id);

            \Log::info('Payment history result:', $result);

            if ($result['success']) {
                return response()->json($result);
            }

            return response()->json($result, 400);
        } catch (\Exception $e) {
            \Log::error('Payment history error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching payment history: ' . $e->getMessage()
            ], 500);
        }
    }
}
