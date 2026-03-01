<?php

namespace App\Models\both;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * feedClass — Query-builder helper for homepage / feed data.
 *
 * Centralises every DB query that the owner and contractor homepages need
 * so that controllers stay thin and feed rules live in one place.
 */
class feedClass
{
    /* =====================================================================
     * CONTRACTOR FEED  (shown to property-owners)
     * ===================================================================== */

    /**
     * Paginated list of active, approved contractors.
     *
     * Rules
     * ─────
     *  • contractor_users.is_active = 1
     *  • contractors.verification_status = 'approved'
     *  • Optionally exclude a user_id (so "both" users don't see themselves)
     *  • Ordered newest-first
     *
     * Filters (all optional):
     *  • search          – full-text keyword match on company_name, services_offered, business_address, type_name
     *  • type_id         – contractor type ID
     *  • location        – partial match on business_address
     *  • province        – partial match on business_address (province name)
     *  • city            – partial match on business_address (city/municipality name)
     *  • min_experience  – minimum years of experience
     *  • max_experience  – maximum years of experience
     *  • picab_category  – PICAB classification (AAAA, AAA, etc.)
     *  • min_completed   – minimum completed projects count
     */
    public function getActiveContractors(?int $excludeUserId = null, int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $query = DB::table('contractors as c')
            ->join('users as u', 'c.user_id', '=', 'u.user_id')
            ->join('contractor_users as cu', function ($join) {
                $join->on('c.contractor_id', '=', 'cu.contractor_id')
                    ->on('c.user_id', '=', 'cu.user_id');
            })
            ->join('contractor_types as ct', 'c.type_id', '=', 'ct.type_id')
            ->where('cu.is_active', 1)
            ->where('c.verification_status', 'approved')
            ->select(
                'c.contractor_id',
                'c.company_name',
                'c.years_of_experience',
                'c.services_offered',
                'c.business_address',
                'c.company_website',
                'c.company_social_media',
                'c.company_description',
                'c.picab_number',
                'c.picab_category',
                'c.business_permit_number',
                'c.completed_projects',
                'c.business_permit_city',
                'c.created_at',
                'ct.type_name',
                'c.type_id',
                'u.user_id',
                'u.username',
                'u.profile_pic',
                'u.cover_photo',
                'c.company_logo',
                'c.company_banner'
            );

        if ($excludeUserId) {
            $query->where('c.user_id', '!=', $excludeUserId);
        }

        // ── Search keyword ──────────────────────────────────────────────
        if (!empty($filters['search'])) {
            $keyword = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('c.company_name', 'LIKE', $keyword)
                  ->orWhere('c.services_offered', 'LIKE', $keyword)
                  ->orWhere('c.business_address', 'LIKE', $keyword)
                  ->orWhere('ct.type_name', 'LIKE', $keyword)
                  ->orWhere('c.company_description', 'LIKE', $keyword);
            });
        }

        // ── Contractor type ─────────────────────────────────────────────
        if (!empty($filters['type_id'])) {
            $query->where('c.type_id', $filters['type_id']);
        }

        // ── Location filters ────────────────────────────────────────────
        if (!empty($filters['location'])) {
            $query->where('c.business_address', 'LIKE', '%' . $filters['location'] . '%');
        }
        if (!empty($filters['province'])) {
            $query->where('c.business_address', 'LIKE', '%' . $filters['province'] . '%');
        }
        if (!empty($filters['city'])) {
            $query->where('c.business_address', 'LIKE', '%' . $filters['city'] . '%');
        }

        // ── Experience range ────────────────────────────────────────────
        if (isset($filters['min_experience']) && $filters['min_experience'] !== '') {
            $query->where('c.years_of_experience', '>=', (int) $filters['min_experience']);
        }
        if (isset($filters['max_experience']) && $filters['max_experience'] !== '') {
            $query->where('c.years_of_experience', '<=', (int) $filters['max_experience']);
        }

        // ── PICAB category ──────────────────────────────────────────────
        if (!empty($filters['picab_category'])) {
            $query->where('c.picab_category', $filters['picab_category']);
        }

        // ── Minimum completed projects ──────────────────────────────────
        if (isset($filters['min_completed']) && $filters['min_completed'] !== '') {
            $query->where('c.completed_projects', '>=', (int) $filters['min_completed']);
        }

        $query->orderBy('c.created_at', 'desc');

        $totalCount = $query->count();
        $offset     = ($page - 1) * $perPage;
        $items      = $query->skip($offset)->take($perPage)->get();

        return $this->paginationEnvelope($items, $totalCount, $page, $perPage);
    }

    /**
     * All contractor types (for dropdown / filter chips).
     */
    public function getContractorTypes()
    {
        return DB::table('contractor_types')
            ->orderBy('type_name')
            ->get();
    }

    /* =====================================================================
     * PROJECT FEED  (shown to contractors)
     * ===================================================================== */

    /**
     * Mark any project whose bidding deadline has passed as "due".
     *
     * This side-effect keeps the feed accurate without a scheduled job.
     * Called once per request before querying the feed.
     */
    public function expirePastDeadlines(): void
    {
        try {
            DB::table('project_relationships')
                ->where('project_post_status', 'approved')
                ->whereNotNull('bidding_due')
                ->where('bidding_due', '<=', date('Y-m-d'))
                ->update([
                    'project_post_status' => 'due',
                    'updated_at'          => now(),
                ]);
        } catch (\Throwable $e) {
            Log::warning('feedClass::expirePastDeadlines failed: ' . $e->getMessage());
        }
    }

    /**
     * Paginated list of open, approved projects for the contractor feed.
     *
     * Rules
     * ─────
     *  • project_relationships.project_post_status = 'approved'
     *  • projects.project_status = 'open'
     *  • bidding_due >= today  (or NULL = no deadline)
     *  • Exclude projects the contractor already bid on (non-cancelled)
     *  • Sort: matching contractor type first, then newest
     *  • Attach bids_count and files[] to every project row
     *
     * Filters (all optional):
     *  • search          – keyword match on title, description, location, type_name, owner_name
     *  • type_id         – contractor/project type ID
     *  • property_type   – Residential, Commercial, Industrial, Agricultural
     *  • location        – partial match on project_location
     *  • province        – partial match on project_location (province name)
     *  • city            – partial match on project_location (city/municipality name)
     *  • budget_min      – minimum budget_range_min
     *  • budget_max      – maximum budget_range_max
     *  • project_status  – 'open' (for bidding) or 'completed' (portfolio view)
     *  • min_lot_size    – minimum lot size (sqm)
     *  • max_lot_size    – maximum lot size (sqm)
     *  • min_floor_area  – minimum floor area (sqm)
     *  • max_floor_area  – maximum floor area (sqm)
     */
    public function getApprovedProjects(?int $contractorId = null, ?int $contractorTypeId = null, int $page = 1, int $perPage = 15, array $filters = []): array
    {
        // Expire stale deadlines first
        $this->expirePastDeadlines();

        // Determine if we're searching completed/all projects or just open ones
        $projectStatusFilter = $filters['project_status'] ?? 'open';

        $query = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->join('contractor_types as ct', 'p.type_id', '=', 'ct.type_id')
            ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->join('users as u', 'po.user_id', '=', 'u.user_id');

        // Status-based filtering
        if ($projectStatusFilter === 'completed') {
            // Show completed projects (portfolio view)
            $query->where('p.project_status', 'completed');
        } elseif ($projectStatusFilter === 'all') {
            // Show all non-deleted projects
            $query->where('pr.project_post_status', 'approved')
                  ->whereNotIn('p.project_status', ['deleted', 'deleted_post']);
        } else {
            // Default: open projects for bidding
            $query->where('pr.project_post_status', 'approved')
                  ->where('p.project_status', 'open')
                  ->where(function ($q) {
                      $q->whereNull('pr.bidding_due')
                        ->orWhere('pr.bidding_due', '>=', now());
                  });
        }

        $query->select(
                'p.project_id',
                'p.project_title',
                'p.project_description',
                'p.project_location',
                'p.budget_range_min',
                'p.budget_range_max',
                'p.lot_size',
                'p.floor_area',
                'p.property_type',
                'p.type_id',
                'ct.type_name',
                'p.project_status',
                'pr.project_post_status',
                'pr.bidding_due as bidding_deadline',
                DB::raw('DATE(pr.created_at) as created_at'),
                'pr.owner_id as owner_id',
                DB::raw("CONCAT(po.first_name, ' ', COALESCE(po.middle_name, ''), ' ', po.last_name) as owner_name"),
                'u.profile_pic as owner_profile_pic',
                'u.user_id as owner_user_id'
            );

        // Exclude projects the contractor already bid on
        if ($contractorId) {
            $bidProjectIds = DB::table('bids')
                ->where('contractor_id', $contractorId)
                ->whereNotIn('bid_status', ['cancelled'])
                ->pluck('project_id');

            if ($bidProjectIds->isNotEmpty()) {
                $query->whereNotIn('p.project_id', $bidProjectIds);
            }
        }

        // ── Search keyword ──────────────────────────────────────────────
        if (!empty($filters['search'])) {
            $keyword = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('p.project_title', 'LIKE', $keyword)
                  ->orWhere('p.project_description', 'LIKE', $keyword)
                  ->orWhere('p.project_location', 'LIKE', $keyword)
                  ->orWhere('ct.type_name', 'LIKE', $keyword)
                  ->orWhere('p.property_type', 'LIKE', $keyword)
                  ->orWhereRaw("CONCAT(po.first_name, ' ', COALESCE(po.middle_name, ''), ' ', po.last_name) LIKE ?", [$keyword]);
            });
        }

        // ── Contractor/project type ─────────────────────────────────────
        if (!empty($filters['type_id'])) {
            $query->where('p.type_id', $filters['type_id']);
        }

        // ── Property type ───────────────────────────────────────────────
        if (!empty($filters['property_type'])) {
            $query->where('p.property_type', $filters['property_type']);
        }

        // ── Location filters ────────────────────────────────────────────
        if (!empty($filters['location'])) {
            $query->where('p.project_location', 'LIKE', '%' . $filters['location'] . '%');
        }
        if (!empty($filters['province'])) {
            $query->where('p.project_location', 'LIKE', '%' . $filters['province'] . '%');
        }
        if (!empty($filters['city'])) {
            $query->where('p.project_location', 'LIKE', '%' . $filters['city'] . '%');
        }

        // ── Budget range ────────────────────────────────────────────────
        if (isset($filters['budget_min']) && $filters['budget_min'] !== '') {
            $query->where('p.budget_range_min', '>=', (float) $filters['budget_min']);
        }
        if (isset($filters['budget_max']) && $filters['budget_max'] !== '') {
            $query->where('p.budget_range_max', '<=', (float) $filters['budget_max']);
        }

        // ── Lot size range ──────────────────────────────────────────────
        if (isset($filters['min_lot_size']) && $filters['min_lot_size'] !== '') {
            $query->where('p.lot_size', '>=', (float) $filters['min_lot_size']);
        }
        if (isset($filters['max_lot_size']) && $filters['max_lot_size'] !== '') {
            $query->where('p.lot_size', '<=', (float) $filters['max_lot_size']);
        }

        // ── Floor area range ────────────────────────────────────────────
        if (isset($filters['min_floor_area']) && $filters['min_floor_area'] !== '') {
            $query->where('p.floor_area', '>=', (float) $filters['min_floor_area']);
        }
        if (isset($filters['max_floor_area']) && $filters['max_floor_area'] !== '') {
            $query->where('p.floor_area', '<=', (float) $filters['max_floor_area']);
        }

        // Sort: matching type first, then newest
        if ($contractorTypeId) {
            $query->orderByRaw('CASE WHEN p.type_id = ? THEN 0 ELSE 1 END ASC', [$contractorTypeId])
                  ->orderBy('pr.created_at', 'desc');
        } else {
            $query->orderBy('pr.created_at', 'desc');
        }

        $totalCount = $query->count();
        $offset     = ($page - 1) * $perPage;
        $projects   = $query->skip($offset)->take($perPage)->get();

        // Attach bids_count + files to each project
        foreach ($projects as $project) {
            $project->bids_count = DB::table('bids')
                ->where('project_id', $project->project_id)
                ->whereNotIn('bid_status', ['cancelled'])
                ->count();

            $project->files = DB::table('project_files')
                ->where('project_id', $project->project_id)
                ->orderBy('file_id', 'asc')
                ->select('file_id', 'file_type', 'file_path')
                ->get()
                ->map(fn ($f) => [
                    'file_id'   => $f->file_id,
                    'file_type' => $f->file_type,
                    'file_path' => $f->file_path,
                ])
                ->values()
                ->toArray();
        }

        return $this->paginationEnvelope($projects, $totalCount, $page, $perPage);
    }

    /**
     * Non-paginated version of getApprovedProjects (for the Blade web view).
     * Returns just the collection so the controller can pass it to the view.
     */
    public function getAllApprovedProjects(): \Illuminate\Support\Collection
    {
        $this->expirePastDeadlines();

        $projects = DB::table('projects')
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->join('contractor_types', 'projects.type_id', '=', 'contractor_types.type_id')
            ->join('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->where('project_relationships.project_post_status', 'approved')
            ->where('projects.project_status', 'open')
            ->where(function ($q) {
                $q->whereNull('project_relationships.bidding_due')
                  ->orWhere('project_relationships.bidding_due', '>=', date('Y-m-d'));
            })
            ->select(
                'projects.project_id',
                'projects.project_title',
                'projects.project_description',
                'projects.project_location',
                'projects.budget_range_min',
                'projects.budget_range_max',
                'projects.lot_size',
                'projects.floor_area',
                'projects.property_type',
                'projects.type_id',
                'contractor_types.type_name',
                'projects.project_status',
                'project_relationships.project_post_status',
                'project_relationships.bidding_due as bidding_deadline',
                'project_relationships.created_at',
                DB::raw("CONCAT(property_owners.first_name, ' ', COALESCE(property_owners.middle_name, ''), ' ', property_owners.last_name) as owner_name"),
                'users.profile_pic as owner_profile_pic',
                'users.user_id as owner_user_id'
            )
            ->orderBy('project_relationships.created_at', 'desc')
            ->get();

        // Attach bids_count + files to each project
        foreach ($projects as $project) {
            $project->bids_count = DB::table('bids')
                ->where('project_id', $project->project_id)
                ->whereNotIn('bid_status', ['cancelled'])
                ->count();

            $project->files = DB::table('project_files')
                ->where('project_id', $project->project_id)
                ->get();
        }

        return $projects;
    }

    /**
     * Get project files for a single project.
     */
    public function getProjectFiles(int $projectId)
    {
        return DB::table('project_files')
            ->where('project_id', $projectId)
            ->get();
    }

    /**
     * Read ENUM column values from a table (e.g. property_type).
     * Used for filter chip options in the contractor feed.
     */
    public function getEnumValues(string $table, string $column): array
    {
        try {
            $columnInfo = DB::select("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$column]);
            if (empty($columnInfo)) {
                return [];
            }

            $type = $columnInfo[0]->Type;
            preg_match('/^enum\((.*)\)$/', $type, $matches);

            if (!isset($matches[1])) {
                return [];
            }

            $values = explode(',', $matches[1]);
            return array_map(fn ($v) => trim($v, "'"), $values);
        } catch (\Exception $e) {
            Log::error("feedClass::getEnumValues({$table}.{$column}): {$e->getMessage()}");
            return [];
        }
    }

    /* =====================================================================
     * HELPERS
     * ===================================================================== */

    /**
     * Wrap a result set in a standard pagination envelope.
     */
    private function paginationEnvelope($items, int $totalCount, int $page, int $perPage): array
    {
        $totalPages = (int) ceil($totalCount / $perPage);

        return [
            'data'       => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $totalCount,
                'total_pages'  => $totalPages,
                'has_more'     => $page < $totalPages,
            ],
        ];
    }
}
