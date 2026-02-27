<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use App\Services\summaryService;
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
    public function __construct(protected summaryService $summaryService) {}

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
     * Check whether a user_id has access to the project (either as owner or contractor).
     */
    private function userCanAccessProject(int $userId, int $projectId): bool
    {
        return DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->leftJoin('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->leftJoin('contractors as c', 'pr.selected_contractor_id', '=', 'c.contractor_id')
            ->where('p.project_id', $projectId)
            ->where(function ($q) use ($userId) {
                $q->where('po.user_id', $userId)
                  ->orWhere('c.user_id', $userId);
            })
            ->exists();
    }
}
