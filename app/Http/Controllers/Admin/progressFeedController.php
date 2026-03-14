<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class progressFeedController extends Controller
{
    public function index()
    {
        return view('admin.progressFeed');
    }

    public function fetch(Request $request)
    {
        $perPage  = max(1, min(50, (int) $request->input('per_page', 15)));
        $status   = $request->input('status', '');
        $search   = trim($request->input('search', ''));
        $dateFrom = $request->input('date_from', '');
        $dateTo   = $request->input('date_to', '');
        $company  = trim($request->input('company', ''));

        // Resolve which rejection-reason column exists in this deployment
        $reasonCol = Schema::hasColumn('progress', 'delete_reason')
            ? 'p.delete_reason'
            : (Schema::hasColumn('progress', 'rejection_reason')
                ? 'p.rejection_reason as delete_reason'
                : null);

        $selectCols = [
            'p.progress_id',
            'p.purpose',
            'p.progress_status',
            'p.submitted_at',
            'mi.item_id',
            'mi.milestone_item_title as item_title',
            'mi.sequence_order',
            'm.milestone_id',
            DB::raw("COALESCE(m.milestone_name, 'Milestone') as milestone_name"),
            DB::raw('COALESCE(proj.project_id, m.project_id) as project_id'),
            DB::raw("COALESCE(proj.project_title, CONCAT('Project #', m.project_id)) as project_title"),
            DB::raw("COALESCE(cu.user_id, mu.user_id, ou.user_id, su.user_id, p.submitted_by_owner_id) as contractor_user_id"),
            DB::raw("COALESCE(
                NULLIF(TRIM(cu.username), ''),
                NULLIF(TRIM(mu.username), ''),
                NULLIF(TRIM(ou.username), ''),
                NULLIF(TRIM(su.username), ''),
                CONCAT('contractor_', COALESCE(m.contractor_id, p.progress_id))
            ) as contractor_username"),
            DB::raw("COALESCE(c.company_logo, cpo.profile_pic, mpo.profile_pic, opo.profile_pic, spo.profile_pic) as contractor_pic"),
            DB::raw("COALESCE(
                NULLIF(TRIM(c.company_name), ''),
                NULLIF(TRIM(CONCAT(COALESCE(cu.first_name, ''), ' ', COALESCE(cu.last_name, ''))), ''),
                NULLIF(TRIM(cu.username), ''),
                NULLIF(TRIM(CONCAT(COALESCE(mu.first_name, ''), ' ', COALESCE(mu.last_name, ''))), ''),
                NULLIF(TRIM(mu.username), ''),
                NULLIF(TRIM(CONCAT(COALESCE(ou.first_name, ''), ' ', COALESCE(ou.last_name, ''))), ''),
                NULLIF(TRIM(ou.username), ''),
                NULLIF(TRIM(CONCAT(COALESCE(su.first_name, ''), ' ', COALESCE(su.last_name, ''))), ''),
                NULLIF(TRIM(su.username), ''),
                CONCAT('Contractor #', COALESCE(m.contractor_id, p.progress_id))
            ) as contractor_name"),
        ];

        if ($reasonCol) {
            $selectCols[] = DB::raw($reasonCol);
        }

        // Join chain:
        // 1. progress → milestone_items → milestones → projects
        // 2. projects → project_relationships (via relationship_id)
        // 3. Resolve contractor via milestones.contractor_id (schema source of truth)
        // 4. Contractor's owner → property_owners → users (contractor user)
        // 5. Fallback: project_relationships.owner_id → property_owners → users (project owner)
        $query = DB::table('progress as p')
            ->join('milestone_items as mi', 'p.milestone_item_id', '=', 'mi.item_id')
            ->join('milestones as m',        'mi.milestone_id', '=', 'm.milestone_id')
            ->leftJoin('projects as proj',   'm.project_id', '=', 'proj.project_id')
            ->leftJoin('project_relationships as pr', function ($join) {
                $join->on('proj.relationship_id', '=', 'pr.rel_id')
                    ->orOn('m.project_id', '=', 'pr.rel_id');
            })
            ->leftJoin('contractors as c', 'm.contractor_id', '=', 'c.contractor_id')
            ->leftJoin('property_owners as cpo', 'c.owner_id', '=', 'cpo.owner_id')
            ->leftJoin('users as cu', 'cpo.user_id', '=', 'cu.user_id')
            ->leftJoin('users as mu', 'm.contractor_id', '=', 'mu.user_id')
            ->leftJoin('property_owners as mpo', 'mu.user_id', '=', 'mpo.user_id')
            ->leftJoin('property_owners as opo', 'pr.owner_id', '=', 'opo.owner_id')
            ->leftJoin('users as ou', 'opo.user_id', '=', 'ou.user_id')
            ->leftJoin('users as su', 'p.submitted_by_owner_id', '=', 'su.user_id')
            ->leftJoin('property_owners as spo', 'su.user_id', '=', 'spo.user_id');

        $query->select($selectCols)
            ->orderBy('p.submitted_at', 'desc');

        if ($status && $status !== 'all') {
            $query->where('p.progress_status', $status);
        } else {
            $query->where('p.progress_status', '!=', 'deleted');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('proj.project_title',        'like', "%{$search}%")
                  ->orWhere('mi.milestone_item_title', 'like', "%{$search}%")
                  ->orWhere('p.purpose',               'like', "%{$search}%")
                  ->orWhere('cu.username',     'like', "%{$search}%")
                  ->orWhere('ou.username',     'like', "%{$search}%")
                  ->orWhere('c.company_name', 'like', "%{$search}%");
            });
        }

        if (!empty($dateFrom)) {
            $query->whereDate('p.submitted_at', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate('p.submitted_at', '<=', $dateTo);
        }

        if ($company !== '') {
            $query->where('c.company_name', $company);
        }

        $paginated   = $query->paginate($perPage);
        $progressIds = collect($paginated->items())->pluck('progress_id')->all();

        // Batch-load all files for this page in one query
        $filesMap = [];
        if (!empty($progressIds)) {
            $allFiles = DB::table('progress_files')
                ->whereIn('progress_id', $progressIds)
                ->select('file_id', 'progress_id', 'file_path', 'original_name')
                ->get();

            foreach ($allFiles as $f) {
                $filesMap[$f->progress_id][] = [
                    'file_id'       => $f->file_id,
                    'file_path'     => $f->file_path,
                    'original_name' => $f->original_name,
                ];
            }
        }

        $items = array_map(function ($row) use ($filesMap) {
            $arr          = (array) $row;
            $arr['files'] = $filesMap[$row->progress_id] ?? [];
            return $arr;
        }, $paginated->items());

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function contractors()
    {
        $companies = DB::table('contractors as c')
            ->join('milestones as m', 'm.contractor_id', '=', 'c.contractor_id')
            ->join('milestone_items as mi', 'mi.milestone_id', '=', 'm.milestone_id')
            ->join('progress as p', 'p.milestone_item_id', '=', 'mi.item_id')
            ->whereNotNull('c.company_name')
            ->where('c.company_name', '!=', '')
            ->distinct()
            ->orderBy('c.company_name')
            ->pluck('c.company_name');

        return response()->json($companies);
    }
}