<?php

namespace App\Models\admin;

use Illuminate\Support\Facades\DB;

/**
 * paymentAdjustmentClass
 * 
 * Handles payment allocation logic, overpayment/underpayment tracking,
 * and carry-forward calculations for milestone payments.
 * 
 * Aligned with mobile implementation in app/Services/milestoneService.php
 */
class paymentAdjustmentClass
{
    /**
     * Process payment allocation after approval
     * 
     * @param int $paymentId
     * @param int $itemId
     * @param int $projectId
     * @return array Status information about the payment allocation
     */
    public static function processPaymentAllocation(int $paymentId, int $itemId, int $projectId): array
    {
        $item = DB::table('milestone_items')->where('item_id', $itemId)->first();
        if (!$item) {
            return ['status' => 'error', 'message' => 'Milestone item not found.'];
        }

        // Expected = adjusted_cost (if set) or original milestone_item_cost
        $expectedAmount = (float) ($item->adjusted_cost ?? $item->milestone_item_cost);
        $originalCost = (float) $item->milestone_item_cost;

        // Sum all approved payments for this item
        $totalPaid = (float) DB::table('milestone_payments')
            ->where('item_id', $itemId)
            ->where('payment_status', 'approved')
            ->sum('amount');

        $difference = $totalPaid - $expectedAmount;

        // Determine status
        if (abs($difference) < 0.01) {
            // Exact payment — if item was completed with prior carry-forward, clear it
            if ($item->item_status === 'completed') {
                self::clearCarryForwardOnNextItem($item);
            }
            return [
                'status'     => 'exact',
                'total_paid' => $totalPaid,
                'expected'   => $expectedAmount,
                'difference' => 0,
            ];
        }

        if ($difference > 0) {
            // ── OVERPAYMENT ──
            // If item was completed with prior carry-forward, clear it (fully paid now)
            if ($item->item_status === 'completed') {
                self::clearCarryForwardOnNextItem($item);
            }
            
            // Record excess, do NOT cascade to next milestone
            self::logPaymentAdjustment([
                'project_id'          => $projectId,
                'milestone_id'        => $item->milestone_id,
                'source_item_id'      => $itemId,
                'target_item_id'      => null,
                'payment_id'          => $paymentId,
                'adjustment_type'     => 'overpayment',
                'original_required'   => $originalCost,
                'total_paid'          => $totalPaid,
                'adjustment_amount'   => $difference,
                'target_original_cost'=> null,
                'target_adjusted_cost'=> null,
                'notes'               => "Overpayment of " . number_format($difference, 2) . " recorded. Excess stays on this item.",
            ]);

            return [
                'status'      => 'overpaid',
                'total_paid'  => $totalPaid,
                'expected'    => $expectedAmount,
                'difference'  => $difference,
                'over_amount' => $difference,
            ];
        }

        // $difference < 0 means underpaid
        if ($item->item_status === 'completed') {
            // ── UNDERPAYMENT on a completed item ──
            $shortfall = abs($difference);

            // Find next sequential item in same milestone
            $nextItem = DB::table('milestone_items')
                ->where('milestone_id', $item->milestone_id)
                ->where('sequence_order', '>', $item->sequence_order)
                ->orderBy('sequence_order', 'asc')
                ->first();

            if ($nextItem) {
                // Set carry_forward_amount to the CURRENT shortfall
                $nextOriginalCost = (float) $nextItem->milestone_item_cost;
                $newAdjustedCost = $nextOriginalCost + $shortfall;

                DB::table('milestone_items')
                    ->where('item_id', $nextItem->item_id)
                    ->update([
                        'adjusted_cost'         => $newAdjustedCost,
                        'carry_forward_amount'  => $shortfall,
                        'updated_at'            => now(),
                    ]);

                // Log the carry-forward
                self::logPaymentAdjustment([
                    'project_id'          => $projectId,
                    'milestone_id'        => $item->milestone_id,
                    'source_item_id'      => $itemId,
                    'target_item_id'      => $nextItem->item_id,
                    'payment_id'          => $paymentId,
                    'adjustment_type'     => 'underpayment',
                    'original_required'   => $originalCost,
                    'total_paid'          => $totalPaid,
                    'adjustment_amount'   => $shortfall,
                    'target_original_cost'=> $nextOriginalCost,
                    'target_adjusted_cost'=> $newAdjustedCost,
                    'notes'               => "Carry-forward applied. Shortfall of " . number_format($shortfall, 2) . " added to item #{$nextItem->sequence_order}.",
                ]);

                return [
                    'status'              => 'underpaid',
                    'total_paid'          => $totalPaid,
                    'expected'            => $expectedAmount,
                    'difference'          => $difference,
                    'shortfall'           => $shortfall,
                    'carried_to_item_id'  => $nextItem->item_id,
                    'carried_to_title'    => $nextItem->milestone_item_title,
                    'new_adjusted_cost'   => $newAdjustedCost,
                ];
            }

            // No next item — last milestone, just log the shortfall
            self::logPaymentAdjustment([
                'project_id'          => $projectId,
                'milestone_id'        => $item->milestone_id,
                'source_item_id'      => $itemId,
                'target_item_id'      => null,
                'payment_id'          => $paymentId,
                'adjustment_type'     => 'underpayment',
                'original_required'   => $originalCost,
                'total_paid'          => $totalPaid,
                'adjustment_amount'   => abs($difference),
                'target_original_cost'=> null,
                'target_adjusted_cost'=> null,
                'notes'               => "Shortfall of " . number_format(abs($difference), 2) . " on last item. No next item to carry forward.",
            ]);

            return [
                'status'     => 'underpaid',
                'total_paid' => $totalPaid,
                'expected'   => $expectedAmount,
                'difference' => $difference,
                'shortfall'  => abs($difference),
                'carried_to_item_id' => null,
            ];
        }

        // Not completed yet — just partial, no carry-forward yet
        return [
            'status'     => 'partial',
            'total_paid' => $totalPaid,
            'expected'   => $expectedAmount,
            'difference' => $difference,
            'remaining'  => abs($difference),
        ];
    }

    /**
     * Insert a row into payment_adjustment_logs
     * 
     * @param array $data
     * @return void
     */
    private static function logPaymentAdjustment(array $data): void
    {
        DB::table('payment_adjustment_logs')->insert(array_merge($data, [
            'created_at' => now(),
        ]));
    }

    /**
     * Clear any carry-forward that was previously applied to the next item
     * 
     * @param object $item
     * @return void
     */
    private static function clearCarryForwardOnNextItem(object $item): void
    {
        $nextItem = DB::table('milestone_items')
            ->where('milestone_id', $item->milestone_id)
            ->where('sequence_order', '>', $item->sequence_order)
            ->orderBy('sequence_order', 'asc')
            ->first();

        if ($nextItem && (float) ($nextItem->carry_forward_amount ?? 0) > 0) {
            $nextOriginalCost = (float) $nextItem->milestone_item_cost;

            DB::table('milestone_items')
                ->where('item_id', $nextItem->item_id)
                ->update([
                    'adjusted_cost'        => $nextOriginalCost,
                    'carry_forward_amount' => 0,
                    'updated_at'           => now(),
                ]);

            // Log the clearing
            self::logPaymentAdjustment([
                'project_id'          => DB::table('milestones')->where('milestone_id', $item->milestone_id)->value('project_id'),
                'milestone_id'        => $item->milestone_id,
                'source_item_id'      => $item->item_id,
                'target_item_id'      => $nextItem->item_id,
                'payment_id'          => null,
                'adjustment_type'     => 'carry_forward_cleared',
                'original_required'   => $nextOriginalCost,
                'total_paid'          => 0,
                'adjustment_amount'   => -($nextItem->carry_forward_amount),
                'target_original_cost'=> $nextOriginalCost,
                'target_adjusted_cost'=> $nextOriginalCost,
                'notes'               => "Carry-forward cleared. Source item now fully paid.",
            ]);
        }
    }

    /**
     * Get payment summary for a milestone item
     * 
     * @param int $itemId
     * @return array
     */
    public static function getItemPaymentSummary(int $itemId): array
    {
        $item = DB::table('milestone_items')->where('item_id', $itemId)->first();
        if (!$item) {
            return ['error' => 'Item not found'];
        }

        $expectedAmount = (float) ($item->adjusted_cost ?? $item->milestone_item_cost);
        $originalCost = (float) $item->milestone_item_cost;
        $carryForward = (float) ($item->carry_forward_amount ?? 0);

        $totalPaid = (float) DB::table('milestone_payments')
            ->where('item_id', $itemId)
            ->where('payment_status', 'approved')
            ->sum('amount');

        $payments = DB::table('milestone_payments')
            ->where('item_id', $itemId)
            ->whereIn('payment_status', ['approved', 'submitted', 'pending'])
            ->orderBy('transaction_date', 'desc')
            ->get();

        $adjustments = DB::table('payment_adjustment_logs')
            ->where(function($q) use ($itemId) {
                $q->where('source_item_id', $itemId)
                  ->orWhere('target_item_id', $itemId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'item_id'          => $itemId,
            'item_title'       => $item->milestone_item_title,
            'original_cost'    => $originalCost,
            'carry_forward'    => $carryForward,
            'adjusted_cost'    => $expectedAmount,
            'total_paid'       => $totalPaid,
            'remaining'        => max(0, $expectedAmount - $totalPaid),
            'status'           => self::derivePaymentStatus($totalPaid, $expectedAmount),
            'payments'         => $payments,
            'adjustments'      => $adjustments,
        ];
    }

    /**
     * Derive payment status based on amounts
     * 
     * @param float $totalPaid
     * @param float $expectedAmount
     * @return string
     */
    private static function derivePaymentStatus(float $totalPaid, float $expectedAmount): string
    {
        $difference = $totalPaid - $expectedAmount;

        if (abs($difference) < 0.01) {
            return 'fully_paid';
        } elseif ($difference > 0) {
            return 'overpaid';
        } elseif ($totalPaid > 0) {
            return 'partially_paid';
        } else {
            return 'unpaid';
        }
    }
}
