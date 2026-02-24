<?php

namespace App\Models\contractor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class progressUploadClass
{
    public function createProgress($data)
    {
        try {
            $progressId = DB::table('progress')->insertGetId([
                'milestone_item_id' => $data['item_id'],
                'purpose' => $data['purpose'],
                'progress_status' => $data['progress_status'] ?? 'submitted'
            ]);

            if (!$progressId) {
                throw new \Exception('Failed to create progress entry in database');
            }

            return $progressId;
        } catch (\Exception $e) {
            Log::error('createProgress error: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function createProgressFile($data)
    {
        try {
            $fileId = DB::table('progress_files')->insertGetId([
                'progress_id' => $data['progress_id'],
                'file_path' => $data['file_path'],
                'original_name' => $data['original_name'] ?? null
            ]);

            if (!$fileId) {
                throw new \Exception('Failed to create progress file entry in database');
            }

            return $fileId;
        } catch (\Exception $e) {
            Log::error('createProgressFile error: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getProgressByItem($itemId, $contractorId = null)
    {
        $query = DB::table('progress as p')
            ->join('milestone_items as mi', 'p.milestone_item_id', '=', 'mi.item_id')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->join('projects as proj', 'm.project_id', '=', 'proj.project_id')
            ->where('p.milestone_item_id', $itemId)
            ->where('p.progress_status', '!=', 'deleted');

        if ($contractorId) {
            $query->where('proj.selected_contractor_id', $contractorId);
        }

        $select = [
            'p.progress_id',
            'p.milestone_item_id as item_id',
            'p.purpose',
            'p.progress_status',
            'p.submitted_at',
            'proj.selected_contractor_id as contractor_id'
        ];

        // Include delete/rejection reason if present in schema
        if (Schema::hasColumn('progress', 'delete_reason')) {
            $select[] = 'p.delete_reason';
        } elseif (Schema::hasColumn('progress', 'rejection_reason')) {
            $select[] = 'p.rejection_reason as delete_reason';
        }

        return $query
            ->select($select)
            ->orderBy('p.submitted_at', 'desc')
            ->get();
    }

    public function getProgressFilesByItem($itemId, $contractorId = null)
    {
        $progressList = $this->getProgressByItem($itemId, $contractorId);

        $result = [];
        foreach ($progressList as $progress) {
            $files = $this->getProgressFiles($progress->progress_id);
            $progress->files = $files;
            $result[] = $progress;
        }

        return $result;
    }

    public function getProgressFiles($progressId)
    {
        return DB::table('progress_files')
            ->where('progress_id', $progressId)
            ->select(
                'file_id',
                'progress_id',
                'file_path',
                'original_name'
            )
            ->get();
    }

    public function getProgressById($progressId)
    {
        $select = [
            'progress_id',
            'milestone_item_id as item_id',
            'purpose',
            'progress_status',
            'submitted_at'
        ];

        if (Schema::hasColumn('progress', 'delete_reason')) {
            $select[] = 'delete_reason';
        } elseif (Schema::hasColumn('progress', 'rejection_reason')) {
            $select[] = 'rejection_reason as delete_reason';
        }

        return DB::table('progress')
            ->where('progress_id', $progressId)
            ->select($select)
            ->first();
    }

    public function getProgressWithFiles($progressId)
    {
        try {
            $progress = $this->getProgressById($progressId);
            if ($progress) {
                $files = $this->getProgressFiles($progressId);
                $progress->files = $files;
            }
            return $progress;
        } catch (\Exception $e) {
            Log::error('getProgressWithFiles error: ' . $e->getMessage(), [
                'progress_id' => $progressId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getProgressFileById($fileId)
    {
        return DB::table('progress_files')
            ->where('file_id', $fileId)
            ->select(
                'file_id',
                'progress_id',
                'file_path',
                'original_name'
            )
            ->first();
    }

    public function updateProgressStatus($progressId, $status, $reason = null)
    {
        $update = [
            'progress_status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Only include reason fields if the corresponding column exists in the DB schema
        if ($reason !== null) {
            // Prefer 'delete_reason' for legacy compatibility if it exists
            if (Schema::hasColumn('progress', 'delete_reason')) {
                $update['delete_reason'] = $reason;
            } elseif (Schema::hasColumn('progress', 'rejection_reason')) {
                // Some schemas may use 'rejection_reason'
                $update['rejection_reason'] = $reason;
            }
        }

        return DB::table('progress')
            ->where('progress_id', $progressId)
            ->update($update);
    }

    public function updateProgress($progressId, $data)
    {
        $updateData = [];

        if (isset($data['purpose'])) {
            $updateData['purpose'] = $data['purpose'];
        }

        if (isset($data['progress_status'])) {
            $updateData['progress_status'] = $data['progress_status'];
        }

        if (!empty($updateData)) {
            // ensure updated_at is set when modifying the progress
            $updateData['updated_at'] = date('Y-m-d H:i:s');

            return DB::table('progress')
                ->where('progress_id', $progressId)
                ->update($updateData);
        }

        return false;
    }

    public function deleteProgressFile($fileId)
    {
        return DB::table('progress_files')
            ->where('file_id', $fileId)
            ->delete();
    }

    public function getProgressFileWithDetails($fileId)
    {
        return DB::table('progress_files as pf')
            ->join('progress as p', 'pf.progress_id', '=', 'p.progress_id')
            ->join('milestone_items as mi', 'p.milestone_item_id', '=', 'mi.item_id')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->join('projects as proj', 'm.project_id', '=', 'proj.project_id')
            ->where('pf.file_id', $fileId)
            ->where('p.progress_status', '!=', 'deleted')
            ->select(
                'pf.file_id',
                'pf.progress_id',
                'pf.file_path',
                'pf.original_name',
                'p.milestone_item_id as item_id',
                'p.purpose',
                'p.progress_status',
                'p.submitted_at',
                'mi.milestone_item_title as item_title',
                'proj.project_id',
                'proj.project_title',
                'proj.selected_contractor_id as contractor_id'
            )
            ->first();
    }

    /**
     * Get the latest progress report for a specific milestone item.
     */
    public function getLatestProgressForItem($itemId)
    {
        return DB::table('progress')
            ->where('milestone_item_id', $itemId)
            ->whereNotIn('progress_status', ['deleted'])
            ->orderBy('submitted_at', 'desc')
            ->first();
    }

    /**
     * Check if a milestone item is unlocked for the contractor.
     * Logic: First item is always unlocked. Subsequent items are unlocked only if the PREVIOUS item is completed.
     */
    public function isItemUnlocked($itemId, $contractorId)
    {
        $item = DB::table('milestone_items')
            ->where('item_id', $itemId)
            ->first();

        if (!$item)
            return false;

        // If it's the first item in the sequence, it's unlocked
        if ($item->sequence_order == 1) {
            return true;
        }

        // Check the previous item in the sequence for the same milestone
        $prevItem = DB::table('milestone_items')
            ->where('milestone_id', $item->milestone_id)
            ->where('sequence_order', $item->sequence_order - 1)
            ->first();

        if (!$prevItem)
            return true; // Fallback if no prev item found for some reason

        // Check if prev item is completed (status in database)
        // Usually, 'item_status' = 'completed' or having an 'approved' progress report
        $isCompleted = ($prevItem->item_status === 'completed');

        // If not explicitly marked as completed, check for an approved progress report
        if (!$isCompleted) {
            $isCompleted = DB::table('progress')
                ->where('milestone_item_id', $prevItem->item_id)
                ->where('progress_status', 'approved')
                ->exists();
        }

        return $isCompleted;
    }
}
