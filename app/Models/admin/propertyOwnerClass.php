<?php

namespace App\Models\admin;

use Illuminate\Support\Facades\DB;

class propertyOwnerClass
{
    public function getPropertyOwners($search = null, $status = null, $dateFrom = null, $dateTo = null, $perPage = 15, $page = null)
    {
        $query = DB::table('property_owners')
            ->leftJoin('occupations', 'property_owners.occupation_id', '=', 'occupations.id')
            ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id')
            ->select(
                'property_owners.*',
                'users.email',
                'users.username',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                DB::raw("CASE WHEN occupations.occupation_name = 'Others' OR occupations.occupation_name IS NULL THEN property_owners.occupation_other ELSE occupations.occupation_name END as occupation")
            );

        // Posted Projects Count
        $query->addSelect([
            'posted_projects_count' => DB::table('project_relationships')
                ->select(DB::raw('COUNT(*)'))
                ->whereColumn('project_relationships.owner_id', 'property_owners.owner_id')
                ->where('project_relationships.project_post_status', 'approved')
        ]);

        // Ongoing Projects Count
        $query->addSelect([
            'ongoing_projects_count' => DB::table('projects')
                ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                ->join('milestones', 'projects.project_id', '=', 'milestones.project_id')
                ->whereColumn('project_relationships.owner_id', 'property_owners.owner_id')
                ->whereNotNull('projects.selected_contractor_id')
                ->where(function($q) {
                    $q->where('milestones.milestone_status', 'approved')
                      ->orWhere('milestones.setup_status', 'approved');
                })
                ->select(DB::raw('COUNT(DISTINCT projects.project_id)'))
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.first_name', 'like', "%{$search}%")
                  ->orWhere('users.last_name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('users.username', 'like', "%{$search}%");
            });
        }

        // Only show active users (not suspended or deleted)
        $query->where('property_owners.is_active', 1);

        // Exclude deleted users
        $query->where('property_owners.verification_status', '!=', 'deleted');

        if ($status) {
            $query->where('property_owners.verification_status', $status === 'verified' ? 'approved' : 'pending');
        } else {
            $query->where('property_owners.verification_status', 'approved');
        }

        if ($dateFrom) {
            $query->whereDate('property_owners.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('property_owners.created_at', '<=', $dateTo);
        }

        return $query->orderBy('property_owners.created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    public function getPropertyOwnerById($id)
    {
        return DB::table('property_owners')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->leftJoin('valid_ids', 'property_owners.valid_id_id', '=', 'valid_ids.id')
            ->leftJoin('occupations', 'property_owners.occupation_id', '=', 'occupations.id')
            ->where('property_owners.owner_id', $id)
            ->select(
                'property_owners.*',
                'users.email',
                'users.username',
                'users.user_type',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'valid_ids.valid_id_name',
                DB::raw("CASE WHEN occupations.occupation_name = 'Others' OR occupations.occupation_name IS NULL THEN property_owners.occupation_other ELSE occupations.occupation_name END as occupation")
            )
            ->first();
    }

    public function fetchOwnerView($id)
    {
        $owner = $this->getPropertyOwnerById($id);

        if (!$owner) return null;

        // If user is also a contractor, fetch contractor details
        if ($owner->user_type === 'both') {
            // Check if owner owns a contractor company
            $contractorDetails = DB::table('contractors')
                ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
                ->where('contractors.owner_id', $owner->owner_id)
                ->select(
                    'contractors.company_name',
                    'contractors.dti_sec_registration_photo',
                    DB::raw("'owner' as position"),
                    DB::raw("CASE WHEN contractor_types.type_name = 'Others' OR contractor_types.type_name IS NULL THEN contractors.contractor_type_other ELSE contractor_types.type_name END as contractor_type")
                )
                ->first();

            // If not owner, check if they are staff member
            if (!$contractorDetails) {
                $contractorDetails = DB::table('contractor_staff')
                    ->join('contractors', 'contractor_staff.contractor_id', '=', 'contractors.contractor_id')
                    ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
                    ->where('contractor_staff.owner_id', $owner->owner_id)
                    ->select(
                        'contractors.company_name',
                        'contractors.dti_sec_registration_photo',
                        'contractor_staff.company_role as position',
                        DB::raw("CASE WHEN contractor_types.type_name = 'Others' OR contractor_types.type_name IS NULL THEN contractors.contractor_type_other ELSE contractor_types.type_name END as contractor_type")
                    )
                    ->first();
            }

            $owner->contractor_details = $contractorDetails;
        }

        // Get Projects
        $projects = DB::table('projects')
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('contractors', 'projects.selected_contractor_id', '=', 'contractors.contractor_id')
            ->leftJoin('property_owners as contractor_owner', 'contractors.owner_id', '=', 'contractor_owner.owner_id')
            ->leftJoin('users as contractor_user', 'contractor_owner.user_id', '=', 'contractor_user.user_id')
            ->where('project_relationships.owner_id', $owner->owner_id)
            ->select(
                'projects.*',
                'project_relationships.created_at',
                'contractors.company_name',
                'contractor_owner.profile_pic as contractor_profile_pic',
                'contractor_user.first_name as contractor_first_name',
                'contractor_user.last_name as contractor_last_name'
            )
            ->orderBy('project_relationships.created_at', 'desc')
            ->get();

        // Calculate counts
        $postedCount = $projects->count();
        $ongoingCount = $projects->where('project_status', 'in_progress')->count();
        $completedCount = $projects->where('project_status', 'completed')->count();

        // Attach to owner object
        $owner->projects = $projects;
        $owner->posted_projects_count = $postedCount;
        $owner->ongoing_projects_count = $ongoingCount;
        $owner->completed_projects_count = $completedCount;

        return $owner;
    }

    public function addPropertyOwner(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Generate Username
            $username = 'owner_' . rand(1000, 9999);

            // Create User
            $userId = DB::table('users')->insertGetId([
                'username' => $username,
                'email' => $data['email'],
                'password_hash' => bcrypt('owner123@!'),
                'OTP_hash' => 'admin_created',
                'user_type' => 'property_owner',
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create Property Owner
            $ownerId = DB::table('property_owners')->insertGetId([
                'user_id' => $userId,
                'profile_pic' => $data['profile_pic'] ?? null,
                'valid_id_id' => $data['valid_id_id'],
                'valid_id_photo' => $data['valid_id_photo'],
                'valid_id_back_photo' => $data['valid_id_back_photo'],
                'police_clearance' => $data['police_clearance'],
                'date_of_birth' => $data['date_of_birth'],
                'age' => $data['age'],
                'occupation_id' => $data['occupation_id'],
                'occupation_other' => $data['occupation_other'],
                'address' => $data['address'],
                'verification_status' => 'approved',
                'is_active' => 1,
                'verification_date' => now(),
                'created_at' => now()
            ]);

            return [
                'user_id' => $userId,
                'owner_id' => $ownerId,
                'username' => $username,
                'email' => $data['email']
            ];
        });
    }

    public function editPropertyOwner($userId, array $data)
    {
        return DB::transaction(function () use ($userId, $data) {
            // Update User
            $userUpdateData = [
                'email' => $data['email'],
                'username' => $data['username'],
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'updated_at' => now()
            ];

            if (!empty($data['password'])) {
                $userUpdateData['password_hash'] = bcrypt($data['password']);
            }

            DB::table('users')->where('user_id', $userId)->update($userUpdateData);

            // Update Property Owner
            $ownerUpdateData = [
                'valid_id_id' => $data['valid_id_id'],
                'date_of_birth' => $data['date_of_birth'],
                'age' => $data['age'],
                'occupation_id' => $data['occupation_id'],
                'occupation_other' => $data['occupation_other'],
                'address' => $data['address']
            ];

            if (isset($data['profile_pic'])) {
                $ownerUpdateData['profile_pic'] = $data['profile_pic'];
            }

            if (isset($data['valid_id_photo'])) {
                $ownerUpdateData['valid_id_photo'] = $data['valid_id_photo'];
            }

            if (isset($data['valid_id_back_photo'])) {
                $ownerUpdateData['valid_id_back_photo'] = $data['valid_id_back_photo'];
            }

            if (isset($data['police_clearance'])) {
                $ownerUpdateData['police_clearance'] = $data['police_clearance'];
            }

            DB::table('property_owners')->where('user_id', $userId)->update($ownerUpdateData);

            return true;
        });
    }

    public function deleteOwner($ownerId, $reason)
    {
        return DB::transaction(function () use ($ownerId, $reason) {
            // Get user_id to update users table as well
            $owner = DB::table('property_owners')->where('owner_id', $ownerId)->first();

            if ($owner) {
                // Update Property Owner
                DB::table('property_owners')
                    ->where('owner_id', $ownerId)
                    ->update([
                        'verification_status' => 'deleted',
                        'deletion_reason' => $reason,
                        'is_active' => 0
                    ]);

                // Update User
                DB::table('users')
                    ->where('user_id', $owner->user_id)
                    ->update([
                        'updated_at' => now()
                    ]);
            }

            return true;
        });
    }

    public function suspendOwner($id, $reason, $duration, $suspensionUntil)
    {
        return DB::transaction(function () use ($id, $reason, $duration, $suspensionUntil) {
            // 1. Update property_owners table
            DB::table('property_owners')
                ->where('owner_id', $id)
                ->update([
                    'is_active' => 0,
                    'suspension_reason' => $reason,
                    'suspension_until' => $suspensionUntil
                ]);

            // Get user_id
            $owner = DB::table('property_owners')->where('owner_id', $id)->first();

            if ($owner) {
                // 2. Suspend any contractor companies owned by this property owner
                $contractors = DB::table('contractors')
                    ->where('owner_id', $id)
                    ->where('is_active', 1) // Only suspend active contractors
                    ->get();

                foreach ($contractors as $contractor) {
                    // Suspend the contractor company
                    DB::table('contractors')
                        ->where('contractor_id', $contractor->contractor_id)
                        ->update([
                            'is_active' => 0,
                            'suspension_reason' => 'Property owner account suspended: ' . $reason,
                            'suspension_until' => $suspensionUntil
                        ]);

                    // Suspend all staff members of this contractor
                    DB::table('contractor_staff')
                        ->where('contractor_id', $contractor->contractor_id)
                        ->where('is_active', 1)
                        ->whereNull('deletion_reason')
                        ->update([
                            'is_active' => 0,
                            'is_suspended' => 1,
                            'suspension_reason' => 'Property owner account suspended: ' . $reason,
                            'suspension_until' => $suspensionUntil
                        ]);

                    // Pause ongoing bids for this contractor
                    $bidIds = DB::table('bids')
                        ->where('contractor_id', $contractor->contractor_id)
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

                // 3. Pause ongoing projects owned by this property owner
                $relationshipIds = DB::table('project_relationships')
                    ->where('owner_id', $id)
                    ->pluck('rel_id');

                if ($relationshipIds->isNotEmpty()) {
                    DB::table('projects')
                        ->whereIn('relationship_id', $relationshipIds)
                        ->whereIn('project_status', ['open', 'in_progress'])
                        ->update([
                            'project_status' => 'terminated' // Using terminated as placeholder for paused
                        ]);
                }
            }

            return $owner;
        });
    }
}
