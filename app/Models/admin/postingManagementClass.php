<?php

namespace App\Models\admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\PostApproved;
use App\Mail\PostRejected;

class postingManagementClass
{
    public function fetchPosts($filters)
    {
        $query = DB::table('project_relationships')
            ->join('projects', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->join('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->select(
                'property_owners.first_name',
                'property_owners.last_name',
                'users.profile_pic',
                'users.user_type',
                'users.email as owner_email',
                'property_owners.phone_number as owner_phone',
                'projects.project_description as project_description',
                'projects.project_location as project_location',
                'projects.property_type as property_type',
                'projects.budget_range_min as budget_range_min',
                'projects.budget_range_max as budget_range_max',
                'projects.lot_size as lot_size',
                'projects.floor_area as floor_area',
                'projects.to_finish as to_finish',
                'projects.project_title',
                'projects.project_id',
                'project_relationships.created_at',
                'project_relationships.project_post_status',
                'project_relationships.rel_id'
            );

        // Filter by Search (Owner Name or Project Title)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('projects.project_title', 'like', "%{$search}%")
                  ->orWhere('property_owners.first_name', 'like', "%{$search}%")
                  ->orWhere('property_owners.last_name', 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(property_owners.first_name, ' ', property_owners.last_name)"), 'like', "%{$search}%");
            });
        }

        // Filter by Date Range
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('project_relationships.created_at', [
                Carbon::parse($filters['date_from'])->startOfDay(),
                Carbon::parse($filters['date_to'])->endOfDay()
            ]);
        } elseif (!empty($filters['date_from'])) {
            $query->where('project_relationships.created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        } elseif (!empty($filters['date_to'])) {
            $query->where('project_relationships.created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        // Filter by Status (Default: under_review) will include 'all' for status
        $status = $filters['status'] ?? 'under_review';
        if ($status !== 'all') {
             $query->where('project_relationships.project_post_status', $status);
        }

        // Sort: Oldest to Latest
        $query->orderBy('project_relationships.created_at', 'asc');

        // Pagination
        return $query->paginate(10);
    }

    public function getPostDetails($projectId)
    {
        $project = DB::table('projects')
            ->where('projects.project_id', $projectId)
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->join('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->select(
                'projects.*',
                'project_relationships.project_post_status',
                'project_relationships.rel_id',
                'project_relationships.admin_reason',
                'project_relationships.created_at as post_created_at',
                'project_relationships.updated_at as post_updated_at',
                'property_owners.first_name',
                'property_owners.last_name',
                'property_owners.phone_number',
                'property_owners.created_at as owner_created_at',
                'users.profile_pic',
                'users.email',
                'users.user_type',
                DB::raw("CONCAT(property_owners.first_name, ' ', property_owners.last_name) AS owner_full_name")
            )
            ->first();

        if (!$project) {
            return null;
        }

        $files = DB::table('project_files')
            ->where('project_id', $projectId)
            ->select('file_path', 'file_type', DB::raw('SUBSTRING_INDEX(file_path, "/", -1) as file_name'))
            ->get();

        return [
            'owner' => [
                'name' => $project->owner_full_name,
                'email' => $project->email,
                'phone' => $project->phone_number,
                'registered_at' => $project->owner_created_at ?? null,
                'type' => $project->user_type,
                'profile_pic' => $project->profile_pic,
            ],
            'project' => [
                'id' => $project->project_id,
                'title' => $project->project_title,
                'description' => $project->project_description,
                'project_location' => $project->project_location,
                'property_type' => $project->property_type,
                'budget_range_min' => $project->budget_range_min,
                'budget_range_max' => $project->budget_range_max,
                'lot_size' => $project->lot_size,
                'floor_area' => $project->floor_area,
                'to_finish' => $project->to_finish,
                'status' => $project->project_post_status,
                'created_at' => $project->post_created_at ?? null,
                'updated_at' => $project->post_updated_at ?? null,
                'admin_reason' => $project->admin_reason,
            ],
            'files' => $files,
        ];
    }

    public function approvePost($projectId)
    {
        return DB::transaction(function () use ($projectId) {
            $project = DB::table('projects')->where('project_id', $projectId)->first();

            if (!$project) {
                return false;
            }

            $updated = DB::table('project_relationships')
                ->where('rel_id', $project->relationship_id)
                ->update(['project_post_status' => 'approved']);

            if ($updated) {
                // Notify property owner that their post was approved
                $ownerUserId = DB::table('project_relationships as pr')
                    ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                    ->where('pr.rel_id', $project->relationship_id)
                    ->value('po.user_id');
                if ($ownerUserId) {
                    \App\Services\NotificationService::create(
                        (int) $ownerUserId,
                        'project_update',
                        'Project Post Approved',
                        "Your project post \"{$project->project_title}\" has been approved and is now visible to contractors.",
                        'high',
                        'project',
                        (int) $projectId,
                        ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $projectId]]
                    );
                }
                return true;
            }
            return false;
        });
    }

    public function rejectPost($projectId, $reason)
    {
        return DB::transaction(function () use ($projectId, $reason) {
            $project = DB::table('projects')->where('project_id', $projectId)->first();

            if (!$project) {
                return false;
            }

            $updated = DB::table('project_relationships')
                ->where('rel_id', $project->relationship_id)
                ->update([
                    'project_post_status' => 'rejected',
                    'admin_reason' => $reason
                ]);

            if ($updated) {
                // Notify property owner that their post was rejected
                $ownerUserId = DB::table('project_relationships as pr')
                    ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                    ->where('pr.rel_id', $project->relationship_id)
                    ->value('po.user_id');
                if ($ownerUserId) {
                    \App\Services\NotificationService::create(
                        (int) $ownerUserId,
                        'project_update',
                        'Project Post Rejected',
                        "Your project post \"{$project->project_title}\" has been rejected. Reason: {$reason}",
                        'high',
                        'project',
                        (int) $projectId,
                        ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $projectId]]
                    );
                }
                return true;
            }
            return false;
        });
    }
}
