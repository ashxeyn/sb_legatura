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
use App\Http\Requests\admin\editSubRequest;
use App\Http\Requests\admin\addSubRequest;
use App\Models\admin\subscriptionClass;

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
            $projectModel = new projectClass();
            $result = $projectModel->updateMilestoneItem($itemId, [
                'milestone_item_title' => $request->milestone_item_title,
                'milestone_item_description' => $request->milestone_item_description,
                'date_to_finish' => $request->date_to_finish,
                'milestone_item_cost' => $request->milestone_item_cost,
                'item_status' => $request->item_status
            ]);

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

            $result = $projectModel->updateProject($id, [
                'project_title' => $request->project_title,
                'project_description' => $request->project_description,
                'property_type' => $request->property_type,
                'lot_size' => $request->lot_size,
                'floor_area' => $request->floor_area,
                'project_location' => $request->project_location,
                'selected_contractor_id' => $request->selected_contractor_id,
                'old_contractor_id' => $currentProject->selected_contractor_id
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
}
