<?php

namespace App\Models\admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class reviewsClass
{
    /**
     * Fetch reviews with filters and pagination
     */
    public function fetchReviews($filters = [])
    {
        $query = DB::table('reviews as r')
            ->join('projects as p', 'r.project_id', '=', 'p.project_id')
            ->join('users as u_rev', 'r.reviewer_user_id', '=', 'u_rev.user_id')
            ->join('users as u_ree', 'r.reviewee_user_id', '=', 'u_ree.user_id')
            // Join for reviewer names
            ->leftJoin('property_owners as po_rev', 'u_rev.user_id', '=', 'po_rev.user_id')
            ->leftJoin('contractors as c_rev', 'po_rev.owner_id', '=', 'c_rev.owner_id')
            // Join for reviewee names
            ->leftJoin('property_owners as po_ree', 'u_ree.user_id', '=', 'po_ree.user_id')
            ->leftJoin('contractors as c_ree', 'po_ree.owner_id', '=', 'c_ree.owner_id')
            ->select(
                'r.review_id',
                'r.rating',
                'r.comment as review_text',
                'r.created_at',
                'p.project_title',
                'po_rev.profile_pic as reviewer_pic',
                'u_rev.user_type as reviewer_type',
                DB::raw("CASE 
                    WHEN u_rev.user_type = 'contractor' THEN c_rev.company_name 
                    WHEN u_rev.user_type = 'property_owner' THEN CONCAT(u_rev.first_name, ' ', u_rev.last_name)
                    ELSE u_rev.username 
                END as reviewer_name"),
                'po_ree.profile_pic as reviewed_pic',
                'u_ree.user_type as reviewed_type',
                DB::raw("CASE 
                    WHEN u_ree.user_type = 'contractor' THEN c_ree.company_name 
                    WHEN u_ree.user_type = 'property_owner' THEN CONCAT(u_ree.first_name, ' ', u_ree.last_name)
                    ELSE u_ree.username 
                END as reviewed_name")
            );

        // Filter: is_deleted = 0
        $query->where('r.is_deleted', 0);

        // Filter: Search (Reviewer, Reviewee, Project Title, or Review Text)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('p.project_title', 'like', "%{$search}%")
                    ->orWhere('r.comment', 'like', "%{$search}%")
                    ->orWhere('u_rev.username', 'like', "%{$search}%")
                    ->orWhere('u_ree.username', 'like', "%{$search}%")
                    ->orWhere('c_rev.company_name', 'like', "%{$search}%")
                    ->orWhere('c_ree.company_name', 'like', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(u_rev.first_name, ' ', u_rev.last_name)"), 'like', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(u_ree.first_name, ' ', u_ree.last_name)"), 'like', "%{$search}%");
            });
        }

        // Filter: Rating
        if (!empty($filters['rating'])) {
            $query->where('r.rating', $filters['rating']);
        }

        // Filter: Date Range
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('r.created_at', [
                Carbon::parse($filters['date_from'])->startOfDay(),
                Carbon::parse($filters['date_to'])->endOfDay()
            ]);
        } elseif (!empty($filters['date_from'])) {
            $query->where('r.created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        } elseif (!empty($filters['date_to'])) {
            $query->where('r.created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        // Sort: Latest to Oldest
        $query->orderBy('r.created_at', 'desc');

        return $query->paginate(10);
    }

    /**
     * Soft delete a review
     */
    public function deleteReview($reviewId, $reason)
    {
        return DB::table('reviews')
            ->where('review_id', $reviewId)
            ->update([
                'is_deleted' => 1,
                'deletion_reason' => $reason
            ]);
    }
}
