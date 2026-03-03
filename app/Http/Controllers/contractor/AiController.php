<?php

namespace App\Http\Controllers\contractor;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

/**
 * AiController — Handles AI Analytics for contractors.
 *
 * Provides:
 * - AI analytics dashboard (view)
 * - Project analysis endpoint (scoped to contractor's projects)
 *
 * Uses AiService for business logic and data access.
 */
class AiController extends Controller
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Display the AI Analytics page for the logged-in contractor.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function showAnalytics(Request $request)
    {
        // Check contractor access
        $accessCheck = $this->checkContractorAccess($request);
        if ($accessCheck !== null) {
            return $accessCheck;
        }

        // Get contractor info
        $user = Session::get('user');
        $contractor = $this->aiService->getContractorByUserId($user->user_id);

        if (!$contractor) {
            return redirect('/contractor/homepage')
                ->with('error', 'Contractor profile not found.');
        }

        // Get AI system status
        $aiUsage = $this->aiService->getSystemStatus();

        // Get contractor's projects for the analysis dropdown
        $projects = $this->aiService->getContractorProjects($contractor->contractor_id);

        // Get prediction history scoped to contractor's projects
        $predictionLogs = $this->aiService->getContractorPredictionLogs(
            $contractor->contractor_id,
            10
        );

        // Get contractor-specific AI stats
        $stats = $this->aiService->getContractorAiStats($contractor->contractor_id);

        return view('contractor.aiAnalytics', [
            'aiUsage'        => $aiUsage,
            'projects'       => $projects,
            'predictionLogs' => $predictionLogs,
            'stats'          => $stats,
            'contractor'     => $contractor,
        ]);
    }

    /**
     * Run AI prediction for a specific project.
     *
     * Only allows analysis of projects owned by the logged-in contractor.
     *
     * @param Request $request
     * @param int $projectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeProject(Request $request, int $projectId)
    {
        // Check contractor access
        $accessCheck = $this->checkContractorAccess($request);
        if ($accessCheck !== null) {
            // For JSON requests, the check already returns JSON
            if ($accessCheck instanceof \Illuminate\Http\JsonResponse) {
                return $accessCheck;
            }
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        // Get contractor info
        $user = Session::get('user');
        $contractor = $this->aiService->getContractorByUserId($user->user_id);

        if (!$contractor) {
            return response()->json([
                'success' => false,
                'message' => 'Contractor profile not found.',
            ], 403);
        }

        // Security: Verify contractor owns this project
        if (!$this->aiService->contractorOwnsProject($contractor->contractor_id, $projectId)) {
            Log::warning('Unauthorized AI analysis attempt', [
                'contractor_id' => $contractor->contractor_id,
                'project_id'    => $projectId,
                'user_id'       => $user->user_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to analyze this project.',
            ], 403);
        }

        // Run the prediction via AiService
        $result = $this->aiService->runPrediction($projectId);

        $statusCode = $result['success'] ? 200 : 500;
        return response()->json($result, $statusCode);
    }

    /**
     * Get contractor AI statistics (API endpoint).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        // Check contractor access
        $accessCheck = $this->checkContractorAccess($request);
        if ($accessCheck !== null) {
            if ($accessCheck instanceof \Illuminate\Http\JsonResponse) {
                return $accessCheck;
            }
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $user = Session::get('user');
        $contractor = $this->aiService->getContractorByUserId($user->user_id);

        if (!$contractor) {
            return response()->json([
                'success' => false,
                'message' => 'Contractor profile not found.',
            ], 403);
        }

        $stats = $this->aiService->getContractorAiStats($contractor->contractor_id);

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }

    /* =====================================================================
     * MOBILE API ENDPOINTS (token / X-User-Id auth)
     * ===================================================================== */

    /**
     * Resolve user from Sanctum token OR X-User-Id header (mobile pattern).
     *
     * @param Request $request
     * @return object|null  user row from DB
     */
    protected function resolveApiUser(Request $request)
    {
        // Try Sanctum token first
        if ($request->user()) {
            return $request->user();
        }

        // Fallback: X-User-Id header (used by mobile api_request)
        $userId = $request->header('X-User-Id');
        if ($userId) {
            return \Illuminate\Support\Facades\DB::table('users')
                ->where('user_id', $userId)
                ->first();
        }

        // Fallback: session
        if (Session::has('user')) {
            return Session::get('user');
        }

        return null;
    }

    /**
     * API: Get AI analytics dashboard data (stats, projects, history, subscription).
     *
     * GET /api/contractor/ai-analytics
     */
    public function apiGetAnalytics(Request $request)
    {
        $user = $this->resolveApiUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $userType = $user->user_type ?? null;
        if (!in_array($userType, ['contractor', 'both', 'staff'])) {
            return response()->json(['success' => false, 'message' => 'Contractor access required'], 403);
        }

        $contractor = $this->aiService->getContractorByUserId($user->user_id);
        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Contractor profile not found'], 403);
        }

        // Check Gold tier subscription
        $subscription = \App\Models\subs\platformPaymentClass::getSubscriptionForUser($user->user_id);
        $isGold = $subscription && strtolower($subscription['plan_key'] ?? '') === 'gold';

        if (!$isGold) {
            return response()->json([
                'success' => true,
                'is_gold' => false,
                'subscription' => $subscription,
                'message' => 'Upgrade to Gold tier to unlock AI Analytics',
            ]);
        }

        // Get data
        $aiUsage = $this->aiService->getSystemStatus();
        $projects = $this->aiService->getContractorProjects($contractor->contractor_id);
        $predictionLogs = $this->aiService->getContractorPredictionLogs($contractor->contractor_id, 20);
        $stats = $this->aiService->getContractorAiStats($contractor->contractor_id);

        return response()->json([
            'success'        => true,
            'is_gold'        => true,
            'ai_status'      => $aiUsage['status'] ?? 'Offline',
            'ai_features'    => $aiUsage['features'] ?? [],
            'projects'       => $projects,
            'prediction_logs' => $predictionLogs->items(),
            'stats'          => $stats,
        ]);
    }

    /**
     * API: Run AI prediction for a project (mobile).
     *
     * POST /api/contractor/ai-analytics/analyze/{id}
     */
    public function apiAnalyzeProject(Request $request, int $projectId)
    {
        $user = $this->resolveApiUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $contractor = $this->aiService->getContractorByUserId($user->user_id);
        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Contractor profile not found'], 403);
        }

        // Check Gold tier
        $subscription = \App\Models\subs\platformPaymentClass::getSubscriptionForUser($user->user_id);
        $isGold = $subscription && strtolower($subscription['plan_key'] ?? '') === 'gold';
        if (!$isGold) {
            return response()->json(['success' => false, 'message' => 'Gold tier subscription required'], 403);
        }

        // Verify ownership
        if (!$this->aiService->contractorOwnsProject($contractor->contractor_id, $projectId)) {
            Log::warning('Unauthorized API AI analysis attempt', [
                'contractor_id' => $contractor->contractor_id,
                'project_id'    => $projectId,
                'user_id'       => $user->user_id,
            ]);
            return response()->json(['success' => false, 'message' => 'You do not have permission to analyze this project.'], 403);
        }

        $result = $this->aiService->runPrediction($projectId);
        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * API: Get contractor AI stats (mobile).
     *
     * GET /api/contractor/ai-analytics/stats
     */
    public function apiGetStats(Request $request)
    {
        $user = $this->resolveApiUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $contractor = $this->aiService->getContractorByUserId($user->user_id);
        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Contractor profile not found'], 403);
        }

        $stats = $this->aiService->getContractorAiStats($contractor->contractor_id);

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }

    /**
     * Check if the user has contractor access.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|null
     */
    protected function checkContractorAccess(Request $request)
    {
        if (!Session::has('user')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success'      => false,
                    'message'      => 'Authentication required',
                    'redirect_url' => '/accounts/login',
                ], 401);
            }
            return redirect('/accounts/login')->with('error', 'Please login first');
        }

        $user = Session::get('user');

        // Check if user has contractor role
        if (!in_array($user->user_type, ['contractor', 'both'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success'      => false,
                    'message'      => 'Access denied. Only contractors can access this page.',
                    'redirect_url' => '/dashboard',
                ], 403);
            }
            return redirect('/dashboard')
                ->with('error', 'Access denied. Only contractors can access this page.');
        }

        return null; // Access granted
    }
}
