<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use App\Services\ProjectUpdateService;
use Illuminate\Http\Request;

/**
 * projectUpdateController — Enhanced v2
 *
 * Contractor-only:
 *   GET  /projects/{id}/update/context          — overview + milestone items
 *   POST /projects/{id}/update/preview          — full preview (budget + milestones)
 *   POST /projects/{id}/update                  — submit
 *   POST /projects/{id}/updates/{eid}/withdraw  — withdraw pending
 *
 * Owner-only:
 *   POST /projects/{id}/updates/{eid}/approve            — approve + apply
 *   POST /projects/{id}/updates/{eid}/reject             — reject
 *   POST /projects/{id}/updates/{eid}/request-changes    — request revision
 *
 * Shared:
 *   GET  /projects/{id}/updates                  — list history
 *   GET  /projects/{id}/update/milestone-items   — items + payment status
 */
class projectUpdateController extends Controller
{
    public function __construct(private ProjectUpdateService $service) {}

    // ── context ──────────────────────────────────────────────────────────
    public function context(Request $request, int $projectId)
    {
        $result = $this->service->getProjectContext($projectId);
        return response()->json($result, $result['success'] ? 200 : 404);
    }

    // ── milestone items (standalone, useful for lazy loading) ────────────
    public function milestoneItems(Request $request, int $projectId)
    {
        $items = $this->service->getMilestoneItemsWithPayments($projectId);
        return response()->json(['success' => true, 'data' => $items]);
    }

    // ── preview (enhanced) ───────────────────────────────────────────────
    public function preview(Request $request, int $projectId)
    {
        $this->sanitizeNumericFields($request);

        $validated = $request->validate([
            'proposed_end_date'      => 'nullable|date|after:today',
            'proposed_budget'        => 'nullable|numeric|min:0',
            'allocation_mode'        => 'nullable|in:percentage,exact',
            'new_items'              => 'nullable|array',
            'new_items.*.title'      => 'required_with:new_items|string|max:255',
            'new_items.*.description'=> 'nullable|string|max:2000',
            'new_items.*.cost'       => 'nullable|numeric|min:0',
            'new_items.*.percentage' => 'nullable|numeric|min:0|max:100',
            'new_items.*.start_date' => 'nullable|date',
            'new_items.*.due_date'   => 'nullable|date',
            'new_items.*.attachments'=> 'nullable|array',
            'edited_items'           => 'nullable|array',
            'edited_items.*.item_id' => 'required_with:edited_items|integer',
            'edited_items.*.cost'    => 'nullable|numeric|min:0',
            'edited_items.*.percentage'=> 'nullable|numeric|min:0|max:100',
            'edited_items.*.title'   => 'nullable|string|max:255',
            'edited_items.*.start_date'=> 'nullable|date',
            'edited_items.*.due_date'=> 'nullable|date',
            'deleted_item_ids'       => 'nullable|array',
            'deleted_item_ids.*'     => 'integer',
        ]);

        $result = $this->service->preview($projectId, $validated);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    // ── list ─────────────────────────────────────────────────────────────
    public function index(Request $request, int $projectId)
    {
        $extensions = $this->service->listByProject($projectId);
        return response()->json(['success' => true, 'data' => $extensions]);
    }

    // ── submit (contractor) ──────────────────────────────────────────────
    public function store(Request $request, int $projectId)
    {
        $this->sanitizeNumericFields($request);

        $validated = $request->validate([
            'user_id'                => 'required|integer',
            'proposed_end_date'      => 'nullable|date|after:today',
            'reason'                 => 'required|string|min:20|max:2000',
            'proposed_budget'        => 'nullable|numeric|min:0',
            'allocation_mode'        => 'nullable|in:percentage,exact',
            'new_items'              => 'nullable|array',
            'new_items.*.title'      => 'required_with:new_items|string|max:255',
            'new_items.*.description'=> 'nullable|string|max:2000',
            'new_items.*.cost'       => 'nullable|numeric|min:0',
            'new_items.*.percentage' => 'nullable|numeric|min:0|max:100',
            'new_items.*.start_date' => 'nullable|date',
            'new_items.*.due_date'   => 'nullable|date',
            'new_items.*.attachments'=> 'nullable|array',
            'edited_items'           => 'nullable|array',
            'edited_items.*.item_id' => 'required_with:edited_items|integer',
            'edited_items.*.cost'    => 'nullable|numeric|min:0',
            'edited_items.*.percentage'=> 'nullable|numeric|min:0|max:100',
            'edited_items.*.title'   => 'nullable|string|max:255',
            'edited_items.*.start_date'=> 'nullable|date',
            'edited_items.*.due_date'=> 'nullable|date',
            'deleted_item_ids'       => 'nullable|array',
            'deleted_item_ids.*'     => 'integer',
            // Backward compat
            'has_additional_cost'    => 'nullable|boolean',
            'additional_amount'      => 'nullable|numeric|min:0',
        ]);

        $result = $this->service->submit(
            $projectId,
            (int) $validated['user_id'],
            $validated
        );

        return response()->json($result, $result['success'] ? 201 : 422);
    }

    // ── approve (owner) ──────────────────────────────────────────────────
    public function approve(Request $request, int $projectId, int $extensionId)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'note'    => 'nullable|string|max:1000',
        ]);

        $result = $this->service->approve(
            $extensionId,
            (int) $validated['user_id'],
            $validated['note'] ?? null
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    // ── reject (owner) ───────────────────────────────────────────────────
    public function reject(Request $request, int $projectId, int $extensionId)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'reason'  => 'required|string|min:5|max:1000',
        ]);

        $result = $this->service->reject(
            $extensionId,
            (int) $validated['user_id'],
            $validated['reason']
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    // ── request changes (owner) ──────────────────────────────────────────
    public function requestChanges(Request $request, int $projectId, int $extensionId)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'notes'   => 'required|string|min:10|max:2000',
        ]);

        $result = $this->service->requestChanges(
            $extensionId,
            (int) $validated['user_id'],
            $validated['notes']
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    // ── withdraw (contractor) ────────────────────────────────────────────
    public function withdraw(Request $request, int $projectId, int $extensionId)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $result = $this->service->withdraw($extensionId, (int) $validated['user_id']);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    // ── Sanitize: strip commas from numeric fields before validation ──────
    private function sanitizeNumericFields(Request $request): void
    {
        $strip = fn ($v) => is_string($v) ? str_replace(',', '', $v) : $v;

        if ($request->has('proposed_budget')) {
            $request->merge(['proposed_budget' => $strip($request->input('proposed_budget'))]);
        }
        if ($request->has('additional_amount')) {
            $request->merge(['additional_amount' => $strip($request->input('additional_amount'))]);
        }

        foreach (['new_items', 'edited_items'] as $group) {
            $items = $request->input($group);
            if (!is_array($items)) continue;
            foreach ($items as $i => $item) {
                if (isset($item['cost'])) {
                    $items[$i]['cost'] = $strip($item['cost']);
                }
                if (isset($item['percentage'])) {
                    $items[$i]['percentage'] = $strip($item['percentage']);
                }
            }
            $request->merge([$group => $items]);
        }
    }
}
