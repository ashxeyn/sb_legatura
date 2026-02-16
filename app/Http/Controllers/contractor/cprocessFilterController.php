<?php

namespace App\Http\Controllers\contractor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\owner\projectsClass;

class cprocessFilterController extends cprocessController
{
    /**
     * Show the contractor homepage with projects.
     * Logic moved here as per user request to unify homepage and filter data preparation.
     */
    public function showHomepage(Request $request)
    {
        $accessCheck = $this->checkContractorAccess($request);
        if ($accessCheck) {
            return $accessCheck;
        }

        // Fetch approved projects via the owner projectsClass (centralized logic)
        try {
            $projectsClass = new projectsClass();
            $projects = $projectsClass->getApprovedProjects();

            // Attach files for each project
            $projects = $projects->map(function ($proj) use ($projectsClass) {
                $proj->files = $projectsClass->getProjectFiles($proj->project_id);
                return $proj;
            });
        }
        catch (\Throwable $e) {
            Log::error('Failed to fetch projects for contractor homepage: ' . $e->getMessage());
            $projects = collect([]);
        }

        // Prepare a lightweight JS-friendly payload for the front-end script
        try {
            $jsProjects = $projects->map(function ($p) {
                $firstFilePath = null;
                if (!empty($p->files)) {
                    $first = null;
                    if (is_array($p->files) && count($p->files) > 0) {
                        $first = $p->files[0];
                    }
                    elseif (method_exists($p->files, 'first')) {
                        $first = $p->files->first();
                    }

                    if (!empty($first)) {
                        $firstFilePath = is_string($first) ? $first : (is_array($first) ? ($first['file_path'] ?? null) : ($first->file_path ?? null));
                    }
                }

                return (object)[
                'project_id' => $p->project_id,
                'title' => $p->project_title,
                'description' => $p->project_description,
                'city' => $p->project_location,
                'deadline' => $p->bidding_due ?? $p->bidding_deadline ?? null,
                'project_type' => $p->type_name ?? $p->property_type ?? null,
                'budget_min' => $p->budget_range_min ?? null,
                'budget_max' => $p->budget_range_max ?? null,
                'status' => $p->project_status ?? 'open',
                'created_at' => $p->created_at ?? null,
                'image' => $firstFilePath ? asset('storage/' . ltrim($firstFilePath, '/')) : null,
                'owner_name' => $p->owner_name ?? null,
                'project' => $p
                ];
            })->toArray();
        }
        catch (\Throwable $e) {
            Log::warning('Failed to prepare jsProjects: ' . $e->getMessage());
            $jsProjects = [];
        }

        return view('contractor.contractor_Homepage', [
            'projects' => $projects,
            'jsProjects' => $jsProjects,
            'propertyTypes' => $this->getEnumValues('projects', 'property_type')
        ]);
    }

    /**
     * Helper to retrieve ENUM values for a specific table column.
     */
    private function getEnumValues($table, $column)
    {
        try {
            $columnInfo = DB::select("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$column]);
            if (empty($columnInfo))
                return [];

            $type = $columnInfo[0]->Type;
            preg_match('/^enum\((.*)\)$/', $type, $matches);

            if (!isset($matches[1]))
                return [];

            $values = explode(',', $matches[1]);
            return array_map(function ($v) {
                return trim($v, "'");
            }, $values);
        }
        catch (\Exception $e) {
            Log::error("Failed to get enum values for $table.$column: " . $e->getMessage());
            return [];
        }
    }
}
