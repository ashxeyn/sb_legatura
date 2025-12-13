<?php

namespace App\Http\Controllers\projectPosting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

// Require the file to load Controller base class and all project request/resource classes (combined in authController.php)
require_once __DIR__ . '/../authController.php';

// Require the file to load all classes from propertyOwnerProjectClass.php
// This is needed because PSR-4 autoloading expects one class per file
// Path: from app/Http/Controllers/projectPosting/ to app/Models/propertyOwner/
require_once __DIR__ . '/../../../Models/propertyOwner/propertyOwnerProjectClass.php';

// Require the file to load all classes from contractorProjectClass.php
require_once __DIR__ . '/../../../Models/contractor/contractorProjectClass.php';

use App\Http\Controllers\Controller;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\StoreContractorProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;

use App\Models\propertyOwner\propertyOwnerProjectService;
use App\Models\contractor\contractorProjectService;
use App\Models\propertyOwner\project as Project;

class projectPostingController extends Controller
{
    protected $projectService;
    protected $contractorService;

    public function __construct()
    {
        // Manually instantiate the services since all classes are in one file
        $this->projectService = new propertyOwnerProjectService();
        $this->contractorService = new contractorProjectService();
    }

    /**
     * Show the property owner project posting form
     *
     * @return \Illuminate\View\View
     */
    public function ownerForm()
    {
        $types = $this->projectService->getContractorTypes();
        return view('projects.projectPosting.propertyOwnerPosting', compact('types'));
    }

    /**
     * Show the contractor project posting form
     *
     * @return \Illuminate\View\View
     */
    public function contractorForm()
    {
        return view('projects.projectPosting.contractorPosting');
    }

    /**
     * Store a property owner project
     *
     * @param StoreProjectRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeOwner(StoreProjectRequest $request)
    {
        $data = $request->validated();
        
        $housePhotos = $request->hasFile('house_photos') ? $request->file('house_photos') : [];
        $landTitleFile = $request->hasFile('land_title') ? $request->file('land_title') : null;
        $blueprintFile = $request->hasFile('blueprint') ? $request->file('blueprint') : null;
        $supportingDocuments = $request->hasFile('supporting_documents') ? $request->file('supporting_documents') : [];
        
        $project = $this->projectService->createProject($data, $landTitleFile, $blueprintFile, $supportingDocuments, $housePhotos);
        
        // Redirect to property owner's project view
        return redirect()->route('projects.show', $project->project_id)->with('success', 'Project created successfully!');
    }

    /**
     * Show property owner project details
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        $project = $this->projectService->getProjectById($id);
        
        if (!$project) {
            return redirect()->route('projects.create')->with('error', 'Project not found');
        }
        
        return view('projects.projectPosting.propertyOwnerPost', compact('project'));
    }

    /**
     * Store a contractor project
     *
     * @param StoreContractorProjectRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeContractor(StoreContractorProjectRequest $request)
    {
        $data = $request->validated();
        
        $mediaFiles = $request->hasFile('media') ? $request->file('media') : [];
        
        // For contractor projects, we need to handle owner_id
        // Since contractors don't have owner_id, we'll use a default or system owner
        // TODO: Implement proper owner_id mapping or create system owner
        // For now, using contractor_id to find/create owner relationship
        $contractor = $this->contractorService->getContractorById($data['contractor_id']);
        
        if (!$contractor) {
            return redirect()->back()->with('error', 'Contractor not found');
        }
        
        // Set owner_id to a default (1) or create logic to map contractor to owner
        // This is a simplified approach - you may need to adjust based on your business logic
        $data['owner_id'] = 1; // TODO: Get proper owner_id from contractor relationship
        
        // Create project
        $project = $this->contractorService->createProject($data, $mediaFiles);
        
        return redirect()->route('contractorProjects.show', $project->project_id)->with('success', 'Project posted successfully');
    }

    /**
     * Show contractor project details
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showContractor($id)
    {
        $project = $this->contractorService->getProjectById($id);
        
        if (!$project) {
            return redirect()->route('contractorProjects.create')->with('error', 'Project not found');
        }
        
        return view('projects.projectPosting.contractorPost', compact('project'));
    }

    /**
     * API: List all projects (JSON response)
     *
     * @param Request $request
     * @return ResourceCollection
     */
    public function apiIndex(Request $request): ResourceCollection
    {
        $projects = Project::query()
            ->when($request->integer('owner_id'), fn($q, $ownerId) => $q->where('owner_id', $ownerId))
            ->latest('project_id')
            ->paginate(10);

        return ProjectResource::collection($projects);
    }

    /**
     * API: Store a new project (JSON response)
     *
     * @param StoreProjectRequest $request
     * @return ProjectResource
     */
    public function apiStore(StoreProjectRequest $request): ProjectResource
    {
        $project = Project::create($request->only([
            'owner_id',
            'project_title',
            'project_description',
            'project_location',
            'budget_range_min',
            'budget_range_max',
            'lot_size',
            'property_type',
            'type_id',
            'to_finish',
            'project_status',
            'selected_contractor_id',
            'bidding_deadline',
        ]));

        return new ProjectResource($project);
    }

    /**
     * API: Show a specific project (JSON response)
     *
     * @param Project $project
     * @return ProjectResource
     */
    public function apiShow(Project $project): ProjectResource
    {
        // Ownership enforcement can be added once auth is mapped to property_owners
        return new ProjectResource($project);
    }

    /**
     * API: Update a project (JSON response)
     *
     * @param UpdateProjectRequest $request
     * @param Project $project
     * @return ProjectResource
     */
    public function apiUpdate(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        $project->fill($request->only([
            'owner_id',
            'project_title',
            'project_description',
            'project_location',
            'budget_range_min',
            'budget_range_max',
            'lot_size',
            'property_type',
            'type_id',
            'to_finish',
            'project_status',
            'selected_contractor_id',
            'bidding_deadline',
        ]));
        $project->save();

        return new ProjectResource($project);
    }

    /**
     * API: Delete a project (JSON response)
     *
     * @param Project $project
     * @return \Illuminate\Http\Response
     */
    public function apiDestroy(Project $project)
    {
        $project->delete();
        return response()->noContent();
    }
}

