<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\admin\rejectPostRequest;
use App\Models\admin\postingManagementClass;
use App\Models\admin\reviewsClass;
use App\Models\admin\reportManagementClass;
use Illuminate\Support\Facades\Http;
use App\Services\AdminActivityLog;

class globalManagementController extends Controller
{
    /**
     * Display the bid management page
     */
    public function bidManagement(Request $request)
    {
        $bids = $this->getAllBids(
            $request->query('search'),
            $request->query('status'),
            $request->query('page', 1)
        );

        if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'bids_html' => view('admin.globalManagement.partials.bidManagementTable', ['bids' => $bids])->render(),
            ]);
        }

        return view('admin.globalManagement.bidManagement', [
            'bids' => $bids,
        ]);
    }

    /**
     * Display the proof of payments page
     */
    public function proofOfPayments(Request $request)
    {
        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $payments = $this->getAllPaymentProofs(
            $filters['search'],
            $filters['status'],
            $request->query('page', 1)
        );

        // Real statistics from DB
        $stats = $this->getPaymentStats();

        if ($request->ajax()) {
            return response()->json([
                'payments_html' => view('admin.globalManagement.partials.paymentsTable', [
                    'payments' => $payments,
                ])->render(),
                'pagination_html' => $payments->links()->render(),
            ]);
        }

        return view('admin.globalManagement.proofOfpayments', [
            'payments' => $payments,
            'stats' => $stats,
        ]);
    }

    /**
     * Get payment stats summary
     */
    private function getPaymentStats(): array
    {
        $rows = DB::table('milestone_payments')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN payment_status = 'submitted' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN payment_status = 'rejected' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN payment_status = 'approved' THEN 1 ELSE 0 END) as completed
            ")
            ->first();

        return [
            'total' => $rows->total ?? 0,
            'pending' => $rows->pending ?? 0,
            'failed' => $rows->failed ?? 0,
            'completed' => $rows->completed ?? 0,
        ];
    }

    /**
     * Display the AI management page
     */
    public function aiManagement()
    {
        // 1. Get System Health (Python connection)
        $aiUsage = $this->getAIUsageStats();

        // 2. Get Prediction History (Joined with Projects to get names)
        $predictionLogs = DB::table('ai_prediction_logs')
            ->join('projects', 'ai_prediction_logs.project_id', '=', 'projects.project_id')
            ->select(
                'ai_prediction_logs.*',
                'projects.project_title',
                'projects.project_location'
            )
            ->orderBy('ai_prediction_logs.created_at', 'desc')
            ->paginate(10);

        // 3. Get All Projects (For the "Run Analysis" dropdown/list)
        $projects = DB::table('projects')
            ->select('project_id', 'project_title', 'project_status')
            ->orderBy('project_title', 'asc')
            ->get();

        return view('admin.globalManagement.aiManagement', [
            'aiUsage' => $aiUsage,
            'predictionLogs' => $predictionLogs,
            'projects' => $projects,
        ]);
    }

    /**
     * ACTION: Run AI Prediction for a specific project
     * Route: POST /admin/global-management/ai-management/analyze/{id}
     */
    public function analyzeProject($id)
    {
        try {
            // 1. Call Python API using configured URL
            $aiUrl = config('services.ai.url', 'http://127.0.0.1:5001');
            $timeout = config('services.ai.timeout', 10);
            $response = Http::timeout($timeout)->get("{$aiUrl}/predict/{$id}");

            if ($response->failed()) {
                return response()->json(['success' => false, 'message' => 'AI Service Unavailable'], 500);
            }

            $data = $response->json();

            // Check if Python returned an error (e.g., project not found)
            if (isset($data['error'])) {
                return response()->json(['success' => false, 'message' => $data['error']], 400);
            }

            // 2. Save to Database (Create History Log)
            DB::table('ai_prediction_logs')->insert([
                'project_id' => $id,
                'prediction' => $data['prediction']['prediction'],
                'delay_probability' => $data['prediction']['delay_probability'],
                'weather_severity' => $data['weather_severity'],
                'ai_response_snapshot' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            AdminActivityLog::log('ai_analysis_run', ['project_id' => $id]);
            return response()->json(['success' => true, 'message' => 'Analysis Complete', 'data' => $data]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the posting management page
     */
    public function postingManagement(Request $request)
    {
        $filters = [
            'search' => $request->query('search'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'status' => $request->query('status', 'under_review'),
        ];

        $model = new postingManagementClass();
        $postings = $model->fetchPosts($filters);

        // Check if viewing a specific post
        $viewPostId = $request->query('view');
        $postDetails = null;
        if ($viewPostId) {
            $postDetails = $model->getPostDetails($viewPostId);
        }

        if ($request->ajax()) {
            return response()->json([
                'owners_html' => view('admin.globalManagement.partials.postManagementTable', ['postings' => $postings])->render()
            ]);
        }

        return view('admin.globalManagement.postingManagement', [
            'postings' => $postings,
            'postDetails' => $postDetails
        ]);
    }

    /**
     * Display the review & rating management page
     */
    public function reviewManagement(Request $request)
    {
        $filters = [
            'search' => $request->query('search'),
            'rating' => $request->query('rating'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $model = new reviewsClass();
        $reviews = $model->fetchReviews($filters);

        if ($request->ajax()) {
            return response()->json([
                'reviews_html' => view('admin.globalManagement.partials.reviewManagementTable', ['reviews' => $reviews])->render()
            ]);
        }

        return view('admin.globalManagement.reviewManagement', [
            'reviews' => $reviews
        ]);
    }

    /**
     * Delete (soft delete) a review
     */
    public function deleteReview(Request $request, $id)
    {
        $request->validate([
            'deletion_reason' => 'required|string|max:500'
        ]);

        $model = new reviewsClass();
        $deleted = $model->deleteReview($id, $request->deletion_reason);

        if ($deleted) {
            AdminActivityLog::log('review_deleted', ['review_id' => $id, 'reason' => $request->deletion_reason]);
            return response()->json(['success' => true, 'message' => 'Review successfully deleted.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to delete review.'], 400);
    }

    /**
     * Get all bids with project and contractor information
     */
    private function getAllBids($search = null, $status = null, $page = 1)
    {
        $query = DB::table('bids')
            ->join('projects', 'bids.project_id', '=', 'projects.project_id')
            ->join('contractors', 'bids.contractor_id', '=', 'contractors.contractor_id')
            ->select(
                'bids.bid_id',
                'bids.proposed_cost as bid_amount',
                'bids.submitted_at as bid_date',
                'bids.bid_status',
                'bids.contractor_notes',
                'bids.reason',
                'bids.decision_date',
                'bids.estimated_timeline',
                'projects.project_title',
                'contractors.company_name',
                'contractors.company_email',
                'contractors.picab_number',
                'contractors.picab_category',
                'contractors.picab_expiration_date',
                'contractors.business_permit_number',
                'contractors.business_permit_city',
                'contractors.business_permit_expiration',
                'contractors.tin_business_reg_number'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('projects.project_title', 'like', "%{$search}%")
                    ->orWhere('contractors.company_name', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('bids.bid_status', $status);
        }

        return $query->orderBy('bids.submitted_at', 'desc')
            ->paginate(10, ['*'], 'page', $page);
    }
    // ----------------------------------------------------------------
// 2. NEW: getBidFiles() â€” AJAX endpoint to load files for a bid
//    Route: GET /admin/global-management/bid-management/files/{id}
// ----------------------------------------------------------------

    public function getBidFiles($id)
    {
        $files = DB::table('bid_files')
            ->where('bid_id', $id)
            ->select('file_id', 'file_name', 'file_path', 'description', 'uploaded_at')
            ->get()
            ->map(function ($f) {
                $f->uploaded_at = \Carbon\Carbon::parse($f->uploaded_at)->format('M d, Y');
                return $f;
            });

        return response()->json($files);
    }

    // ----------------------------------------------------------------
// 3. NEW: updateBid() â€” AJAX PUT to update status/cost/notes
//    Route: PUT /admin/global-management/bid-management/{id}
// ----------------------------------------------------------------

    public function updateBid(Request $request, $id)
    {
        $allowed = ['submitted', 'under_review', 'accepted', 'rejected', 'cancelled'];
        $status = $request->input('bid_status');

        if ($status && !in_array($status, $allowed)) {
            return response()->json(['success' => false, 'message' => 'Invalid status.'], 422);
        }

        $data = array_filter([
            'bid_status' => $status,
            'proposed_cost' => $request->input('proposed_cost'),
            'contractor_notes' => $request->input('contractor_notes'),
        ], fn($v) => !is_null($v));

        if (empty($data)) {
            return response()->json(['success' => false, 'message' => 'No data to update.'], 422);
        }

        $updated = DB::table('bids')->where('bid_id', $id)->update($data);

        if ($updated !== false) {
            AdminActivityLog::log('bid_updated', ['bid_id' => $id, 'status' => $status]);
            return response()->json(['success' => true, 'message' => 'Bid updated successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to update bid.'], 400);
    }

    // ----------------------------------------------------------------
// 4. NEW: deleteBid() â€” AJAX DELETE
//    Route: DELETE /admin/global-management/bid-management/{id}
// ----------------------------------------------------------------

    public function deleteBid($id)
    {
        // Also remove associated files
        DB::table('bid_files')->where('bid_id', $id)->delete();

        $deleted = DB::table('bids')->where('bid_id', $id)->delete();

        if ($deleted) {
            AdminActivityLog::log('bid_deleted', ['bid_id' => $id]);
            return response()->json(['success' => true, 'message' => 'Bid deleted.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to delete bid.'], 400);
    }
    /**
     * Get all payment proofs â€” joined correctly using milestone_payments schema.
     *
     * milestone_payments columns used:
     *   payment_id, item_id, project_id, owner_id, contractor_user_id,
     *   amount, payment_type, transaction_number, receipt_photo,
     *   transaction_date, payment_status, reason, updated_at
     *
     * owner_id  â†’ property_owners.owner_id
     * contractor_user_id â†’ users.user_id â†’ property_owners â†’ contractors (company_name)
     * item_id   â†’ milestone_items.item_id (milestone_item_title)
     */
    private function getAllPaymentProofs($search = null, $status = null, $page = 1)
    {
        $query = DB::table('milestone_payments as mp')
            // project info
            ->join('projects as p', 'mp.project_id', '=', 'p.project_id')
            // owner info
            ->leftJoin('property_owners as po', 'mp.owner_id', '=', 'po.owner_id')
            ->leftJoin('users as owner_u', 'owner_u.user_id', '=', 'po.user_id')
            // contractor info
            ->leftJoin('contractors as c', 'mp.contractor_id', '=', 'c.contractor_id')
            // milestone item title
            ->leftJoin('milestone_items as mi', 'mp.item_id', '=', 'mi.item_id')
            ->select(
                'mp.payment_id',
                'mp.amount',
                'mp.transaction_date as payment_date',
                'mp.payment_status',
                'mp.payment_type',
                'mp.transaction_number',
                'mp.receipt_photo',
                'mp.reason',
                'mp.updated_at',
                'p.project_title',
                DB::raw("CONCAT(owner_u.first_name, ' ', owner_u.last_name) as owner_name"),
                'c.company_name',
                'c.company_email',
                'mi.milestone_item_title'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('p.project_title', 'like', "%{$search}%")
                    ->orWhere('c.company_name', 'like', "%{$search}%")
                    ->orWhere('u.first_name', 'like', "%{$search}%")
                    ->orWhere('u.last_name', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('mp.payment_status', $status);
        }

        return $query->orderBy('mp.transaction_date', 'desc')
            ->paginate(10, ['*'], 'page', $page);
    }


    /**
     * Get AI usage statistics by connecting to Python Service
     * (single, authoritative definition â€” no duplicate)
     */
    private function getAIUsageStats()
    {
        // Safe defaults
        $aiData = [
            'status' => 'Offline',
            'features' => [],
        ];

        try {
            $aiUrl = config('services.ai.url', 'http://127.0.0.1:5001');
            $response = Http::timeout(5)->get("{$aiUrl}/system-status");

            if ($response->successful()) {
                $data = $response->json();
                // Only read keys that Python actually returns
                $aiData['status'] = $data['service_status'] ?? 'Offline';
                $aiData['features'] = $data['active_features'] ?? [];
            }
        } catch (\Exception $e) {
            // Keep offline defaults on failure
        }

        return $aiData;
    }

    /**
     * Get all postings (projects) with posting stats
     */
    private function getAllPostings($search = null, $status = null, $page = 1)
    {
        $query = DB::table('projects')
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('users as po_user', 'property_owners.user_id', '=', 'po_user.user_id')
            ->leftJoin('bids', 'projects.project_id', '=', 'bids.project_id')
            ->select(
                'projects.project_id',
                'projects.project_title',
                'projects.project_status',
                'project_relationships.created_at as posted_at',
                DB::raw("CONCAT(po_user.first_name, ' ', po_user.last_name) as owner_name"),
                DB::raw('COUNT(DISTINCT bids.bid_id) as bid_count')
            )
            ->groupBy(
                'projects.project_id',
                'projects.project_title',
                'projects.project_status',
                'project_relationships.created_at',
                'po_user.first_name',
                'po_user.last_name'
            );

        if ($search) {
            $query->where('projects.project_title', 'like', "%{$search}%");
        }

        if ($status) {
            $query->where('projects.project_status', $status);
        }

        return $query->paginate(15, ['*'], 'page', $page);
    }

    // =============================================
    // API METHODS FOR AJAX CALLS
    // =============================================

    /**
     * Get bids as JSON (for AJAX)
     */
    public function getBidsApi(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $page = $request->input('page', 1);

        $bids = $this->getAllBids($search, $status, $page);

        return response()->json($bids);
    }

    /**
     * Approve a bid
     */
    public function approveBid($id)
    {
        $updated = DB::table('bids')
            ->where('bid_id', $id)
            ->update(['bid_status' => 'approved']);

        if ($updated) {
            AdminActivityLog::log('bid_approved', ['bid_id' => $id]);
            return response()->json(['success' => true, 'message' => 'Bid approved']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to approve bid'], 400);
    }

    /**
     * Reject a bid
     */
    public function rejectBid(Request $request, $id)
    {
        $reason = $request->input('reason', 'Rejected by admin');

        $updated = DB::table('bids')
            ->where('bid_id', $id)
            ->update([
                'bid_status' => 'rejected',
                'rejection_reason' => $reason
            ]);

        if ($updated) {
            AdminActivityLog::log('bid_rejected', ['bid_id' => $id, 'reason' => $reason]);
            return response()->json(['success' => true, 'message' => 'Bid rejected']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to reject bid'], 400);
    }

    /**
     * Get payments as JSON (for AJAX)
     */

    public function getPaymentDetail($id)
    {
        $payment = DB::table('milestone_payments as mp')
            ->join('projects as p', 'mp.project_id', '=', 'p.project_id')
            ->leftJoin('property_owners as po', 'mp.owner_id', '=', 'po.owner_id')
            ->leftJoin('users as owner_u', 'owner_u.user_id', '=', 'po.user_id')
            // contractor info
            ->leftJoin('contractors as c', 'mp.contractor_id', '=', 'c.contractor_id')
            ->leftJoin('milestone_items as mi', 'mp.item_id', '=', 'mi.item_id')
            ->select(
                'mp.*',
                'p.project_title',
                'p.project_description',
                DB::raw("CONCAT(owner_u.first_name, ' ', owner_u.last_name) as owner_name"),
                'c.company_name',
                'c.company_email',
                'mi.milestone_item_title'
            )
            ->where('mp.payment_id', $id)
            ->first();

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $payment]);
    }

    /**
     * Update payment fields freely (amount, transaction_number, payment_type, status, reason).
     * Route: PUT /admin/global-management/proof-of-payments/{id}
     */
    public function updatePayment(Request $request, $id)
    {
        $allowed = ['submitted', 'approved', 'rejected', 'deleted'];
        $status = $request->input('payment_status');

        if ($status && !in_array($status, $allowed)) {
            return response()->json(['success' => false, 'message' => 'Invalid status.'], 422);
        }

        $allowedMethods = ['cash', 'check', 'bank_transfer', 'online_payment'];
        $method = $request->input('payment_type');
        if ($method && !in_array($method, $allowedMethods)) {
            return response()->json(['success' => false, 'message' => 'Invalid payment method.'], 422);
        }

        // Build update payload â€” only include fields that were actually sent
        $data = [];

        if (!is_null($status)) {
            $data['payment_status'] = $status;
        }
        if (!is_null($method)) {
            $data['payment_type'] = $method;
        }
        if ($request->has('amount') && $request->input('amount') !== '') {
            $data['amount'] = (float) $request->input('amount');
        }
        if ($request->has('transaction_number')) {
            $data['transaction_number'] = $request->input('transaction_number');
        }
        if ($request->has('reason')) {
            $data['reason'] = $request->input('reason');
        }

        if (empty($data)) {
            return response()->json(['success' => false, 'message' => 'No data to update.'], 422);
        }

        $data['updated_at'] = now();

        $updated = DB::table('milestone_payments')
            ->where('payment_id', $id)
            ->update($data);

        if ($updated !== false) {
            AdminActivityLog::log('payment_updated', ['payment_id' => $id, 'status' => $status]);
            return response()->json(['success' => true, 'message' => 'Payment updated successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to update payment.'], 400);
    }

    /**
     * Verify / approve a payment.
     */
    public function verifyPayment($id)
    {
        try {
            DB::beginTransaction();

            // Get payment details before approval
            $payment = DB::table('milestone_payments')
                ->where('payment_id', $id)
                ->first();

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
            }

            // Update payment status to approved
            $updated = DB::table('milestone_payments')
                ->where('payment_id', $id)
                ->update(['payment_status' => 'approved', 'updated_at' => now()]);

            if (!$updated) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Failed to approve payment.'], 400);
            }

            // Process payment allocation (overpayment/underpayment logic)
            $allocation = \App\Models\admin\paymentAdjustmentClass::processPaymentAllocation(
                $id,
                $payment->item_id,
                $payment->project_id
            );

            DB::commit();

            AdminActivityLog::log('payment_verified', ['payment_id' => $id, 'amount' => $payment->amount ?? null]);

            // Build response message based on allocation status
            $message = 'Payment approved.';
            if ($allocation['status'] === 'overpaid') {
                $message .= ' Overpayment of PHP ' . number_format($allocation['over_amount'], 2) . ' recorded.';
            } elseif ($allocation['status'] === 'underpaid' && isset($allocation['carried_to_title'])) {
                $message .= ' Shortfall of PHP ' . number_format($allocation['shortfall'], 2) . ' carried forward to "' . $allocation['carried_to_title'] . '".';
            } elseif ($allocation['status'] === 'underpaid') {
                $message .= ' Shortfall of PHP ' . number_format($allocation['shortfall'], 2) . ' recorded (last milestone).';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'allocation' => $allocation
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a payment.
     */
    public function rejectPayment(Request $request, $id)
    {
        $reason = $request->input('reason', 'Rejected by admin');
        $updated = DB::table('milestone_payments')
            ->where('payment_id', $id)
            ->update(['payment_status' => 'rejected', 'reason' => $reason, 'updated_at' => now()]);

        if ($updated) {
            AdminActivityLog::log('payment_rejected', ['payment_id' => $id, 'reason' => $reason]);
            return response()->json(['success' => true, 'message' => 'Payment rejected.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to reject payment.'], 400);
    }

    /**
     * Soft-delete a payment record.
     */
    public function deletePayment($id)
    {
        $deleted = DB::table('milestone_payments')
            ->where('payment_id', $id)
            ->update(['payment_status' => 'deleted', 'updated_at' => now()]);

        if ($deleted) {
            AdminActivityLog::log('payment_deleted', ['payment_id' => $id]);
            return response()->json(['success' => true, 'message' => 'Payment deleted.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to delete payment.'], 400);
    }
    /**
     * AJAX: paginated payments list (JSON).
     */
    public function getPaymentsApi(Request $request)
    {
        $payments = $this->getAllPaymentProofs(
            $request->input('search'),
            $request->input('status'),
            $request->input('page', 1)
        );

        return response()->json($payments);
    }

    /**
     * Get postings as JSON (for AJAX)
     */
    public function getPostingsApi(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $page = $request->input('page', 1);

        $postings = $this->getAllPostings($search, $status, $page);

        return response()->json($postings);
    }



    /**
     * Get full project details including files.
     */
    public function getPostDetails($id)
    {
        $model = new postingManagementClass();
        $details = $model->getPostDetails($id);

        if ($details) {
            return response()->json(['success' => true, 'data' => $details]);
        }

        return response()->json(['success' => false, 'message' => 'Post not found'], 404);
    }

    /**
     * Approve a posting
     */
    public function approvePosting($id)
    {
        $model = new postingManagementClass();
        $approved = $model->approvePost($id);

        if ($approved) {
            AdminActivityLog::log('posting_approved', ['project_id' => $id]);
            return response()->json(['success' => true, 'message' => 'Posting approved']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to approve posting'], 400);
    }

    /**
     * Reject a posting
     */
    public function rejectPosting(rejectPostRequest $request, $id)
    {
        $reason = $request->validated('reason');

        $model = new postingManagementClass();
        $rejected = $model->rejectPost($id, $reason);

        if ($rejected) {
            AdminActivityLog::log('posting_rejected', ['project_id' => $id, 'reason' => $reason]);
            return response()->json(['success' => true, 'message' => 'Posting rejected']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to reject posting'], 400);
    }

    /**
     * Get AI statistics as JSON
     */
    public function getAiStatsApi()
    {
        return response()->json($this->getAIUsageStats());
    }

    /**
     * Display the Report Management page
     */
    public function reportManagement(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 15;

        $counts = reportManagementClass::getCounts();
        $moderationCases = reportManagementClass::getAllModerationCases([], $page, $perPage);
        $reports = $moderationCases['data'];
        $reporterStats = reportManagementClass::getReporterStats();

        return view('admin.globalManagement.reportManagement', [
            'counts' => $counts,
            'reports' => $reports,
            'casesPagination' => $moderationCases['pagination'],
            'reporterStats' => $reporterStats,
        ]);
    }

    /**
     * API: Get filtered reports for AJAX
     */
    public function getReportsApi(Request $request)
    {
        $filters = [
            'status' => $request->input('status', 'all'),
            'source_type' => $request->input('source_type', 'all'),
            'case_type' => $request->input('case_type', 'all'),
            'search' => $request->input('search'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, (int) $request->input('per_page', 15));

        $moderationCases = reportManagementClass::getAllModerationCases($filters, $page, $perPage);
        $counts = reportManagementClass::getCounts();

        return response()->json([
            'success' => true,
            'reports' => $moderationCases['data'],
            'pagination' => $moderationCases['pagination'],
            'counts' => $counts,
        ]);
    }

    /**
     * API: Get report detail with evidence for the View modal
     */
    public function getReportDetail(Request $request, $source, $id)
    {
        $detail = reportManagementClass::getReportWithEvidence($source, $id);

        if (!$detail) {
            return response()->json(['success' => false, 'message' => 'Report not found'], 404);
        }

        return response()->json(['success' => true, 'report' => $detail]);
    }

    /**
     * API: Get user profile card for suspension modal
     */
    public function getUserProfileCard(Request $request, $userId)
    {
        $profile = reportManagementClass::getUserProfileCard($userId);

        if (!$profile) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        return response()->json(['success' => true, 'profile' => $profile]);
    }

    /**
     * API: Resolve reported user from a moderation case and return profile data.
     */
    public function getCaseReportedUserProfile(Request $request, $source, $id)
    {
        $reportedUserId = (int) (reportManagementClass::resolveReportedUserIdForCase($source, $id) ?? 0);
        if ($reportedUserId <= 0) {
            return response()->json(['success' => false, 'message' => 'Unable to resolve reported user for this case'], 404);
        }

        $profile = reportManagementClass::getUserProfileCard($reportedUserId);
        if (!$profile) {
            return response()->json(['success' => false, 'message' => 'Reported user profile not found'], 404);
        }

        return response()->json([
            'success' => true,
            'reported_user_id' => $reportedUserId,
            'profile' => $profile,
        ]);
    }

    /**
     * Dismiss a report (mark as invalid, no action against reported user)
     */
    public function dismissReport(Request $request, $source, $id)
    {
        $reason = $request->input('reason');
        if (empty($reason)) {
            return response()->json(['success' => false, 'message' => 'Dismissal reason is required'], 422);
        }

        $adminId = session('admin_user_id');
        $result = reportManagementClass::updateReportStatus($source, $id, 'dismissed', $reason, $adminId);

        AdminActivityLog::log('report_dismissed', [
            'source' => $source,
            'report_id' => $id,
            'reason' => $reason,
        ]);

        return response()->json(['success' => (bool) $result]);
    }

    /**
     * Confirm a report: resolve the report + suspend the user + hide offending content
     * Single-step execution
     */
    public function confirmReport(Request $request, $source, $id)
    {
        $suspensionReason = $request->input('suspension_reason');
        $suspensionType   = $request->input('suspension_type', 'temporary');
        $suspensionUntil  = $request->input('suspension_until');
        $reportedUserId   = $request->input('reported_user_id');

        if (empty($suspensionReason)) {
            return response()->json(['success' => false, 'message' => 'Suspension reason is required'], 422);
        }
        if ($suspensionType === 'temporary' && empty($suspensionUntil)) {
            return response()->json(['success' => false, 'message' => 'Suspension end date is required for temporary bans'], 422);
        }
        if (empty($reportedUserId)) {
            return response()->json(['success' => false, 'message' => 'Reported user ID is required'], 422);
        }

        $adminId = session('admin_user_id');

        try {
            DB::beginTransaction();

            // 1. Resolve the report
            reportManagementClass::updateReportStatus($source, $id, 'resolved', $suspensionReason, $adminId);

            // 2. Hide offending content (reviews, posts, etc.)
            reportManagementClass::hideContent($source, $id);

            // 3. Suspend the reported user
            $user = DB::table('users')->where('user_id', $reportedUserId)->first();

            if ($user) {
                $duration = $suspensionType === 'permanent' ? 'permanent' : 'temporary';
                $suspUntilDate = $suspensionType === 'permanent' ? '9999-12-31' : $suspensionUntil;
                $owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();

                if ($user->user_type === 'property_owner' || $user->user_type === 'both') {
                    if ($owner) {
                        $ownerModel = new \App\Models\admin\propertyOwnerClass();
                        $ownerModel->suspendOwner($owner->owner_id, $suspensionReason, $duration, $suspUntilDate);
                    }
                }

                if ($user->user_type === 'contractor' || $user->user_type === 'both') {
                    if ($owner) {
                        $contractor = DB::table('contractors')->where('owner_id', $owner->owner_id)->first();
                        if ($contractor) {
                            $contractorModel = new \App\Models\admin\contractorClass();
                            $contractorModel->suspendContractor($contractor->contractor_id, $suspensionReason, $duration, $suspUntilDate);
                        }
                    }
                }
            }

            DB::commit();

            AdminActivityLog::log('report_confirmed_and_suspended', [
                'source' => $source,
                'report_id' => $id,
                'reported_user_id' => $reportedUserId,
                'suspension_type' => $suspensionType,
                'suspension_until' => $suspensionUntil,
            ]);

            return response()->json(['success' => true, 'message' => 'Report resolved and user suspended successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a report's status (basic status change)
     */
    public function updateReportStatus(Request $request, $source, $id)
    {
        $status = $request->input('status');
        $adminNotes = $request->input('admin_notes');
        $adminId = session('admin_user_id');

        if (!in_array($status, ['under_review', 'resolved', 'dismissed'])) {
            return response()->json(['success' => false, 'message' => 'Invalid status'], 422);
        }

        $result = reportManagementClass::updateReportStatus($source, $id, $status, $adminNotes, $adminId);

        // Keep moderation behavior intact: resolved reports should hide the offending content.
        if ($result && $status === 'resolved') {
            reportManagementClass::hideContent($source, $id);
        }

        AdminActivityLog::log('report_status_updated', [
            'source' => $source,
            'report_id' => $id,
            'new_status' => $status,
        ]);

        return response()->json(['success' => (bool) $result]);
    }

    /**
     * Apply moderation approval with sanction in one step.
     */
    public function applyResolutionAction(Request $request, $source, $id)
    {
        $actionType = $request->input('action_type'); // warning, temporary_ban, permanent_ban
        $reason = trim((string) $request->input('reason', ''));
        $reportedUserId = (int) $request->input('reported_user_id', 0);
        $banUntil = $request->input('ban_until');

        $adminActionLabel = match ($actionType) {
            'warning' => 'Warned',
            'temporary_ban' => 'Temporarily Banned',
            'permanent_ban' => 'Permanently Banned',
            default => null,
        };

        if (!in_array($actionType, ['warning', 'temporary_ban', 'permanent_ban'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid resolution action'], 422);
        }

        if ($reason === '') {
            return response()->json(['success' => false, 'message' => 'Resolution action reason is required'], 422);
        }

        if ($actionType === 'temporary_ban' && empty($banUntil)) {
            return response()->json(['success' => false, 'message' => 'Ban end date is required for temporary bans'], 422);
        }

        // Disputes keep their own dedicated resolution workflow.
        if ($source === 'dispute') {
            $currentStatus = DB::table('disputes')->where('dispute_id', $id)->value('dispute_status');
            if ($currentStatus !== 'resolved') {
                return response()->json(['success' => false, 'message' => 'Case must be resolved before applying a resolution action'], 422);
            }
        } else {
            $table = match ($source) {
                'post' => 'post_reports',
                'content' => 'content_reports',
                'review' => 'review_reports',
                'user' => 'user_reports',
                default => null,
            };

            if (!$table) {
                return response()->json(['success' => false, 'message' => 'Invalid source'], 422);
            }

            $currentStatus = DB::table($table)->where('report_id', $id)->value('status');
            if ($currentStatus === null) {
                return response()->json(['success' => false, 'message' => 'Case not found'], 404);
            }
        }

        if ($reportedUserId <= 0) {
            $reportedUserId = (int) (reportManagementClass::resolveReportedUserIdForCase($source, $id) ?? 0);
        }

        if ($reportedUserId <= 0) {
            return response()->json(['success' => false, 'message' => 'Unable to resolve the reported user for this case'], 422);
        }

        $user = DB::table('users')->where('user_id', $reportedUserId)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Reported user not found'], 404);
        }

        try {
            DB::beginTransaction();

            if ($source !== 'dispute') {
                $adminId = session('admin_user_id');
                reportManagementClass::updateReportStatus(
                    $source,
                    $id,
                    'resolved',
                    $reason,
                    $adminId,
                    $adminActionLabel
                );

                // Review/showcase/project reports are hidden on approval.
                if (in_array($source, ['post', 'content', 'review'], true)) {
                    reportManagementClass::hideContent($source, $id);
                }
            }

            if ($actionType === 'warning') {
                reportManagementClass::notifyUser(
                    $reportedUserId,
                    'Official Warning from Admin',
                    "A moderation warning has been issued on your account. Reason: {$reason}",
                    'Admin Announcement'
                );
            } else {
                $suspensionType = $actionType === 'permanent_ban' ? 'permanent' : 'temporary';
                $suspUntilDate = $actionType === 'permanent_ban' ? '9999-12-31' : $banUntil;
                $this->suspendResolvedUser($user, $reason, $suspensionType, $suspUntilDate);

                $durationLabel = $actionType === 'permanent_ban' ? 'permanent' : ('until ' . $banUntil);
                reportManagementClass::notifyUser(
                    $reportedUserId,
                    'Account Restriction Applied',
                    "A moderation action has been applied to your account. Reason: {$reason}. Restriction: {$durationLabel}.",
                    'Admin Announcement'
                );
            }

            AdminActivityLog::log('case_resolution_action_applied', [
                'source' => $source,
                'report_id' => $id,
                'reported_user_id' => $reportedUserId,
                'action_type' => $actionType,
                'admin_action' => $adminActionLabel,
                'ban_until' => $banUntil,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Resolution action applied successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    private function suspendResolvedUser($user, string $reason, string $duration, string $suspUntilDate): void
    {
        $owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();

        if ($user->user_type === 'property_owner' || $user->user_type === 'both') {
            if ($owner) {
                $ownerModel = new \App\Models\admin\propertyOwnerClass();
                $ownerModel->suspendOwner($owner->owner_id, $reason, $duration, $suspUntilDate);
            }
        }

        if ($user->user_type === 'contractor' || $user->user_type === 'both') {
            if ($owner) {
                $contractor = DB::table('contractors')->where('owner_id', $owner->owner_id)->first();
                if ($contractor) {
                    $contractorModel = new \App\Models\admin\contractorClass();
                    $contractorModel->suspendContractor($contractor->contractor_id, $reason, $duration, $suspUntilDate);
                }
            }
        }
    }

    /**
     * API: Get reporter statistics for Report History tab
     */
    public function getReporterStatsApi(Request $request)
    {
        $stats = reportManagementClass::getReporterStats();
        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /**
     * API: Admin search for users, posts, or reviews (Direct Admin Action tab)
     */
    public function adminSearch(Request $request)
    {
        $type = $request->input('type', 'user');
        $query = $request->input('query', '');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 15;

        $results = match ($type) {
            'user'   => reportManagementClass::searchUsers($query, $page, $perPage),
            'showcase' => reportManagementClass::searchPosts($query, $page, $perPage),
            'project' => reportManagementClass::searchProjects($query, $page, $perPage),
            'post'   => reportManagementClass::searchPosts($query, $page, $perPage),
            'review' => reportManagementClass::searchReviews($query, $page, $perPage),
            default  => ['data' => [], 'total' => 0, 'page' => 1, 'per_page' => $perPage, 'last_page' => 1],
        };

        return response()->json(['success' => true, 'type' => $type, 'results' => $results['data'], 'pagination' => [
            'total' => $results['total'],
            'page' => $results['page'],
            'per_page' => $results['per_page'],
            'last_page' => $results['last_page'],
        ]]);
    }

    /**
      * Admin direct action: suspend user, hide/unhide showcase project review content
     */
    public function adminDirectAction(Request $request)
    {
                $actionType = $request->input('action_type'); // suspend_user, hide/unhide post/project/review
                $targetId = (int) $request->input('target_id');
                $reason = trim((string) $request->input('reason', ''));

                if (empty($actionType) || $targetId <= 0) {
            return response()->json(['success' => false, 'message' => 'Action type and target ID are required'], 422);
        }

        if (empty($reason) && in_array($actionType, ['suspend_user', 'hide_post', 'hide_project', 'hide_review'], true)) {
            return response()->json(['success' => false, 'message' => 'A reason is required for this action'], 422);
        }

        if (empty($reason) && in_array($actionType, ['unhide_post', 'unhide_project', 'unhide_review'], true)) {
            $reason = 'Restored by admin after moderation review';
        }

        $adminId = session('user')->admin_id ?? null;
        $targetType = null;

        try {
            DB::beginTransaction();

            if ($actionType === 'suspend_user') {
                $suspensionType  = $request->input('suspension_type', 'temporary');
                $suspensionUntil = $request->input('suspension_until');

                if ($suspensionType === 'temporary' && empty($suspensionUntil)) {
                    return response()->json(['success' => false, 'message' => 'Suspension end date is required for temporary bans'], 422);
                }

                $user = DB::table('users')->where('user_id', $targetId)->first();
                if (!$user) {
                    return response()->json(['success' => false, 'message' => 'User not found'], 404);
                }

                $duration = $suspensionType === 'permanent' ? 'permanent' : 'temporary';
                $suspUntilDate = $suspensionType === 'permanent' ? '9999-12-31' : $suspensionUntil;
                $owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();

                if ($user->user_type === 'property_owner' || $user->user_type === 'both') {
                    if ($owner) {
                        $ownerModel = new \App\Models\admin\propertyOwnerClass();
                        $ownerModel->suspendOwner($owner->owner_id, $reason, $duration, $suspUntilDate);
                    }
                }

                if ($user->user_type === 'contractor' || $user->user_type === 'both') {
                    if ($owner) {
                        $contractor = DB::table('contractors')->where('owner_id', $owner->owner_id)->first();
                        if ($contractor) {
                            $contractorModel = new \App\Models\admin\contractorClass();
                            $contractorModel->suspendContractor($contractor->contractor_id, $reason, $duration, $suspUntilDate);
                        }
                    }
                }

                // Notify suspended user
                reportManagementClass::notifyUser($user->user_id, 'Your Account Has Been Suspended',
                    "Your account has been suspended by an administrator. Reason: {$reason}. Duration: {$duration}.",
                    'Admin Announcement');

                AdminActivityLog::log('admin_direct_suspend', [
                    'user_id' => $targetId,
                    'suspension_type' => $suspensionType,
                    'suspension_until' => $suspensionUntil,
                    'reason' => $reason,
                ]);
                $targetType = 'user';

            } elseif ($actionType === 'hide_post') {
                $updated = reportManagementClass::adminHidePost($targetId, $reason);
                if (!$updated) {
                    return response()->json(['success' => false, 'message' => 'Showcase post not found or already hidden'], 404);
                }

                AdminActivityLog::log('admin_direct_hide_post', [
                    'post_id' => $targetId,
                    'reason' => $reason,
                ]);
                $targetType = 'showcase';

            } elseif ($actionType === 'hide_project') {
                $updated = reportManagementClass::adminHideProject($targetId, $reason);
                if (!$updated) {
                    return response()->json(['success' => false, 'message' => 'Project post not found or already hidden'], 404);
                }

                AdminActivityLog::log('admin_direct_hide_project', [
                    'project_id' => $targetId,
                    'reason' => $reason,
                ]);
                $targetType = 'project';

            } elseif ($actionType === 'hide_review') {
                $updated = reportManagementClass::adminHideReview($targetId, $reason);
                if (!$updated) {
                    return response()->json(['success' => false, 'message' => 'Review not found or already hidden'], 404);
                }

                AdminActivityLog::log('admin_direct_hide_review', [
                    'review_id' => $targetId,
                    'reason' => $reason,
                ]);
                $targetType = 'review';

            } elseif ($actionType === 'unhide_post') {
                $updated = reportManagementClass::adminUnhidePost($targetId, $reason);
                if (!$updated) {
                    return response()->json(['success' => false, 'message' => 'Showcase post not found or already visible'], 404);
                }

                AdminActivityLog::log('admin_direct_unhide_post', [
                    'post_id' => $targetId,
                    'reason' => $reason,
                ]);
                $targetType = 'showcase';

            } elseif ($actionType === 'unhide_project') {
                $updated = reportManagementClass::adminUnhideProject($targetId, $reason);
                if (!$updated) {
                    return response()->json(['success' => false, 'message' => 'Project post not found or already visible'], 404);
                }

                AdminActivityLog::log('admin_direct_unhide_project', [
                    'project_id' => $targetId,
                    'reason' => $reason,
                ]);
                $targetType = 'project';

            } elseif ($actionType === 'unhide_review') {
                $updated = reportManagementClass::adminUnhideReview($targetId, $reason);
                if (!$updated) {
                    return response()->json(['success' => false, 'message' => 'Review not found or already visible'], 404);
                }

                AdminActivityLog::log('admin_direct_unhide_review', [
                    'review_id' => $targetId,
                    'reason' => $reason,
                ]);
                $targetType = 'review';

            } else {
                return response()->json(['success' => false, 'message' => 'Invalid action type'], 422);
            }

            if (Schema::hasTable('admin_direct_actions')) {
                DB::table('admin_direct_actions')->insert([
                    'admin_id' => $adminId,
                    'action_type' => $actionType,
                    'target_type' => $targetType,
                    'target_id' => $targetId,
                    'reason' => $reason,
                    'metadata' => json_encode([
                        'ip_address' => $request->ip(),
                        'user_agent' => (string) $request->header('User-Agent', ''),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Action completed successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
