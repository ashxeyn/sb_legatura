<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\admin\rejectPostRequest;
use App\Models\admin\postingManagementClass;

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
    public function proofOfPayments()
    {
        $payments = $this->getAllPaymentProofs();
        return view('admin.globalManagement.proofOfpayments', [
            'payments' => $payments
        ]);
    }

    /**
     * Display the AI management page
     */
    public function aiManagement()
    {
        $aiUsage = $this->getAIUsageStats();
        return view('admin.globalManagement.aiManagement', [
            'aiUsage' => $aiUsage
        ]);
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
            ->join('projects', 'bids.project_id', '=', 'projects.project_id')
            ->join('contractors', 'bids.contractor_id', '=', 'contractors.contractor_id')
            ->select(
                'bids.bid_id',
                'bids.proposed_cost as bid_amount',
                'bids.submitted_at as bid_date',
                'bids.bid_status',
                'projects.project_title',
                'contractors.company_name',
                DB::raw("'N/A' as contact_person")
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

        return $query->paginate(15, ['*'], 'page', $page);
    }

    /**
     * Get all payment proofs with payment details
     */
    private function getAllPaymentProofs($search = null, $status = null, $page = 1)
    {
        // Map legacy `payment_proofs` usage to `milestone_payments` table in current schema.
        $paymentsTable = 'milestone_payments';
        $projectsTable = 'projects';
        $projRelTable = 'project_relationships';
        $ownersTable = 'property_owners';
        $usersTable = 'users';

        $query = DB::table($paymentsTable)
            ->join($projectsTable, "$paymentsTable.project_id", '=', "$projectsTable.project_id")
            ->leftJoin($projRelTable, "$projectsTable.relationship_id", '=', "$projRelTable.rel_id")
            ->leftJoin($ownersTable, "$projRelTable.owner_id", '=', "$ownersTable.owner_id")
            ->select(
                "$paymentsTable.payment_id as proof_id",
                "$paymentsTable.amount",
                "$paymentsTable.transaction_date as payment_date",
                "$paymentsTable.payment_status as proof_status",
                "$projectsTable.project_title",
                DB::raw("CONCAT($ownersTable.first_name, ' ', $ownersTable.last_name) as owner_name"),
                "$paymentsTable.receipt_photo as proof_file"
            );

        // if users.email is available, join users and allow email search
        if (Schema::hasColumn($ownersTable, 'user_id') && Schema::hasColumn($usersTable, 'user_id')) {
            $query->leftJoin($usersTable, "$ownersTable.user_id", '=', "$usersTable.user_id");
        }

        if ($search) {
            $query->where(function ($q) use ($search, $projectsTable, $usersTable) {
                $q->where("$projectsTable.project_title", 'like', "%{$search}%");
                if (Schema::hasColumn($usersTable, 'email')) {
                    $q->orWhere("$usersTable.email", 'like', "%{$search}%");
                }
            });
        }

        if ($status) {
            $query->where("$paymentsTable.payment_status", $status);
        }

        return $query->paginate(15, ['*'], 'page', $page);
    }

    /**
     * Get AI usage statistics
     */
    private function getAIUsageStats()
    {
        // ai_logs table does not exist in schema; return stub data
        return [
            'total_requests' => 0,
            'daily_usage' => 0,
            'monthly_usage' => 0,
            'top_features' => collect()
        ];
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
    public function getPaymentsApi(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $page = $request->input('page', 1);

        $payments = $this->getAllPaymentProofs($search, $status, $page);
        
        return response()->json($payments);
    }

    /**
     * Verify a payment
     */
    public function verifyPayment($id)
    {
        $updated = DB::table('milestone_payments')
            ->where('payment_id', $id)
            ->update(['payment_status' => 'approved']);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Payment verified']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to verify payment'], 400);
    }

    /**
     * Reject a payment
     */
    public function rejectPayment(Request $request, $id)
    {
        $reason = $request->input('reason', 'Rejected by admin');

        $updated = DB::table('milestone_payments')
            ->where('payment_id', $id)
            ->update([
                'payment_status' => 'rejected',
                'reason' => $reason
            ]);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Payment rejected']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to reject payment'], 400);
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
        $stats = $this->getAIUsageStats();
        
        return response()->json($stats);
    }
}
               