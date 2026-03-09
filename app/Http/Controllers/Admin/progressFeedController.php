<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProgressFeedController extends Controller
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
            'proj.project_id',
            'proj.project_title',
            'u.user_id  as contractor_user_id',
            'u.username as contractor_username',
            'u.profile_pic as contractor_pic',
            DB::raw("COALESCE(c.company_name, u.username) as contractor_name"),
        ];

        if ($reasonCol) {
            $selectCols[] = DB::raw($reasonCol);
        }

        $query = DB::table('progress as p')
            ->join('milestone_items as mi', 'p.milestone_item_id', '=', 'mi.item_id')
            ->join('milestones as m',        'mi.milestone_id', '=', 'm.milestone_id')
            ->join('projects as proj',       'm.project_id', '=', 'proj.project_id')
            ->join('contractors as c', 'proj.selected_contractor_id', '=', 'c.contractor_id')
            ->join('users as u',       'c.user_id', '=', 'u.user_id')
            ->select($selectCols)
            ->orderBy('p.submitted_at', 'desc');

        if ($status && $status !== 'all') {
            $query->where('p.progress_status', $status);
        } else {
            // Exclude deleted by default; show them only when explicitly filtered
            $query->where('p.progress_status', '!=', 'deleted');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('proj.project_title',        'like', "%{$search}%")
                  ->orWhere('mi.milestone_item_title', 'like', "%{$search}%")
                  ->orWhere('p.purpose',               'like', "%{$search}%")
                  ->orWhere('u.username',     'like', "%{$search}%")
                  ->orWhere('c.company_name', 'like', "%{$search}%");
            });
        }

        if (!empty($dateFrom)) {
            $query->whereDate('p.submitted_at', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate('p.submitted_at', '<=', $dateTo);
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
}