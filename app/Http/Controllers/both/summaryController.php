<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use App\Services\SummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * summaryController — Read-only project & milestone summary reports.
 *
 * Routes (controller handles auth via user_id):
 *   GET /api/projects/{projectId}/summary
 *   GET /api/projects/{projectId}/milestones/{itemId}/summary
 */
class summaryController extends Controller
{
    public function __construct(protected SummaryService $summaryService) {}

    /**
     * Full project lifecycle summary.
     */
    public function projectSummary(Request $request, int $projectId)
    {
        // Auth: verify user has access to this project
        $userId = $request->query('user_id') ?? $request->header('X-User-Id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (!$this->userCanAccessProject($userId, $projectId)) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $result = $this->summaryService->getProjectSummary($projectId);

        return response()->json($result, $result['success'] ? 200 : 404);
    }

    /**
     * Single milestone item lifecycle summary.
     */
    public function milestoneSummary(Request $request, int $projectId, int $itemId)
    {
        $userId = $request->query('user_id') ?? $request->header('X-User-Id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (!$this->userCanAccessProject($userId, $projectId)) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $result = $this->summaryService->getMilestoneSummary($projectId, $itemId);

        return response()->json($result, $result['success'] ? 200 : 404);
    }

    /**
     * Check whether a user_id has access to the project (either as owner or contractor/staff).
     *
     * Checks:
     *  1. Owner via project_relationships.owner_id → property_owners.user_id
     *  2. Contractor owner via selected_contractor_id (projects or project_relationships)
     *  3. Contractor linked through an accepted bid
     *  4. Staff member (contractor_users) whose contractor has an accepted bid
     */
    private function userCanAccessProject(int $userId, int $projectId): bool
    {
        // 1. Owner check
        $isOwner = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->where('p.project_id', $projectId)
            ->where('po.user_id', $userId)
            ->exists();

        if ($isOwner) return true;

        // 2. Direct contractor owner (via selected_contractor_id)
        $isContractorDirect = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->leftJoin('contractors as c1', 'p.selected_contractor_id', '=', 'c1.contractor_id')
            ->leftJoin('contractors as c2', 'pr.selected_contractor_id', '=', 'c2.contractor_id')
            ->where('p.project_id', $projectId)
            ->where(function ($q) use ($userId) {
                $q->where('c1.user_id', $userId)
                  ->orWhere('c2.user_id', $userId);
            })
            ->exists();

        if ($isContractorDirect) return true;

        // 3. Contractor linked through accepted bid
        $contractorId = DB::table('contractors')->where('user_id', $userId)->value('contractor_id');
        if ($contractorId) {
            $hasAcceptedBid = DB::table('bids')
                ->where('project_id', $projectId)
                ->where('contractor_id', $contractorId)
                ->where('bid_status', 'accepted')
                ->exists();
            if ($hasAcceptedBid) return true;
        }

        // 4. Staff member whose contractor has an accepted bid
        $staffContractorId = DB::table('contractor_users')
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->value('contractor_id');

        if ($staffContractorId) {
            $staffHasBid = DB::table('bids')
                ->where('project_id', $projectId)
                ->where('contractor_id', $staffContractorId)
                ->where('bid_status', 'accepted')
                ->exists();
            if ($staffHasBid) return true;
        }

        return false;
    }
}
