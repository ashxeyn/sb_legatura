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
        return $this->belongsTo(\App\Models\admin\propertyOwnerClass::class, 'owner_id', 'owner_id');
    }

    // Get paginated list of contractors with search, status, and date filters
    public function getContractors($search = null, $status = null, $dateFrom = null, $dateTo = null, $perPage = 15)
    {
        $query = DB::table('contractors')
            ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
            ->leftJoin('bids', 'contractors.contractor_id', '=', 'bids.contractor_id')
            ->select(
                'contractors.contractor_id',
                'contractors.owner_id',
                'contractors.company_logo',
                'contractors.company_banner',
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
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'property_owners.profile_pic',
                DB::raw("CASE WHEN contractor_types.type_name = 'Others' OR contractor_types.type_name IS NULL THEN contractors.contractor_type_other ELSE contractor_types.type_name END as contractor_type_name"),
                DB::raw('COUNT(DISTINCT bids.bid_id) as bids_count')
            )
            ->groupBy(
                'contractors.contractor_id',
                'contractors.owner_id',
                'contractors.company_logo',
                'contractors.company_banner',
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
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'property_owners.profile_pic',
                'contractor_types.type_name'
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

        if ($dateFrom) {
            $query->whereDate('contractors.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('contractors.created_at', '<=', $dateTo);
        }

        return $query->orderBy('contractors.created_at', 'desc')->paginate($perPage);
    }

    // Get contractor details by ID with owner and type information
    public function getContractorById($id)
    {
        return DB::table('contractors')
            ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
            ->select(
                'contractors.*',
                'users.user_id',
                'users.email',
                'users.username',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'property_owners.profile_pic',
                DB::raw("CASE WHEN contractor_types.type_name = 'Others' OR contractor_types.type_name IS NULL THEN contractors.contractor_type_other ELSE contractor_types.type_name END as contractor_type_name")
            )
            ->where('contractors.contractor_id', $id)
            ->first();
    }

    // Create new contractor linked to property owner
    public function addContractor($data)
    {
        return DB::transaction(function () use ($data) {
            // If an existing owner_id is provided, create a contractor record linked to that owner.
            if (!empty($data['owner_id'])) {
                $ownerId = $data['owner_id'];

                $owner = DB::table('property_owners')->where('owner_id', $ownerId)->first();
                if (!$owner) {
                    throw new \Exception('Property owner not found');
                }

                // Ensure owner is verified and active
                if (isset($owner->verification_status) && $owner->verification_status !== 'approved') {
                    throw new \Exception('Selected property owner is not verified');
                }
                if (isset($owner->is_active) && !$owner->is_active) {
                    throw new \Exception('Selected property owner is not active');
                }

                // Prevent linking if owner already has a contractor or is contractor staff
                $hasContractor = DB::table('contractors')
                    ->where('owner_id', $ownerId)
                    ->where('verification_status', '!=', 'deleted')
                    ->exists();
                if ($hasContractor) {
                    throw new \Exception('Selected property owner already owns a contractor');
                }

                $hasStaff = DB::table('contractor_staff')
                    ->where('owner_id', $ownerId)
                    ->whereNull('deletion_reason')
                    ->where('is_active', 1)
                    ->exists();
                if ($hasStaff) {
                    throw new \Exception('Selected property owner is already a staff member of a contractor');
                }

                // Ensure the related user_type reflects that they're also a contractor owner
                $user = DB::table('users')->where('user_id', $owner->user_id)->first();
                if ($user && $user->user_type !== 'both') {
                    DB::table('users')->where('user_id', $user->user_id)->update([
                        'user_type' => 'both',
                        'updated_at' => now()
                    ]);
                }

                // Create Contractor record using the existing owner
                $contractorId = DB::table('contractors')->insertGetId([
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
                    'company_email' => $data['company_email'] ?? null,
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
                ]);

                return [
                    'contractor_id' => $contractorId,
                    'owner_id' => $ownerId,
                    'user_id' => $owner->user_id,
                    'email' => $data['company_email'] ?? null,
                    'username' => $user->username ?? null
                ];
            }

            // Reject creation without an existing owner_id.
            // This endpoint requires linking to an existing property owner.
            throw new \Exception('owner_id is required. Please select an existing property owner.');
        });
    }

    // Update contractor information and linked user email
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

            DB::table('contractors')
                ->where('contractor_id', $contractorId)
                ->update($contractorUpdateData);

            return true;
        });
    }

    // Delete contractor and cascade deletion to staff members
    public function deleteContractor($contractorId, $reason)
    {
        return DB::transaction(function () use ($contractorId, $reason) {
            // Get contractor to find owner_id
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

                // Update contractor_staff table (mark all staff as deleted)
                DB::table('contractor_staff')
                    ->where('contractor_id', $contractorId)
                    ->update([
                        'is_active' => 0,
                        'deletion_reason' => $reason
                    ]);

                // Check if owner has other contractor companies or is staff elsewhere
                $ownerHasOtherContractors = DB::table('contractors')
                    ->where('owner_id', $contractor->owner_id)
                    ->where('contractor_id', '!=', $contractorId)
                    ->where('verification_status', '!=', 'deleted')
                    ->exists();

                $ownerIsStaffElsewhere = DB::table('contractor_staff')
                    ->where('owner_id', $contractor->owner_id)
                    ->where('contractor_id', '!=', $contractorId)
                    ->where('is_active', 1)
                    ->exists();

                // If owner has no other contractor connections, change user_type back to property_owner
                if (!$ownerHasOtherContractors && !$ownerIsStaffElsewhere) {
                    $owner = DB::table('property_owners')->where('owner_id', $contractor->owner_id)->first();
                    if ($owner) {
                        DB::table('users')
                            ->where('user_id', $owner->user_id)
                            ->update(['user_type' => 'property_owner']);
                    }
                }
            }

            return true;
        });
    }

    // Fetch contractor view with projects, team members, and representative info
    public function fetchContractorView($contractorId)
    {
        // Get main contractor data
        $contractor = DB::table('contractors')
            ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
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

        // Get Representative (from contractor_staff with role 'representative')
        $representative = DB::table('contractor_staff')
            ->join('property_owners', 'contractor_staff.owner_id', '=', 'property_owners.owner_id')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->where('contractor_staff.contractor_id', $contractorId)
            ->where('contractor_staff.company_role', 'representative')
            ->where('contractor_staff.is_active', 1)
            ->select(
                'users.first_name',
                'users.middle_name',
                'users.last_name',
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
                'property_owners.profile_pic as owner_profile_pic'
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
                'users.email',
                'users.username',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'property_owners.profile_pic'
            )
            ->orderByRaw("FIELD(contractor_staff.company_role, 'manager', 'engineer', 'architect', 'representative', 'others')")
            ->get();

        $contractor->team_members = $teamMembers;

        return $contractor;
    }

    // Add existing property owner as team member to contractor
    public function addTeamMember($data)
    {
        return DB::transaction(function () use ($data) {
            // The owner_id should be passed in $data
            // This links an existing property owner as a staff member

            // Create Contractor Staff entry with is_active = 0 (pending)
            DB::table('contractor_staff')->insert([
                'contractor_id' => $data['contractor_id'],
                'owner_id' => $data['owner_id'],
                'company_role' => $data['role'],
                'role_if_others' => $data['role_other'] ?? null,
                'is_active' => 0, // Pending status
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

    // Cancel team member invitation
    public function cancelInvitation($staffId, $reason)
    {
        return DB::transaction(function () use ($staffId, $reason) {
            // Update the contractor_staff record with deletion_reason (invitation cancelled)
            // Keep is_suspended = 0 since this is a cancelled invitation, not a suspension
            $updated = DB::table('contractor_staff')
                ->where('staff_id', $staffId)
                ->update([
                    'deletion_reason' => $reason,
                    'is_active' => 0
                ]);

            return $updated > 0;
        });
    }

    // Reapply cancelled team member invitation
    public function reapplyInvitation($staffId)
    {
        return DB::transaction(function () use ($staffId) {
            // Reapply invitation by clearing deletion_reason, effectively putting it back to pending
            $updated = DB::table('contractor_staff')
                ->where('staff_id', $staffId)
                ->update([
                    'deletion_reason' => null,
                    'is_active' => 0
                ]);

            return $updated > 0;
        });
    }

    // Change contractor representative and demote previous representative
    public function changeRepresentative($contractorId, $newRepresentativeStaffId)
    {
        return DB::transaction(function () use ($contractorId, $newRepresentativeStaffId) {
            // Get the contractor to find the owner_id
            $contractor = DB::table('contractors')
                ->where('contractor_id', $contractorId)
                ->first();

            if (!$contractor) {
                throw new \Exception('Contractor not found.');
            }

            // Get the current representative using contractor_id
            $currentRepresentative = DB::table('contractor_staff')
                ->where('contractor_id', $contractorId)
                ->where('company_role', 'representative')
                ->where('is_active', 1)
                ->first();

            // Get the new representative details - must be ACTIVE only
            // Cannot be: Pending (is_active=0), Deactivated (is_suspended=1), or Cancelled (deletion_reason!=NULL)
            $newRepresentative = DB::table('contractor_staff')
                ->where('staff_id', $newRepresentativeStaffId)
                ->where('contractor_id', $contractorId)
                ->where('is_active', 1)  // Must be active
                ->where('is_suspended', 0)  // Not deactivated
                ->whereNull('deletion_reason')  // Not cancelled
                ->first();

            if (!$newRepresentative) {
                throw new \Exception('Selected team member not found or is not active.');
            }

            // If there's a current representative, demote them
            if ($currentRepresentative) {
                // Save their current role (representative) to company_role_before before changing it
                // Then restore to their previous role if available
                $previousRole = $currentRepresentative->company_role_before ?: 'manager';

                DB::table('contractor_staff')
                    ->where('staff_id', $currentRepresentative->staff_id)
                    ->update([
                        'company_role' => $previousRole,
                        'company_role_before' => 'representative', // Save that they were representative before
                        'role_if_others' => null
                    ]);
            }

            // Promote the new representative
            // Save their current role to company_role_before before promoting
            // If they have no previous role (newly added as representative), set a default
            $previousRole = $newRepresentative->company_role;
            if (empty($previousRole) || $previousRole === 'representative') {
                $previousRole = 'manager'; // Default role if none exists
            }

            DB::table('contractor_staff')
                ->where('staff_id', $newRepresentativeStaffId)
                ->update([
                    'company_role_before' => $previousRole,
                    'company_role' => 'representative',
                    'is_active' => 0, // Set to pending
                    'role_if_others' => null
                ]);

            return [
                'success' => true,
                'new_representative' => $newRepresentative,
                'previous_representative' => $currentRepresentative
            ];
        });
    }
    // Suspend contractor and cascade suspension to staff and bids
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

            // Suspend all staff members of this contractor
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
