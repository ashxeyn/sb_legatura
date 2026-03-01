<?php

namespace App\Http\Controllers\contractor;

use App\Http\Controllers\Controller;
use App\Http\Requests\contractor\progressUploadRequest;
use App\Models\contractor\progressUploadClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;
use App\Services\NotificationService;

class progressUploadController extends Controller
{
    protected $progressUploadClass;

    public function __construct()
    {
        $this->progressUploadClass = new progressUploadClass();
    }

    public function showUploadPage(Request $request)
    {
        $authCheck = $this->checkContractorAccess($request);
        if ($authCheck) {
            return $authCheck;
        }

        $user = Session::get('user');

        // Get c id
        $contractor = DB::table('contractors')
            ->where('user_id', $user->user_id)
            ->first();

        if (!$contractor) {
            return redirect('/dashboard')->with('error', 'Contractor profile not found');
        }

        $itemId = $request->query('item_id');
        $projectId = $request->query('project_id');

        if (!$itemId || !$projectId) {
            return redirect('/both/projects')->with('error', 'Invalid request parameters');
        }

        // Get milestone item details and verify contractor access
        $item = DB::table('milestone_items as mi')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->join('projects as p', 'm.project_id', '=', 'p.project_id')
            ->where('mi.item_id', $itemId)
            ->where('p.project_id', $projectId)
            ->where('p.selected_contractor_id', $contractor->contractor_id)
            ->select('mi.*', 'm.milestone_id', 'p.project_id', 'p.project_title')
            ->first();

        if (!$item) {
            return redirect('/both/projects')->with('error', 'Milestone item not found or access denied');
        }

        $itemArray = (array) $item;
        $project = (object) ['project_id' => $item->project_id, 'project_title' => $item->project_title];

        return view('contractor.progressUpload', [
            'item' => $itemArray,
            'project' => $project
        ]);
    }

    private function checkContractorAccess(Request $request)
    {
        // Support both session-based auth (web) and token-based auth (mobile API)
        $user = Session::get('user');

        // If no session user, try to authenticate via Sanctum token
        if (!$user) {
            $bearerToken = $request->bearerToken();
            if ($bearerToken) {
                // Find the token in the database
                $token = PersonalAccessToken::findToken($bearerToken);
                if ($token) {
                    // Get the user associated with the token
                    $user = $token->tokenable;
                    // Store user in session for downstream code that expects it there
                    if ($user && !Session::has('user')) {
                        Session::put('user', $user);
                    }
                }
            }

            // Fallback to request->user() if available (when middleware is applied)
            if (!$user && $request->user()) {
                $user = $request->user();
                if (!Session::has('user')) {
                    Session::put('user', $user);
                }
            }
        }

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                    'redirect_url' => '/accounts/login'
                ], 401);
            } else {
                return redirect('/accounts/login');
            }
        }

        // Check if yung user is c
        if (!in_array($user->user_type, ['contractor', 'both'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only contractors can upload progress.',
                    'redirect_url' => '/dashboard'
                ], 403);
            } else {
                return redirect('/dashboard')->with('error', 'Access denied. Only contractors can upload progress.');
            }
        }

        if ($user->user_type === 'both') {
            $currentRole = Session::get('current_role', 'contractor');
            if ($currentRole !== 'contractor') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. Please switch to contractor role to upload progress.',
                        'redirect_url' => '/dashboard',
                        'suggested_action' => 'switch_to_contractor'
                    ], 403);
                } else {
                    return redirect('/dashboard')->with('error', 'Please switch to contractor role to upload progress.');
                }
            }
        }

        return null;
    }

    public function uploadProgress(progressUploadRequest $request)
    {
        try {
            $authCheck = $this->checkContractorAccess($request);
            if ($authCheck) {
                return $authCheck;
            }

            $user = Session::get('user');

            $contractor = DB::table('contractors')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$contractor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor profile not found'
                ], 404);
            }

            $validated = $request->validated();

            // ── Single active progress report enforcement ──
            // Prevent uploading a new report if there's already one that is 'submitted' (pending review).
            // Allow new uploads if previous ones are 'approved', 'rejected' or 'deleted'.
            $existingActiveReport = DB::table('progress')
                ->where('milestone_item_id', $validated['item_id'])
                ->where('progress_status', 'submitted')
                ->exists();

            if ($existingActiveReport) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot submit a new progress report while another is pending review.'
                ], 422);
            }

            // Verify that the milestone item belongs to a project assigned to this contractor
            // Check via selected_contractor_id OR via the milestone's own contractor_id
            $milestoneItem = DB::table('milestone_items as mi')
                ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                ->join('projects as p', 'm.project_id', '=', 'p.project_id')
                ->where('mi.item_id', $validated['item_id'])
                ->where(function ($query) use ($contractor) {
                    $query->where('p.selected_contractor_id', $contractor->contractor_id)
                        ->orWhere('m.contractor_id', $contractor->contractor_id);
                })
                ->select('mi.item_id', 'm.milestone_id', 'p.project_id', 'p.project_title', 'mi.milestone_item_title')
                ->first();

            if (!$milestoneItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Milestone item not found or you do not have access to it'
                ], 403);
            }

            // ── Sequential enforcement: previous item must be completed first ──
            if (!$this->progressUploadClass->isItemUnlocked($milestoneItem->item_id, $contractor->contractor_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must complete the previous milestone item first before submitting progress for this one.',
                ], 422);
            }

            // Validate that files are present
            // Check for both 'progress_files' (array) and single file uploads
            $files = [];
            if ($request->hasFile('progress_files')) {
                $uploadedFiles = $request->file('progress_files');
                if (is_array($uploadedFiles)) {
                    $files = $uploadedFiles;
                } else {
                    $files = [$uploadedFiles];
                }
            } else {
                // Try alternative format: progress_files[0], progress_files[1], etc.
                $allFiles = $request->allFiles();
                foreach ($allFiles as $key => $file) {
                    if (strpos($key, 'progress_files') === 0 || is_numeric($key)) {
                        if (is_array($file)) {
                            $files = array_merge($files, $file);
                        } else {
                            $files[] = $file;
                        }
                    }
                }
            }

            if (count($files) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please upload at least one progress file'
                ], 400);
            }

            if (count($files) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please upload at least one progress file'
                ], 400);
            }

            // Create progress entry first
            $progressId = $this->progressUploadClass->createProgress([
                'item_id' => $validated['item_id'],
                'purpose' => $validated['purpose'],
                'progress_status' => 'submitted'
            ]);

            // Auto-advance item_status from not_started to in_progress
            DB::table('milestone_items')
                ->where('item_id', $validated['item_id'])
                ->where('item_status', 'not_started')
                ->update(['item_status' => 'in_progress', 'updated_at' => now()]);

            // Ensure the progress_uploads directory exists
            if (!Storage::disk('public')->exists('progress_uploads')) {
                Storage::disk('public')->makeDirectory('progress_uploads');
            }

            // Handle multiple progress files
            $uploadedFiles = [];
            $uploadedFilePaths = [];

            try {
                foreach ($files as $file) {
                    if (!$file->isValid()) {
                        throw new \Exception('Invalid file uploaded: ' . $file->getClientOriginalName());
                    }

                    $fileExtension = $file->getClientOriginalExtension();
                    $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
                    $storagePath = $file->storeAs('progress_uploads', $fileName, 'public');

                    if (!$storagePath) {
                        throw new \Exception('Failed to store file: ' . $file->getClientOriginalName());
                    }

                    // Use original file name for display
                    $originalFileName = $file->getClientOriginalName();

                    $fileId = $this->progressUploadClass->createProgressFile([
                        'progress_id' => $progressId,
                        'file_path' => $storagePath,
                        'original_name' => $originalFileName
                    ]);

                    if (!$fileId) {
                        // If database insert fails, delete the uploaded file
                        if (Storage::disk('public')->exists($storagePath)) {
                            Storage::disk('public')->delete($storagePath);
                        }
                        throw new \Exception('Failed to save file record to database: ' . $originalFileName);
                    }

                    $uploadedFiles[] = [
                        'file_id' => $fileId,
                        'file_name' => $originalFileName,
                        'file_path' => $storagePath
                    ];

                    $uploadedFilePaths[] = $storagePath;
                }
            } catch (\Exception $fileException) {
                // If file upload fails, delete the progress entry and uploaded files
                if ($progressId) {
                    $this->progressUploadClass->updateProgressStatus($progressId, 'deleted');
                }

                // Delete any uploaded files
                foreach ($uploadedFilePaths as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }

                throw $fileException;
            }

            // Notify project owner about progress upload
            $ownerUserId = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->where('p.project_id', $milestoneItem->project_id)
                ->value('po.user_id');
            if ($ownerUserId) {
                NotificationService::create($ownerUserId, 'progress_submitted', 'Progress Uploaded', "Contractor uploaded progress for \"{$milestoneItem->milestone_item_title}\" on \"{$milestoneItem->project_title}\".", 'normal', 'progress', (int) $progressId, ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $milestoneItem->project_id, 'tab' => 'progress']]);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Progress files uploaded successfully',
                    'data' => [
                        'progress_id' => $progressId,
                        'uploaded_files' => $uploadedFiles,
                        'files_count' => count($uploadedFiles),
                        'milestone_item' => [
                            'item_id' => $milestoneItem->item_id,
                            'title' => $milestoneItem->milestone_item_title,
                            'project_title' => $milestoneItem->project_title
                        ]
                    ]
                ], 201);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Progress files uploaded successfully',
                    'files_count' => count($uploadedFiles)
                ], 201);
            }
        } catch (\Exception $e) {
            $userId = null;
            $contractorId = null;
            $itemId = null;

            if (isset($user) && $user && isset($user->user_id)) {
                $userId = $user->user_id;
            }
            if (isset($contractor) && $contractor && isset($contractor->contractor_id)) {
                $contractorId = $contractor->contractor_id;
            }
            if (isset($validated) && isset($validated['item_id'])) {
                $itemId = $validated['item_id'];
            }

            \Log::error('Progress upload error: ' . $e->getMessage(), [
                'user_id' => $userId,
                'contractor_id' => $contractorId,
                'item_id' => $itemId,
                'request_data' => $request->except(['progress_files']), // Exclude files from log
                'file_count' => $request->hasFile('progress_files') ? (is_array($request->file('progress_files')) ? count($request->file('progress_files')) : 1) : 0,
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error uploading progress files: ' . $e->getMessage(),
                    'error' => $e->getMessage(),
                    'error_type' => get_class($e)
                ], 500);
            } else {
                return redirect('/contractor/progress/upload')->with('error', 'Error uploading progress files: ' . $e->getMessage());
            }
        }
    }

    private function mapMimeTypeToEnum($mimeType, $extension)
    {
        $mapping = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/zip' => 'zip',
            'application/x-zip-compressed' => 'zip',
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png'
        ];

        if (isset($mapping[$mimeType])) {
            return $mapping[$mimeType];
        }

        $extension = strtolower($extension);
        if (in_array($extension, ['pdf', 'doc', 'docx', 'zip', 'jpg', 'jpeg', 'png'])) {
            return $extension === 'jpeg' ? 'jpg' : $extension;
        }

        return 'pdf';
    }

    public function getProgressFiles(Request $request, $itemId)
    {
        try {
            $authCheck = $this->checkContractorAccess($request);
            if ($authCheck) {
                return $authCheck;
            }

            $user = Session::get('user');

            $contractor = DB::table('contractors')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$contractor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor profile not found'
                ], 404);
            }

            // Check if this is a request for a single progress entry
            if ($request->has('progress_id')) {
                $progressId = $request->input('progress_id');

                if (!$progressId || !is_numeric($progressId)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid progress ID provided'
                    ], 400);
                }

                $progress = $this->progressUploadClass->getProgressWithFiles($progressId);

                if (!$progress) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Progress not found'
                    ], 404);
                }

                // Verify ownership through project
                $milestoneItem = DB::table('milestone_items as mi')
                    ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                    ->join('projects as p', 'm.project_id', '=', 'p.project_id')
                    ->where('mi.item_id', $progress->item_id)
                    ->where('p.selected_contractor_id', $contractor->contractor_id)
                    ->select('p.project_id', 'p.project_title', 'mi.milestone_item_title as item_title')
                    ->first();

                if (!$milestoneItem) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Progress not found or access denied'
                    ], 403);
                }

                // Ensure files is an array (convert collection to array if needed)
                $files = $progress->files ?? [];
                if (is_object($files) && method_exists($files, 'toArray')) {
                    $files = $files->toArray();
                } elseif (is_object($files)) {
                    $files = (array) $files;
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Progress retrieved successfully',
                    'data' => [
                        'progress_id' => $progress->progress_id,
                        'item_id' => $progress->item_id,
                        'project_id' => $milestoneItem->project_id,
                        'item_title' => $milestoneItem->item_title,
                        'purpose' => $progress->purpose,
                        'progress_status' => $progress->progress_status,
                        'delete_reason' => isset($progress->delete_reason) ? $progress->delete_reason : null,
                        'submitted_at' => $progress->submitted_at,
                        'files' => $files
                    ]
                ], 200);
            }

            // Otherwise get all progress entries with files for the item
            $progressList = $this->progressUploadClass->getProgressFilesByItem($itemId, $contractor->contractor_id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Progress files retrieved successfully',
                    'data' => [
                        'progress_list' => $progressList,
                        'total_count' => count($progressList)
                    ]
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'progress_list' => $progressList
                ], 200);
            }
        } catch (\Exception $e) {
            \Log::error('getProgressFiles error: ' . $e->getMessage(), [
                'item_id' => $itemId,
                'progress_id' => $request->input('progress_id'),
                'user_id' => $user->user_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving progress files: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get progress files for both owners and contractors
     * This endpoint allows either role to view progress reports for a milestone item
     */
    public function getProgressFilesForBoth(Request $request, $itemId)
    {
        try {
            \Log::info('getProgressFilesForBoth called', ['item_id' => $itemId]);

            // Support both session-based auth (web) and token-based auth (mobile API)
            $user = Session::get('user');

            // If no session user, try to authenticate via Sanctum token
            if (!$user) {
                $bearerToken = $request->bearerToken();
                if ($bearerToken) {
                    // Find the token in the database
                    $token = PersonalAccessToken::findToken($bearerToken);
                    if ($token) {
                        // Get the user associated with the token
                        $user = $token->tokenable;
                        // Store user in session for downstream code that expects it there
                        if ($user && !Session::has('user')) {
                            Session::put('user', $user);
                        }
                        \Log::info('getProgressFilesForBoth: Using Sanctum token auth', ['user_id' => $user->user_id ?? null]);
                    }
                }

                // Fallback to request->user() if available (when middleware is applied)
                if (!$user && $request->user()) {
                    $user = $request->user();
                    if (!Session::has('user')) {
                        Session::put('user', $user);
                    }
                }
            }

            if (!$user) {
                \Log::warning('getProgressFilesForBoth: No user in session or token');
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Authentication required',
                        'redirect_url' => '/accounts/login'
                    ], 401);
                }
                return redirect('/accounts/login')->with('error', 'Please login to continue');
            }

            \Log::info('getProgressFilesForBoth user found', ['user_id' => $user->user_id ?? null]);

            // Check if user has access to this milestone item (either as owner or contractor)
            // projects -> relationship_id -> project_relationships -> owner_id -> property_owners
            $milestoneItem = DB::table('milestone_items as mi')
                ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                ->join('projects as p', 'm.project_id', '=', 'p.project_id')
                ->leftJoin('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->leftJoin('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->leftJoin('contractors as c', 'p.selected_contractor_id', '=', 'c.contractor_id')
                ->where('mi.item_id', $itemId)
                ->where(function ($query) use ($user) {
                    $query->where('po.user_id', $user->user_id)
                        ->orWhere('c.user_id', $user->user_id);
                })
                ->select('mi.item_id', 'p.project_id', 'p.project_title')
                ->first();

            \Log::info('getProgressFilesForBoth milestone item check', ['found' => $milestoneItem ? 'yes' : 'no']);

            if (!$milestoneItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Milestone item not found or access denied'
                ], 403);
            }

            // Get progress reports for this item (no contractor filter - both can see)
            $progressList = $this->progressUploadClass->getProgressFilesByItem($itemId, null);

            \Log::info('getProgressFilesForBoth progress list', ['count' => count($progressList), 'data' => $progressList]);

            return response()->json([
                'success' => true,
                'message' => 'Progress files retrieved successfully',
                'data' => [
                    'progress_list' => $progressList,
                    'total_count' => count($progressList)
                ]
            ], 200);
        } catch (\Exception $e) {
            $userId = null;
            if (isset($user) && $user) {
                $userId = is_object($user) ? ($user->user_id ?? $user->id ?? null) : ($user['user_id'] ?? null);
            }

            \Log::error('getProgressFilesForBoth error: ' . $e->getMessage(), [
                'item_id' => $itemId,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error retrieving progress files: ' . $e->getMessage(),
                    'data' => [
                        'progress_list' => [],
                        'total_count' => 0
                    ]
                ], 500);
            } else {
                return redirect('/accounts/login')->with('error', 'Error retrieving progress files: ' . $e->getMessage());
            }
        }
    }

    public function updateProgress(progressUploadRequest $request, $progressId)
    {
        try {
            $authCheck = $this->checkContractorAccess($request);
            if ($authCheck) {
                return $authCheck;
            }

            $user = Session::get('user');

            $contractor = DB::table('contractors')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$contractor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor profile not found'
                ], 404);
            }

            $validated = $request->validated();

            // Get the progress and verify ownership
            $progress = $this->progressUploadClass->getProgressById($progressId);

            if (!$progress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Progress not found'
                ], 404);
            }

            // Verify ownership through project
            $milestoneItem = DB::table('milestone_items as mi')
                ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                ->join('projects as p', 'm.project_id', '=', 'p.project_id')
                ->where('mi.item_id', $progress->item_id)
                ->where(function ($query) use ($contractor) {
                    $query->where('p.selected_contractor_id', $contractor->contractor_id)
                        ->orWhere('m.contractor_id', $contractor->contractor_id);
                })
                ->first();

            if (!$milestoneItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Progress not found or access denied'
                ], 403);
            }

            if (!in_array($progress->progress_status, ['needs_revision', 'submitted'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This progress report cannot be edited. Only reports with status "needs_revision" or "submitted" can be modified.'
                ], 403);
            }

            $updateData = [];
            if (isset($validated['purpose'])) {
                $updateData['purpose'] = $validated['purpose'];
            }
            if (!empty($updateData)) {
                $this->progressUploadClass->updateProgress($progressId, $updateData);
            }

            // Handle deleted files
            if ($request->has('deleted_file_ids') && $request->deleted_file_ids) {
                $deletedIds = explode(',', $request->deleted_file_ids);
                foreach ($deletedIds as $deleteId) {
                    $deleteId = trim($deleteId);
                    if ($deleteId) {
                        $fileToDelete = $this->progressUploadClass->getProgressFileById($deleteId);
                        if ($fileToDelete && $fileToDelete->progress_id == $progressId) {
                            $this->progressUploadClass->deleteProgressFile($deleteId);
                        }
                    }
                }
            }

            // Handle new file uploads
            $uploadedFiles = [];
            if ($request->hasFile('progress_files')) {
                $files = $request->file('progress_files');
                if (!is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $file) {
                    $fileExtension = $file->getClientOriginalExtension();
                    $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
                    $storagePath = $file->storeAs('progress_uploads', $fileName, 'public');

                    $originalFileName = $file->getClientOriginalName();

                    $newFileId = $this->progressUploadClass->createProgressFile([
                        'progress_id' => $progressId,
                        'file_path' => $storagePath,
                        'original_name' => $originalFileName
                    ]);

                    $uploadedFiles[] = [
                        'file_id' => $newFileId,
                        'file_path' => $storagePath,
                        'original_name' => $originalFileName
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully',
                'data' => [
                    'updated_progress_id' => $progressId,
                    'new_files' => $uploadedFiles
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Progress update error: ' . $e->getMessage(), [
                'progress_id' => $progressId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating progress',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteProgress(Request $request, $progressId)
    {
        try {
            $authCheck = $this->checkContractorAccess($request);
            if ($authCheck) {
                return $authCheck;
            }

            $user = Session::get('user');

            $contractor = DB::table('contractors')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$contractor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor profile not found'
                ], 404);
            }

            // Get the progress and verify ownership
            $progress = $this->progressUploadClass->getProgressById($progressId);

            if (!$progress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Progress not found'
                ], 404);
            }

            // Verify ownership through project
            $milestoneItem = DB::table('milestone_items as mi')
                ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                ->join('projects as p', 'm.project_id', '=', 'p.project_id')
                ->where(function ($query) use ($contractor) {
                    $query->where('p.selected_contractor_id', $contractor->contractor_id)
                        ->orWhere('m.contractor_id', $contractor->contractor_id);
                })
                ->first();

            if (!$milestoneItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Progress not found or access denied'
                ], 403);
            }

            // Only allow deletion if status is needs_revision or submitted
            if (!in_array($progress->progress_status, ['needs_revision', 'submitted'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This progress report cannot be deleted. Only reports with status "needs_revision" or "submitted" can be removed.'
                ], 403);
            }

            $deleteReason = $request->input('reason') ?? $request->input('delete_reason') ?? null;

            if (empty(trim((string) $deleteReason))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Delete reason is required'
                ], 400);
            }

            $this->progressUploadClass->updateProgressStatus($progressId, 'deleted', $deleteReason);

            return response()->json([
                'success' => true,
                'message' => 'Progress report deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Progress delete error: ' . $e->getMessage(), [
                'progress_id' => $progressId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting progress report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approveProgress(Request $request, $progressId)
    {
        try {
            \Log::info('approveProgress called', ['progress_id' => $progressId, 'bearer_token' => $request->bearerToken() ? 'present' : 'missing']);

            // Support both session-based auth (web) and token-based auth (mobile API)
            $user = Session::get('user');

            // If no session user, try to authenticate via Sanctum token
            if (!$user) {
                $bearerToken = $request->bearerToken();
                \Log::info('approveProgress: No session user, checking bearer token', ['token_present' => $bearerToken ? 'yes' : 'no']);

                if ($bearerToken) {
                    // Find the token in the database
                    $token = PersonalAccessToken::findToken($bearerToken);
                    if ($token) {
                        // Get the user associated with the token
                        $user = $token->tokenable;
                        \Log::info('approveProgress: Token found, user authenticated', ['user_id' => $user->user_id ?? null]);
                        // Store user in session for downstream code that expects it there
                        if ($user && !Session::has('user')) {
                            Session::put('user', $user);
                        }
                    } else {
                        \Log::warning('approveProgress: Token not found in database');
                    }
                }

                // Fallback to request->user() if available (when middleware is applied)
                if (!$user && $request->user()) {
                    $user = $request->user();
                    \Log::info('approveProgress: Using request->user()', ['user_id' => $user->user_id ?? null]);
                    if (!Session::has('user')) {
                        Session::put('user', $user);
                    }
                }
            } else {
                \Log::info('approveProgress: Using session user', ['user_id' => $user->user_id ?? null]);
            }

            if (!$user) {
                \Log::warning('approveProgress: No user found, returning 401');
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                    'redirect_url' => '/accounts/login'
                ], 401);
            }

            // Only owner can approve
            if (!in_array($user->user_type, ['property_owner', 'both'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only owners can approve progress.'
                ], 403);
            }
            if ($user->user_type === 'both') {
                $currentRole = Session::get('current_role', 'owner');
                if ($currentRole !== 'owner') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. Please switch to owner role to approve progress.'
                    ], 403);
                }
            }

            // Find progress and verify owner
            $progress = DB::table('progress')
                ->join('milestone_items as mi', 'progress.milestone_item_id', '=', 'mi.item_id')
                ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                ->join('projects as p', 'm.project_id', '=', 'p.project_id')
                ->leftJoin('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->where('progress.progress_id', $progressId)
                ->select('progress.*', 'pr.owner_id')
                ->first();

            if (!$progress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Progress report not found.'
                ], 404);
            }

            // Get owner_id from property_owners table
            $owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();
            $ownerId = $owner ? $owner->owner_id : null;

            $hasPermission = false;
            if ($ownerId && $progress->owner_id) {
                // Compare owner_id from property_owners table
                $hasPermission = ($progress->owner_id == $ownerId);
            } else {
                // Legacy: compare user_id directly
                $hasPermission = ($progress->owner_id == $user->user_id);
            }

            if (!$hasPermission) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to approve this progress.'
                ], 403);
            }

            // Update status
            $this->progressUploadClass->updateProgressStatus($progressId, 'approved');

            // Notify contractor that progress was approved
            $progInfo = DB::table('progress as pr')
                ->join('milestone_items as mi', 'pr.milestone_item_id', '=', 'mi.item_id')
                ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                ->join('projects as p', 'm.project_id', '=', 'p.project_id')
                ->where('pr.progress_id', $progressId)
                ->select('m.contractor_id', 'mi.milestone_item_title', 'p.project_id', 'p.project_title')
                ->first();
            if ($progInfo) {
                $cUserId = DB::table('contractor_users')->where('contractor_id', $progInfo->contractor_id)->where('is_active', 1)->where('is_deleted', 0)->value('user_id');
                if ($cUserId) {
                    NotificationService::create($cUserId, 'progress_approved', 'Progress Approved', "Your progress for \"{$progInfo->milestone_item_title}\" on \"{$progInfo->project_title}\" was approved.", 'normal', 'progress', (int) $progressId, ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $progInfo->project_id, 'tab' => 'progress']]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Progress report approved successfully.'
            ], 200);
        } catch (\Exception $e) {
            $userId = null;
            if (isset($user) && $user) {
                $userId = is_object($user) ? ($user->user_id ?? $user->id ?? null) : ($user['user_id'] ?? null);
            }

            \Log::error('approveProgress error: ' . $e->getMessage(), [
                'progress_id' => $progressId,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error approving progress report: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a progress report (Owner)
     */
    public function rejectProgress(Request $request, $progressId)
    {
        try {
            \Log::info('rejectProgress called', ['progress_id' => $progressId, 'bearer_token' => $request->bearerToken() ? 'present' : 'missing']);

            // Support both session-based auth (web) and token-based auth (mobile API)
            $user = Session::get('user');

            // If no session user, try to authenticate via Sanctum token
            if (!$user) {
                $bearerToken = $request->bearerToken();
                \Log::info('rejectProgress: No session user, checking bearer token', ['token_present' => $bearerToken ? 'yes' : 'no']);

                if ($bearerToken) {
                    // Find the token in the database
                    $token = PersonalAccessToken::findToken($bearerToken);
                    if ($token) {
                        // Get the user associated with the token
                        $user = $token->tokenable;
                        \Log::info('rejectProgress: Token found, user authenticated', ['user_id' => $user->user_id ?? null]);
                        // Store user in session for downstream code that expects it there
                        if ($user && !Session::has('user')) {
                            Session::put('user', $user);
                        }
                    } else {
                        \Log::warning('rejectProgress: Token not found in database');
                    }
                }

                // Fallback to request->user() if available (when middleware is applied)
                if (!$user && $request->user()) {
                    $user = $request->user();
                    \Log::info('rejectProgress: Using request->user()', ['user_id' => $user->user_id ?? null]);
                    if (!Session::has('user')) {
                        Session::put('user', $user);
                    }
                }
            } else {
                \Log::info('rejectProgress: Using session user', ['user_id' => $user->user_id ?? null]);
            }

            if (!$user) {
                \Log::warning('rejectProgress: No user found, returning 401');
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                    'redirect_url' => '/accounts/login'
                ], 401);
            }

            // Only owner can reject
            if (!in_array($user->user_type, ['property_owner', 'both'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only owners can reject progress.'
                ], 403);
            }
            if ($user->user_type === 'both') {
                $currentRole = Session::get('current_role', 'owner');
                if ($currentRole !== 'owner') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. Please switch to owner role to reject progress.'
                    ], 403);
                }
            }

            // Find progress and verify owner
            $progress = DB::table('progress')
                ->join('milestone_items as mi', 'progress.milestone_item_id', '=', 'mi.item_id')
                ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                ->join('projects as p', 'm.project_id', '=', 'p.project_id')
                ->leftJoin('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->where('progress.progress_id', $progressId)
                ->select('progress.*', 'pr.owner_id')
                ->first();

            if (!$progress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Progress report not found.'
                ], 404);
            }

            // Get owner_id from property_owners table
            $owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();
            $ownerId = $owner ? $owner->owner_id : null;

            $hasPermission = false;
            if ($ownerId && $progress->owner_id) {
                $hasPermission = ($progress->owner_id == $ownerId);
            } else {
                $hasPermission = ($progress->owner_id == $user->user_id);
            }

            if (!$hasPermission) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to reject this progress.'
                ], 403);
            }

            // Optional rejection reason
            $reason = $request->input('reason', null);

            // Update status
            $this->progressUploadClass->updateProgressStatus($progressId, 'rejected', $reason);

            // Notify contractor that progress was rejected
            $progInfo = DB::table('progress as pr')
                ->join('milestone_items as mi', 'pr.milestone_item_id', '=', 'mi.item_id')
                ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                ->join('projects as p', 'm.project_id', '=', 'p.project_id')
                ->where('pr.progress_id', $progressId)
                ->select('m.contractor_id', 'mi.milestone_item_title', 'p.project_id', 'p.project_title')
                ->first();
            if ($progInfo) {
                $cUserId = DB::table('contractor_users')->where('contractor_id', $progInfo->contractor_id)->where('is_active', 1)->where('is_deleted', 0)->value('user_id');
                if ($cUserId) {
                    $reasonNote = $reason ? " Reason: {$reason}" : '';
                    NotificationService::create($cUserId, 'progress_rejected', 'Progress Rejected', "Your progress for \"{$progInfo->milestone_item_title}\" on \"{$progInfo->project_title}\" was rejected.{$reasonNote}", 'high', 'progress', (int) $progressId, ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $progInfo->project_id, 'tab' => 'progress']]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Progress report rejected successfully.'
            ], 200);
        } catch (\Exception $e) {
            $userId = null;
            if (isset($user) && $user) {
                $userId = is_object($user) ? ($user->user_id ?? $user->id ?? null) : ($user['user_id'] ?? null);
            }

            \Log::error('rejectProgress error: ' . $e->getMessage(), [
                'progress_id' => $progressId,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error rejecting progress report: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a progress document with proper rendering/download logic
     */
    public function viewProgressDocument(Request $request)
    {
        $filePath = $request->query('file');
        $documentName = $request->query('name', 'Document');

        // Validate file path
        if (!$filePath) {
            abort(404, 'Document not found');
        }

        // Security: Ensure the file path doesn't contain directory traversal
        if (str_contains($filePath, '..') || str_contains($filePath, '//')) {
            abort(403, 'Invalid file path');
        }

        // Generate the document URL using Laravel storage asset
        $documentUrl = asset('storage/' . ltrim($filePath, '/'));

        // Get the file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // For PDFs, browsers have built-in viewers that users prefer
        if ($extension === 'pdf') {
            return redirect($documentUrl);
        }

        return view('contractor.progressViewer', [
            'documentUrl' => $documentUrl,
            'documentName' => $documentName,
            'extension' => $extension,
            'filePath' => $filePath
        ]);
    }
}
