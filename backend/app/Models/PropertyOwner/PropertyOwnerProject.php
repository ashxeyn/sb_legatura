<?php

namespace App\Models\PropertyOwner;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


// Property Owner Model
class PropertyOwner extends Model
{
    use HasFactory;

    protected $table = 'property_owners';
    protected $primaryKey = 'owner_id';
    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'last_name',
        'middle_name',
        'first_name',
        'phone_number',
        'address',
        'valid_id_id',
        'valid_id_number',
        'valid_id_photo',
        'police_clearance',
        'date_of_birth',
        'age',
        'occupation_id',
        'occupation_other',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'owner_id', 'owner_id');
    }
}

// Project Model
class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';
    protected $primaryKey = 'project_id';
    public $incrementing = true;

    protected $fillable = [
        'owner_id',
        'project_title',
        'project_description',
        'project_location',
        'budget_range_min',
        'budget_range_max',
        'lot_size',
        'floor_area',
        'property_type',
        'type_id',
        'contractor_type_other',
        'to_finish',
        'project_status',
        'selected_contractor_id',
        'bidding_deadline',
    ];

    protected $casts = [
        'budget_range_min' => 'decimal:2',
        'budget_range_max' => 'decimal:2',
        'bidding_deadline' => 'datetime',
    ];

    // Relationships
    public function owner()
    {
        return $this->belongsTo(PropertyOwner::class, 'owner_id', 'owner_id');
    }

    public function contractorType()
    {
        return $this->belongsTo(ContractorType::class, 'type_id', 'type_id');
    }

    public function selectedContractor()
    {
        return $this->belongsTo(Contractor::class, 'selected_contractor_id', 'contractor_id');
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class, 'project_id', 'project_id');
    }
}


// Project File Model
class ProjectFile extends Model
{
    use HasFactory;

    protected $table = 'project_files';
    protected $primaryKey = 'file_id';
    public $incrementing = true;

    // Disable automatic timestamps since table uses uploaded_at instead
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'file_type',
        'file_path',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    // Relationship
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }
}


// Contractor Type Model
class ContractorType extends Model
{
    use HasFactory;

    protected $table = 'contractor_types';
    protected $primaryKey = 'type_id';
    public $incrementing = true;

    protected $fillable = [
        'type_name',
    ];

    // Relationships
    public function projects()
    {
        return $this->hasMany(Project::class, 'type_id', 'type_id');
    }
}


// Contractor Model
class Contractor extends Model
{
    use HasFactory;

    protected $table = 'contractors';
    protected $primaryKey = 'contractor_id';
    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'company_name',
        'years_of_experience',
        'type_id',
        'contractor_type_other',
        'services_offered',
        'business_address',
        'company_email',
        'company_phone',
        'company_website',
        'company_social_media',
        'company_description',
        'picab_number',
        'picab_category',
        'picab_expiration_date',
        'business_permit_number',
        'business_permit_city',
        'business_permit_expiration',
        'tin_business_reg_number',
        'dti_sec_registration_photo',
        'verification_status',
        'verification_date',
        'verified_by',
        'rejection_reason',
        'completed_projects',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function contractorType()
    {
        return $this->belongsTo(ContractorType::class, 'type_id', 'type_id');
    }
}

// Property Owner Project Service
class PropertyOwnerProjectService
{
    
    // Get all contractor types
    public function getContractorTypes()
    {
        return ContractorType::query()
            ->orderBy('type_name')
            ->get(['type_id', 'type_name']);
    }

    
    // Create a new project for property owner
    public function createProject(array $data, $landTitleFile = null, $blueprintFile = null, $supportingDocuments = [], $housePhotos = [])
    {
        DB::beginTransaction();
        
        try {
           
            $data['project_location'] = implode(', ', [
                $data['street_address'],
                $data['city_municipality'],
                $data['province_state_region'],
                $data['postal_code']
            ]);
            
           
            $data['to_finish'] = $data['timeline_max'];
            
           
            if (isset($data['floor_area']) && is_array($data['floor_area'])) {
                $data['floor_area'] = (int) round(array_sum($data['floor_area']));
            }
            
         
            if (!empty($data['bidding_deadline'])) {
                $data['bidding_deadline'] = $this->normalizeDateTimeLocal($data['bidding_deadline']);
            }
            
           
            $data['project_status'] = 'open';
            
           
            if (isset($data['type_id']) && $data['type_id'] == 9 && !empty($data['contractor_type_other'])) {
                
            } else {
             
                unset($data['contractor_type_other']);
            }
            
          
            unset($data['street_address'], $data['city_municipality'], 
                  $data['province_state_region'], $data['postal_code'],
                  $data['timeline_min'], $data['timeline_max']);
            
           
            $project = Project::create($data);
            
           
            if (!empty($housePhotos)) {
                foreach ($housePhotos as $file) {
                    $filePath = $file->store('project_files/house_photos', 'public');
                    
                    ProjectFile::create([
                        'project_id' => $project->project_id,
                        'file_path' => $filePath,
                        'uploaded_at' => now(),
                    ]);
                }
            }
            
            // Handle land title upload (required)
            if ($landTitleFile) {
                $landTitlePath = $landTitleFile->store('project_files/land_titles', 'public');
                
                ProjectFile::create([
                    'project_id' => $project->project_id,
                    'file_type' => 'others', 
                    'file_path' => $landTitlePath,
                    'uploaded_at' => now(),
                ]);
            }
            
            // Handle blueprint upload (optional)
            if ($blueprintFile) {
                $blueprintPath = $blueprintFile->store('project_files/blueprints', 'public');
                
                ProjectFile::create([
                    'project_id' => $project->project_id,
                    'file_type' => 'blueprint',
                    'file_path' => $blueprintPath,
                    'uploaded_at' => now(),
                ]);
            }
            
            // Handle supporting documents upload (optional)
            if (!empty($supportingDocuments)) {
                foreach ($supportingDocuments as $file) {
                    $filePath = $file->store('project_files/supporting_documents', 'public');
                    
                    ProjectFile::create([
                        'project_id' => $project->project_id,
                        'file_type' => 'others', 
                        'file_path' => $filePath,
                        'uploaded_at' => now(),
                    ]);
                }
            }
            
            DB::commit();
            return $project;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    
    // Get property owner by user_id
    public function getPropertyOwnerByUserId($userId)
    {
        return PropertyOwner::where('user_id', $userId)->first();
    }

    
    // Get property owner by owner_id
    public function getPropertyOwnerById($ownerId)
    {
        return PropertyOwner::find($ownerId);
    }

    
    // Get all projects for a property owner
    public function getOwnerProjects($ownerId)
    {
        return Project::where('owner_id', $ownerId)
            ->with(['contractorType', 'files'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    
    // Get project by Project ID with relationships
    public function getProjectById($projectId)
    {
        return Project::with(['contractorType', 'files', 'owner'])
            ->find($projectId);
    }


    // Convert datetime-local format to database format
    private function normalizeDateTimeLocal(string $value): string
    {
        // Convert "YYYY-MM-DDTHH:MM" to "YYYY-MM-DD HH:MM:00"
        $v = str_replace('T', ' ', $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $v)) {
            return $v . ':00';
        }
        return $v;
    }
}