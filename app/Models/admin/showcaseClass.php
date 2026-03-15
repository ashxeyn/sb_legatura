<?php

namespace App\Models\admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class showcaseClass
{
    /**
     * Fetch showcase posts with filters and pagination
     */
    public function fetchShowcases($filters = [])
    {
        $query = DB::table('showcases as pp')
            ->join('users as u', 'pp.user_id', '=', 'u.user_id')
            ->leftJoin('property_owners as po', 'u.user_id', '=', 'po.user_id')
            ->leftJoin('contractors as c', 'po.owner_id', '=', 'c.owner_id')
            ->select(
                'pp.post_id',
                'pp.title',
                'pp.content',
                'pp.location',
                'pp.status',
                'pp.is_highlighted',
                'pp.highlighted_at',
                'pp.rejection_reason',
                'pp.linked_project_id',
                'pp.created_at',
                'c.company_logo as contractor_pic',
                DB::raw("COALESCE(c.company_name, u.username) as contractor_name")
            );

        // Filter: Search (Contractor Name or Showcase Title)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('pp.title', 'like', "%{$search}%")
                    ->orWhere('pp.content', 'like', "%{$search}%")
                    ->orWhere('c.company_name', 'like', "%{$search}%")
                    ->orWhere('u.username', 'like', "%{$search}%");
            });
        }

        // Filter: Status (default: all)
        $status = $filters['status'] ?? 'all';
        if ($status !== 'all') {
            $query->where('pp.status', $status);
        }

        // Filter: Date Range
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('pp.created_at', [
                Carbon::parse($filters['date_from'])->startOfDay(),
                Carbon::parse($filters['date_to'])->endOfDay()
            ]);
        } elseif (!empty($filters['date_from'])) {
            $query->where('pp.created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        } elseif (!empty($filters['date_to'])) {
            $query->where('pp.created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        // Sort: Latest first
        $query->orderBy('pp.created_at', 'desc');

        return $query->paginate(10);
    }

    /**
     * Get showcase details by post ID
     */
    public function getShowcaseDetails($postId)
    {
        $post = DB::table('showcases as pp')
            ->join('users as u', 'pp.user_id', '=', 'u.user_id')
            ->leftJoin('property_owners as po', 'u.user_id', '=', 'po.user_id')
            ->leftJoin('contractors as c', 'po.owner_id', '=', 'c.owner_id')
            ->where('pp.post_id', $postId)
            ->select(
                'pp.*',
                'c.company_logo as contractor_pic',
                'u.email as contractor_email',
                DB::raw("COALESCE(c.company_name, u.username) as contractor_name")
            )
            ->first();

        if (!$post) {
            return null;
        }

        // Get images
        $images = DB::table('showcase_images')
            ->where('post_id', $postId)
            ->orderBy('sort_order')
            ->get();

        // Get linked project title if exists
        $linkedProjectTitle = null;
        if ($post->linked_project_id) {
            $linkedProjectTitle = DB::table('projects')
                ->where('project_id', $post->linked_project_id)
                ->value('project_title');
        }

        return [
            'contractor' => [
                'name' => $post->contractor_name,
                'email' => $post->contractor_email,
                'profile_pic' => $post->contractor_pic,
            ],
            'post' => [
                'post_id' => $post->post_id,
                'title' => $post->title,
                'content' => $post->content,
                'location' => $post->location,
                'status' => $post->status,
                'is_highlighted' => $post->is_highlighted,
                'highlighted_at' => $post->highlighted_at,
                'rejection_reason' => $post->rejection_reason,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
            ],
            'images' => $images,
            'linked_project_title' => $linkedProjectTitle,
        ];
    }

    /**
     * Approve a showcase post
     */
    public function approveShowcase($postId)
    {
        return DB::transaction(function () use ($postId) {
            $post = DB::table('showcases')
                ->join('users', 'showcases.user_id', '=', 'users.user_id')
                ->where('showcases.post_id', $postId)
                ->select('showcases.*', 'users.email', 'users.username')
                ->first();

            if (!$post) {
                return false;
            }

            $updated = DB::table('showcases')
                ->where('post_id', $postId)
                ->update([
                    'status' => 'approved',
                    'updated_at' => now(),
                ]);

            if ($updated) {
                \App\Services\NotificationService::create(
                    (int) $post->user_id,
                    'project_update',
                    'Showcase Approved',
                    "Your showcase post \"{$post->title}\" has been approved and is now visible in the public feed.",
                    'high',
                    'showcase',
                    (int) $postId,
                    ['screen' => 'ShowcasePostDetail', 'params' => ['postId' => (int) $postId]]
                );

                try {
                    if ($post->email) {
                        $subject = "Showcase Approved";
                        $body = "Dear {$post->username},\n\nYour showcase post \"{$post->title}\" has been approved and is now visible in the public feed.\n\nThank you for sharing your work on our platform.";
                        \Illuminate\Support\Facades\Mail::raw($body, function ($m) use ($post, $subject) {
                            $m->to($post->email)->subject($subject);
                        });
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send approve showcase email: ' . $e->getMessage());
                }

                return true;
            }
            return false;
        });
    }

    /**
     * Reject a showcase post
     */
    public function rejectShowcase($postId, $reason)
    {
        return DB::transaction(function () use ($postId, $reason) {
            $post = DB::table('showcases')
                ->join('users', 'showcases.user_id', '=', 'users.user_id')
                ->where('showcases.post_id', $postId)
                ->select('showcases.*', 'users.email', 'users.username')
                ->first();

            if (!$post) {
                return false;
            }

            $updated = DB::table('showcases')
                ->where('post_id', $postId)
                ->update([
                    'status' => 'rejected',
                    'rejection_reason' => $reason,
                    'updated_at' => now(),
                ]);

            if ($updated) {
                \App\Services\NotificationService::create(
                    (int) $post->user_id,
                    'project_update',
                    'Showcase Rejected',
                    "Your showcase post \"{$post->title}\" has been rejected. Reason: {$reason}",
                    'high',
                    'showcase',
                    (int) $postId,
                    ['screen' => 'ShowcasePostDetail', 'params' => ['postId' => (int) $postId]]
                );

                try {
                    if ($post->email) {
                        $subject = "Showcase Rejected";
                        $body = "Dear {$post->username},\n\nYour showcase post \"{$post->title}\" has been rejected.\n\nReason: {$reason}\n\nPlease review our guidelines and feel free to submit anew.";
                        \Illuminate\Support\Facades\Mail::raw($body, function ($m) use ($post, $subject) {
                            $m->to($post->email)->subject($subject);
                        });
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send reject showcase email: ' . $e->getMessage());
                }

                return true;
            }
            return false;
        });
    }

    /**
     * Delete a showcase
     */
    public function deleteShowcase($postId, $reason)
    {
        return DB::transaction(function () use ($postId, $reason) {
            $post = DB::table('showcases')->where('post_id', $postId)->first();

            if (!$post) {
                return false;
            }

            $updated = DB::table('showcases')
                ->where('post_id', $postId)
                ->update([
                    'status' => 'deleted',
                    'is_highlighted' => 0,
                    'rejection_reason' => $reason,
                    'updated_at' => now(),
                ]);

            if ($updated) {
                \App\Services\NotificationService::create(
                    (int) $post->user_id,
                    'project_update',
                    'Showcase Deleted',
                    "Your showcase post \"{$post->title}\" has been deleted by an administrator. Reason: {$reason}",
                    'high',
                    'showcase',
                    (int) $postId,
                    ['screen' => 'ShowcasePostDetail', 'params' => ['postId' => (int) $postId]]
                );
                return true;
            }
            return false;
        });
    }

    /**
     * Restore a deleted showcase
     */
    public function restoreShowcase($postId)
    {
        return DB::transaction(function () use ($postId) {
            $post = DB::table('showcases')->where('post_id', $postId)->first();

            if (!$post) {
                return false;
            }

            $updated = DB::table('showcases')
                ->where('post_id', $postId)
                ->update([
                    'status' => 'approved',
                    'is_highlighted' => 0,
                    'rejection_reason' => '',
                    'updated_at' => now(),
                ]);

            if ($updated) {
                \App\Services\NotificationService::create(
                    (int) $post->user_id,
                    'project_update',
                    'Showcase Restored',
                    "Your deleted showcase post \"{$post->title}\" has been restored and approved.",
                    'normal',
                    'showcase',
                    (int) $postId,
                    ['screen' => 'ShowcasePostDetail', 'params' => ['postId' => (int) $postId]]
                );
                return true;
            }
            return false;
        });
    }

    /**
     * Get showcase stats
     */
    public function getStats()
    {
        return (object) [
            'total' => DB::table('showcases')->count(),
            'approved' => DB::table('showcases')->where('status', 'approved')->count(),
            'pending' => DB::table('showcases')->where('status', 'pending')->count(),
            'rejected' => DB::table('showcases')->where('status', 'rejected')->count(),
        ];
    }
}
