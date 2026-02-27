<?php

namespace App\Services;

use App\Models\both\feedClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * feedService — Business logic for assembling homepage feeds.
 *
 * Keeps controllers thin. Both the web (Blade) and API (mobile) homepage
 * actions funnel through this service so feed rules are in one place.
 */
class feedService
{
    protected feedClass $feed;

    public function __construct(feedClass $feed)
    {
        $this->feed = $feed;
    }

    /* =====================================================================
     * OWNER HOMEPAGE  — shows contractor cards
     * ===================================================================== */

    /**
     * Build the full page payload for the owner web homepage.
     *
     * @param int|null $excludeUserId  If user_type = 'both', exclude own profile
     * @return array{contractors: \Illuminate\Support\Collection, jsContractors: array, contractorTypes: \Illuminate\Support\Collection}
     */
    public function ownerHomepageData(?int $excludeUserId = null): array
    {
        $result = $this->feed->getActiveContractors($excludeUserId, page: 1, perPage: 1000);
        $contractors = collect($result['data']);

        $jsContractors = $contractors->map(function ($c) {
            return [
                'contractor_id'        => $c->contractor_id,
                'company_name'         => $c->company_name,
                'contact_person'       => $c->username ?? '',
                'years_of_experience'  => $c->years_of_experience ?? 0,
                'contractor_type_name' => $c->type_name ?? 'Contractor',
                'city'                 => $c->business_permit_city ?? '',
                'province'             => '',
                'average_rating'       => 4.5,
                'total_reviews'        => 0,
                'completed_projects'   => $c->completed_projects ?? 0,
                'specialization'       => $c->services_offered ?? $c->type_name ?? '',
                'cover_photo'          => $c->cover_photo ?? null,
            ];
        })->toArray();

        return [
            'contractors'     => $contractors,
            'jsContractors'   => $jsContractors,
            'contractorTypes' => $this->feed->getContractorTypes(),
        ];
    }

    /**
     * API: paginated contractor list for mobile owner feed.
     */
    public function ownerFeedApi(?int $excludeUserId, int $page = 1, int $perPage = 15, array $filters = []): array
    {
        return $this->feed->getActiveContractors($excludeUserId, $page, $perPage, $filters);
    }

    /* =====================================================================
     * CONTRACTOR HOMEPAGE  — shows project cards
     * ===================================================================== */

    /**
     * Build the full page payload for the contractor web homepage.
     *
     * @return array{projects: \Illuminate\Support\Collection, jsProjects: array, propertyTypes: array}
     */
    public function contractorHomepageData(): array
    {
        $projects = $this->feed->getAllApprovedProjects();

        $jsProjects = $this->buildJsProjects($projects);

        return [
            'projects'      => $projects,
            'jsProjects'    => $jsProjects,
            'propertyTypes' => $this->feed->getEnumValues('projects', 'property_type'),
        ];
    }

    /**
     * API: paginated project list for mobile contractor feed.
     *
     * Resolves the contractor's type_id so matching projects sort first,
     * and excludes projects the contractor already bid on.
     */
    public function contractorFeedApi(?int $userId, int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $contractorId     = null;
        $contractorTypeId = null;

        if ($userId) {
            $contractor = DB::table('contractors')->where('user_id', $userId)->first();
            if ($contractor) {
                $contractorId     = $contractor->contractor_id;
                $contractorTypeId = $contractor->type_id;
            }
        }

        return $this->feed->getApprovedProjects($contractorId, $contractorTypeId, $page, $perPage, $filters);
    }

    /* =====================================================================
     * SHARED / LOOKUP
     * ===================================================================== */

    /**
     * All contractor types (for dropdowns and filter chips).
     */
    public function getContractorTypes()
    {
        return $this->feed->getContractorTypes();
    }

    /* =====================================================================
     * HELPERS
     * ===================================================================== */

    /**
     * Transform a project collection into a flat JS-friendly array
     * used by the Blade views for client-side filtering.
     */
    private function buildJsProjects($projects): array
    {
        try {
            return $projects->map(function ($p) {
                $firstFilePath = null;
                if (!empty($p->files)) {
                    $first = null;
                    if (is_array($p->files) && count($p->files) > 0) {
                        $first = $p->files[0];
                    } elseif (method_exists($p->files, 'first')) {
                        $first = $p->files->first();
                    }

                    if (!empty($first)) {
                        $firstFilePath = is_string($first)
                            ? $first
                            : (is_array($first) ? ($first['file_path'] ?? null) : ($first->file_path ?? null));
                    }
                }

                // Build files array with full URLs for JS
                $jsFiles = [];
                if (!empty($p->files) && is_iterable($p->files)) {
                    foreach ($p->files as $f) {
                        $fPath = is_string($f) ? $f : (is_array($f) ? ($f['file_path'] ?? '') : ($f->file_path ?? ''));
                        $fType = is_object($f) ? ($f->file_type ?? '') : (is_array($f) ? ($f['file_type'] ?? '') : '');
                        $jsFiles[] = [
                            'file_path' => $fPath,
                            'file_type' => $fType,
                            'url'       => $fPath ? asset('storage/' . ltrim($fPath, '/')) : '',
                        ];
                    }
                }

                return (object) [
                    'project_id'   => $p->project_id,
                    'title'        => $p->project_title,
                    'description'  => $p->project_description,
                    'city'         => $p->project_location,
                    'project_location' => $p->project_location,
                    'deadline'     => $p->bidding_due ?? $p->bidding_deadline ?? null,
                    'bidding_deadline' => $p->bidding_due ?? $p->bidding_deadline ?? null,
                    'project_type' => $p->type_name ?? $p->property_type ?? null,
                    'type_name'    => $p->type_name ?? null,
                    'budget_min'   => $p->budget_range_min ?? null,
                    'budget_max'   => $p->budget_range_max ?? null,
                    'budget_range_min' => $p->budget_range_min ?? null,
                    'budget_range_max' => $p->budget_range_max ?? null,
                    'status'       => $p->project_status ?? 'open',
                    'created_at'   => $p->created_at ?? null,
                    'image'        => $firstFilePath ? asset('storage/' . ltrim($firstFilePath, '/')) : null,
                    'owner_name'   => $p->owner_name ?? null,
                    'owner_profile_pic' => $p->owner_profile_pic ?? null,
                    'lot_size'     => $p->lot_size ?? null,
                    'floor_area'   => $p->floor_area ?? null,
                    'bids_count'   => $p->bids_count ?? 0,
                    'files'        => $jsFiles,
                    'project'      => $p,
                ];
            })->toArray();
        } catch (\Throwable $e) {
            Log::warning('feedService::buildJsProjects failed: ' . $e->getMessage());
            return [];
        }
    }
}
