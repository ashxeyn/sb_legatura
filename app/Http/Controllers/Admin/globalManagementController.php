<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\admin\rejectPostRequest;
use App\Models\admin\postingManagementClass;
use Illuminate\Support\Facades\Http;

class globalManagementController extends Controller
{
    /**
     * Display the bid management page
     */
    public function bidManagement()
    {
        $bids = $this->getAllBids();
        return view('admin.globalManagement.bidManagement', [
            'bids' => $bids
        ]);
    }

    /**
     * Display the proof of payments page
     */
    public function proofOfPayments(Request $request)
    {
        $filters = [
            'search'    => $request->query('search'),
            'status'    => $request->query('status'),
            'date_from' => $request->query('date_from'),
            'date_to'   => $request->query('date_to'),
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
            'stats'    => $stats,
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
            'total'     => $rows->total     ?? 0,
            'pending'   => $rows->pending   ?? 0,
            'failed'    => $rows->failed    ?? 0,
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
            'aiUsage'        => $aiUsage,
            'predictionLogs' => $predictionLogs,
            'projects'       => $projects,
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
                'project_id'           => $id,
                'prediction'           => $data['prediction']['prediction'],
                'delay_probability'    => $data['prediction']['delay_probability'],
                'weather_severity'     => $data['weather_severity'],
                'ai_response_snapshot' => json_encode($data),
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

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

        if ($request->ajax()) {
            return response()->json([
                'owners_html' => view('admin.globalManagement.partials.postManagementTable', ['postings' => $postings])->render()
            ]);
        }

        return view('admin.globalManagement.postingManagement', [
            'postings' => $postings
        ]);
    }

    /**
     * Get all bids with project and contractor information
     */
    private function getAllBids($search = null, $status = null, $page = 1)
    {
        $query = DB::table('bids')
            ->join('projects',    'bids.project_id',    '=', 'projects.project_id')
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
                $q->where('projects.project_title',  'like', "%{$search}%")
                ->orWhere('contractors.company_name', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('bids.bid_status', $status);
        }

        return $query->orderBy('bids.submitted_at', 'desc')
                    ->paginate(15, ['*'], 'page', $page);
    }
// ----------------------------------------------------------------
// 2. NEW: getBidFiles() — AJAX endpoint to load files for a bid
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
// 3. NEW: updateBid() — AJAX PUT to update status/cost/notes
//    Route: PUT /admin/global-management/bid-management/{id}
// ----------------------------------------------------------------

    public function updateBid(Request $request, $id)
    {
        $allowed = ['submitted', 'under_review', 'accepted', 'rejected', 'cancelled'];
        $status  = $request->input('bid_status');

        if ($status && !in_array($status, $allowed)) {
            return response()->json(['success' => false, 'message' => 'Invalid status.'], 422);
        }

        $data = array_filter([
            'bid_status'       => $status,
            'proposed_cost'    => $request->input('proposed_cost'),
            'contractor_notes' => $request->input('contractor_notes'),
        ], fn($v) => !is_null($v));

        if (empty($data)) {
            return response()->json(['success' => false, 'message' => 'No data to update.'], 422);
        }

        $updated = DB::table('bids')->where('bid_id', $id)->update($data);

        if ($updated !== false) {
            return response()->json(['success' => true, 'message' => 'Bid updated successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to update bid.'], 400);
    }

// ----------------------------------------------------------------
// 4. NEW: deleteBid() — AJAX DELETE
//    Route: DELETE /admin/global-management/bid-management/{id}
// ----------------------------------------------------------------

    public function deleteBid($id)
    {
        // Also remove associated files
        DB::table('bid_files')->where('bid_id', $id)->delete();

        $deleted = DB::table('bids')->where('bid_id', $id)->delete();

        if ($deleted) {
            return response()->json(['success' => true, 'message' => 'Bid deleted.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to delete bid.'], 400);
    }
   /**
     * Get all payment proofs — joined correctly using milestone_payments schema.
     *
     * milestone_payments columns used:
     *   payment_id, item_id, project_id, owner_id, contractor_user_id,
     *   amount, payment_type, transaction_number, receipt_photo,
     *   transaction_date, payment_status, reason, updated_at
     *
     * owner_id  → property_owners.owner_id
     * contractor_user_id → users.user_id → contractors.user_id (company_name)
     * item_id   → milestone_items.item_id (milestone_item_title)
     */
    private function getAllPaymentProofs($search = null, $status = null, $page = 1)
    {
        $query = DB::table('milestone_payments as mp')
            // project info
            ->join('projects as p', 'mp.project_id', '=', 'p.project_id')
            // owner info
            ->leftJoin('property_owners as po', 'mp.owner_id', '=', 'po.owner_id')
            // ── FIX: go through contractor_users first ──
            ->leftJoin('contractor_users as cu', 'mp.contractor_user_id', '=', 'cu.contractor_user_id')
            ->leftJoin('contractors as c', 'cu.contractor_id', '=', 'c.contractor_id')
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
                DB::raw("CONCAT(po.first_name, ' ', po.last_name) as owner_name"),
                'c.company_name',
                'c.company_email',
                'mi.milestone_item_title'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('p.project_title',  'like', "%{$search}%")
                  ->orWhere('c.company_name', 'like', "%{$search}%")
                  ->orWhere('po.first_name',  'like', "%{$search}%")
                  ->orWhere('po.last_name',   'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('mp.payment_status', $status);
        }

        return $query->orderBy('mp.transaction_date', 'desc')
                     ->paginate(15, ['*'], 'page', $page);
    }


     /**
     * Get AI usage statistics by connecting to Python Service
     * (single, authoritative definition — no duplicate)
     */
    private function getAIUsageStats()
    {
        // Safe defaults
        $aiData = [
            'status'   => 'Offline',
            'features' => [],
        ];

        try {
            $aiUrl = config('services.ai.url', 'http://127.0.0.1:5001');
            $response = Http::timeout(5)->get("{$aiUrl}/system-status");

            if ($response->successful()) {
                $data = $response->json();
                // Only read keys that Python actually returns
                $aiData['status']   = $data['service_status']  ?? 'Offline';
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
            ->leftJoin('bids', 'projects.project_id', '=', 'bids.project_id')
            ->select(
                'projects.project_id',
                'projects.project_title',
                'projects.project_status',
                'project_relationships.created_at as posted_at',
                DB::raw("CONCAT(property_owners.first_name, ' ', property_owners.last_name) as owner_name"),
                DB::raw('COUNT(DISTINCT bids.bid_id) as bid_count')
            )
            ->groupBy(
                'projects.project_id',
                'projects.project_title',
                'projects.project_status',
                'project_relationships.created_at',
                'property_owners.first_name',
                'property_owners.last_name'
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
            // ── FIX: correct contractor join ──
            ->leftJoin('contractor_users as cu', 'mp.contractor_user_id', '=', 'cu.contractor_user_id')
            ->leftJoin('contractors as c', 'cu.contractor_id', '=', 'c.contractor_id')
            ->leftJoin('milestone_items as mi', 'mp.item_id', '=', 'mi.item_id')
            ->select(
                'mp.*',
                'p.project_title',
                'p.project_description',
                DB::raw("CONCAT(po.first_name, ' ', po.last_name) as owner_name"),
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
        $status  = $request->input('payment_status');

        if ($status && !in_array($status, $allowed)) {
            return response()->json(['success' => false, 'message' => 'Invalid status.'], 422);
        }

        $allowedMethods = ['cash', 'check', 'bank_transfer', 'online_payment'];
        $method = $request->input('payment_type');
        if ($method && !in_array($method, $allowedMethods)) {
            return response()->json(['success' => false, 'message' => 'Invalid payment method.'], 422);
        }

        // Build update payload — only include fields that were actually sent
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

        return ($updated !== false)
            ? response()->json(['success' => true,  'message' => 'Payment updated successfully.'])
            : response()->json(['success' => false, 'message' => 'Failed to update payment.'], 400);
    }

    /**
     * Verify / approve a payment.
     */
    public function verifyPayment($id)
    {
        $updated = DB::table('milestone_payments')
            ->where('payment_id', $id)
            ->update(['payment_status' => 'approved', 'updated_at' => now()]);

        return $updated
            ? response()->json(['success' => true,  'message' => 'Payment approved.'])
            : response()->json(['success' => false, 'message' => 'Failed to approve payment.'], 400);
    }

    /**
     * Reject a payment.
     */
    public function rejectPayment(Request $request, $id)
    {
        $reason  = $request->input('reason', 'Rejected by admin');
        $updated = DB::table('milestone_payments')
            ->where('payment_id', $id)
            ->update(['payment_status' => 'rejected', 'reason' => $reason, 'updated_at' => now()]);

        return $updated
            ? response()->json(['success' => true,  'message' => 'Payment rejected.'])
            : response()->json(['success' => false, 'message' => 'Failed to reject payment.'], 400);
    }

    /**
     * Soft-delete a payment record.
     */
    public function deletePayment($id)
    {
        $deleted = DB::table('milestone_payments')
            ->where('payment_id', $id)
            ->update(['payment_status' => 'deleted', 'updated_at' => now()]);

        return $deleted
            ? response()->json(['success' => true,  'message' => 'Payment deleted.'])
            : response()->json(['success' => false, 'message' => 'Failed to delete payment.'], 400);
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
}
