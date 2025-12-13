<?php

namespace App\Models\applyBids;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

// Require contractor model to ensure it's loaded
require_once __DIR__ . '/../contractor/contractorProjectClass.php';

// Bid Model
class bid extends Model
{
    use HasFactory;

    protected $table = 'bids';
    protected $primaryKey = 'bid_id';
    public $incrementing = true;
    
    // Disable automatic timestamps since table uses submitted_at instead
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'contractor_id',
        'proposed_cost',
        'estimated_timeline',
        'contractor_notes',
        'bid_status',
        'submitted_at',
        'decision_date',
    ];

    protected $casts = [
        'proposed_cost' => 'decimal:2',
        'estimated_timeline' => 'integer',
        'submitted_at' => 'datetime',
        'decision_date' => 'datetime',
    ];

    // Relationships
    public function project()
    {
        // Use the propertyOwner namespace project model
        return $this->belongsTo(\App\Models\propertyOwner\project::class, 'project_id', 'project_id');
    }

    public function contractor()
    {
        // Using the correct namespace for contractor model
        return $this->belongsTo(\App\Models\contractor\contractor::class, 'contractor_id', 'contractor_id');
    }

    public function files()
    {
        return $this->hasMany(bidFile::class, 'bid_id', 'bid_id');
    }
}

// Bid File Model
class bidFile extends Model
{
    use HasFactory;

    protected $table = 'bid_files';
    protected $primaryKey = 'file_id';
    public $incrementing = true;

    // Disable automatic timestamps since table uses uploaded_at instead
    public $timestamps = false;

    protected $fillable = [
        'bid_id',
        'file_name',
        'file_path',
        'description',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];
    
    // Set uploaded_at automatically when creating
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($bidFile) {
            if (empty($bidFile->uploaded_at)) {
                $bidFile->uploaded_at = now();
            }
        });
    }

    // Relationship
    public function bid()
    {
        return $this->belongsTo(bid::class, 'bid_id', 'bid_id');
    }
}

// Bid Application Service
class bidApplicationService
{
    /**
     * Create a new bid application
     *
     * @param array $data
     * @param array $files
     * @return bid
     */
    public function createBid($data, $files = [])
    {
        DB::beginTransaction();
        try {
            // Create bid - using insert instead of create to avoid timestamp issues
            $bidId = DB::table('bids')->insertGetId([
                'project_id' => $data['project_id'],
                'contractor_id' => $data['contractor_id'],
                'proposed_cost' => $data['proposed_cost'],
                'estimated_timeline' => $data['estimated_timeline'],
                'contractor_notes' => $data['contractor_notes'] ?? null,
                'bid_status' => 'submitted',
                'submitted_at' => now(),
            ]);
            
            // Get the created bid
            $bid = bid::find($bidId);

            // Handle file uploads
            if (!empty($files)) {
                foreach ($files as $file) {
                    $filePath = $file->store('bid_files/supporting_documents', 'public');
                    $fileName = $file->getClientOriginalName();
                    
                    // Use DB insert to avoid timestamp issues
                    DB::table('bid_files')->insert([
                        'bid_id' => $bid->bid_id,
                        'file_name' => $fileName,
                        'file_path' => $filePath,
                        'description' => null,
                        'uploaded_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return $bid;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get bid by ID
     *
     * @param int $bidId
     * @return bid|null
     */
    public function getBidById($bidId)
    {
        return bid::with(['project', 'contractor', 'files'])->find($bidId);
    }

    /**
     * Get all bids for a project
     *
     * @param int $projectId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBidsByProjectId($projectId)
    {
        return bid::where('project_id', $projectId)
            ->with(['contractor', 'files'])
            ->get();
    }

    /**
     * Get all bids for a contractor
     *
     * @param int $contractorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBidsByContractorId($contractorId)
    {
        return bid::where('contractor_id', $contractorId)
            ->with(['project', 'files'])
            ->get();
    }

    /**
     * Check if contractor has already submitted a bid for a project
     *
     * @param int $projectId
     * @param int $contractorId
     * @return bool
     */
    public function hasBidForProject($projectId, $contractorId)
    {
        return bid::where('project_id', $projectId)
            ->where('contractor_id', $contractorId)
            ->exists();
    }

    /**
     * Get contractor by user_id
     *
     * @param int $userId
     * @return \App\Models\contractor\contractor|null
     */
    public function getContractorByUserId($userId)
    {
        return \App\Models\contractor\contractor::where('user_id', $userId)->first();
    }
}

