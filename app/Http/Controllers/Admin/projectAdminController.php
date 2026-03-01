<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class projectAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = DB::table('projects')
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->select('projects.*', 'property_owners.first_name', 'property_owners.last_name')
            ->orderBy('projects.project_id', 'desc')
            ->paginate(20);

        return view('admin.projects.index', compact('q'));
    }

    public function show($projectId)
    {
        $project = DB::table('projects')->where('project_id', $projectId)->first();
        if (!$project) return redirect()->route('admin.projectManagement.listOfProjects')->with('error','Project not found');

        // fetch bids (assumes bids table exists)
        $bids = DB::table('bids')
            ->where('project_id', $projectId)
            ->leftJoin('contractors', 'bids.contractor_id', '=', 'contractors.contractor_id')
            ->select('bids.*','contractors.company_name','contractors.years_of_experience')
            ->orderBy('bids.submitted_at','desc')
            ->get();

        // fetch milestones and payment plan
        $milestones = DB::table('milestones')->where('project_id', $projectId)->get();

        return view('admin.projects.show', compact('project','bids','milestones'));
    }

    public function approve($projectId)
    {
        DB::table('projects')->where('project_id', $projectId)->update([
            'project_status' => 'open',
        ]);

        // Notify property owner
        $ownerUserId = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->where('p.project_id', $projectId)
            ->value('po.user_id');
        if ($ownerUserId) {
            $projTitle = DB::table('projects')->where('project_id', $projectId)->value('project_title');
            NotificationService::create(
                (int) $ownerUserId,
                'project_update',
                'Project Approved',
                "Your project \"{$projTitle}\" has been approved and is now open for bidding.",
                'high',
                'project',
                (int) $projectId,
                ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $projectId]]
            );
        }

        // create audit log
        $user = session('user');
        DB::table('admin_audit_logs')->insert([
            'admin_user_id' => $user->admin_user_id ?? $user->id ?? null,
            'action' => "Approved project {$projectId}",
            'meta' => json_encode(['project_id'=>$projectId]),
            'created_at'=>now()
        ]);
        return back()->with('success','Project approved.');
    }

    public function reject($projectId, Request $request)
    {
        $reason = $request->input('reason', 'Rejected by admin');
        DB::table('projects')->where('project_id', $projectId)->update([
            'project_status' => 'rejected',
        ]);

        // Notify property owner
        $ownerUserId = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->where('p.project_id', $projectId)
            ->value('po.user_id');
        if ($ownerUserId) {
            $projTitle = DB::table('projects')->where('project_id', $projectId)->value('project_title');
            NotificationService::create(
                (int) $ownerUserId,
                'project_update',
                'Project Rejected',
                "Your project \"{$projTitle}\" has been rejected. Reason: {$reason}",
                'high',
                'project',
                (int) $projectId,
                ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $projectId]]
            );
        }

        $user = session('user');
        DB::table('admin_audit_logs')->insert([
           'admin_user_id' => $user->admin_user_id ?? $user->id ?? null,
            'action' => "Rejected project {$projectId}",
            'meta' => json_encode(['project_id'=>$projectId,'reason'=>$reason]),
            'created_at'=>now()
        ]);
        return back()->with('success','Project rejected.');
    }

    public function assignContractor($projectId, Request $request)
    {
        $contractorId = $request->input('contractor_id');
        // validation
        $contractor = DB::table('contractors')->where('contractor_id', $contractorId)->first();
        if (!$contractor) return back()->with('error','Contractor not found');

        $user = session('user');

        DB::table('projects')->where('project_id', $projectId)->update([
            'selected_contractor_id' => $contractorId,
            'project_status' => 'in_progress', // mark as started when assigned
        ]);
        DB::table('admin_audit_logs')->insert([
            'admin_user_id' => $user->admin_user_id ?? $user->id ?? null,
            'action' => "Assigned contractor {$contractorId} to project {$projectId}",
            'meta' => json_encode(['project_id'=>$projectId,'contractor_id'=>$contractorId]),
            'created_at'=>now()
        ]);
        return back()->with('success','Contractor assigned.');
    }

    /**
     * List of projects - admin view with filters
     */
    public function listOfProjects(Request $request)
    {
        $query = DB::table('projects')
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('contractors', 'projects.selected_contractor_id', '=', 'contractors.contractor_id')
            ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id')
            ->select(
                'projects.*',
                'property_owners.first_name',
                'property_owners.last_name',
                DB::raw('COALESCE(users.email, "") as owner_email'),
                'contractors.company_name'
            );

        // Apply filters
        if ($request->has('status') && $request->input('status')) {
            $query->where('projects.project_status', $request->input('status'));
        }

        if ($request->has('search') && $request->input('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('projects.project_title', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        $projects = $query->orderBy('projects.project_id', 'desc')->paginate(15);

        return view('admin.projectManagement.listOfProjects', [
            'projects' => $projects
        ]);
    }

    /**
     * Show subscription management
     */
    public function subscriptions(Request $request)
    {
        // subscriptions table does not exist in schema; return empty stub
        $subscriptions = collect();

        return view('admin.projectManagement.subscriptions', [
            'subscriptions' => $subscriptions
        ]);
    }

    /**
     * Show disputes and reports with analytics
     */
    // disputesReports moved to projectManagementController

    /**
     * Show messages/communications
     */
    public function messages(Request $request)
    {
        // messages table has no project_id column; return an empty paginator
        $messages = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, 1, ['path' => request()->url()]);

        return view('admin.projectManagement.messages', [
            'messages' => $messages
        ]);
    }

    // =============================================
    // API METHODS FOR AJAX CALLS
    // =============================================

    /**
     * Get projects as JSON (for AJAX)
     */
    public function getProjectsApi(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $page = $request->input('page', 1);

        $query = DB::table('projects')
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('contractors', 'projects.selected_contractor_id', '=', 'contractors.contractor_id')
            ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id')
            ->select(
                'projects.*',
                'property_owners.first_name',
                'property_owners.last_name',
                DB::raw('COALESCE(users.email, "") as owner_email'),
                'contractors.company_name'
            );

        if ($search) {
            $query->where('projects.project_title', 'like', "%{$search}%");
        }

        if ($status) {
            $query->where('projects.project_status', $status);
        }

        $projects = $query->orderBy('projects.project_id', 'desc')->paginate(15, ['*'], 'page', $page);

        return response()->json($projects);
    }

    /**
     * Get subscriptions as JSON (for AJAX)
     * Note: subscriptions table does not exist in current schema; returning empty stub
     */
    public function getSubscriptionsApi(Request $request)
    {
        return response()->json([
            'data' => [],
            'current_page' => 1,
            'total' => 0,
            'per_page' => 15,
            'last_page' => 1
        ]);
    }

    /**
     * Get messages as JSON (for AJAX)
     * Note: messages table has no project_id column; returning stub data
     */
    public function getMessagesApi(Request $request)
    {
        $page = $request->input('page', 1);

        return response()->json([
            'data' => [],
            'current_page' => $page,
            'total' => 0,
            'per_page' => 20,
            'last_page' => 1
        ]);
    }

    /**
     * Get disputes as JSON (for AJAX)
     */
    public function getDisputesApi(Request $request)
    {
        $page = $request->input('page', 1);

        $disputes = DB::table('disputes')
            ->join('projects', 'disputes.project_id', '=', 'projects.project_id')
            ->select(
                'disputes.*',
                'projects.project_title'
            )
            ->orderBy('disputes.created_at', 'desc')
            ->paginate(20, ['*'], 'page', $page);

        return response()->json($disputes);
    }

    /**
     * Get analytics data (projects, disputes, stats)
     */
    public function getProjectsAnalyticsApi()
    {
        // Projects by status
        $projectsByStatus = DB::table('projects')
            ->select('project_status', DB::raw('count(*) as count'))
            ->groupBy('project_status')
            ->get();

        // Projects by property type
        $projectsByType = DB::table('projects')
            ->select('property_type', DB::raw('count(*) as count'))
            ->groupBy('property_type')
            ->get();

        // Disputes count
        $disputes = DB::table('disputes')->count();

        return response()->json([
            'by_status' => $projectsByStatus,
            'by_type' => $projectsByType,
            'disputes' => $disputes
        ]);
    }
}
