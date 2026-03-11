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
        'deletion_reason'
    ];

    public function bids(): HasMany
    {
        return $this->hasMany(bid::class, 'contractor_id', 'contractor_id');
    }

    /**
     * Get the user associated with this contractor through property_owners.
     * contractors.owner_id → property_owners.owner_id → users.user_id
     */
    public function getUser()
    {
        return DB::table('property_owners')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->where('property_owners.owner_id', $this->owner_id)
            ->select('users.*')
            ->first();
    }

    /**
     * Get contractors with filters
     */
    public function getContractors($search = null, $status = null, $dateFrom = null, $dateTo = null, $perPage = 15)
    {
        $query = DB::table('contractors')
            ->leftJoin('property_owners as owner_po', 'contractors.owner_id', '=', 'owner_po.owner_id')
            ->leftJoin('users as owner_u', 'owner_po.user_id', '=', 'owner_u.user_id')
            ->select(
                'contractors.*',
                'owner_u.email',
                'owner_u.username',
                'owner_u.first_name as authorized_rep_fname',
                'owner_u.last_name as authorized_rep_lname',
                'owner_u.middle_name as authorized_rep_mname',
                DB::raw('(SELECT COUNT(*) FROM bids WHERE bids.contractor_id = contractors.contractor_id) as bids_count')
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('contractors.company_name', 'like', "%{$search}%")
                  ->orWhere('owner_u.first_name', 'like', "%{$search}%")
                  ->orWhere('owner_u.last_name', 'like', "%{$search}%")
                  ->orWhere('owner_u.email', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('contractors.verification_status', $status === 'verified' ? 'approved' : 'pending');
        } else {
            $query->where('contractors.verification_status', 'approved');
        }

        // Filter by contractor status: active, not deleted
        $query->where('contractors.is_active', 1)
              ->where('contractors.verification_status', '!=', 'deleted');

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
            ->leftJoin('property_owners as owner_po', 'contractors.owner_id', '=', 'owner_po.owner_id')
            ->leftJoin('users as owner_u', 'owner_po.user_id', '=', 'owner_u.user_id')
            ->select(
                'contractors.*',
                'owner_u.email',
                'owner_u.username',
                'owner_po.profile_pic',
                'owner_u.first_name as authorized_rep_fname',
                'owner_u.last_name as authorized_rep_lname',
                'owner_u.middle_name as authorized_rep_mname'
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
                'username' => $username,
                'email' => $data['company_email'],
                'password_hash' => bcrypt('contractor123@!'),
                'OTP_hash' => 'admin_created',
                'user_type' => 'contractor',
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ));

            // Create Property Owner profile for this user
            $ownerId = DB::table('property_owners')->insertGetId(array(
                'user_id' => $userId,
                'address' => $data['business_address'] ?? '',
                'date_of_birth' => $data['date_of_birth'] ?? '2000-01-01',
                'age' => $data['age'] ?? 0,
                'verification_status' => 'approved',
                'is_active' => 1,
                'created_at' => now()
            ));

            // Create Contractor (align fields with new schema)
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
            // For contractor edits, do not write profile_pic into users table; company logo belongs to contractors table.
            if (isset($data['password_hash'])) {
                $userUpdateData['password_hash'] = $data['password_hash'];
            }

            DB::table('users')
                ->where('user_id', $userId)
                ->update($userUpdateData);

            // Update Contractor
            $contractorUpdateData = [
                'company_logo' => $data['company_logo'] ?? null,
                'company_banner' => $data['company_banner'] ?? null,
                'company_name' => $data['company_name'],
                'company_start_date' => $data['company_start_date'],
                'years_of_experience' => $data['years_of_experience'],
                'type_id' => $data['type_id'],
                'contractor_type_other' => $data['contractor_type_other'],
                'services_offered' => $data['services_offered'],
                'business_address' => $data['business_address'],
                'company_email' => $data['company_email'],
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

            // If a profile_pic was uploaded via the admin UI, ensure it's stored only as the contractor's company logo
            if (isset($data['profile_pic'])) {
                $contractorUpdateData['company_logo'] = $data['profile_pic'];
            }

            DB::table('contractors')
                ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                ->where('property_owners.user_id', $userId)
                ->update($contractorUpdateData);

            // Update representative name on users table
            $nameUpdate = [];
            if (isset($data['authorized_rep_fname'])) $nameUpdate['first_name'] = $data['authorized_rep_fname'];
            if (isset($data['authorized_rep_lname'])) $nameUpdate['last_name'] = $data['authorized_rep_lname'];
            if (isset($data['authorized_rep_mname'])) $nameUpdate['middle_name'] = $data['authorized_rep_mname'];
            if (!empty($nameUpdate)) {
                DB::table('users')->where('user_id', $userId)->update($nameUpdate);
            }

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
                        'verification_status' => 'deleted',
                        'is_active' => 0,
                        'deletion_reason' => $reason
                    ]);

                // Deactivate all staff members
                DB::table('contractor_staff')
                    ->where('contractor_id', $contractorId)
                    ->update([
                        'is_active' => 0,
                        'deletion_reason' => $reason
                    ]);

                // Update User timestamp via property_owners
                $ownerId = $contractor->owner_id;
                $ownerUserId = DB::table('property_owners')->where('owner_id', $ownerId)->value('user_id');
                if ($ownerUserId) {
                    DB::table('users')
                        ->where('user_id', $ownerUserId)
                        ->update(['updated_at' => now()]);
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
            ->join('property_owners as owner_po', 'contractors.owner_id', '=', 'owner_po.owner_id')
            ->join('users', 'owner_po.user_id', '=', 'users.user_id')
            ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
            ->where('contractors.contractor_id', $contractorId)
            ->select(
                'contractors.*',
                'users.email',
                'users.username',
                'owner_po.profile_pic',
                'owner_po.cover_photo',
                'users.user_type',
                'users.created_at as member_since',
                DB::raw("CASE WHEN contractors.verification_status = 'approved' THEN 1 ELSE 0 END as is_active"),
                DB::raw("CASE WHEN contractor_types.type_name = 'Others' OR contractor_types.type_name IS NULL THEN contractors.contractor_type_other ELSE contractor_types.type_name END as contractor_type_name")
            )
            ->first();

        if (!$contractor) return null;

        // Get Representative (representative role from contractor_staff)
        $representative = DB::table('contractor_staff')
            ->join('property_owners as rep_po', 'contractor_staff.owner_id', '=', 'rep_po.owner_id')
            ->join('users as rep_u', 'rep_po.user_id', '=', 'rep_u.user_id')
            ->where('contractor_staff.contractor_id', $contractorId)
            ->where('contractor_staff.company_role', 'representative')
            ->select(
                'rep_u.first_name as authorized_rep_fname',
                'rep_u.last_name as authorized_rep_lname',
                'rep_u.middle_name as authorized_rep_mname',
                'contractor_staff.company_role as role',
                'rep_po.profile_pic as rep_profile_pic',
                'rep_u.email as rep_email',
                'rep_u.username as rep_username'
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
            ->join('property_owners as staff_po', 'contractor_staff.owner_id', '=', 'staff_po.owner_id')
            ->join('users as staff_u', 'staff_po.user_id', '=', 'staff_u.user_id')
            ->where('contractor_staff.contractor_id', $contractorId)
            ->select(
                'contractor_staff.*',
                'staff_u.first_name as authorized_rep_fname',
                'staff_u.last_name as authorized_rep_lname',
                'staff_u.middle_name as authorized_rep_mname',
                'staff_u.email',
                'staff_u.username',
                'staff_po.profile_pic',
                'contractor_staff.company_role as role'
            )
            ->orderByRaw("FIELD(contractor_staff.company_role, 'manager', 'engineer', 'architect', 'representative', 'others')")
            ->get();

        $contractor->team_members = $teamMembers;

        // Store original is_active based on verification_status
        $originalIsActive = $contractor->is_active;

        // Check if contractor is suspended (suspension fields are on contractors table)
        if ($contractor->suspension_reason || $contractor->suspension_until) {
            $contractor->is_active = 0;
        } else {
            $contractor->is_active = $originalIsActive;
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
                'username' => $username,
                'email' => $data['email'],
                'password_hash' => bcrypt('teammember123@!'),
                'OTP_hash' => 'admin_created',
                'user_type' => 'property_owner',
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create Property Owner profile for this staff user
            $staffOwnerId = DB::table('property_owners')->insertGetId([
                'user_id' => $userId,
                'address' => '',
                'date_of_birth' => '2000-01-01',
                'age' => 0,
                'verification_status' => 'approved',
                'is_active' => 1,
                'created_at' => now()
            ]);

            // Create Contractor Staff (Team Member)
            DB::table('contractor_staff')->insert([
                'contractor_id' => $data['contractor_id'],
                'owner_id' => $staffOwnerId,
                'company_role' => $data['role'],
                'role_if_others' => $data['role_other'] ?? null,
                'is_active' => 1,
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
            $currentRepresentative = DB::table('contractor_staff')
                ->where('contractor_id', $contractorId)
                ->where('company_role', 'representative')
                ->where('is_active', 1)
                ->first();

            // Get the new representative details
            $newRepresentative = DB::table('contractor_staff')
                ->where('staff_id', $newRepresentativeId)
                ->where('contractor_id', $contractorId)
                ->where('is_active', 1)
                ->first();

            if (!$newRepresentative) {
                throw new \Exception('Selected team member not found.');
            }

            // If there's a current representative, demote them
            if ($currentRepresentative) {
                $previousRole = $currentRepresentative->company_role_before ?: 'manager';

                DB::table('contractor_staff')
                    ->where('staff_id', $currentRepresentative->staff_id)
                    ->update([
                        'company_role' => $previousRole,
                        'company_role_before' => null
                    ]);
            }

            // Promote the new representative
            DB::table('contractor_staff')
                ->where('staff_id', $newRepresentativeId)
                ->update([
                    'company_role_before' => $newRepresentative->company_role,
                    'company_role' => 'representative'
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
                ->update([
                    'is_active' => 0,
                    'is_suspended' => 1,
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
