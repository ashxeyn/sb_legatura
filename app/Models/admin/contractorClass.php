<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class contractorClass extends Model
{
    protected $table = 'contractors';
    protected $primaryKey = 'contractor_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'company_name',
        'company_start_date',
        'years_of_experience',
        'type_id',
        'contractor_type_other',
        'services_offered',
        'business_address',
        'company_email',
        'company_phone',
        'company_website',
        'company_social_media',
        'picab_number',
        'picab_category',
        'picab_expiration_date',
        'business_permit_number',
        'business_permit_city',
        'business_permit_expiration',
        'tin_business_reg_number',
        'dti_sec_registration_photo',
        'verification_status',
        'is_active',
        'suspension_until',
        'suspension_reason',
        'deletion_reason',
        'verification_date'
    ];

    public function bids(): HasMany
    {
        return $this->hasMany(bid::class, 'contractor_id', 'contractor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get contractors with filters
     */
    public function getContractors($search = null, $status = null, $dateFrom = null, $dateTo = null, $perPage = 15)
    {
        $query = DB::table('contractors')
            ->leftJoin('users', 'contractors.user_id', '=', 'users.user_id')
            ->leftJoin('contractor_users', function($join) {
                $join->on('contractors.contractor_id', '=', 'contractor_users.contractor_id')
                     ->where('contractor_users.role', '=', 'owner');
            })
            ->leftJoin('bids', 'contractors.contractor_id', '=', 'bids.contractor_id')
            ->select(
                'contractors.*',
                'users.email',
                'users.username',
                'users.profile_pic',
                'contractor_users.authorized_rep_fname',
                'contractor_users.authorized_rep_lname',
                'contractor_users.authorized_rep_mname',
                DB::raw('COUNT(bids.bid_id) as bids_count')
            )
            ->groupBy(
                'contractors.contractor_id',
                'contractors.user_id',
                'contractors.company_name',
                'contractors.company_start_date',
                'contractors.years_of_experience',
                'contractors.type_id',
                'contractors.contractor_type_other',
                'contractors.services_offered',
                'contractors.business_address',
                'contractors.company_email',
                'contractors.company_phone',
                'contractors.company_website',
                'contractors.company_social_media',
                'contractors.company_description',
                'contractors.picab_number',
                'contractors.picab_category',
                'contractors.picab_expiration_date',
                'contractors.business_permit_number',
                'contractors.business_permit_city',
                'contractors.business_permit_expiration',
                'contractors.tin_business_reg_number',
                'contractors.dti_sec_registration_photo',
                'contractors.verification_status',
                'contractors.is_active',
                'contractors.suspension_until',
                'contractors.suspension_reason',
                'contractors.deletion_reason',
                'contractors.verification_date',
                'contractors.rejection_reason',
                'contractors.completed_projects',
                'contractors.created_at',
                'contractors.updated_at',
                'users.email',
                'users.username',
                'users.profile_pic',
                'contractor_users.authorized_rep_fname',
                'contractor_users.authorized_rep_lname',
                'contractor_users.authorized_rep_mname'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('contractors.company_name', 'like', "%{$search}%")
                  ->orWhere('contractor_users.authorized_rep_fname', 'like', "%{$search}%")
                  ->orWhere('contractor_users.authorized_rep_lname', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('contractors.verification_status', $status === 'verified' ? 'approved' : 'pending');
        } else {
            $query->where('contractors.verification_status', 'approved');
        }

        // Filter by owner status: active, not deleted, not suspended
        $query->where('contractor_users.is_active', 1)
              ->where('contractor_users.is_deleted', 0)
              ->where(function($q) {
                  $q->whereNull('contractor_users.suspension_until')
                    ->orWhere('contractor_users.suspension_until', 0);
              });

        if ($dateFrom) {
            $query->whereDate('contractors.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('contractors.created_at', '<=', $dateTo);
        }

        return $query->orderBy('contractors.created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get contractor by ID
     */
    public function getContractorById($id)
    {
        return DB::table('contractors')
            ->leftJoin('users', 'contractors.user_id', '=', 'users.user_id')
            ->leftJoin('contractor_users', function($join) {
                $join->on('contractors.contractor_id', '=', 'contractor_users.contractor_id')
                     ->where('contractor_users.role', '=', 'owner');
            })
            ->select(
                'contractors.*',
                'users.email',
                'users.username',
                'users.profile_pic',
                'contractor_users.authorized_rep_fname',
                'contractor_users.authorized_rep_lname',
                'contractor_users.authorized_rep_mname'
            )
            ->where('contractors.contractor_id', $id)
            ->first();
    }

    /**
     * Add a new contractor
     */
    public function addContractor($data)
    {
        return DB::transaction(function () use ($data) {
            // Generate Username
            do {
                $username = 'contractor_' . mt_rand(1000, 9999);
            } while (DB::table('users')->where('username', $username)->exists());

            // Create User (use DB insert to align with propertyOwner flow and DB schema)
            $userId = DB::table('users')->insertGetId(array(
                'profile_pic' => $data['profile_pic'] ?? null,
                'username' => $username,
                'email' => $data['company_email'],
                'password_hash' => bcrypt('contractor123@!'),
                'OTP_hash' => 'admin_created',
                'user_type' => 'contractor',
                'created_at' => now(),
                'updated_at' => now()
            ));

            // Create Contractor (align fields with legatura.sql)
            $contractorId = DB::table('contractors')->insertGetId(array(
                'user_id' => $userId,
                'company_name' => $data['company_name'],
                'company_start_date' => $data['company_start_date'],
                'years_of_experience' => $data['years_of_experience'] ?? 0,
                'type_id' => $data['type_id'],
                'contractor_type_other' => $data['contractor_type_other'] ?? null,
                'services_offered' => $data['services_offered'] ?? null,
                'business_address' => $data['business_address'] ?? null,
                'company_email' => $data['company_email'],
                'company_phone' => $data['company_phone'] ?? null,
                'company_website' => $data['company_website'] ?? null,
                'company_social_media' => $data['company_social_media'] ?? null,
                'company_description' => $data['company_description'] ?? null,
                'picab_number' => $data['picab_number'] ?? null,
                'picab_category' => $data['picab_category'] ?? null,
                'picab_expiration_date' => $data['picab_expiration_date'] ?? null,
                'business_permit_number' => $data['business_permit_number'] ?? null,
                'business_permit_city' => $data['business_permit_city'] ?? null,
                'business_permit_expiration' => $data['business_permit_expiration'] ?? null,
                'tin_business_reg_number' => $data['tin_business_reg_number'] ?? null,
                'dti_sec_registration_photo' => $data['dti_sec_registration_photo'] ?? null,
                'verification_status' => 'approved',
                'verification_date' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ));

            // Create Contractor User (Representative)
            DB::table('contractor_users')->insert(array(
                'contractor_id' => $contractorId,
                'user_id' => $userId,
                'authorized_rep_fname' => $data['first_name'],
                'authorized_rep_lname' => $data['last_name'],
                'authorized_rep_mname' => $data['middle_name'],
                'phone_number' => $data['company_phone'] ?? null,
                'role' => 'owner',
                'is_active' => 1,
                'created_at' => now()
            ));

            return array(
                'username' => $username,
                'email' => $data['company_email']
            );
        });
    }

    /**
     * Edit an existing contractor
     */
    public function editContractor($userId, $data)
    {
        return DB::transaction(function () use ($userId, $data) {
            // Update User
            $userUpdateData = [
                'email' => $data['company_email'],
                'updated_at' => $data['updated_at']
            ];
            if (isset($data['profile_pic'])) {
                $userUpdateData['profile_pic'] = $data['profile_pic'];
            }
            if (isset($data['password_hash'])) {
                $userUpdateData['password_hash'] = $data['password_hash'];
            }

            DB::table('users')
                ->where('user_id', $userId)
                ->update($userUpdateData);

            // Update Contractor
            $contractorUpdateData = [
                'company_name' => $data['company_name'],
                'company_start_date' => $data['company_start_date'],
                'years_of_experience' => $data['years_of_experience'],
                'type_id' => $data['type_id'],
                'contractor_type_other' => $data['contractor_type_other'],
                'services_offered' => $data['services_offered'],
                'business_address' => $data['business_address'],
                'company_email' => $data['company_email'],
                'company_phone' => $data['company_phone'],
                'company_website' => $data['company_website'],
                'company_social_media' => $data['company_social_media'],
                'picab_number' => $data['picab_number'],
                'picab_category' => $data['picab_category'],
                'picab_expiration_date' => $data['picab_expiration_date'],
                'business_permit_number' => $data['business_permit_number'],
                'business_permit_city' => $data['business_permit_city'],
                'business_permit_expiration' => $data['business_permit_expiration'],
                'tin_business_reg_number' => $data['tin_business_reg_number'],
                'updated_at' => $data['updated_at']
            ];

            if (isset($data['dti_sec_registration_photo'])) {
                $contractorUpdateData['dti_sec_registration_photo'] = $data['dti_sec_registration_photo'];
            }

            DB::table('contractors')
                ->where('user_id', $userId)
                ->update($contractorUpdateData);

            // Update Contractor User (Representative)
            DB::table('contractor_users')
                ->where('user_id', $userId)
                ->where('role', 'owner')
                ->update([
                    'authorized_rep_fname' => $data['authorized_rep_fname'],
                    'authorized_rep_lname' => $data['authorized_rep_lname'],
                    'authorized_rep_mname' => $data['authorized_rep_mname'],
                    'phone_number' => $data['phone_number']
                ]);

            return true;
        });
    }

    public function deleteContractor($contractorId, $reason)
    {
        return DB::transaction(function () use ($contractorId, $reason) {
            // Get contractor to find user_id
            $contractor = DB::table('contractors')->where('contractor_id', $contractorId)->first();

            if ($contractor) {
                // Update Contractors table
                DB::table('contractors')
                    ->where('contractor_id', $contractorId)
                    ->update([
                        'verification_status' => 'deleted'
                    ]);

                // Update Contractor Users table
                DB::table('contractor_users')
                    ->where('contractor_id', $contractorId)
                    ->where('role', 'owner')
                    ->update([
                        'is_deleted' => 1,
                        'is_active' => 0,
                        'deletion_reason' => $reason
                    ]);

                // Update User
                DB::table('users')
                    ->where('user_id', $contractor->user_id)
                    ->update([
                        'updated_at' => now()
                    ]);
            }

            return true;
        });
    }

    /**
     * Fetch contractor view with all related data
     */
    public function fetchContractorView($contractorId)
    {
        // Get main contractor data
        $contractor = DB::table('contractors')
            ->join('users', 'contractors.user_id', '=', 'users.user_id')
            ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
            ->where('contractors.contractor_id', $contractorId)
            ->select(
                'contractors.*',
                'users.email',
                'users.username',
                'users.profile_pic',
                'users.cover_photo',
                'users.user_type',
                'users.created_at as member_since',
                DB::raw("CASE WHEN contractors.verification_status = 'approved' THEN 1 ELSE 0 END as is_active"),
                DB::raw("CASE WHEN contractor_types.type_name = 'Others' OR contractor_types.type_name IS NULL THEN contractors.contractor_type_other ELSE contractor_types.type_name END as contractor_type_name")
            )
            ->first();

        if (!$contractor) return null;

        // Get Representative (representative role from contractor_users)
        $representative = DB::table('contractor_users')
            ->join('users', 'contractor_users.user_id', '=', 'users.user_id')
            ->where('contractor_users.contractor_id', $contractorId)
            ->where('contractor_users.role', 'representative')
            ->select(
                'contractor_users.authorized_rep_fname',
                'contractor_users.authorized_rep_lname',
                'contractor_users.authorized_rep_mname',
                'contractor_users.phone_number',
                'contractor_users.role',
                'users.profile_pic as rep_profile_pic',
                'users.email as rep_email',
                'users.username as rep_username'
            )
            ->first();

        $contractor->representative = $representative;

        // Get Projects where this contractor is selected
        $projects = DB::table('projects')
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('users as owner_users', 'property_owners.user_id', '=', 'owner_users.user_id')
            ->where('projects.selected_contractor_id', $contractorId)
            ->select(
                'projects.*',
                'project_relationships.created_at',
                'property_owners.first_name as owner_first_name',
                'property_owners.last_name as owner_last_name',
                'owner_users.profile_pic as owner_profile_pic'
            )
            ->orderBy('project_relationships.created_at', 'desc')
            ->get();

        // Calculate project counts
        $completedCount = $projects->where('project_status', 'completed')->count();
        $ongoingCount = $projects->where('project_status', 'in_progress')->count();
        $totalProjects = $projects->count();

        // Attach to contractor object
        $contractor->projects = $projects;
        $contractor->completed_projects_count = $completedCount;
        $contractor->ongoing_projects_count = $ongoingCount;
        $contractor->total_projects_count = $totalProjects;

        // Get Team Members (all contractor_users for this contractor)
        $teamMembers = DB::table('contractor_users')
            ->join('users', 'contractor_users.user_id', '=', 'users.user_id')
            ->where('contractor_users.contractor_id', $contractorId)
            ->select(
                'contractor_users.*',
                'users.email',
                'users.username',
                'users.profile_pic'
            )
            ->orderByRaw("FIELD(contractor_users.role, 'owner', 'manager', 'engineer', 'architect', 'representative', 'others')")
            ->get();

        $contractor->team_members = $teamMembers;

        // Store original is_active based on verification_status
        $originalIsActive = $contractor->is_active;

        // Check if OWNER is suspended (only owner suspension affects entire contractor)
        $ownerSuspended = DB::table('contractor_users')
            ->where('contractor_id', $contractorId)
            ->where('role', 'owner')
            ->where('is_active', 0)
            ->first();

        if ($ownerSuspended) {
            $contractor->is_active = 0;
            $contractor->suspension_reason = $ownerSuspended->suspension_reason ?? null;
            $contractor->suspension_until = $ownerSuspended->suspension_until ?? null;
        } else {
            // Owner is not suspended - restore original is_active and clear suspension fields
            $contractor->is_active = $originalIsActive;
            $contractor->suspension_reason = null;
            $contractor->suspension_until = null;
        }

        return $contractor;
    }

    /**
     * Add a new team member to a contractor
     */
    public function addTeamMember($data)
    {
        return DB::transaction(function () use ($data) {
            // Generate Username
            do {
                $username = 'staff_' . mt_rand(1000, 9999);
            } while (DB::table('users')->where('username', $username)->exists());

            // Create User (type: staff)
            $userId = DB::table('users')->insertGetId([
                'profile_pic' => $data['profile_pic'] ?? null,
                'username' => $username,
                'email' => $data['email'],
                'password_hash' => bcrypt('teammember123@!'),
                'OTP_hash' => 'admin_created',
                'user_type' => 'staff',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create Contractor User (Team Member)
            DB::table('contractor_users')->insert([
                'contractor_id' => $data['contractor_id'],
                'user_id' => $userId,
                'authorized_rep_fname' => $data['first_name'],
                'authorized_rep_mname' => $data['middle_name'] ?? null,
                'authorized_rep_lname' => $data['last_name'],
                'phone_number' => $data['phone_number'],
                'role' => $data['role'],
                'if_others' => $data['role_other'] ?? null,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => now()
            ]);

            return [
                'user_id' => $userId,
                'username' => $username,
                'email' => $data['email']
            ];
        });
    }

    public function changeRepresentative($contractorId, $newRepresentativeId)
    {
        return DB::transaction(function () use ($contractorId, $newRepresentativeId) {
            // Get the current representative
            $currentRepresentative = DB::table('contractor_users')
                ->where('contractor_id', $contractorId)
                ->where('role', 'representative')
                ->where('is_deleted', 0)
                ->first();

            // Get the new representative details
            $newRepresentative = DB::table('contractor_users')
                ->where('contractor_user_id', $newRepresentativeId)
                ->where('contractor_id', $contractorId)
                ->where('is_deleted', 0)
                ->first();

            if (!$newRepresentative) {
                throw new \Exception('Selected team member not found.');
            }

            // If there's a current representative, demote them
            if ($currentRepresentative) {
                // Save their current role to role_other if needed, then change to their previous role
                // If they have a role_other value, restore it to role
                $previousRole = $currentRepresentative->role_other ?: 'manager';

                DB::table('contractor_users')
                    ->where('contractor_user_id', $currentRepresentative->contractor_user_id)
                    ->update([
                        'role' => $previousRole,
                        'role_other' => null // Clear role_other after restoration
                    ]);
            }

            // Promote the new representative
            // Save their current role to role_other before promoting
            DB::table('contractor_users')
                ->where('contractor_user_id', $newRepresentativeId)
                ->update([
                    'role_other' => $newRepresentative->role, // Save current role
                    'role' => 'representative' // Promote to representative
                ]);

            return [
                'success' => true,
                'new_representative' => $newRepresentative,
                'previous_representative' => $currentRepresentative
            ];
        });
    }

    /**
     * Suspend contractor (suspends all members when owner is suspended)
     */
    public function suspendContractor($id, $reason, $duration, $suspensionUntil)
    {
        return DB::transaction(function () use ($id, $reason, $duration, $suspensionUntil) {
            // Suspend ALL contractor_users for this contractor (owner suspension affects entire company)
            DB::table('contractor_users')
                ->where('contractor_id', $id)
                ->update([
                    'is_active' => 0,
                    'suspension_reason' => $reason,
                    'suspension_until' => $suspensionUntil
                ]);

            // Get contractor info
            $contractor = DB::table('contractors')->where('contractor_id', $id)->first();

            if ($contractor) {
                // Pause ongoing projects/bids
                $bidIds = DB::table('bids')
                    ->where('contractor_id', $id)
                    ->whereIn('bid_status', ['pending', 'approved'])
                    ->pluck('bid_id');

                if ($bidIds->isNotEmpty()) {
                    DB::table('bids')
                        ->whereIn('bid_id', $bidIds)
                        ->update([
                            'bid_status' => 'withdrawn',
                            'updated_at' => now()
                        ]);
                }
            }

            return $contractor;
        });
    }
}
