<?php

namespace App\Models\contractor;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

// Contractor Model (using existing table structure)
class contractor extends Model
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
        return $this->belongsTo(contractorType::class, 'type_id', 'type_id');
    }

    public function projects()
    {
        return $this->hasMany(project::class, 'selected_contractor_id', 'contractor_id');
    }
}

// Project Model (for contractor posting)
class project extends Model
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
    public function contractor()
    {
        return $this->belongsTo(contractor::class, 'selected_contractor_id', 'contractor_id');
    }

    public function contractorType()
    {
        return $this->belongsTo(contractorType::class, 'type_id', 'type_id');
    }

    public function files()
    {
        return $this->hasMany(projectFile::class, 'project_id', 'project_id');
    }
}


// Project File Model
class projectFile extends Model
{
    use HasFactory;

    protected $table = 'project_files';
    protected $primaryKey = 'file_id';
    public $incrementing = true;

    // Disable automatic timestamps since table uses uploaded_at instead
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'file_path',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    // Relationship
    public function project()
    {
        return $this->belongsTo(project::class, 'project_id', 'project_id');
    }
}

// Contractor Type Model
class contractorType extends Model
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
        return $this->hasMany(project::class, 'type_id', 'type_id');
    }
}

// Contractor Project Service
class contractorProjectService
{
    
    // Get all contractor types
    public function getContractorTypes()
    {
        return contractorType::query()
            ->orderBy('type_name')
            ->get(['type_id', 'type_name']);
    }

    
    // Create a new project for contractor
    public function createProject(array $data, $mediaFiles = [])
    {
        DB::beginTransaction();
        
        try {
            // Set project status to open
            $data['project_status'] = 'open';
            
            // Set default values if not provided
            if (empty($data['property_type'])) {
                $data['property_type'] = 'Residential'; 
            }
            
            if (empty($data['lot_size'])) {
                $data['lot_size'] = 0; 
            }
            
            if (empty($data['floor_area'])) {
                $data['floor_area'] = 0; 
            }
            
            if (empty($data['budget_range_min'])) {
                $data['budget_range_min'] = 0;
            }
            
            if (empty($data['budget_range_max'])) {
                $data['budget_range_max'] = 0;
            }
            
            if (empty($data['to_finish'])) {
                $data['to_finish'] = null;
            }
            
            // Set type_id from contractor if not provided
            if (empty($data['type_id'])) {
                $contractor = contractor::find($data['contractor_id']);
                if ($contractor && $contractor->type_id) {
                    $data['type_id'] = $contractor->type_id; 
                } else {
                    $data['type_id'] = 1; 
                }
            }
            
            if (empty($data['bidding_deadline'])) {
                $data['bidding_deadline'] = now()->addDays(30); 
            } else {
                
                $data['bidding_deadline'] = $this->normalizeDateTimeLocal($data['bidding_deadline']);
            }
            
            // Create project
            $project = project::create($data);
            
            // Handle media upload (photo/video) - optional
            if (!empty($mediaFiles)) {
                foreach ($mediaFiles as $file) {
                    $filePath = $file->store('project_files/contractor_media', 'public');
                    
                    projectFile::create([
                        'project_id' => $project->project_id,
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

    
    // Get contractor by user_id
    public function getContractorByUserId($userId)
    {
        return contractor::where('user_id', $userId)->first();
    }

    
    // Get contractor by contractor_id
    public function getContractorById($contractorId)
    {
        return contractor::find($contractorId);
    }

    
    // Get all projects for a contractor
    public function getContractorProjects($contractorId)
    {
        return project::where('selected_contractor_id', $contractorId)
            ->with(['contractorType', 'files'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    
    // Get project by ID with relationships
    public function getProjectById($projectId)
    {
        return project::with(['contractorType', 'files', 'contractor'])
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