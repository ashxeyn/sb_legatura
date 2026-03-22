<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use App\Services\SummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;

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
     * Download project summary as PDF (server-side generation via DomPDF).
     */
    public function projectSummaryPdf(Request $request, int $projectId)
    {
        $userId = $request->query('user_id') ?? $request->header('X-User-Id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (!$this->userCanAccessProject($userId, $projectId)) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $result = $this->summaryService->getProjectSummary($projectId);
        if (!($result['success'] ?? false)) {
            return response()->json($result, 404);
        }

        $html = view('reports.project-report', $result)->render();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Project_Report_' . $projectId . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Check whether a user_id has access to the project (either as owner or contractor/staff).
     *
     * Checks:
     *  1. Owner via project_relationships.owner_id → property_owners.user_id
    *  2. Contractor owner via project_relationships.selected_contractor_id
     *  3. Contractor linked through an accepted bid
     *  4. Staff member (contractor_staff) whose contractor has an accepted bid
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

        // 2. Direct contractor owner (via project_relationships.selected_contractor_id)
        $isContractorDirect = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->leftJoin('contractors as c2', 'pr.selected_contractor_id', '=', 'c2.contractor_id')
            ->leftJoin('property_owners as po2', 'c2.owner_id', '=', 'po2.owner_id')
            ->where('p.project_id', $projectId)
            ->where(function ($q) use ($userId) {
                $q->where('po2.user_id', $userId);
            })
            ->exists();

        if ($isContractorDirect) return true;

        // 3. Contractor linked through accepted bid
        $contractorOwnerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
        $contractorId = $contractorOwnerId ? DB::table('contractors')->where('owner_id', $contractorOwnerId)->value('contractor_id') : null;
        if ($contractorId) {
            $hasAcceptedBid = DB::table('bids')
                ->where('project_id', $projectId)
                ->where('contractor_id', $contractorId)
                ->where('bid_status', 'accepted')
                ->exists();
            if ($hasAcceptedBid) return true;
        }

        // 4. Staff member whose contractor has an accepted bid
        $staffContractorId = $contractorOwnerId ? DB::table('contractor_staff')
            ->where('owner_id', $contractorOwnerId)
            ->where('is_active', 1)
            ->whereNull('deletion_reason')
            ->value('contractor_id') : null;

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
