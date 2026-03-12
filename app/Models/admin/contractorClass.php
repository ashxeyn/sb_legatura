<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\user;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class contractorClass extends Model
{
    protected $table = 'contractors';
    protected $primaryKey = 'contractor_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'owner_id',
        'company_logo',
        'company_banner',
        'company_name',
        'company_start_date',
        'years_of_experience',
        'type_id',
        'contractor_type_other',
        'services_offered',
        'business_address',
        'company_email',
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
        'is_active',
        'suspension_until',
        'suspension_reason',
        'deletion_reason',
        'rejection_reason'
    ];

    public function bids(): HasMany
    {
        return $this->hasMany(bid::class, 'contractor_id', 'contractor_id');
    }

    public function propertyOwner()
    {
        return $this->hasOneThrough(
            User::class,
            \App\Models\owner\propertyOwnerClass::class,
            'owner_id',      // FK on property_owners
            'user_id',       // FK on users
            'owner_id',      // LK on contractors
            'user_id'        // LK on property_owners
        );
    }

    /**
     * Get contractors with filters
     */
    public function getContractors($search = null, $status = null, $dateFrom = null, $dateTo = null, $perPage = 15)
    {
        $query = DB::table('contractors')
            ->leftJoin('property_owners as cpo', 'contractors.owner_id', '=', 'cpo.owner_id')
            ->leftJoin('users', 'cpo.user_id', '=', 'users.user_id')
            ->leftJoin('bids', 'contractors.contractor_id', '=', 'bids.contractor_id')
            ->select(
                'contractors.*',
                'users.email',
                'users.username',
                'users.profile_pic',
                'users.first_name as authorized_rep_fname',
                'users.last_name as authorized_rep_lname',
                'users.middle_name as authorized_rep_mname',
                DB::raw('COUNT(bids.bid_id) as bids_count')
            )
            ->groupBy(
                'contractors.contractor_id',
                'contractors.owner_id',
                'contractors.company_name',
                'contractors.company_start_date',
                'contractors.years_of_experience',
                'contractors.type_id',
                'contractors.contractor_type_other',
                'contractors.services_offered',
                'contractors.business_address',
                'contractors.company_email',
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
                'contractors.verification_date',
                'contractors.is_active',
                'contractors.suspension_until',
                'contractors.suspension_reason',
                'contractors.deletion_reason',
                'contractors.rejection_reason',
                'contractors.completed_projects',
                'contractors.created_at',
                'contractors.updated_at',
                'users.email',
                'users.username',
                'users.profile_pic',
                'users.first_name',
                'users.last_name',
                'users.middle_name'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('contractors.company_name', 'like', "%{$search}%")
                  ->orWhere('users.first_name', 'like', "%{$search}%")
                  ->orWhere('users.last_name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        // Only show active contractors
        $query->where('contractors.is_active', 1);

        // Exclude deleted contractors
        $query->where('contractors.verification_status', '!=', 'deleted');

        if ($status) {
            $query->where('contractors.verification_status', $status === 'verified' ? 'approved' : 'pending');
        } else {
            $query->where('contractors.verification_status', 'approved');
        }

        // Filter by active, not deleted, not suspended
        $query->where('contractors.is_active', 1)
              ->whereNull('contractors.deletion_reason')
              ->where(function($q) {
                  $q->whereNull('contractors.suspension_until')
                    ->orWhere('contractors.suspension_until', 0);
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
            ->leftJoin('property_owners as cpo', 'contractors.owner_id', '=', 'cpo.owner_id')
            ->leftJoin('users', 'cpo.user_id', '=', 'users.user_id')
            ->select(
                'contractors.*',
                'users.user_id',
                'users.email',
                'users.username',
                'users.profile_pic',
                'users.first_name as authorized_rep_fname',
                'users.last_name as authorized_rep_lname',
                'users.middle_name as authorized_rep_mname'
            )
            ->where('contractors.contractor_id', $id)
            ->first();
    }

    /**
     * Add a new contractor (creates property owner first, then links contractor)
     */
    public function addContractor($data)
    {
        return DB::transaction(function () use ($data) {
            // First, create the property owner (user + property_owner record)
            // Generate username from email
            $username = explode('@', $data['company_email'])[0] . rand(100, 999);

            // Create User
            $userId = DB::table('users')->insertGetId([
                'email' => $data['company_email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'],
                'password_hash' => bcrypt('contractor123@!'),
                'OTP_hash' => 'admin_created',
                'user_type' => 'contractor',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create Property Owner record (identity hub)
            $ownerId = DB::table('property_owners')->insertGetId(array(
                'user_id' => $userId,
                'phone_number' => $data['company_phone'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ));

            // Create Contractor (align fields with legatura.sql)
            $contractorId = DB::table('contractors')->insertGetId(array(
                'owner_id' => $ownerId,
                'company_logo' => $data['company_logo'] ?? null,
                'company_banner' => $data['company_banner'] ?? null,
                'company_name' => $data['company_name'],
                'company_start_date' => $data['company_start_date'],
                'years_of_experience' => $data['years_of_experience'] ?? 0,
                'type_id' => $data['type_id'],
                'contractor_type_other' => $data['contractor_type_other'] ?? null,
                'services_offered' => $data['services_offered'] ?? null,
                'business_address' => $data['business_address'] ?? null,
                'company_email' => $data['company_email'],
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
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ));

            // Create Contractor Staff record (owner role)
            DB::table('contractor_staff')->insert(array(
                'contractor_id' => $contractorId,
                'owner_id' => $ownerId,
                'phone_number' => $data['company_phone'] ?? null,
                'company_role' => 'owner',
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
    public function editContractor($contractorId, $data)
    {
        return DB::transaction(function () use ($contractorId, $data) {
            // Get contractor to find owner_id
            $contractor = DB::table('contractors')->where('contractor_id', $contractorId)->first();

            if (!$contractor) {
                throw new \Exception('Contractor not found');
            }

            // Get property owner to update user email if changed
            $owner = DB::table('property_owners')->where('owner_id', $contractor->owner_id)->first();

            if ($owner && isset($data['company_email'])) {
                // Update user email
                DB::table('users')
                    ->where('user_id', $owner->user_id)
                    ->update([
                        'email' => $data['company_email'],
                        'updated_at' => now()
                    ]);
            }

            // Update Contractor
            $contractorUpdateData = [
                'company_name' => $data['company_name'],
                'company_start_date' => $data['company_start_date'],
                'years_of_experience' => $data['years_of_experience'],
                'type_id' => $data['type_id'],
                'contractor_type_other' => $data['contractor_type_other'] ?? null,
                'services_offered' => $data['services_offered'],
                'business_address' => $data['business_address'],
                'company_email' => $data['company_email'],
                'company_website' => $data['company_website'] ?? null,
                'company_social_media' => $data['company_social_media'] ?? null,
                'company_description' => $data['company_description'] ?? null,
                'picab_number' => $data['picab_number'],
                'picab_category' => $data['picab_category'],
                'picab_expiration_date' => $data['picab_expiration_date'],
                'business_permit_number' => $data['business_permit_number'],
                'business_permit_city' => $data['business_permit_city'],
                'business_permit_expiration' => $data['business_permit_expiration'],
                'tin_business_reg_number' => $data['tin_business_reg_number'],
                'updated_at' => now()
            ];

            if (isset($data['dti_sec_registration_photo'])) {
                $contractorUpdateData['dti_sec_registration_photo'] = $data['dti_sec_registration_photo'];
            }

            if (isset($data['company_logo'])) {
                $contractorUpdateData['company_logo'] = $data['company_logo'];
            }

            if (isset($data['company_banner'])) {
                $contractorUpdateData['company_banner'] = $data['company_banner'];
            }

            // Find contractor via property_owners
            $ownerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');

            DB::table('contractors')
                ->where('owner_id', $ownerId)
                ->update($contractorUpdateData);

            // Update User names
            DB::table('users')
                ->where('user_id', $userId)
                ->update([
                    'first_name' => $data['authorized_rep_fname'],
                    'last_name' => $data['authorized_rep_lname'],
                    'middle_name' => $data['authorized_rep_mname'],
                ]);

            // Update Contractor Staff (owner record)
            if ($ownerId) {
                DB::table('contractor_staff')
                    ->where('owner_id', $ownerId)
                    ->where('company_role', 'owner')
                    ->update([
                        'phone_number' => $data['phone_number']
                    ]);
            }

            return true;
        });
    }

    public function deleteContractor($contractorId, $reason)
    {
        return DB::transaction(function () use ($contractorId, $reason) {
            // Get contractor to find owner
            $contractor = DB::table('contractors')->where('contractor_id', $contractorId)->first();

            if ($contractor) {
                // Update Contractors table
                DB::table('contractors')
                    ->where('contractor_id', $contractorId)
                    ->update([
                        'verification_status' => 'deleted',
                        'is_active' => 0,
                        'deletion_reason' => $reason
                    ]);

                // Update Contractor Staff table
                DB::table('contractor_staff')
                    ->where('contractor_id', $contractorId)
                    ->where('company_role', 'owner')
                    ->update([
                        'is_active' => 0,
                        'deletion_reason' => $reason
                    ]);

                // Update User
                $userId = DB::table('property_owners')
                    ->where('owner_id', $contractor->owner_id)
                    ->value('user_id');
                if ($userId) {
                    DB::table('users')
                        ->where('user_id', $userId)
                        ->update([
                            'updated_at' => now()
                        ]);
                }
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
            ->join('property_owners as cpo', 'contractors.owner_id', '=', 'cpo.owner_id')
            ->join('users', 'cpo.user_id', '=', 'users.user_id')
            ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
            ->where('contractors.contractor_id', $contractorId)
            ->select(
                'contractors.*',
                'users.email',
                'users.username',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'users.user_type',
                'property_owners.profile_pic',
                'property_owners.cover_photo',
                'contractors.created_at as member_since',
                DB::raw("CASE WHEN contractor_types.type_name = 'Others' OR contractor_types.type_name IS NULL THEN contractors.contractor_type_other ELSE contractor_types.type_name END as contractor_type_name")
            )
            ->first();

        if (!$contractor)
            return null;

        // Get Representative (representative role from contractor_staff)
        $representative = DB::table('contractor_staff')
            ->join('property_owners', 'contractor_staff.owner_id', '=', 'property_owners.owner_id')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->where('contractor_staff.contractor_id', $contractorId)
            ->where('contractor_staff.company_role', 'representative')
            ->select(
                'users.first_name as authorized_rep_fname',
                'users.last_name as authorized_rep_lname',
                'users.middle_name as authorized_rep_mname',
                'contractor_staff.phone_number',
                'contractor_staff.company_role as role',
                'users.profile_pic as rep_profile_pic',
                'users.email as rep_email',
                'users.username as rep_username',
                'property_owners.profile_pic as rep_profile_pic',
                'contractor_staff.company_role as role'
            )
            ->first();

        $contractor->representative = $representative;

        // Get Projects where this contractor is selected
        $projects = DB::table('projects')
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('users as owner_users', 'property_owners.user_id', '=', 'owner_users.user_id')
            ->where('project_relationships.selected_contractor_id', $contractorId)
            ->select(
                'projects.*',
                'project_relationships.created_at',
                'owner_users.first_name as owner_first_name',
                'owner_users.last_name as owner_last_name',
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

        // Get Team Members (all contractor_staff for this contractor)
        $teamMembers = DB::table('contractor_staff')
            ->join('property_owners', 'contractor_staff.owner_id', '=', 'property_owners.owner_id')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->where('contractor_staff.contractor_id', $contractorId)
            ->select(
                'contractor_staff.*',
                'users.first_name as authorized_rep_fname',
                'users.last_name as authorized_rep_lname',
                'users.middle_name as authorized_rep_mname',
                'users.email',
                'users.username',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'property_owners.profile_pic'
            )
            ->orderByRaw("FIELD(contractor_staff.company_role, 'owner', 'manager', 'engineer', 'architect', 'representative', 'others')")
            ->get();

        $contractor->team_members = $teamMembers;

        // Store original is_active based on verification_status
        $originalIsActive = $contractor->is_active;

        // Check if OWNER is suspended (only owner suspension affects entire contractor)
        $ownerSuspended = DB::table('contractor_staff')
            ->where('contractor_id', $contractorId)
            ->where('company_role', 'owner')
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
     * Add a new team member to a contractor (links existing property owner as staff)
     */
    public function addTeamMember($data)
    {
        return DB::transaction(function () use ($data) {
            // The owner_id should be passed in $data
            // This links an existing property owner as a staff member

            // Create User (type: staff)
            $userId = DB::table('users')->insertGetId([
                'profile_pic' => $data['profile_pic'] ?? null,
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'username' => $username,
                'email' => $data['email'],
                'password_hash' => bcrypt('teammember123@!'),
                'OTP_hash' => 'admin_created',
                'user_type' => 'staff',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create Property Owner record (identity hub for staff)
            $ownerId = DB::table('property_owners')->insertGetId([
                'user_id' => $userId,
                'phone_number' => $data['phone_number'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create Contractor Staff (Team Member)
            DB::table('contractor_staff')->insert([
                'contractor_id' => $data['contractor_id'],
                'owner_id' => $ownerId,
                'phone_number' => $data['phone_number'],
                'company_role' => $data['role'],
                'if_others' => $data['role_other'] ?? null,
                'is_active' => 1,
                'created_at' => now()
            ]);

            // Do NOT change user_type - keep it as 'property_owner'
            // A property owner can be a staff member in multiple contractors without changing their user_type

            return [
                'owner_id' => $data['owner_id'],
                'contractor_id' => $data['contractor_id']
            ];
        });
    }

    public function cancelInvitation($staffId, $reason)
    {
        return DB::transaction(function () use ($contractorId, $newRepresentativeId) {
            // Get the current representative
            $currentRepresentative = DB::table('contractor_staff')
                ->where('contractor_id', $contractorId)
                ->where('company_role', 'representative')
                ->whereNull('deletion_reason')
                ->first();

            // Get the new representative details
            $newRepresentative = DB::table('contractor_staff')
                ->where('staff_id', $newRepresentativeId)
                ->where('contractor_id', $contractorId)
                ->whereNull('deletion_reason')
                ->first();

            if (!$newRepresentative) {
                throw new \Exception('Selected team member not found or is not active.');
            }

            // If there's a current representative, demote them
            if ($currentRepresentative) {
                // Save their current role to role_other if needed, then change to their previous role
                // If they have a role_other value, restore it to company_role
                $previousRole = $currentRepresentative->role_other ?: 'manager';

                DB::table('contractor_staff')
                    ->where('staff_id', $currentRepresentative->staff_id)
                    ->update([
                        'company_role' => $previousRole,
                        'role_other' => null // Clear role_other after restoration
                    ]);
            }

            // Promote the new representative
            // Save their current role to role_other before promoting
            DB::table('contractor_staff')
                ->where('staff_id', $newRepresentativeId)
                ->update([
                    'role_other' => $newRepresentative->company_role, // Save current role
                    'company_role' => 'representative' // Promote to representative
                ]);

            return [
                'success' => true,
                'new_representative' => $newRepresentative,
                'previous_representative' => $currentRepresentative
            ];
        });
    }
    /**
     * Suspend contractor
     */
    public function suspendContractor($id, $reason, $duration, $suspensionUntil)
    {
        return DB::transaction(function () use ($id, $reason, $duration, $suspensionUntil) {
            // Update contractors table
            DB::table('contractors')
                ->where('contractor_id', $id)
                ->update([
                    'is_active' => 0,
                    'suspension_reason' => $reason,
                    'suspension_until' => $suspensionUntil
                ]);

            // Suspend ALL contractor_staff for this contractor (owner suspension affects entire company)
            DB::table('contractor_staff')
                ->where('contractor_id', $id)
                ->where('is_active', 1) // Only suspend active staff
                ->whereNull('deletion_reason') // Not deleted/cancelled
                ->update([
                    'is_active' => 0,
                    'is_suspended' => 1,
                    'suspension_reason' => 'Contractor company suspended: ' . $reason,
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
