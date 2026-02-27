<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use App\Services\feedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * HomepageController — Dedicated controller for both-role homepage / feed.
 *
 * Replaces the homepage methods previously scattered across:
 *   • owner\projectsController     ::showHomepage(), apiGetContractors(), apiGetContractorTypes(), apiGetApprovedProjects()
 *   • contractor\cprocessController ::showHomepage()
 *   • contractor\cprocessFilterController ::showHomepage()
 *
 * Web routes  → Blade views (session auth)
 * API routes  → JSON responses (bearer-token or session auth)
 */
class homepageController extends Controller
{
    protected feedService $feedService;

    public function __construct(feedService $feedService)
    {
        $this->feedService = $feedService;
    }

    /* =====================================================================
     * WEB — Owner Homepage  (Blade)
     * ===================================================================== */

    /**
     * GET /owner/homepage
     *
     * Show the property-owner homepage with active contractor cards.
     */
    public function ownerHomepage(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/accounts/login');
        }

        $currentRole = session('current_role', $user->user_type ?? null);
        $userType    = $user->user_type ?? null;
        $isOwner     = in_array($userType, ['property_owner', 'both'])
                    && in_array($currentRole, ['owner', 'property_owner']);

        if (!$isOwner) {
            return redirect('/dashboard')->with('error', 'Only property owners can access this page.');
        }

        $excludeUserId = ($userType === 'both') ? $user->user_id : null;

        // Pagination params for contractors list
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;

        // Use the API-style feed to get paginated data
        $result = $this->feedService->ownerFeedApi($excludeUserId, $page, $perPage, []);

        // Normalize contractors collection for Blade
        $contractors = collect($result['data']);
        $pagination = $result['pagination'] ?? ['current_page' => $page, 'per_page' => $perPage, 'total' => $contractors->count(), 'has_more' => false];

        // If AJAX request, return only the rendered contractor cards HTML (for infinite scroll)
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $html = view('owner.partials.contractor_cards_list', ['contractors' => $contractors])->render();
            return response()->json([
                'success'    => true,
                'html'       => $html,
                'pagination' => $pagination,
            ], 200);
        }

        // Also prepare the jsContractors payload (first page) for client-side use
        $jsContractors = $this->feedService->ownerHomepageData($excludeUserId)['jsContractors'] ?? [];

        return view('owner.propertyOwner_Homepage', [
            'contractors'     => $contractors,
            'pagination'      => $pagination,
            'jsContractors'   => $jsContractors,
            'contractorTypes' => $this->feedService->getContractorTypes(),
        ]);
    }

    /* =====================================================================
     * WEB — Contractor Homepage  (Blade)
     * ===================================================================== */

    /**
     * GET /contractor/homepage
     *
     * Show the contractor homepage with open project cards.
     */
    public function contractorHomepage(Request $request)
    {
        $accessCheck = $this->checkContractorAccess($request);
        if ($accessCheck) {
            return $accessCheck;
        }

        try {
            $data = $this->feedService->contractorHomepageData();
        } catch (\Throwable $e) {
            Log::error('HomepageController::contractorHomepage failed: ' . $e->getMessage());
            $data = [
                'projects'      => collect([]),
                'jsProjects'    => [],
                'propertyTypes' => [],
            ];
        }

        return view('contractor.contractor_Homepage', $data);
    }

    /* =====================================================================
     * API — Contractor list  (Mobile — owner feed)
     * ===================================================================== */

    /**
     * GET /api/contractors  ?exclude_user_id=&page=&per_page=&search=&type_id=&province=&city=&min_experience=&max_experience=&picab_category=&min_completed=
     *
     * Paginated active contractors for the mobile owner feed.
     * Supports full-text search and advanced filters.
     */
    public function apiGetContractors(Request $request)
    {
        try {
            $page        = max(1, (int) $request->query('page', 1));
            $perPage     = min(50, max(1, (int) $request->query('per_page', 15)));
            $excludeUser = $request->query('exclude_user_id');

            // Collect filter params (all optional)
            $filters = array_filter([
                'search'          => $request->query('search'),
                'type_id'         => $request->query('type_id'),
                'location'        => $request->query('location'),
                'province'        => $request->query('province'),
                'city'            => $request->query('city'),
                'min_experience'  => $request->query('min_experience'),
                'max_experience'  => $request->query('max_experience'),
                'picab_category'  => $request->query('picab_category'),
                'min_completed'   => $request->query('min_completed'),
            ], fn ($v) => $v !== null && $v !== '');

            $result = $this->feedService->ownerFeedApi(
                $excludeUser ? (int) $excludeUser : null,
                $page,
                $perPage,
                $filters
            );

            return response()->json([
                'success'    => true,
                'message'    => 'Contractors retrieved successfully',
                'data'       => $result['data'],
                'pagination' => $result['pagination'],
                'filters'    => $filters,
            ], 200);
        } catch (\Exception $e) {
            Log::error('apiGetContractors error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving contractors: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* =====================================================================
     * API — Contractor types  (dropdown data)
     * ===================================================================== */

    /**
     * GET /api/contractor-types
     *
     * Full list of contractor types for dropdowns / filter chips.
     */
    public function apiGetContractorTypes(Request $request)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Contractor types retrieved successfully',
                'data'    => $this->feedService->getContractorTypes(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('apiGetContractorTypes error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving contractor types: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* =====================================================================
     * API — Project list  (Mobile — contractor feed)
     * ===================================================================== */

    /**
     * GET /api/contractor/projects  ?user_id=&page=&per_page=&search=&type_id=&property_type=&province=&city=&budget_min=&budget_max=&project_status=
     *
     * Paginated open projects for the mobile contractor feed.
     * Excludes already-bid projects and sorts matching type first.
     * Supports full-text search and advanced filters.
     */
    public function apiGetApprovedProjects(Request $request)
    {
        try {
            $page    = max(1, (int) $request->query('page', 1));
            $perPage = min(50, max(1, (int) $request->query('per_page', 15)));
            $userId  = $this->resolveUserId($request);

            // Collect filter params (all optional)
            $filters = array_filter([
                'search'         => $request->query('search'),
                'type_id'        => $request->query('type_id'),
                'property_type'  => $request->query('property_type'),
                'location'       => $request->query('location'),
                'province'       => $request->query('province'),
                'city'           => $request->query('city'),
                'budget_min'     => $request->query('budget_min'),
                'budget_max'     => $request->query('budget_max'),
                'project_status' => $request->query('project_status'),
                'min_lot_size'   => $request->query('min_lot_size'),
                'max_lot_size'   => $request->query('max_lot_size'),
                'min_floor_area' => $request->query('min_floor_area'),
                'max_floor_area' => $request->query('max_floor_area'),
            ], fn ($v) => $v !== null && $v !== '');

            $result = $this->feedService->contractorFeedApi($userId, $page, $perPage, $filters);

            return response()->json([
                'success'    => true,
                'message'    => 'Projects retrieved successfully',
                'data'       => $result['data'],
                'pagination' => $result['pagination'],
                'filters'    => $filters,
            ], 200);
        } catch (\Exception $e) {
            Log::error('apiGetApprovedProjects error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving projects: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* =====================================================================
     * API — Filter options  (dropdown data for mobile filter sheet)
     * ===================================================================== */

    /**
     * GET /api/search/filter-options
     *
     * Returns all available filter options for the mobile search/filter UI:
     *  • contractor_types   — id + name for type dropdown
     *  • property_types     — ENUM values from projects table
     *  • project_statuses   — available project status values
     *  • picab_categories   — available PICAB classifications
     */
    public function apiGetFilterOptions(Request $request)
    {
        try {
            $contractorTypes = $this->feedService->getContractorTypes();
            $propertyTypes   = (new \App\Models\both\feedClass)->getEnumValues('projects', 'property_type');

            $picabCategories = ['AAAA', 'AAA', 'AA', 'A', 'B', 'C', 'D', 'Trade/E'];

            $projectStatuses = [
                ['value' => 'open', 'label' => 'Open for Bidding'],
                ['value' => 'completed', 'label' => 'Completed Projects'],
                ['value' => 'all', 'label' => 'All Projects'],
            ];

            return response()->json([
                'success' => true,
                'data'    => [
                    'contractor_types'  => $contractorTypes,
                    'property_types'    => $propertyTypes,
                    'project_statuses'  => $projectStatuses,
                    'picab_categories'  => $picabCategories,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('apiGetFilterOptions error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving filter options: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* =====================================================================
     * PRIVATE HELPERS
     * ===================================================================== */

    /**
     * Resolve the current user_id from query param → session → bearer token.
     */
    private function resolveUserId(Request $request): ?int
    {
        $userId = $request->query('user_id');
        if ($userId) {
            return (int) $userId;
        }

        $user = Session::get('user');
        if (!$user) {
            $bearerToken = $request->bearerToken();
            if ($bearerToken) {
                $token = PersonalAccessToken::findToken($bearerToken);
                if ($token) {
                    $user = $token->tokenable;
                }
            }
        }

        return $user ? (int) ($user->user_id ?? $user->id ?? null) : null;
    }

    /**
     * Check that the current session user has contractor access.
     * Returns a redirect / JSON 401/403 response on failure, or null on success.
     */
    private function checkContractorAccess(Request $request)
    {
        if (!Session::has('user')) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Authentication required', 'redirect_url' => '/accounts/login'], 401)
                : redirect('/accounts/login')->with('error', 'Please login first');
        }

        $user = Session::get('user');

        if (!in_array($user->user_type, ['contractor', 'both'])) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Access denied. Only contractors can access this page.', 'redirect_url' => '/dashboard'], 403)
                : redirect('/dashboard')->with('error', 'Access denied. Only contractors can access this page.');
        }

        if ($user->user_type === 'both') {
            $currentRole = Session::get('current_role', 'contractor');
            if ($currentRole !== 'contractor') {
                Session::put('current_role', 'contractor');
                try {
                    DB::table('users')
                        ->where('user_id', $user->user_id)
                        ->update(['preferred_role' => 'contractor']);
                } catch (\Exception $e) {
                    Log::warning('Failed to update preferred_role', [
                        'user_id' => $user->user_id,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        }

        return null;
    }
}
