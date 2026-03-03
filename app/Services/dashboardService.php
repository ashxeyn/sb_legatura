<?php

namespace App\Services;

use App\Models\both\dashboardClass;
use App\Models\both\feedClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * dashboardService — Business logic for assembling dashboard data.
 *
 * Keeps controllers thin. Both the web (Blade) and API (mobile) dashboard
 * actions funnel through this service so dashboard rules are in one place.
 */
class dashboardService
{
    protected dashboardClass $dashboard;

    public function __construct(dashboardClass $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    /* =====================================================================
     * OWNER DASHBOARD
     * ===================================================================== */

    /**
     * Build the full page payload for the owner-specific web dashboard.
     *
     * Used by GET /owner/dashboard → Blade view owner.propertyOwner_Dashboard
     * (currently renders with no PHP variables — kept for consistency).
     */
    public function ownerDashboardData(int $userId): array
    {
        $owner = $this->dashboard->getOwnerByUserId($userId);
        if (!$owner) {
            return [
                'projects' => collect([]),
                'stats'    => ['total' => 0, 'pending' => 0, 'active' => 0, 'inProgress' => 0],
            ];
        }

        $projects = $this->dashboard->getOwnerProjects($owner->owner_id);
        $stats    = $this->dashboard->computeOwnerStats($projects);

        return [
            'projects' => $projects,
            'stats'    => $stats,
        ];
    }

    /**
     * Owner dashboard stats for API consumers (mobile).
     */
    public function ownerStatsApi(int $userId): array
    {
        $data = $this->ownerDashboardData($userId);

        return [
            'success' => true,
            'stats'   => $data['stats'],
        ];
    }

    /* =====================================================================
     * CONTRACTOR DASHBOARD
     * ===================================================================== */

    /**
     * Build the full page payload for the contractor web dashboard.
     *
     * Used by GET /contractor/dashboard → Blade view contractor.contractor_Dashboard
     * Expects: projects (collection), stats (array), userName (string|null)
     */
    public function contractorDashboardData(int $userId): array
    {
        $contractor = $this->dashboard->getContractorByUserId($userId);
        if (!$contractor) {
            return [
                'projects' => collect([]),
                'stats'    => ['total' => 0, 'pending' => 0, 'active' => 0, 'inProgress' => 0, 'completed' => 0],
            ];
        }

        $projects = $this->dashboard->getContractorProjects($contractor->contractor_id);
        $stats    = $this->dashboard->computeContractorStats($projects);

        return [
            'projects' => $projects,
            'stats'    => $stats,
        ];
    }

    /**
     * Contractor dashboard stats for API consumers (mobile).
     */
    public function contractorStatsApi(int $userId): array
    {
        $data = $this->contractorDashboardData($userId);

        return [
            'success' => true,
            'stats'   => $data['stats'],
        ];
    }

    /* =====================================================================
     * UNIFIED DASHBOARD
     * ===================================================================== */

    /**
     * Build the payload for the unified /dashboard route.
     *
     * Resolves role from the given user object and returns the feed items
     * and metadata the both.dashboard Blade view expects.
     *
     * @param object $user  Session user object
     * @param string|null $currentRole  Resolved current role
     * @param string|null $userType     user_type column value
     * @return array  Keys: feedItems, isOwner, contractorTypes, currentRole, userType, feedType, contractorProjectsForMilestone
     */
    public function unifiedDashboardData(object $user, ?string $currentRole, ?string $userType): array
    {
        $isOwner = in_array($userType, ['property_owner', 'both'])
                && in_array($currentRole, ['owner', 'property_owner']);

        $FeedService = app(\App\Services\FeedService::class);
        $feedItems = [];
        $feedType = 'projects';
        $contractorProjectsForMilestone = [];

        if ($isOwner) {
            $ownerId = null;
            $owner = $this->dashboard->getOwnerByUserId($user->user_id);
            $ownerId = $owner ? $owner->owner_id : null;

            if ($ownerId) {
                $excludeUserId = ($userType === 'both') ? $user->user_id : null;
                $result = $FeedService->ownerFeedApi($excludeUserId, page: 1, perPage: 1000);
                $feedItems = collect($result['data']);
                $feedType = 'contractors';
            }
        } else {
            // Contractor view: Show all approved projects
            $feedModel = app(\App\Models\both\feedClass::class);
            $feedItems = $feedModel->getAllApprovedProjects();
            $feedType = 'projects';

            // Get contractor projects for milestone setup
            if (in_array($userType, ['contractor', 'both'])) {
                $contractor = DB::table('contractors')->where('user_id', $user->user_id)->first();
                if ($contractor) {
                    $contractorClass = new \App\Models\contractor\contractorClass();
                    $contractorProjectsForMilestone = $contractorClass->getContractorProjects($contractor->contractor_id);
                }
            }
        }

        $contractorTypes = $FeedService->getContractorTypes();

        return [
            'feedItems'                      => $feedItems,
            'isOwner'                        => $isOwner,
            'contractorTypes'                => $contractorTypes,
            'currentRole'                    => $currentRole,
            'userType'                       => $userType,
            'feedType'                       => $feedType,
            'contractorProjectsForMilestone' => $contractorProjectsForMilestone,
        ];
    }
}
