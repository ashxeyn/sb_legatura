<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

/**
 * PostService — Showcase / social posting for project portfolios.
 *
 * Responsibilities:
 *   1. CRUD for showcases (create, update, delete)
 *   2. User-specific posts (profile Posts tab)
 *   3. Simple feed with freshness + boost ordering
 */
class PostService
{
    /* ═══════════════════════════════════════════════════════════════════
     * CRUD
     * ═══════════════════════════════════════════════════════════════════ */

    /**
     * Create a new showcase post.
     *
     * @param  int   $userId
     * @param  array $data  Keys: title, content, linked_project_id
     * @param  array $images  Array of UploadedFile objects
     * @return array
     */
    public function createPost(int $userId, array $data, array $images = []): array
    {
        $content = trim($data['content'] ?? '');
        if (empty($content)) {
            return ['success' => false, 'message' => 'Content is required.'];
        }

        try {
            DB::beginTransaction();

            $postId = DB::table('showcases')->insertGetId([
                'user_id'            => $userId,
                'title'              => $data['title'] ?? null,
                'content'            => $content,
                'linked_project_id'  => $data['linked_project_id'] ?? null,
                'location'           => $data['location'] ?? null,
                'status'             => 'pending',
                'is_highlighted'     => false,
                'highlighted_at'     => null,
                'boost_tier'         => null,
                'boost_expiration'   => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // Store images
            foreach ($images as $i => $image) {
                $filename = time() . '_post_' . $postId . '_' . $i . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('post_images', $filename, 'public');

                DB::table('showcase_images')->insert([
                    'post_id'       => $postId,
                    'file_path'     => $path,
                    'original_name' => $image->getClientOriginalName(),
                    'sort_order'    => $i,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            DB::commit();

            Log::info('PostService: Post created', ['post_id' => $postId, 'user_id' => $userId]);

            $post = $this->getPostById($postId);
            return ['success' => true, 'message' => 'Post created successfully.', 'data' => $post];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PostService createPost error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create post.'];
        }
    }

    /**
     * Update an existing post (only by owner).
     */
    public function updatePost(int $userId, int $postId, array $data): array
    {
        $post = DB::table('showcases')->where('post_id', $postId)->first();
        if (!$post) return ['success' => false, 'message' => 'Post not found.'];
        if ((int) $post->user_id !== $userId) return ['success' => false, 'message' => 'Unauthorized.'];

        $updatePayload = [];
        $allowedKeys = ['title', 'content', 'location', 'status'];
        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $data)) {
                $updatePayload[$key] = $data[$key];
            }
        }
        $updatePayload['updated_at'] = now();

        DB::table('showcases')->where('post_id', $postId)->update($updatePayload);

        return ['success' => true, 'message' => 'Post updated.', 'data' => $this->getPostById($postId)];
    }

    /**
     * Soft-delete a post.
     */
    public function deletePost(int $userId, int $postId): array
    {
        $post = DB::table('showcases')->where('post_id', $postId)->first();
        if (!$post) return ['success' => false, 'message' => 'Post not found.'];
        if ((int) $post->user_id !== $userId) return ['success' => false, 'message' => 'Unauthorized.'];

        DB::table('showcases')->where('post_id', $postId)->update([
            'status'     => 'deleted',
            'updated_at' => now(),
        ]);

        return ['success' => true, 'message' => 'Post deleted.'];
    }

    /**
     * Get a single post with images + tagged user info.
     */
    public function getPostById(int $postId): ?object
    {
        $post = DB::table('showcases as pp')
            ->leftJoin('users as u', 'pp.user_id', '=', 'u.user_id')
            ->leftJoin('contractors as c', 'u.user_id', '=', 'c.user_id')
            ->leftJoin('property_owners as po', 'u.user_id', '=', 'po.user_id')
            ->leftJoin('projects as lp', 'pp.linked_project_id', '=', 'lp.project_id')
            ->where('pp.post_id', $postId)
            ->select(
                'pp.*',
                'u.username', 'u.profile_pic', 'u.user_type',
                'c.company_name', 'c.company_logo',
                'po.first_name as owner_first_name', 'po.last_name as owner_last_name',
                'lp.project_title as linked_project_title',
                'lp.project_status as linked_project_status'
            )
            ->first();

        if ($post) {
            $post->images = DB::table('showcase_images')
                ->where('post_id', $postId)
                ->orderBy('sort_order')
                ->get()
                ->toArray();

            // Display name (post author)
            if (!empty($post->company_name)) {
                $post->display_name = $post->company_name;
            } elseif (!empty($post->owner_first_name)) {
                $post->display_name = trim($post->owner_first_name . ' ' . ($post->owner_last_name ?? ''));
            } else {
                $post->display_name = $post->username;
            }

            // Avatar
            $post->avatar = $post->company_logo ?? $post->profile_pic ?? null;


        }

        return $post;
    }

    /**
     * Get posts for a specific user (for profile page).
     */
    public function getUserPosts(int $userId, int $page = 1, int $perPage = 20): array
    {
        $query = DB::table('showcases')
            ->where('user_id', $userId)
            ->where('status', '!=', 'deleted');

        $total = $query->count();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $posts = (clone $query)
            ->orderByDesc('is_highlighted')
            ->orderByDesc('highlighted_at')
            ->orderByDesc('created_at')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Batch-load images
        if ($posts->isNotEmpty()) {
            $postIds = $posts->pluck('post_id')->toArray();
            $images = DB::table('showcase_images')
                ->whereIn('post_id', $postIds)
                ->orderBy('sort_order')
                ->get()
                ->groupBy('post_id');

            $posts = $posts->map(function ($post) use ($images) {
                $post->images = isset($images[$post->post_id]) ? $images[$post->post_id]->values()->toArray() : [];
                return $post;
            });
        }

        return [
            'posts'      => $posts,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'total_pages'  => $totalPages,
                'has_more'     => $page < $totalPages,
            ],
        ];
    }

    /* ═══════════════════════════════════════════════════════════════════
     * UNIFIED FEED (projects for bidding + showcase posts)
     * ═══════════════════════════════════════════════════════════════════ */

    /**
     * Get unified feed: open bidding projects + showcase posts merged
     * by created_at DESC with proper pagination.
     *
     * Each item: { feed_type: 'project'|'showcase', item_id, created_at, data: {...} }
     */
    public function getUnifiedFeed(int $userId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        // ── Determine user role ──
        $user = DB::table('users')->where('user_id', $userId)->first();
        $role = $user->preferred_role ?? $user->user_type ?? 'property_owner';
        // Contractors (or "both" with contractor preference) see bidding projects;
        // staff users are contractor team members and should follow contractor feed rules.
        // Property owners see contractor profiles instead.
        $isContractor = in_array($role, ['contractor', 'both', 'staff']);
        $isOwner      = !$isContractor;
        $contractorId = null;

        if ($isContractor) {
            $contractorId = DB::table('contractors')
                ->where('user_id', $userId)
                ->value('contractor_id');

            // Staff/representative users do not own the contractor account directly;
            // resolve their parent contractor_id from contractor_users.
            if (!$contractorId) {
                $contractorId = DB::table('contractor_users')
                    ->where('user_id', $userId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->value('contractor_id');
            }
        }

        // ── Count totals ──
        $projectCount = 0;
        if ($isContractor) {
            $projectCount = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->where('p.project_status', 'open')
                ->where('pr.project_post_status', 'approved')
                ->where(function ($q) {
                    $q->whereNull('pr.bidding_due')
                      ->orWhere('pr.bidding_due', '>=', now());
                })
                ->when($contractorId, function ($q) use ($contractorId) {
                    $q->whereNotExists(function ($sub) use ($contractorId) {
                        $sub->select(DB::raw(1))
                            ->from('bids as b')
                            ->whereColumn('b.project_id', 'p.project_id')
                            ->where('b.contractor_id', $contractorId)
                            ->whereNotIn('b.bid_status', ['cancelled']);
                    });
                })
                ->count();
        }

        $contractorCount = 0;
        if ($isOwner) {
            $contractorCount = DB::table('contractors as c')
                ->join('users as u', 'c.user_id', '=', 'u.user_id')
                ->where('c.verification_status', 'approved')
                ->where('c.user_id', '!=', $userId)
                ->count();
        }

        $postCount = DB::table('showcases')
            ->where('showcases.status', 'approved')
            ->count();

        $total     = $projectCount + $contractorCount + $postCount;
        $totalPages = max(1, (int) ceil($total / $perPage));

        // Fetch enough rows from each table to assemble the page.
        // Worst case: all page items come from one table → need page*perPage from each.
        $fetchLimit = $page * $perPage;

        // ── 1. Open bidding projects (contractors only) ──
        $projects = collect();
        if ($isContractor) {
            $projects = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->join('users as u', 'po.user_id', '=', 'u.user_id')
                ->leftJoin('contractor_types as ct', 'p.type_id', '=', 'ct.type_id')
                ->where('p.project_status', 'open')
                ->where('pr.project_post_status', 'approved')
                ->where(function ($q) {
                    $q->whereNull('pr.bidding_due')
                      ->orWhere('pr.bidding_due', '>=', now());
                })
                ->when($contractorId, function ($q) use ($contractorId) {
                    $q->whereNotExists(function ($sub) use ($contractorId) {
                        $sub->select(DB::raw(1))
                            ->from('bids as b')
                            ->whereColumn('b.project_id', 'p.project_id')
                            ->where('b.contractor_id', $contractorId)
                            ->whereNotIn('b.bid_status', ['cancelled']);
                    });
                })
                ->select(
                    'p.project_id', 'p.project_title', 'p.project_description',
                    'p.project_location', 'p.budget_range_min', 'p.budget_range_max',
                    'p.lot_size', 'p.floor_area', 'p.property_type', 'p.type_id',
                    'ct.type_name', 'p.project_status',
                    'pr.project_post_status',
                    'pr.bidding_due as bidding_deadline',
                    'pr.owner_id',
                    'u.user_id as owner_user_id',
                    'u.profile_pic as owner_profile_pic',
                    DB::raw("CONCAT(po.first_name, ' ', po.last_name) as owner_name"),
                    'pr.created_at'
                )
                ->orderByDesc('pr.created_at')
                ->limit($fetchLimit)
                ->get()
                ->map(fn ($p) => (object) [
                    'feed_type'  => 'project',
                    'item_id'    => $p->project_id,
                    'created_at' => $p->created_at,
                    'data'       => $p,
                ]);
        }

        // ── 1b. Contractor profiles (property owners only) ──
        $contractors = collect();
        if ($isOwner) {
            $contractors = DB::table('contractors as c')
                ->join('users as u', 'c.user_id', '=', 'u.user_id')
                ->join('contractor_types as ct', 'c.type_id', '=', 'ct.type_id')
                ->where('c.verification_status', 'approved')
                ->where('c.user_id', '!=', $userId)
                ->select(
                    'c.contractor_id',
                    'c.company_name',
                    'c.years_of_experience',
                    'c.services_offered',
                    'c.business_address',
                    'c.company_description',
                    'c.completed_projects',
                    'c.company_logo',
                    'c.company_banner',
                    'c.created_at',
                    'ct.type_name',
                    'c.type_id',
                    'u.user_id',
                    'u.username',
                    'u.profile_pic',
                    'u.cover_photo'
                )
                ->orderByDesc('c.created_at')
                ->limit($fetchLimit)
                ->get()
                ->map(fn ($c) => (object) [
                    'feed_type'  => 'contractor',
                    'item_id'    => $c->contractor_id,
                    'created_at' => $c->created_at,
                    'data'       => $c,
                ]);
        }

        // ── 2. Showcase posts ──
        $posts = DB::table('showcases as pp')
            ->join('users as u', 'pp.user_id', '=', 'u.user_id')
            ->leftJoin('contractors as c', 'u.user_id', '=', 'c.user_id')
            ->leftJoin('property_owners as po', 'u.user_id', '=', 'po.user_id')
            ->leftJoin('projects as lp', 'pp.linked_project_id', '=', 'lp.project_id')
            ->leftJoin('milestones as ms', function ($join) {
                $join->on('ms.project_id', '=', 'lp.project_id')
                     ->on('ms.contractor_id', '=', 'lp.selected_contractor_id')
                     ->where('ms.setup_status', '=', 'approved');
            })
            ->where('pp.status', 'approved')
            ->select(
                'pp.*',
                'u.username', 'u.profile_pic', 'u.user_type',
                'c.company_name', 'c.company_logo',
                'po.first_name as owner_first_name',
                'po.last_name as owner_last_name',
                'lp.project_title as linked_project_title',
                'ms.milestone_name as linked_milestone_name'
            )
            ->orderByDesc('pp.created_at')
            ->limit($fetchLimit)
            ->get()
            ->map(function ($p) {
                if (!empty($p->company_name)) {
                    $p->display_name = $p->company_name;
                } elseif (!empty($p->owner_first_name)) {
                    $p->display_name = trim($p->owner_first_name . ' ' . ($p->owner_last_name ?? ''));
                } else {
                    $p->display_name = $p->username;
                }
                $p->avatar = $p->company_logo ?? $p->profile_pic ?? null;

                return (object) [
                    'feed_type'  => 'showcase',
                    'item_id'    => $p->post_id,
                    'created_at' => $p->created_at,
                    'data'       => $p,
                ];
            });

        // ── 3. Merge, sort by created_at DESC, paginate ──
        $merged   = $projects->merge($contractors)->merge($posts)->sortByDesc('created_at')->values();
        $pageItems = $merged->slice($offset, $perPage)->values();

        // ── 4. Hydrate projects with bids_count + files ──
        foreach ($pageItems->filter(fn ($i) => $i->feed_type === 'project') as $item) {
            $item->data->bids_count = DB::table('bids')
                ->where('project_id', $item->data->project_id)
                ->whereNotIn('bid_status', ['cancelled'])
                ->count();

            $item->data->files = DB::table('project_files')
                ->where('project_id', $item->data->project_id)
                ->orderBy('file_id')
                ->select('file_id', 'file_type', 'file_path')
                ->get()
                ->toArray();
        }

        // ── 5. Hydrate showcase posts with images ──
        $showcaseItems = $pageItems->filter(fn ($i) => $i->feed_type === 'showcase');
        if ($showcaseItems->isNotEmpty()) {
            $postIds = $showcaseItems->map(fn ($i) => $i->data->post_id)->toArray();
            $images  = DB::table('showcase_images')
                ->whereIn('post_id', $postIds)
                ->orderBy('sort_order')
                ->get()
                ->groupBy('post_id');

            foreach ($showcaseItems as $item) {
                $item->data->images = isset($images[$item->data->post_id])
                    ? $images[$item->data->post_id]->values()->toArray()
                    : [];
            }
        }

        return [
            'items'      => $pageItems->values(),
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'total_pages'  => $totalPages,
                'has_more'     => $page < $totalPages,
            ],
        ];
    }

    /**
     * Search the unified feed with role-aware visibility.
     *
     * Scope rules:
     * - Contractor/staff search sees: owner projects, owner showcases, owner profiles.
     * - Property-owner search sees: contractor showcases, contractor profiles.
     *
     * @param  string $scope all|users|posts
     */
    public function searchUnifiedFeed(int $userId, string $keyword, string $scope = 'all', int $page = 1, int $perPage = 20): array
    {
        $scope = in_array($scope, ['all', 'users', 'posts'], true) ? $scope : 'all';
        $keyword = trim($keyword);

        if ($keyword === '') {
            return [
                'items' => collect(),
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => 0,
                    'total_pages' => 0,
                    'has_more' => false,
                ],
            ];
        }

        $offset = ($page - 1) * $perPage;
        $like = '%' . $keyword . '%';

        $user = DB::table('users')->where('user_id', $userId)->first();
        $role = $user->preferred_role ?? $user->user_type ?? 'property_owner';
        $isContractor = in_array($role, ['contractor', 'both', 'staff'], true);
        $isOwner = !$isContractor;
        $contractorId = null;

        if ($isContractor) {
            $contractorId = DB::table('contractors')
                ->where('user_id', $userId)
                ->value('contractor_id');

            if (!$contractorId) {
                $contractorId = DB::table('contractor_users')
                    ->where('user_id', $userId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->value('contractor_id');
            }
        }

        $users = collect();
        $projects = collect();
        $showcases = collect();

        if ($scope !== 'posts') {
            if ($isContractor) {
                $users = DB::table('property_owners as po')
                    ->join('users as u', 'po.user_id', '=', 'u.user_id')
                    ->where('po.verification_status', 'approved')
                    ->where('u.user_id', '!=', $userId)
                    ->where(function ($q) use ($like) {
                        $q->where('u.username', 'LIKE', $like)
                          ->orWhere('po.first_name', 'LIKE', $like)
                          ->orWhere('po.last_name', 'LIKE', $like)
                          ->orWhere('po.address', 'LIKE', $like)
                          ->orWhereRaw("CONCAT(po.first_name, ' ', po.last_name) LIKE ?", [$like]);
                    })
                    ->select(
                        'po.owner_id',
                        'u.user_id',
                        'u.username',
                        'u.profile_pic',
                        'u.cover_photo',
                        'po.address',
                        'po.created_at',
                        DB::raw("CONCAT(po.first_name, ' ', po.last_name) as display_name")
                    )
                    ->orderByDesc('po.created_at')
                    ->get()
                    ->map(fn ($o) => (object) [
                        'feed_type' => 'owner',
                        'item_id' => $o->owner_id,
                        'created_at' => $o->created_at,
                        'data' => $o,
                    ]);
            }

            if ($isOwner) {
                $users = DB::table('contractors as c')
                    ->join('users as u', 'c.user_id', '=', 'u.user_id')
                    ->join('contractor_types as ct', 'c.type_id', '=', 'ct.type_id')
                    ->where('c.verification_status', 'approved')
                    ->where('u.user_id', '!=', $userId)
                    ->where(function ($q) use ($like) {
                        $q->where('c.company_name', 'LIKE', $like)
                          ->orWhere('u.username', 'LIKE', $like)
                          ->orWhere('c.services_offered', 'LIKE', $like)
                          ->orWhere('c.business_address', 'LIKE', $like)
                          ->orWhere('ct.type_name', 'LIKE', $like);
                    })
                    ->select(
                        'c.contractor_id',
                        'c.company_name',
                        'c.years_of_experience',
                        'c.services_offered',
                        'c.business_address',
                        'c.company_description',
                        'c.completed_projects',
                        'c.company_logo',
                        'c.company_banner',
                        'c.created_at',
                        'ct.type_name',
                        'ct.type_id',
                        'u.user_id',
                        'u.username',
                        'u.profile_pic',
                        'u.cover_photo'
                    )
                    ->orderByDesc('c.created_at')
                    ->get()
                    ->map(fn ($c) => (object) [
                        'feed_type' => 'contractor',
                        'item_id' => $c->contractor_id,
                        'created_at' => $c->created_at,
                        'data' => $c,
                    ]);
            }
        }

        if ($scope !== 'users') {
            if ($isContractor) {
                $projects = DB::table('projects as p')
                    ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                    ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                    ->join('users as u', 'po.user_id', '=', 'u.user_id')
                    ->leftJoin('contractor_types as ct', 'p.type_id', '=', 'ct.type_id')
                    ->where('p.project_status', 'open')
                    ->where('pr.project_post_status', 'approved')
                    ->where(function ($q) {
                        $q->whereNull('pr.bidding_due')
                          ->orWhere('pr.bidding_due', '>=', now());
                    })
                    ->when($contractorId, function ($q) use ($contractorId) {
                        $q->whereNotExists(function ($sub) use ($contractorId) {
                            $sub->select(DB::raw(1))
                                ->from('bids as b')
                                ->whereColumn('b.project_id', 'p.project_id')
                                ->where('b.contractor_id', $contractorId)
                                ->whereNotIn('b.bid_status', ['cancelled']);
                        });
                    })
                    ->where(function ($q) use ($like) {
                        $q->where('p.project_title', 'LIKE', $like)
                          ->orWhere('p.project_description', 'LIKE', $like)
                          ->orWhere('p.project_location', 'LIKE', $like)
                          ->orWhere('ct.type_name', 'LIKE', $like)
                          ->orWhereRaw("CONCAT(po.first_name, ' ', po.last_name) LIKE ?", [$like]);
                    })
                    ->select(
                        'p.project_id', 'p.project_title', 'p.project_description',
                        'p.project_location', 'p.budget_range_min', 'p.budget_range_max',
                        'p.lot_size', 'p.floor_area', 'p.property_type', 'p.type_id',
                        'ct.type_name', 'p.project_status',
                        'pr.project_post_status',
                        'pr.bidding_due as bidding_deadline',
                        'pr.owner_id',
                        'u.user_id as owner_user_id',
                        'u.profile_pic as owner_profile_pic',
                        DB::raw("CONCAT(po.first_name, ' ', po.last_name) as owner_name"),
                        'pr.created_at'
                    )
                    ->orderByDesc('pr.created_at')
                    ->get()
                    ->map(fn ($p) => (object) [
                        'feed_type' => 'project',
                        'item_id' => $p->project_id,
                        'created_at' => $p->created_at,
                        'data' => $p,
                    ]);
            }

            $showcases = DB::table('showcases as pp')
                ->join('users as u', 'pp.user_id', '=', 'u.user_id')
                ->leftJoin('contractors as c', 'u.user_id', '=', 'c.user_id')
                ->leftJoin('property_owners as po', 'u.user_id', '=', 'po.user_id')
                ->leftJoin('projects as lp', 'pp.linked_project_id', '=', 'lp.project_id')
                ->leftJoin('milestones as ms', function ($join) {
                    $join->on('ms.project_id', '=', 'lp.project_id')
                        ->on('ms.contractor_id', '=', 'lp.selected_contractor_id')
                        ->where('ms.setup_status', '=', 'approved');
                })
                ->where('pp.status', 'approved')
                ->where(function ($q) use ($isContractor, $isOwner) {
                    if ($isContractor) {
                        $q->whereNotNull('po.owner_id');
                    }
                    if ($isOwner) {
                        $q->whereNotNull('c.contractor_id');
                    }
                })
                ->where(function ($q) use ($like) {
                    $q->where('pp.title', 'LIKE', $like)
                      ->orWhere('pp.content', 'LIKE', $like)
                      ->orWhere('pp.location', 'LIKE', $like)
                      ->orWhere('u.username', 'LIKE', $like)
                      ->orWhere('c.company_name', 'LIKE', $like)
                      ->orWhereRaw("CONCAT(po.first_name, ' ', po.last_name) LIKE ?", [$like]);
                })
                ->select(
                    'pp.*',
                    'u.username', 'u.profile_pic', 'u.user_type',
                    'c.company_name', 'c.company_logo',
                    'po.first_name as owner_first_name',
                    'po.last_name as owner_last_name',
                    'lp.project_title as linked_project_title',
                    'ms.milestone_name as linked_milestone_name'
                )
                ->orderByDesc('pp.created_at')
                ->get()
                ->map(function ($p) {
                    if (!empty($p->company_name)) {
                        $p->display_name = $p->company_name;
                    } elseif (!empty($p->owner_first_name)) {
                        $p->display_name = trim($p->owner_first_name . ' ' . ($p->owner_last_name ?? ''));
                    } else {
                        $p->display_name = $p->username;
                    }
                    $p->avatar = $p->company_logo ?? $p->profile_pic ?? null;

                    return (object) [
                        'feed_type' => 'showcase',
                        'item_id' => $p->post_id,
                        'created_at' => $p->created_at,
                        'data' => $p,
                    ];
                });
        }

        if ($scope === 'users') {
            $merged = $users->sortByDesc('created_at')->values();
        } elseif ($scope === 'posts') {
            $merged = $projects->merge($showcases)->sortByDesc('created_at')->values();
        } else {
            $merged = $projects->merge($users)->merge($showcases)->sortByDesc('created_at')->values();
        }

        $total = $merged->count();
        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;
        $pageItems = $merged->slice($offset, $perPage)->values();

        foreach ($pageItems->filter(fn ($i) => $i->feed_type === 'project') as $item) {
            $item->data->bids_count = DB::table('bids')
                ->where('project_id', $item->data->project_id)
                ->whereNotIn('bid_status', ['cancelled'])
                ->count();

            $item->data->files = DB::table('project_files')
                ->where('project_id', $item->data->project_id)
                ->orderBy('file_id')
                ->select('file_id', 'file_type', 'file_path')
                ->get()
                ->toArray();
        }

        $showcaseItems = $pageItems->filter(fn ($i) => $i->feed_type === 'showcase');
        if ($showcaseItems->isNotEmpty()) {
            $postIds = $showcaseItems->map(fn ($i) => $i->data->post_id)->toArray();
            $images = DB::table('showcase_images')
                ->whereIn('post_id', $postIds)
                ->orderBy('sort_order')
                ->get()
                ->groupBy('post_id');

            foreach ($showcaseItems as $item) {
                $item->data->images = isset($images[$item->data->post_id])
                    ? $images[$item->data->post_id]->values()->toArray()
                    : [];
            }
        }

        return [
            'items' => $pageItems,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_more' => $totalPages > 0 ? $page < $totalPages : false,
            ],
        ];
    }

    /* ═══════════════════════════════════════════════════════════════════
     * SHOWCASE-ONLY FEED (legacy, boost → freshness ordering)
     * ═══════════════════════════════════════════════════════════════════ */

    /**
     * Get social feed for a user.
     * Order: highlighted first → boosted (gold > silver) → newest.
     *
     * @param  int   $userId  Viewer's user_id
     * @param  int   $page
     * @param  int   $perPage
     * @param  array $filters  (reserved for future use)
     * @return array{posts: Collection, pagination: array}
     */
    public function getFeedForUser(int $userId, int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $query = DB::table('showcases as pp')
            ->join('users as u', 'pp.user_id', '=', 'u.user_id')
            ->where('pp.status', 'approved')
            ->where('pp.user_id', '!=', $userId)
            ->select('pp.*', 'u.username', 'u.profile_pic', 'u.user_type');

        $total = (clone $query)->count();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        // Order: highlighted → active boosts (gold then silver) → newest
        $paginated = $query
            ->orderByDesc('pp.is_highlighted')
            ->orderByRaw("CASE
                WHEN pp.boost_tier = 'gold'   AND (pp.boost_expiration IS NULL OR pp.boost_expiration > NOW()) THEN 2
                WHEN pp.boost_tier = 'silver' AND (pp.boost_expiration IS NULL OR pp.boost_expiration > NOW()) THEN 1
                ELSE 0
            END")
            ->orderByDesc('pp.created_at')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Hydrate with images and display info
        $paginated = $this->hydratePostsForFeed($paginated);

        return [
            'posts' => $paginated,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'total_pages'  => $totalPages,
                'has_more'     => $page < $totalPages,
            ],
        ];
    }

    /* ─── Hydration ────────────────────────────────────────────────── */

    private function hydratePostsForFeed(Collection $posts): Collection
    {
        if ($posts->isEmpty()) return $posts;

        $postIds = $posts->pluck('post_id')->toArray();
        $userIds = $posts->pluck('user_id')->unique()->toArray();

        // Batch load images
        $images = DB::table('showcase_images')
            ->whereIn('post_id', $postIds)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('post_id');

        // Batch load contractor/owner info for display names
        $contractors = DB::table('contractors')
            ->whereIn('user_id', $userIds)
            ->pluck('company_name', 'user_id');
        $owners = DB::table('property_owners')
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        return $posts->map(function ($post) use ($images, $contractors, $owners) {
            $post->images = isset($images[$post->post_id]) ? $images[$post->post_id]->values()->toArray() : [];

            // Display name
            if (isset($contractors[$post->user_id]) && !empty($contractors[$post->user_id])) {
                $post->display_name = $contractors[$post->user_id];
            } elseif (isset($owners[$post->user_id])) {
                $o = $owners[$post->user_id];
                $post->display_name = trim(($o->first_name ?? '') . ' ' . ($o->last_name ?? ''));
            } else {
                $post->display_name = $post->username ?? 'User';
            }

            return $post;
        });
    }
}
