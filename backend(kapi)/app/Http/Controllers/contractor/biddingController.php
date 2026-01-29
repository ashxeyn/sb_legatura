<?php

namespace App\Http\Controllers\contractor;

use App\Http\Controllers\Controller;
use App\Http\Requests\contractor\biddingRequest;
use App\Models\contractor\biddingClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class biddingController extends Controller
{
    protected $biddingClass;

    public function __construct(biddingClass $biddingClass)
    {
        $this->biddingClass = $biddingClass;
    }

    public function showProjectOverview($projectId)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/accounts/login');
        }

        // Get contractor info
        $contractor = DB::table('contractors')
            ->where('user_id', $user->user_id)
            ->first();

        if (!$contractor) {
            return redirect('/dashboard')->with('error', 'Contractor profile not found.');
        }

        // Get project details
        $project = $this->biddingClass->getProjectForBidding($projectId);

        if (!$project) {
            return redirect('/dashboard')->with('error', 'Project not found or not available for bidding.');
        }

        // Check if bidding deadline has passed
        $biddingDeadline = $project->bidding_deadline;
        $canBid = true;
        if ($biddingDeadline) {
            $canBid = strtotime($biddingDeadline) >= time();
        }

        // Get project files
        $projectFiles = $this->biddingClass->getProjectFiles($projectId);

        // Get existing bid if any
        $existingBid = $this->biddingClass->getContractorBid($projectId, $contractor->contractor_id);
        $bidFiles = [];
        if ($existingBid) {
            $bidFiles = $this->biddingClass->getBidFiles($existingBid->bid_id);
        }

        return view('contractor.projectOverview', compact('project', 'projectFiles', 'contractor', 'existingBid', 'bidFiles', 'canBid'));
    }

    public function store(biddingRequest $request)
    {
        try {
            $user = Session::get('user');
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not authenticated.'], 401);
            }

            // Get contractor info
            $contractor = DB::table('contractors')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$contractor) {
                return response()->json(['success' => false, 'message' => 'Contractor profile not found.'], 404);
            }

            // Check if bid already exists
            $existingBid = $this->biddingClass->getContractorBid($request->project_id, $contractor->contractor_id);

            if ($existingBid) {
                // If bid exists and is not cancelled, don't allow resubmission
                if ($existingBid->bid_status !== 'cancelled') {
                    return response()->json(['success' => false, 'message' => 'You have already submitted a bid for this project.'], 400);
                }

                // If bid was cancelled, update it instead of creating new one
                $this->biddingClass->updateBid($existingBid->bid_id, [
                    'proposed_cost' => $request->proposed_cost,
                    'estimated_timeline' => $request->estimated_timeline,
                    'contractor_notes' => $request->contractor_notes
                ]);

                // Update status back to submitted
                $this->biddingClass->reactivateBid($existingBid->bid_id);
                $bidId = $existingBid->bid_id;
            } else {
                // Create new bid
                $bidId = $this->biddingClass->createBid([
                    'project_id' => $request->project_id,
                    'contractor_id' => $contractor->contractor_id,
                    'proposed_cost' => $request->proposed_cost,
                    'estimated_timeline' => $request->estimated_timeline,
                    'contractor_notes' => $request->contractor_notes
                ]);
            }

            // Handle file uploads
            if ($request->hasFile('bid_files')) {
                foreach ($request->file('bid_files') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('bid_files', $fileName, 'public');

                    $this->biddingClass->createBidFile([
                        'bid_id' => $bidId,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'description' => null
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bid submitted successfully!',
                'bid_id' => $bidId
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(biddingRequest $request)
    {
        try {
            $user = Session::get('user');
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not authenticated.'], 401);
            }

            // Get contractor info
            $contractor = DB::table('contractors')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$contractor) {
                return response()->json(['success' => false, 'message' => 'Contractor profile not found.'], 404);
            }

            // Verify bid belongs to contractor
            $bid = DB::table('bids')
                ->where('bid_id', $request->bid_id)
                ->where('contractor_id', $contractor->contractor_id)
                ->first();

            if (!$bid) {
                return response()->json(['success' => false, 'message' => 'Bid not found or you do not have permission to edit it.'], 404);
            }

            // Check if bid can be edited (only submitted or under_review status)
            if (!in_array($bid->bid_status, ['submitted', 'under_review'])) {
                return response()->json(['success' => false, 'message' => 'This bid cannot be edited in its current status.'], 400);
            }

            // Update bid
            $this->biddingClass->updateBid($request->bid_id, [
                'proposed_cost' => $request->proposed_cost,
                'estimated_timeline' => $request->estimated_timeline,
                'contractor_notes' => $request->contractor_notes
            ]);

            // Handle new file uploads
            if ($request->hasFile('bid_files')) {
                foreach ($request->file('bid_files') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('bid_files', $fileName, 'public');

                    $this->biddingClass->createBidFile([
                        'bid_id' => $request->bid_id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'description' => null
                    ]);
                }
            }

            // Handle file deletions
            if ($request->has('delete_files')) {
                foreach ($request->delete_files as $fileId) {
                    $this->biddingClass->deleteBidFile($fileId);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bid updated successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Request $request, $bidId)
    {
        try {
            $user = Session::get('user');
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not authenticated.'], 401);
            }

            // Get contractor info
            $contractor = DB::table('contractors')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$contractor) {
                return response()->json(['success' => false, 'message' => 'Contractor profile not found.'], 404);
            }

            // Verify bid belongs to contractor
            $bid = DB::table('bids')
                ->where('bid_id', $bidId)
                ->where('contractor_id', $contractor->contractor_id)
                ->first();

            if (!$bid) {
                return response()->json(['success' => false, 'message' => 'Bid not found or you do not have permission to cancel it.'], 404);
            }

            // Check if bid can be cancelled (only submitted or under_review status, not already cancelled)
            if (!in_array($bid->bid_status, ['submitted', 'under_review'])) {
                return response()->json(['success' => false, 'message' => 'This bid cannot be cancelled in its current status.'], 400);
            }

            if ($bid->bid_status === 'cancelled') {
                return response()->json(['success' => false, 'message' => 'This bid has already been cancelled.'], 400);
            }

            // Cancel bid (delete it)
            $this->biddingClass->cancelBid($bidId);

            return response()->json([
                'success' => true,
                'message' => 'Bid cancelled successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint to get all bids for a project (for property owner)
     */
    public function getProjectBids(Request $request, $projectId)
    {
        try {
            $userId = $request->query('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required'
                ], 400);
            }

            // Get owner record to verify ownership
            $owner = DB::table('property_owners')
                ->where('user_id', $userId)
                ->first();

            if (!$owner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property owner record not found'
                ], 404);
            }

            // Verify project belongs to owner
            $project = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->where('p.project_id', $projectId)
                ->where('pr.owner_id', $owner->owner_id)
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or unauthorized'
                ], 404);
            }

            // Get all bids for the project with contractor details
            $bids = DB::table('bids as b')
                ->join('contractors as c', 'b.contractor_id', '=', 'c.contractor_id')
                ->join('users as u', 'c.user_id', '=', 'u.user_id')
                ->leftJoin('contractor_types as ct', 'c.type_id', '=', 'ct.type_id')
                ->select(
                    'b.bid_id',
                    'b.project_id',
                    'b.contractor_id',
                    'b.proposed_cost',
                    'b.estimated_timeline',
                    'b.contractor_notes',
                    'b.bid_status',
                    'b.submitted_at',
                    'c.company_name',
                    'c.company_phone',
                    'c.company_email',
                    'c.company_website',
                    'c.years_of_experience',
                    'c.completed_projects',
                    'c.picab_category',
                    'u.username',
                    'u.profile_pic',
                    'ct.type_name'
                )
                ->where('b.project_id', $projectId)
                ->whereNotIn('b.bid_status', ['cancelled'])
                ->orderBy('b.submitted_at', 'desc')
                ->get();

            // Get bid files for each bid
            foreach ($bids as $bid) {
                $files = DB::table('bid_files')
                    ->where('bid_id', $bid->bid_id)
                    ->select('file_id', 'bid_id', 'file_name', 'file_path', 'description', 'uploaded_at')
                    ->get();
                $bid->files = $files;
            }

            return response()->json([
                'success' => true,
                'message' => 'Bids retrieved successfully',
                'data' => $bids
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving bids: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint for mobile app to submit a bid
     */
    public function apiSubmitBid(Request $request, $projectId)
    {
        try {
            $userId = $request->input('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required'
                ], 400);
            }

            // Get contractor info
            $contractor = DB::table('contractors')
                ->where('user_id', $userId)
                ->first();

            if (!$contractor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor profile not found'
                ], 404);
            }

            // Validate input
            $validated = $request->validate([
                'proposed_cost' => 'required|numeric|min:0',
                'estimated_timeline' => 'required|string',
                'contractor_notes' => 'nullable|string'
            ]);

            // Check if bid already exists
            $existingBid = DB::table('bids')
                ->where('project_id', $projectId)
                ->where('contractor_id', $contractor->contractor_id)
                ->whereNotIn('bid_status', ['cancelled'])
                ->first();

            if ($existingBid) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already submitted a bid for this project'
                ], 400);
            }

            // Create bid
            $bidId = DB::table('bids')->insertGetId([
                'project_id' => $projectId,
                'contractor_id' => $contractor->contractor_id,
                'proposed_cost' => $validated['proposed_cost'],
                'estimated_timeline' => $validated['estimated_timeline'],
                'contractor_notes' => $validated['contractor_notes'] ?? null,
                'bid_status' => 'submitted',
                'submitted_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bid submitted successfully',
                'data' => [
                    'bid_id' => $bidId
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting bid: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint to get contractor's own bid for a project
     */
    public function apiGetMyBid(Request $request, $projectId)
    {
        try {
            $userId = $request->input('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required'
                ], 400);
            }

            // Get contractor info
            $contractor = DB::table('contractors')
                ->where('user_id', $userId)
                ->first();

            if (!$contractor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor profile not found'
                ], 404);
            }

            // Get contractor's bid for this project
            $bid = DB::table('bids')
                ->where('project_id', $projectId)
                ->where('contractor_id', $contractor->contractor_id)
                ->whereNotIn('bid_status', ['cancelled'])
                ->first();

            if (!$bid) {
                return response()->json([
                    'success' => true,
                    'message' => 'No bid found',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bid retrieved successfully',
                'data' => $bid
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving bid: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint to get all bids for a contractor
     */
    public function apiGetMyBids(Request $request)
    {
        try {
            $userId = $request->input('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required'
                ], 400);
            }

            // Get contractor info
            $contractor = DB::table('contractors')
                ->where('user_id', $userId)
                ->first();

            if (!$contractor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor profile not found'
                ], 404);
            }

            // Get all bids for this contractor with project details
            $bids = DB::table('bids')
                ->join('projects', 'bids.project_id', '=', 'projects.project_id')
                ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
                ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id')
                ->leftJoin('contractor_types', 'projects.type_id', '=', 'contractor_types.type_id')
                ->where('bids.contractor_id', $contractor->contractor_id)
                ->whereNotIn('bids.bid_status', ['cancelled'])
                ->select(
                    'bids.bid_id',
                    'bids.project_id',
                    'bids.proposed_cost',
                    'bids.estimated_timeline',
                    'bids.contractor_notes',
                    'bids.bid_status',
                    'bids.submitted_at',
                    'projects.project_title',
                    'projects.project_description',
                    'projects.project_location',
                    'projects.budget_range_min',
                    'projects.budget_range_max',
                    'projects.lot_size',
                    'projects.floor_area',
                    'projects.property_type',
                    'projects.to_finish',
                    'projects.project_status',
                    'contractor_types.type_name',
                    'project_relationships.bidding_due',
                    'users.username as owner_name'
                )
                ->orderBy('bids.submitted_at', 'desc')
                ->get();

            // Fetch project files for each bid
            foreach ($bids as $bid) {
                $projectFiles = DB::table('project_files')
                    ->where('project_id', $bid->project_id)
                    ->select('file_type', 'file_path')
                    ->get();
                $bid->project_files = $projectFiles;
            }

            return response()->json([
                'success' => true,
                'message' => 'Bids retrieved successfully',
                'data' => $bids
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving bids: ' . $e->getMessage()
            ], 500);
        }
    }
}

