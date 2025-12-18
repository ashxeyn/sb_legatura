<?php
namespace App\Models\admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class projectClass
{
    public static function analytics()
    {
        return DB::table('projects')
            ->select('project_status', DB::raw('count(*) as count'))
            ->groupBy('project_status')
            ->get()
            ->toArray();
    }

    public static function successRate()
    {
        return DB::table('projects')
            ->select('property_type', DB::raw('count(*) as count'))
            ->where('project_status', 'completed')
            ->groupBy('property_type')
            ->get()
            ->toArray();
    }

    public static function timeline($months = 12)
    {
        $months = max(1, (int) $months);
        $result = [
            'dateRange' => now()->subMonths($months - 1)->format('M Y') . ' - ' . now()->format('M Y'),
            'months' => [],
            'newProjects' => [],
            'completedProjects' => []
        ];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $result['months'][] = $date->format('M');

            if (Schema::hasColumn('projects', 'created_at')) {
                $newCount = DB::table('projects')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
            } else {
                $newCount = DB::table('project_relationships')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
            }
            $result['newProjects'][] = $newCount;

            if (Schema::hasColumn('projects', 'updated_at')) {
                $completedCount = DB::table('projects')
                    ->whereYear('updated_at', $date->year)
                    ->whereMonth('updated_at', $date->month)
                    ->where('project_status', 'completed')
                    ->count();
            } else {
                $completedCount = DB::table('projects')
                    ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                    ->where('project_status', 'completed')
                    ->whereYear('project_relationships.created_at', $date->year)
                    ->whereMonth('project_relationships.created_at', $date->month)
                    ->count();
            }
            $result['completedProjects'][] = $completedCount;
        }

        return $result;
    }
}
