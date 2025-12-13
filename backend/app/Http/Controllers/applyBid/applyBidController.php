<?php

namespace App\Http\Controllers\applyBid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

require_once __DIR__ . '/../authController.php';

use App\Http\Controllers\Controller;

require_once __DIR__ . '/../../../Models/applyBids/applyBidClass.php';
require_once __DIR__ . '/../../../Models/propertyOwner/propertyOwnerProjectClass.php';
require_once __DIR__ . '/../../../Models/contractor/contractorProjectClass.php';

use App\Models\applyBids\bidApplicationService;
use App\Models\propertyOwner\propertyOwnerProjectService as ProjectService;
use App\Models\contractor\contractor;

class applyBidController extends Controller
{
    protected $bidService;
    protected $projectService;

    public function __construct()
    {
        $this->bidService = new bidApplicationService();
        $this->projectService = new ProjectService();
    }

    /**
     * Display all property owner projects for contractors to browse
     *
     * @return \Illuminate\View\View
     */
    public function browseProjects()
    {
        // Force fresh query from database (no caching)
        $projects = $this->projectService->getAllOpenProjects();
        
        return view('projects.applyBids.contractorViewProjects', compact('projects'));
    }

    /**
     * Display project details for contractors
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showProjectDetails($id)
    {
        $project = $this->projectService->getProjectById($id);
        
        if (!$project) {
            return redirect()->route('contractor.browse.projects')->with('error', 'Project not found');
        }
        
        // Only show open projects to contractors
        if ($project->project_status !== 'open' || $project->owner_id === null) {
            return redirect()->route('contractor.browse.projects')->with('error', 'Project is not available for bidding');
        }
        
        return view('projects.applyBids.contractorProjectDetails', compact('project'));
    }

    /**
     * Show the bid application form
     *
     * @param int $projectId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showBidForm($projectId)
    {
        // Get project details
        $project = $this->projectService->getProjectById($projectId);
        if (!$project) {
            return redirect()->route('contractor.browse.projects')->with('error', 'Project not found');
        }

        // Check if project is open for bidding
        if ($project->project_status !== 'open' || $project->owner_id === null) {
            return redirect()->route('contractor.browse.projects')->with('error', 'Project is not available for bidding');
        }

        // Get all contractors with their contractor type relationship
        $contractors = contractor::with('contractorType')->get();
        
        $contractor = null;
        $user = Session::get('user');
        if ($user) {
            $contractor = $this->bidService->getContractorByUserId($user->user_id);
            if ($contractor) {
                // Load contractor type relationship
                $contractor->load('contractorType');
            }
            // Check if contractor has already submitted a bid
            if ($contractor && $this->bidService->hasBidForProject($projectId, $contractor->contractor_id)) {
                return redirect()->route('contractor.project.details', $projectId)->with('error', 'You have already submitted a bid for this project');
            }
        }

        return view('projects.applyBids.applyBid', compact('project', 'contractor', 'contractors'));
    }

    /**
     * Submit the bid application
     *
     * @param Request $request
     * @param int $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitBid(Request $request, $projectId)
    {
        // Validate request
        $request->validate([
            'contractor_id' => 'required|integer|exists:contractors,contractor_id',
            'proposed_cost' => 'required|numeric|min:0',
            'estimated_timeline' => 'required|integer|min:1',
            'contractor_notes' => 'nullable|string|max:5000',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png,zip|max:10240', // 10MB max per file
        ]);

        // Get project details
        $project = $this->projectService->getProjectById($projectId);
        if (!$project) {
            return redirect()->route('contractor.browse.projects')->with('error', 'Project not found.');
        }

        // Check if project is open for bidding
        if ($project->project_status !== 'open' || $project->owner_id === null) {
            return redirect()->route('contractor.browse.projects')->with('error', 'Project is not available for bidding.');
        }

        // Check if bidding deadline has passed
        if (now() > $project->bidding_deadline) {
            return redirect()->route('contractor.browse.projects')->with('error', 'Bidding deadline has passed for this project.');
        }

        // Get contractor_id from request (no login required)
        $contractorId = $request->contractor_id;

        // Verify that the selected contractor exists
        $selectedContractor = contractor::find($contractorId);
        if (!$selectedContractor) {
            return redirect()->route('contractor.browse.projects')->with('error', 'Selected contractor not found.');
        }
        
        // Note: We allow any contractor to bid, even if their type doesn't match the project type
        // This provides flexibility for contractors who may have multiple capabilities

        // Check if contractor has already submitted a bid
        if ($this->bidService->hasBidForProject($projectId, $contractorId)) {
            return redirect()->route('contractor.browse.projects')->with('error', 'This contractor has already submitted a bid for this project.');
        }

        try {
            // Prepare bid data
            $bidData = [
                'project_id' => $projectId,
                'contractor_id' => $contractorId,
                'proposed_cost' => $request->proposed_cost,
                'estimated_timeline' => $request->estimated_timeline,
                'contractor_notes' => $request->contractor_notes,
            ];

            // Get uploaded files
            $files = $request->hasFile('supporting_documents') ? $request->file('supporting_documents') : [];

            // Create bid
            $bid = $this->bidService->createBid($bidData, $files);

            return redirect()->route('contractor.browse.projects')->with('success', 'Bid submitted successfully! Your bid has been received and is under review.');
        } catch (\Exception $e) {
            return redirect()->route('contractor.browse.projects')->with('error', 'Failed to submit bid: ' . $e->getMessage());
        }
    }
}

