<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\StoreContractorProjectRequest;
use Illuminate\Http\Request;

// Require the file to load all classes from PropertyOwnerProject.php
// This is needed because PSR-4 autoloading expects one class per file
// Path: from app/Http/Controllers/Web/ to app/Models/PropertyOwner/
require_once __DIR__ . '/../../../Models/PropertyOwner/PropertyOwnerProject.php';

// Require the file to load all classes from ContractorProject.php
require_once __DIR__ . '/../../../Models/Contractor/ContractorProject.php';

use App\Models\PropertyOwner\PropertyOwnerProjectService;
use App\Models\Contractor\ContractorProjectService;

class ProjectWebController extends Controller
{
    protected $projectService;
    protected $contractorService;

    public function __construct()
    {
        // Manually instantiate the services since all classes are in one file
        $this->projectService = new PropertyOwnerProjectService();
        $this->contractorService = new ContractorProjectService();
    }

    public function ownerForm()
    {
        $types = $this->projectService->getContractorTypes();
        return view('projects.projectPosting.Propertyowner', compact('types'));
    }

    public function contractorForm()
    {
        return view('projects.projectPosting.Contractor');
    }

    public function storeOwner(StoreProjectRequest $request)
    {
        $data = $request->validated();
        
        $housePhotos = $request->hasFile('house_photos') ? $request->file('house_photos') : [];
        $landTitleFile = $request->hasFile('land_title') ? $request->file('land_title') : null;
        $blueprintFile = $request->hasFile('blueprint') ? $request->file('blueprint') : null;
        $supportingDocuments = $request->hasFile('supporting_documents') ? $request->file('supporting_documents') : [];
        
        $project = $this->projectService->createProject($data, $landTitleFile, $blueprintFile, $supportingDocuments, $housePhotos);
        
        return redirect()->route('projects.show', $project->project_id)->with('success', 'Project created successfully');
    }

    public function show($id)
    {
        $project = $this->projectService->getProjectById($id);
        
        if (!$project) {
            return redirect()->route('projects.create')->with('error', 'Project not found');
        }
        
        return view('projects.projectPosting.PropertyownerPost', compact('project'));
    }

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

    public function showContractor($id)
    {
        $project = $this->contractorService->getProjectById($id);
        
        if (!$project) {
            return redirect()->route('contractorProjects.create')->with('error', 'Project not found');
        }
        
        return view('projects.projectPosting.contractorPost', compact('project'));
    }
}


