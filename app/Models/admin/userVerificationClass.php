<?php

namespace App\Models\admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\user;

class userVerificationClass
{
    /**
     * Get verification request details with profile data
     */
    public function getVerificationDetails($userId, $type = null)
    {
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        $data = [
            'user' => $user,
            'profile' => null,
            'representative' => null
        ];

        // If type is explicitly specified, fetch that profile type first
        if ($type === 'property_owner') {
            // Fetch property owner profile
            $profile = DB::table('property_owners')
                ->leftJoin('valid_ids', 'property_owners.valid_id_id', '=', 'valid_ids.id')
                ->leftJoin('occupations', 'property_owners.occupation_id', '=', 'occupations.id')
                ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id')
                ->where('property_owners.user_id', $userId)
                ->select(
                    'property_owners.*',
                    'valid_ids.valid_id_name as valid_id_type',
                    'occupations.occupation_name',
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name'
                )
                ->first();

            if ($profile) {
                // Set birthdate for compatibility
                $profile->birthdate = $profile->date_of_birth ?? null;
                
                // Set occupation - prioritize occupation_name from join, fallback to occupation_other
                $profile->occupation = $profile->occupation_name ?? $profile->occupation_other ?? null;
                
                // Set valid_id_number (not stored, so N/A)
                $profile->valid_id_number = 'N/A';
                
                // Ensure all required fields exist
                $profile->first_name = $profile->first_name ?? null;
                $profile->middle_name = $profile->middle_name ?? null;
                $profile->last_name = $profile->last_name ?? null;
            }
            $data['profile'] = $profile;
            
            return $data;
        } elseif ($type === 'contractor') {
            // Fetch contractor profile
            $contractor = DB::table('contractors')
                ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
                ->leftJoin('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                ->leftJoin('users as owner_users', 'property_owners.user_id', '=', 'owner_users.user_id')
                ->where('property_owners.user_id', $userId)
                ->select(
                    'contractors.*',
                    'contractor_types.type_name as contractor_type',
                    'owner_users.first_name',
                    'owner_users.middle_name',
                    'owner_users.last_name',
                    'owner_users.email as owner_email'
                )
                ->first();

            if ($contractor) {
                $contractor->pcab_license_number = $contractor->picab_number ?? null;
                $contractor->pcab_category = $contractor->picab_category ?? null;
                $contractor->pcab_validity = $contractor->picab_expiration_date ?? null;
                $contractor->tin_number = $contractor->tin_business_reg_number ?? null;
                $contractor->experience_years = $contractor->years_of_experience ?? null;
                $contractor->business_permit_validity = $contractor->business_permit_expiration ?? null;

                $data['profile'] = $contractor;
                
                // Representative info is now from the owner
                $data['representative'] = (object)[
                    'authorized_rep_fname' => $contractor->first_name,
                    'authorized_rep_mname' => $contractor->middle_name,
                    'authorized_rep_lname' => $contractor->last_name,
                    'email' => $contractor->owner_email
                ];
            }
            
            return $data;
        }

        // No type specified or unknown type: Try fetching contractor profile first (legacy behavior)
        $contractor = DB::table('contractors')
            ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
            ->leftJoin('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('users as owner_users', 'property_owners.user_id', '=', 'owner_users.user_id')
            ->where('property_owners.user_id', $userId)
            ->select(
                'contractors.*',
                'contractor_types.type_name as contractor_type',
                'owner_users.first_name',
                'owner_users.middle_name',
                'owner_users.last_name',
                'owner_users.email as owner_email'
            )
            ->first();

        if ($contractor) {
            $contractor->pcab_license_number = $contractor->picab_number ?? null;
            $contractor->pcab_category = $contractor->picab_category ?? null;
            $contractor->pcab_validity = $contractor->picab_expiration_date ?? null;
            $contractor->tin_number = $contractor->tin_business_reg_number ?? null;
            $contractor->experience_years = $contractor->years_of_experience ?? null;
            $contractor->business_permit_validity = $contractor->business_permit_expiration ?? null;

            $data['profile'] = $contractor;
            
            // Representative info is now from the owner
            $data['representative'] = (object)[
                'authorized_rep_fname' => $contractor->first_name,
                'authorized_rep_mname' => $contractor->middle_name,
                'authorized_rep_lname' => $contractor->last_name,
                'email' => $contractor->owner_email
            ];
        } else {
            // Fallback: try property owner profile
            $profile = DB::table('property_owners')
                ->leftJoin('valid_ids', 'property_owners.valid_id_id', '=', 'valid_ids.id')
                ->leftJoin('occupations', 'property_owners.occupation_id', '=', 'occupations.id')
                ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id')
                ->where('property_owners.user_id', $userId)
                ->select(
                    'property_owners.*',
                    'valid_ids.valid_id_name as valid_id_type',
                    'occupations.occupation_name',
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name'
                )
                ->first();

            if ($profile) {
                // Set birthdate for compatibility
                $profile->birthdate = $profile->date_of_birth ?? null;
                
                // Set occupation - prioritize occupation_name from join, fallback to occupation_other
                $profile->occupation = $profile->occupation_name ?? $profile->occupation_other ?? null;
                
                // Set valid_id_number (not stored, so N/A)
                $profile->valid_id_number = 'N/A';
                
                // Ensure all required fields exist
                $profile->first_name = $profile->first_name ?? null;
                $profile->middle_name = $profile->middle_name ?? null;
                $profile->last_name = $profile->last_name ?? null;
            }
            $data['profile'] = $profile;
        }

        return $data;
    }

    /**
     * Approve a verification request
     */
    public function approveVerification($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Prefer role parameter if provided, otherwise detect profiles directly
        $args = func_get_args();
        $targetRole = isset($args[1]) ? $args[1] : null;

        // If a specific role was requested, only update that profile table
        if ($targetRole === 'contractor') {
            // Find contractor_id by joining through property_owners
            $contractorId = DB::table('contractors')
                ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                ->where('property_owners.user_id', $userId)
                ->value('contractors.contractor_id');

            if (!$contractorId) {
                \Log::warning("ApproveVerification: No contractor found for user_id {$userId}");
                return ['success' => false, 'message' => 'Contractor profile not found'];
            }

            // Approve contractor profile and mark contractor as active
            $updatePayload = [
                'verification_status' => 'approved',
                'verification_date' => now(),
            ];
            if (Schema::hasColumn('contractors', 'is_active')) {
                $updatePayload['is_active'] = 1;
            } else {
                \Log::warning("ApproveVerification: 'is_active' column missing on contractors table for user_id {$userId}");
            }

            $affected = DB::table('contractors')
                ->where('contractor_id', $contractorId)
                ->update($updatePayload);

            \Log::info('ApproveVerification: contractors update', ['user_id' => $userId, 'contractor_id' => $contractorId, 'affected_rows' => $affected]);

            // Ensure users.user_type reflects contractor (unless both)
            if ($user->user_type !== 'both' && $user->user_type !== 'contractor') {
                DB::table('users')->where('user_id', $userId)->update(['user_type' => 'contractor']);
            }
        } elseif ($targetRole === 'property_owner') {
            $updatePayload = [
                'verification_status' => 'approved',
                'verification_date' => now()
            ];
            if (Schema::hasColumn('property_owners', 'is_active')) {
                $updatePayload['is_active'] = 1;
            } else {
                \Log::warning("ApproveVerification: 'is_active' column missing on property_owners table for user_id {$userId}");
            }

            DB::table('property_owners')
                ->where('user_id', $userId)
                ->update($updatePayload);

            if ($user->user_type !== 'both' && $user->user_type !== 'property_owner') {
                DB::table('users')->where('user_id', $userId)->update(['user_type' => 'property_owner']);
            }

            if ($user->user_type !== 'both' && $user->user_type !== 'property_owner') {
                DB::table('users')->where('user_id', $userId)->update(['user_type' => 'property_owner']);
            }
        } else {
            // No explicit targetRole: detect which profile rows exist and update them
            $contractorId = DB::table('contractors')
                ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                ->where('property_owners.user_id', $userId)
                ->value('contractors.contractor_id');
            
            $hasOwner = DB::table('property_owners')->where('user_id', $userId)->exists();

            if ($contractorId) {
                $updatePayload = [
                    'verification_status' => 'approved',
                    'verification_date' => now(),
                ];
                if (Schema::hasColumn('contractors', 'is_active')) {
                    $updatePayload['is_active'] = 1;
                }
                $affected = DB::table('contractors')
                    ->where('contractor_id', $contractorId)
                    ->update($updatePayload);
                \Log::info('ApproveVerification: contractors update', ['user_id' => $userId, 'contractor_id' => $contractorId, 'affected_rows' => $affected]);
            }

            if ($hasOwner) {
                $ownerPayload = [
                    'verification_status' => 'approved',
                    'verification_date' => now()
                ];
                if (Schema::hasColumn('property_owners', 'is_active')) {
                    $ownerPayload['is_active'] = 1;
                }
                DB::table('property_owners')
                    ->where('user_id', $userId)
                    ->update($ownerPayload);
            }

            // Update users.user_type to reflect available profiles
            if ($contractorId && $hasOwner) {
                $updateData = ['user_type' => 'both'];

                // Preserve the user's current active role so they are NOT auto-switched
                if (empty($user->preferred_role)) {
                    $preservedRole = $user->user_type;
                    if ($preservedRole === 'property_owner') {
                        $preservedRole = 'owner';
                    }
                    if (in_array($preservedRole, ['contractor', 'owner'])) {
                        $updateData['preferred_role'] = $preservedRole;
                    }
                }

                DB::table('users')->where('user_id', $userId)->update($updateData);
            } elseif ($contractorId) {
                DB::table('users')->where('user_id', $userId)->update(['user_type' => 'contractor']);
            } elseif ($hasOwner) {
                DB::table('users')->where('user_id', $userId)->update(['user_type' => 'property_owner']);
            }
        }

        return ['success' => true, 'message' => 'User verified successfully'];
    }

    /**
     * Reject a verification request
     */
    public function rejectVerification($userId, $reason)
    {
        $args = func_get_args();
        // Support old signature for backward compatibility
        $targetRole = isset($args[2]) ? $args[2] : null;

        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // If target is contractor, ONLY update contractors table
        if ($targetRole === 'contractor') {
            // Find contractor_id by joining through property_owners
            $contractorId = DB::table('contractors')
                ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                ->where('property_owners.user_id', $userId)
                ->value('contractors.contractor_id');

            if (!$contractorId) {
                \Log::warning("RejectVerification: No contractor found for user_id {$userId}");
                return ['success' => false, 'message' => 'Contractor profile not found'];
            }

            // Update contractors table only (verification fields moved here)
            $rejectPayload = [
                'verification_status' => 'rejected',
                'rejection_reason' => $reason,
            ];
            if (Schema::hasColumn('contractors', 'is_active')) {
                $rejectPayload['is_active'] = 0;
            }
            $affectedContractors = DB::table('contractors')
                ->where('contractor_id', $contractorId)
                ->update($rejectPayload);
            \Log::info('RejectVerification: contractors update', [
                'user_id' => $userId,
                'contractor_id' => $contractorId,
                'affected_rows' => $affectedContractors
            ]);
        }
        // If target is owner, ONLY update property_owners table
        elseif ($targetRole === 'property_owner') {
            $rejectPayload = [
                'verification_status' => 'rejected',
                'rejection_reason' => $reason,
                'verification_date' => now()
            ];
            if (Schema::hasColumn('property_owners', 'is_active')) {
                $rejectPayload['is_active'] = 0;
            }
            $affectedOwners = DB::table('property_owners')->where('user_id', $userId)->update($rejectPayload);
            \Log::info('RejectVerification: property_owners update', [
                'user_id' => $userId,
                'affected_rows' => $affectedOwners
            ]);
        }

        return ['success' => true, 'message' => 'Role verification rejected'];
    }

    /**
     * Prepare a user to re-apply for a specific role
     */
    public function prepareReapply($userId, $role)
    {
        if ($role === 'contractor') {
            // Find contractor_id by joining through property_owners
            $contractorId = DB::table('contractors')
                ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                ->where('property_owners.user_id', $userId)
                ->value('contractors.contractor_id');

            if (!$contractorId) {
                return 0;
            }

            return DB::table('contractors')
                ->where('contractor_id', $contractorId)
                ->update([
                    'verification_status' => 'pending',
                    'rejection_reason' => null,
                    'verification_date' => null
                ]);
        } else {
            return DB::table('property_owners')
                ->where('user_id', $userId)
                ->update([
                    'verification_status' => 'pending',
                    'rejection_reason' => null,
                    'verification_date' => null
                ]);
        }
    }
}
