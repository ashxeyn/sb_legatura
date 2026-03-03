<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Services\postService;

/**
 * postController — CRUD + Feed for Facebook-style project posts.
 *
 * Endpoints:
 *   POST   /api/posts                  — Create a post
 *   PUT    /api/posts/{id}             — Update a post
 *   DELETE /api/posts/{id}             — Soft-delete a post
 *   GET    /api/posts/{id}             — Get single post
 *   GET    /api/posts/user/{userId}    — Get posts for a user
 *   GET    /api/feed                   — Get scored social feed
 */
class postController extends Controller
{
    protected postService $postService;

    public function __construct(postService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Create a new showcase post (with image uploads).
     *
     * POST /api/posts
     * Body (multipart/form-data):
     *   content (required), title, tagged_user_id, linked_project_id
     *   images[] (files, optional)
     */
    public function store(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);

        $request->validate([
            'content'            => 'required|string|min:10|max:5000',
            'title'              => 'nullable|string|max:255',
            'linked_project_id'  => 'nullable|integer',
            'location'           => 'nullable|string|max:500',
            'images'             => 'nullable|array|max:10',
            'images.*'           => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $images = $request->file('images', []);
        $result = $this->postService->createPost($userId, $request->all(), $images);

        return response()->json($result, $result['success'] ? 201 : 422);
    }

    /**
     * Update an existing post.
     *
     * PUT /api/posts/{id}
     */
    public function update(Request $request, int $id)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);

        $request->validate([
            'content'            => 'nullable|string|min:10|max:5000',
            'title'              => 'nullable|string|max:255',
            'location'           => 'nullable|string|max:500',
            'status'             => 'nullable|string|in:open,closed',
        ]);

        $result = $this->postService->updatePost($userId, $id, $request->all());
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Soft-delete a post.
     *
     * DELETE /api/posts/{id}
     */
    public function destroy(Request $request, int $id)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);

        $result = $this->postService->deletePost($userId, $id);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Get a single post.
     *
     * GET /api/posts/{id}
     */
    public function show(int $id)
    {
        $post = $this->postService->getPostById($id);
        if (!$post) return response()->json(['success' => false, 'message' => 'Post not found.'], 404);

        return response()->json(['success' => true, 'data' => $post]);
    }

    /**
     * Get posts for a specific user (profile posts tab).
     *
     * GET /api/posts/user/{userId}?page=1&per_page=20
     */
    public function forUser(Request $request, int $userId)
    {
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));

        $data = $this->postService->getUserPosts($userId, $page, $perPage);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get social feed for the authenticated user.
     *
     * GET /api/feed?page=1&per_page=20
     */
    public function feed(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);

        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));

        $data = $this->postService->getFeedForUser($userId, $page, $perPage);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get unified feed (bidding projects + showcase posts merged).
     *
     * GET /api/unified-feed?page=1&per_page=20
     */
    public function unifiedFeed(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);

        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));

        $data = $this->postService->getUnifiedFeed($userId, $page, $perPage);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Return the authenticated user's completed projects for showcase linking.
     *
     * GET /api/posts/completed-projects
     */
    public function completedProjects(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);

        // Already showcased project IDs (to mark them)
        $showcasedIds = DB::table('project_posts')
            ->where('user_id', $userId)
            ->whereNotNull('linked_project_id')
            ->where('status', '!=', 'deleted')
            ->pluck('linked_project_id')
            ->toArray();

        // Build query based on whether user is contractor, owner, or both
        $contractor = DB::table('contractors')->where('user_id', $userId)->first();
        $owner      = DB::table('property_owners')->where('user_id', $userId)->first();

        $projects = collect();

        // Contractor: projects they were selected for
        if ($contractor) {
            $contractorProjects = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->leftJoin('contractor_types as ct', 'p.type_id', '=', 'ct.type_id')
                ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->where('p.selected_contractor_id', $contractor->contractor_id)
                ->where('p.project_status', 'completed')
                ->select(
                    'p.project_id',
                    'p.project_title',
                    'p.project_description',
                    'p.project_location',
                    'p.budget_range_min',
                    'p.budget_range_max',
                    'p.property_type',
                    'ct.type_name as contractor_type_name',
                    DB::raw("CONCAT(po.first_name, ' ', po.last_name) as owner_name"),
                    'pr.created_at as completed_at'
                )
                ->orderByDesc('pr.created_at')
                ->get();
            $projects = $projects->merge($contractorProjects);
        }

        // Owner: projects they own that are completed
        if ($owner) {
            $ownerProjects = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->leftJoin('contractor_types as ct', 'p.type_id', '=', 'ct.type_id')
                ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->where('pr.owner_id', $owner->owner_id)
                ->where('p.project_status', 'completed')
                ->select(
                    'p.project_id',
                    'p.project_title',
                    'p.project_description',
                    'p.project_location',
                    'p.budget_range_min',
                    'p.budget_range_max',
                    'p.property_type',
                    'ct.type_name as contractor_type_name',
                    DB::raw("CONCAT(po.first_name, ' ', po.last_name) as owner_name"),
                    'pr.created_at as completed_at'
                )
                ->orderByDesc('pr.created_at')
                ->get();
            $projects = $projects->merge($ownerProjects);
        }

        // Deduplicate (in case user is both contractor and owner on the same project)
        $projects = $projects->unique('project_id')->values();

        // Mark already showcased
        $projects = $projects->map(function ($p) use ($showcasedIds) {
            $p->already_showcased = in_array($p->project_id, $showcasedIds);
            return $p;
        });

        return response()->json(['success' => true, 'data' => $projects]);
    }

    /* ─── Auth helper ──────────────────────────────────────────────── */

    private function resolveUserId(Request $request): ?int
    {
        $user = Session::get('user') ?: $request->user();
        if (!$user && $request->bearerToken()) {
            try {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
                if ($token && $token->tokenable) $user = $token->tokenable;
            } catch (\Throwable $e) {
                Log::warning('postController bearer fallback: ' . $e->getMessage());
            }
        }
        if (!$user) return null;
        return is_object($user) ? ($user->user_id ?? $user->id ?? null) : ($user['user_id'] ?? null);
    }
}
