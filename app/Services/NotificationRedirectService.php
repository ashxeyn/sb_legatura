<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Resolves the correct redirect URL for a notification based on its
 * reference_type + reference_id (and optionally the granular sub-type stored
 * in action_link JSON).
 *
 * Design decisions:
 * - `action_link` is treated as a **precomputed cache / optional fallback**.
 *   The canonical routing logic lives here, computed at click-time so it
 *   always reflects the current state of referenced entities.
 * - Each reference_type has a dedicated resolver method that fetches only the
 *   parent IDs it needs (e.g. milestone → project_id).
 * - If the referenced record has been deleted or is inaccessible, the service
 *   returns a safe fallback (dashboard) with a human-readable flash message.
 *
 * Adding a new notification type:
 * 1. Add a case to resolveByReferenceType() or resolveBySubType().
 * 2. If it needs parent-ID lookup, add a private resolve*() helper.
 * 3. That's it — the controller and frontend need no changes.
 */
class NotificationRedirectService
{
    // ─── Public entry point ────────────────────────────────────────────

    /**
     * Resolve the redirect target for a notification row.
     *
     * @param  object $notification  DB row from `notifications` table
     * @param  string $userRole      'property_owner' | 'contractor' | 'admin'
     * @return array{url: string, flash: string|null}
     */
    public static function resolve(object $notification, string $userRole): array
    {
        $subType = self::extractSubType($notification);

        // 1. Try sub-type–specific routing first (most precise)
        $result = self::resolveBySubType($subType, $notification, $userRole);
        if ($result !== null) {
            return $result;
        }

        // 2. Fall back to reference_type routing
        $result = self::resolveByReferenceType(
            $notification->reference_type,
            $notification->reference_id,
            $userRole
        );
        if ($result !== null) {
            return $result;
        }

        // 3. Ultimate fallback — dashboard
        return self::dashboardFallback($userRole, 'The referenced item could not be found.');
    }

    /**
     * Resolve redirect and return an API-friendly payload (for mobile clients
     * that need the URL without a 302 redirect).
     *
     * Includes a `mobile` key with `screen` + `params` so mobile apps can
     * navigate without parsing URL strings.
     */
    public static function resolveForApi(object $notification, string $userRole): array
    {
        $resolved = self::resolve($notification, $userRole);
        $subType  = self::extractSubType($notification);

        return [
            'redirect_url'   => $resolved['url'],
            'flash_message'  => $resolved['flash'],
            'reference_type' => $notification->reference_type,
            'reference_id'   => $notification->reference_id,
            'mobile'         => self::resolveForMobile($resolved['url'], $subType, $notification, $userRole),
        ];
    }

    /**
     * Map a notification directly to a mobile-friendly {screen, params} object.
     *
     * Uses the precomputed action_link data (screen, params.projectId, params.tab)
     * instead of parsing URL strings. Falls back to resolveProjectIdFromRef()
     * only when action_link lacks a project_id.
     *
     * Mobile screen names:
     *   'dashboard', 'messages', 'profile', 'home'
     *
     * Dashboard sub_screens:
     *   'project_detail', 'my_bids', 'members'
     *
     * initial_action (contractor MyProjects):
     *   'project_timeline', 'milestone_setup', 'project_detail'
     *
     * initial_section (owner/contractor ProjectDetails):
     *   'bids', 'milestones'
     */
    private static function resolveForMobile(string $url, string $subType, object $notification, string $userRole): array
    {
        // Extract precomputed navigation data from action_link JSON
        $actionData = [];
        if ($notification->action_link) {
            $actionData = json_decode($notification->action_link, true) ?? [];
        }
        $projectId = $actionData['params']['projectId'] ?? null;
        $disputeId = $actionData['params']['disputeId'] ?? null;

        switch ($subType) {
            // ── Milestone lifecycle → project timeline / milestones section ──
            case 'milestone_submitted':
            case 'milestone_approved':
            case 'milestone_rejected':
            case 'milestone_completed':
            case 'milestone_item_completed':
            case 'milestone_deleted':
            case 'milestone_resubmitted':
            case 'milestone_updated':
                if (!$projectId) {
                    $projectId = self::resolveProjectIdFromRef($notification);
                }
                return [
                    'screen' => 'dashboard',
                    'params' => [
                        'sub_screen'      => 'project_detail',
                        'project_id'      => $projectId,
                        'initial_action'  => 'project_timeline',
                        'initial_section' => 'milestones',
                    ],
                ];

            // ── Payment lifecycle → project timeline → specific item → payments tab ──
            case 'payment_submitted':
            case 'payment_approved':
            case 'payment_rejected':
            case 'payment_fully_paid':
            case 'payment_overpaid':
            case 'payment_underpaid_carry':
            case 'payment_due':
            case 'payment_overdue':
                if (!$projectId) {
                    $projectId = self::resolveProjectIdFromRef($notification);
                }
                // Resolve the milestone item ID so the mobile app can drill
                // straight into the item's full-detail payments tab.
                $itemId = ($notification->reference_type === 'milestone_item')
                    ? $notification->reference_id
                    : null;
                return [
                    'screen' => 'dashboard',
                    'params' => [
                        'sub_screen'      => 'project_detail',
                        'project_id'      => $projectId,
                        'initial_action'  => 'project_timeline',
                        'initial_section' => 'milestones',
                        'initial_item_id' => $itemId,
                        'initial_item_tab' => 'payments',
                    ],
                ];

            // ── Progress updates → project timeline ──
            case 'progress_submitted':
            case 'progress_approved':
            case 'progress_rejected':
            case 'progress_updated':
                if (!$projectId) {
                    $projectId = self::resolveProjectIdFromRef($notification);
                }
                return [
                    'screen' => 'dashboard',
                    'params' => [
                        'sub_screen'      => 'project_detail',
                        'project_id'      => $projectId,
                        'initial_action'  => 'project_timeline',
                        'initial_section' => 'milestones',
                    ],
                ];

            // ── Bid received → owner sees project bids ──
            case 'bid_received':
                if (!$projectId) {
                    $projectId = self::resolveProjectIdFromRef($notification);
                }
                return [
                    'screen' => 'dashboard',
                    'params' => [
                        'sub_screen'      => 'project_detail',
                        'project_id'      => $projectId,
                        'initial_section' => 'bids',
                    ],
                ];

            // ── Bid accepted/rejected → contractor sees my bids ──
            case 'bid_accepted':
            case 'bid_rejected':
                return [
                    'screen' => 'dashboard',
                    'params' => [
                        'sub_screen' => 'my_bids',
                    ],
                ];

            // ── Dispute events → project detail with dispute context ──
            case 'dispute_opened':
            case 'dispute_updated':
            case 'dispute_resolved':
            case 'dispute_cancelled':
            case 'dispute_under_review':
            case 'dispute_rejected':
                if (!$projectId) {
                    $projectId = self::resolveProjectIdFromRef($notification);
                }
                return [
                    'screen' => 'dashboard',
                    'params' => [
                        'sub_screen' => 'project_detail',
                        'project_id' => $projectId,
                        'dispute_id' => $disputeId ?? $notification->reference_id,
                    ],
                ];

            // ── Project-level alerts → project timeline ──
            case 'project_completed':
            case 'project_halted':
            case 'project_terminated':
            case 'project_update':
                $pid = $projectId ?? $notification->reference_id;
                return [
                    'screen' => 'dashboard',
                    'params' => [
                        'sub_screen'      => 'project_detail',
                        'project_id'      => $pid,
                        'initial_action'  => 'project_timeline',
                        'initial_section' => 'milestones',
                    ],
                ];

            // ── Team events → members screen (contractor) ──
            case 'team_member_added':
            case 'team_member_status':
            case 'team_invite':
            case 'team_removed':
            case 'team_role_changed':
            case 'team_access_changed':
                return [
                    'screen' => 'dashboard',
                    'params' => [
                        'sub_screen' => 'members',
                    ],
                ];

            default:
                return [
                    'screen' => 'dashboard',
                    'params' => [],
                ];
        }
    }

    /**
     * Resolve project_id from notification reference when action_link data
     * doesn't contain it. This is the only path that requires DB lookups.
     */
    private static function resolveProjectIdFromRef(object $notification): ?int
    {
        $refType = $notification->reference_type;
        $refId   = $notification->reference_id;

        if (!$refType || !$refId) {
            return null;
        }

        switch ($refType) {
            case 'project':
                return $refId;

            case 'milestone':
                $ms = DB::table('milestones')->where('milestone_id', $refId)->select('project_id')->first();
                return $ms?->project_id;

            case 'milestone_item':
                $item = DB::table('milestone_items as mi')
                    ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                    ->where('mi.item_id', $refId)
                    ->select('m.project_id')
                    ->first();
                return $item?->project_id;

            case 'payment':
                $pay = DB::table('milestone_payments as mp')
                    ->join('milestone_items as mi', 'mp.milestone_item_id', '=', 'mi.item_id')
                    ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                    ->where('mp.payment_id', $refId)
                    ->select('m.project_id')
                    ->first();
                return $pay?->project_id;

            case 'progress':
                $prog = DB::table('progress as p')
                    ->join('milestone_items as mi', 'p.milestone_item_id', '=', 'mi.item_id')
                    ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                    ->where('p.progress_id', $refId)
                    ->select('m.project_id')
                    ->first();
                return $prog?->project_id;

            case 'bid':
                $bid = DB::table('bids')->where('bid_id', $refId)->select('project_id')->first();
                return $bid?->project_id;

            case 'dispute':
                $dispute = DB::table('disputes')->where('dispute_id', $refId)->select('project_id')->first();
                return $dispute?->project_id;

            default:
                return null;
        }
    }

    // ─── Sub-type routing ──────────────────────────────────────────────

    /**
     * Route by the granular sub-type embedded in action_link JSON.
     * Returns null when the sub-type has no special routing (fall through to
     * reference_type routing).
     */
    private static function resolveBySubType(
        string  $subType,
        object  $notification,
        string  $userRole
    ): ?array {
        $refType = $notification->reference_type;
        $refId   = $notification->reference_id;

        switch ($subType) {
            // ── Milestone lifecycle → project page scrolled to milestone ──
            case 'milestone_submitted':
            case 'milestone_approved':
            case 'milestone_rejected':
            case 'milestone_completed':
            case 'milestone_item_completed':
            case 'milestone_deleted':
            case 'milestone_resubmitted':
            case 'milestone_updated':
                return self::resolveMilestoneRedirect($refType, $refId, $userRole);

            // ── Payment lifecycle → project page, payment section ──
            case 'payment_submitted':
            case 'payment_approved':
            case 'payment_rejected':
            case 'payment_updated':
            case 'payment_deleted':
            case 'payment_fully_paid':
            case 'payment_overpaid':
            case 'payment_underpaid_carry':
                return self::resolvePaymentRedirect($refType, $refId, $userRole);

            // ── Payment reminders → same as payment ──
            case 'payment_due':
            case 'payment_overdue':
                return self::resolvePaymentRedirect($refType, $refId, $userRole);

            // ── Progress updates → project milestone report ──
            case 'progress_submitted':
            case 'progress_approved':
            case 'progress_rejected':
            case 'progress_updated':
                return self::resolveProgressRedirect($refType, $refId, $userRole);

            // ── Bid events → project bids page (owner) or my-bids (contractor) ──
            case 'bid_accepted':
            case 'bid_rejected':
                return self::resolveBidRedirect($refType, $refId, 'contractor');

            case 'bid_received':
                return self::resolveBidRedirect($refType, $refId, 'property_owner');

            // ── Dispute events → dispute detail page ──
            case 'dispute_opened':
            case 'dispute_updated':
            case 'dispute_cancelled':
            case 'dispute_under_review':
            case 'dispute_resolved':
            case 'dispute_rejected':
                return self::resolveDisputeRedirect($refId, $userRole);

            // ── Project-level alerts → project page ──
            case 'project_completed':
            case 'project_halted':
            case 'project_terminated':
            case 'project_update':
                return self::resolveProjectRedirect($refId, $userRole);

            // ── Team events → project page ──
            case 'team_invite':
            case 'team_removed':
            case 'team_role_changed':
            case 'team_access_changed':
                return self::resolveProjectRedirect($refId, $userRole);

            default:
                return null; // fall through to reference_type routing
        }
    }

    // ─── Reference-type routing (generic fallback) ─────────────────────

    /**
     * Route by reference_type when sub-type routing doesn't match or isn't set.
     */
    private static function resolveByReferenceType(
        ?string $referenceType,
        ?int    $referenceId,
        string  $userRole
    ): ?array {
        if (!$referenceType || !$referenceId) {
            return null;
        }

        switch ($referenceType) {
            case 'project':
                return self::resolveProjectRedirect($referenceId, $userRole);

            case 'milestone':
                return self::resolveMilestoneRedirect($referenceType, $referenceId, $userRole);

            case 'milestone_item':
                return self::resolveMilestoneItemRedirect($referenceId, $userRole);

            case 'payment':
                return self::resolvePaymentRedirect($referenceType, $referenceId, $userRole);

            case 'bid':
                return self::resolveBidRedirect($referenceType, $referenceId, $userRole);

            case 'dispute':
                return self::resolveDisputeRedirect($referenceId, $userRole);

            case 'conversation':
                return self::resolveConversationRedirect($referenceId, $userRole);

            case 'review':
                return self::resolveReviewRedirect($referenceId, $userRole);

            case 'user':
                return self::resolveProfileRedirect($referenceId, $userRole);

            default:
                Log::warning('NotificationRedirectService: unknown reference_type', [
                    'reference_type' => $referenceType,
                    'reference_id'   => $referenceId,
                ]);
                return null;
        }
    }

    // ─── Individual resolvers ──────────────────────────────────────────

    /**
     * Milestone → look up project_id, then redirect to project page.
     */
    private static function resolveMilestoneRedirect(
        ?string $refType,
        ?int    $refId,
        string  $userRole
    ): array {
        if (!$refId) {
            return self::dashboardFallback($userRole, 'Milestone reference is missing.');
        }

        // If reference_type is 'milestone', resolve milestone → project
        if ($refType === 'milestone') {
            $milestone = DB::table('milestones')
                ->where('milestone_id', $refId)
                ->select('project_id')
                ->first();

            if (!$milestone) {
                return self::dashboardFallback($userRole, 'The milestone no longer exists.');
            }

            $projectId = $milestone->project_id;
            $prefix = self::rolePrefix($userRole);
            return [
                'url'   => "/{$prefix}/projects/milestone-report?project_id={$projectId}&milestone_id={$refId}",
                'flash' => null,
            ];
        }

        // reference_type is 'project' but sub-type is milestone-related
        // refId is the project_id in this case
        if ($refType === 'project') {
            $project = DB::table('projects')->where('project_id', $refId)->first();
            if (!$project) {
                return self::dashboardFallback($userRole, 'The project no longer exists.');
            }
            $prefix = self::rolePrefix($userRole);
            return [
                'url'   => "/{$prefix}/projects/milestone-report?project_id={$refId}",
                'flash' => null,
            ];
        }

        // Fallback: treat refId as milestone_id
        $milestone = DB::table('milestones')
            ->where('milestone_id', $refId)
            ->select('project_id')
            ->first();

        if (!$milestone) {
            return self::dashboardFallback($userRole, 'The milestone no longer exists.');
        }

        $prefix = self::rolePrefix($userRole);
        return [
            'url'   => "/{$prefix}/projects/milestone-report?project_id={$milestone->project_id}&milestone_id={$refId}",
            'flash' => null,
        ];
    }

    /**
     * Milestone item → look up milestone → project, then redirect.
     */
    private static function resolveMilestoneItemRedirect(int $itemId, string $userRole): array
    {
        $item = DB::table('milestone_items')
            ->where('item_id', $itemId)
            ->select('milestone_id')
            ->first();

        if (!$item) {
            return self::dashboardFallback($userRole, 'The milestone item no longer exists.');
        }

        $milestone = DB::table('milestones')
            ->where('milestone_id', $item->milestone_id)
            ->select('project_id')
            ->first();

        if (!$milestone) {
            return self::dashboardFallback($userRole, 'The related milestone no longer exists.');
        }

        $prefix = self::rolePrefix($userRole);
        return [
            'url'   => "/{$prefix}/projects/milestone-report?project_id={$milestone->project_id}&milestone_id={$item->milestone_id}&item_id={$itemId}",
            'flash' => null,
        ];
    }

    /**
     * Payment → resolve through milestone_item or milestone to project.
     */
    private static function resolvePaymentRedirect(?string $refType, ?int $refId, string $userRole): array
    {
        if (!$refId) {
            return self::dashboardFallback($userRole, 'Payment reference is missing.');
        }

        // If reference_type is 'milestone_item', look up via item
        if ($refType === 'milestone_item') {
            return self::resolveMilestoneItemRedirect($refId, $userRole);
        }

        // If reference_type is 'milestone', resolve to project
        if ($refType === 'milestone') {
            return self::resolveMilestoneRedirect($refType, $refId, $userRole);
        }

        // If reference_type is 'project', go directly
        if ($refType === 'project') {
            return self::resolveProjectRedirect($refId, $userRole);
        }

        // If reference_type is 'payment', look up the payment record
        if ($refType === 'payment') {
            $payment = DB::table('milestone_payments')
                ->where('payment_id', $refId)
                ->select('milestone_item_id')
                ->first();

            if (!$payment || !$payment->milestone_item_id) {
                return self::dashboardFallback($userRole, 'The payment record no longer exists.');
            }

            return self::resolveMilestoneItemRedirect($payment->milestone_item_id, $userRole);
        }

        return self::dashboardFallback($userRole, 'Could not resolve payment location.');
    }

    /**
     * Progress → resolve through milestone or milestone_item to project page.
     */
    private static function resolveProgressRedirect(?string $refType, ?int $refId, string $userRole): array
    {
        if (!$refId) {
            return self::dashboardFallback($userRole, 'Progress reference is missing.');
        }

        // Progress notifications typically reference milestone_item or milestone
        if ($refType === 'milestone_item') {
            return self::resolveMilestoneItemRedirect($refId, $userRole);
        }

        if ($refType === 'milestone') {
            return self::resolveMilestoneRedirect($refType, $refId, $userRole);
        }

        if ($refType === 'project') {
            $prefix = self::rolePrefix($userRole);
            return [
                'url'   => "/{$prefix}/projects/milestone-progress-report?project_id={$refId}",
                'flash' => null,
            ];
        }

        // Try treating refId as a progress record
        $progress = DB::table('progress')
            ->where('progress_id', $refId)
            ->select('milestone_item_id')
            ->first();

        if ($progress && $progress->milestone_item_id) {
            return self::resolveMilestoneItemRedirect($progress->milestone_item_id, $userRole);
        }

        return self::dashboardFallback($userRole, 'The progress record no longer exists.');
    }

    /**
     * Bid → project bids page (owner) or my-bids (contractor).
     */
    private static function resolveBidRedirect(?string $refType, ?int $refId, string $userRole): array
    {
        if (!$refId) {
            return self::dashboardFallback($userRole, 'Bid reference is missing.');
        }

        // refId is typically project_id when reference_type = 'project'
        if ($refType === 'project') {
            if ($userRole === 'property_owner') {
                return [
                    'url'   => "/owner/projects/{$refId}/bids",
                    'flash' => null,
                ];
            }
            // Contractor sees their bids list
            return [
                'url'   => '/contractor/mybids',
                'flash' => null,
            ];
        }

        // refType = 'bid' → look up the bid's project_id
        if ($refType === 'bid') {
            $bid = DB::table('bids')
                ->where('bid_id', $refId)
                ->select('project_id')
                ->first();

            if (!$bid) {
                return self::dashboardFallback($userRole, 'The bid no longer exists.');
            }

            if ($userRole === 'property_owner') {
                return [
                    'url'   => "/owner/projects/{$bid->project_id}/bids",
                    'flash' => null,
                ];
            }
            return [
                'url'   => '/contractor/mybids',
                'flash' => null,
            ];
        }

        return self::dashboardFallback($userRole, 'Could not resolve bid location.');
    }

    /**
     * Dispute → dispute detail page.
     */
    private static function resolveDisputeRedirect(?int $refId, string $userRole): array
    {
        if (!$refId) {
            return self::dashboardFallback($userRole, 'Dispute reference is missing.');
        }

        $dispute = DB::table('disputes')
            ->where('dispute_id', $refId)
            ->first();

        if (!$dispute) {
            return self::dashboardFallback($userRole, 'The dispute no longer exists.');
        }

        return [
            'url'   => "/both/disputes/{$refId}",
            'flash' => null,
        ];
    }

    /**
     * Conversation → messages page.
     */
    private static function resolveConversationRedirect(int $conversationId, string $userRole): array
    {
        $conversation = DB::table('conversations')
            ->where('conversation_id', $conversationId)
            ->first();

        if (!$conversation) {
            return self::dashboardFallback($userRole, 'The conversation no longer exists.');
        }

        $prefix = self::rolePrefix($userRole);
        return [
            'url'   => "/{$prefix}/messages?conversation={$conversationId}",
            'flash' => null,
        ];
    }

    /**
     * Review → user profile with reviews tab.
     */
    private static function resolveReviewRedirect(int $reviewId, string $userRole): array
    {
        $prefix = self::rolePrefix($userRole);
        return [
            'url'   => "/{$prefix}/profile?tab=reviews",
            'flash' => null,
        ];
    }

    /**
     * Project → project page (role-aware).
     */
    private static function resolveProjectRedirect(?int $projectId, string $userRole): array
    {
        if (!$projectId) {
            return self::dashboardFallback($userRole, 'Project reference is missing.');
        }

        $project = DB::table('projects')
            ->where('project_id', $projectId)
            ->first();

        if (!$project) {
            return self::projectListFallback($userRole, 'The project no longer exists.');
        }

        // Check if project is archived/deleted
        $relationship = DB::table('project_relationships')
            ->where('rel_id', $project->relationship_id)
            ->first();

        if ($relationship && $relationship->project_post_status === 'deleted') {
            return self::projectListFallback($userRole, 'This project has been archived.');
        }

        $prefix = self::rolePrefix($userRole);
        return [
            'url'   => "/{$prefix}/projects/milestone-report?project_id={$projectId}",
            'flash' => null,
        ];
    }

    /**
     * Profile → user profile page.
     */
    private static function resolveProfileRedirect(int $userId, string $userRole): array
    {
        $prefix = self::rolePrefix($userRole);
        return [
            'url'   => "/{$prefix}/profile",
            'flash' => null,
        ];
    }

    // ─── Helpers ───────────────────────────────────────────────────────

    /**
     * Extract the granular sub-type from the action_link JSON.
     */
    private static function extractSubType(object $notification): string
    {
        if ($notification->action_link) {
            $decoded = json_decode($notification->action_link, true);
            if (is_array($decoded) && isset($decoded['notification_sub_type'])) {
                return $decoded['notification_sub_type'];
            }
        }
        return 'general';
    }

    /**
     * Map user role to URL path prefix.
     */
    private static function rolePrefix(string $userRole): string
    {
        return match ($userRole) {
            'contractor' => 'contractor',
            'admin'      => 'admin',
            default      => 'owner',   // property_owner and fallback
        };
    }

    /**
     * Fallback: redirect to role-appropriate dashboard with a flash message.
     */
    private static function dashboardFallback(string $userRole, string $message): array
    {
        $prefix = self::rolePrefix($userRole);
        return [
            'url'   => "/{$prefix}/dashboard",
            'flash' => $message,
        ];
    }

    /**
     * Fallback: redirect to role-appropriate project list with a flash message.
     */
    private static function projectListFallback(string $userRole, string $message): array
    {
        $prefix = self::rolePrefix($userRole);
        return [
            'url'   => "/{$prefix}/projects",
            'flash' => $message,
        ];
    }
}
