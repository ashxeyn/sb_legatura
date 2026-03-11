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
    public function getVerificationDetails($userId)
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

        // Try fetching contractor profile first regardless of user_type (helps when user_type is out-of-sync)
        $profile = DB::table('contractors')
            ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
            ->leftJoin('property_owners as cpo', 'contractors.owner_id', '=', 'cpo.owner_id')
            ->where('cpo.user_id', $userId)
            ->select('contractors.*', 'contractor_types.type_name as contractor_type')
            ->first();

        if ($profile) {
            $profile->pcab_license_number = $profile->picab_number ?? null;
            $profile->pcab_category = $profile->picab_category ?? null;
            $profile->pcab_validity = $profile->picab_expiration_date ?? null;
            $profile->tin_number = $profile->tin_business_reg_number ?? null;
            $profile->experience_years = $profile->years_of_experience ?? null;
            $profile->business_permit_validity = $profile->business_permit_expiration ?? null;

            $data['profile'] = $profile;
            $data['representative'] = DB::table('contractor_staff')
                ->join('property_owners as staff_po', 'contractor_staff.owner_id', '=', 'staff_po.owner_id')
                ->join('users as staff_u', 'staff_po.user_id', '=', 'staff_u.user_id')
                ->where('contractor_staff.contractor_id', $profile->contractor_id)
                ->where('contractor_staff.company_role', 'representative')
                ->select('contractor_staff.*', 'staff_u.first_name', 'staff_u.last_name', 'staff_u.middle_name', 'staff_u.email')
                ->first();
        } else {
            // Fallback: try property owner profile
            $profile = DB::table('property_owners')
                ->leftJoin('valid_ids', 'property_owners.valid_id_id', '=', 'valid_ids.id')
                ->leftJoin('occupations', 'property_owners.occupation_id', '=', 'occupations.id')
                ->where('property_owners.user_id', $userId)
                ->select('property_owners.*', 'valid_ids.valid_id_name as valid_id_type', 'occupations.occupation_name')
                ->first();

            if ($profile) {
                $profile->birthdate = $profile->date_of_birth ?? null;
                $profile->occupation = $profile->occupation_name ?? $profile->occupation_other ?? null;
                $profile->valid_id_number = 'N/A';
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

            $ownerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
            $affected = DB::table('contractors')
                ->where('owner_id', $ownerId)
                ->update($updatePayload);

            \Log::info('ApproveVerification: contractors update', ['user_id' => $userId, 'affected_rows' => $affected]);

            // Also activate any contractor_staff records for this contractor
            try {
                $contractorId = DB::table('contractors')->where('owner_id', $ownerId)->value('contractor_id');
                if ($contractorId) {
                    DB::table('contractor_staff')
                        ->where('contractor_id', $contractorId)
                        ->update(['is_active' => 1]);
                }
            } catch (\Throwable $e) {
                \Log::warning('ApproveVerification: failed to activate contractor_staff', ['user_id' => $userId, 'error' => $e->getMessage()]);
            }
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
            $hasContractor = DB::table('contractors')
                ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                ->where('property_owners.user_id', $userId)
                ->exists();
            $hasOwner = DB::table('property_owners')->where('user_id', $userId)->exists();

            if ($hasContractor) {
                $updatePayload = [
                    'verification_status' => 'approved',
                    'verification_date' => now(),
                ];
                if (Schema::hasColumn('contractors', 'is_active')) {
                    $updatePayload['is_active'] = 1;
                }
                $cOwnerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
                $affected = DB::table('contractors')
                    ->where('owner_id', $cOwnerId)
                    ->update($updatePayload);
                \Log::info('ApproveVerification: contractors update', ['user_id' => $userId, 'affected_rows' => $affected]);

                // Activate any contractor_staff records linking staff to the contractor
                try {
                    $contractorId = DB::table('contractors')->where('owner_id', $cOwnerId)->value('contractor_id');
                    if ($contractorId) {
                        DB::table('contractor_staff')
                            ->where('contractor_id', $contractorId)
                            ->update(['is_active' => 1]);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('ApproveVerification (auto): failed to activate contractor_staff', ['user_id' => $userId, 'error' => $e->getMessage()]);
                }
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
            if ($hasContractor && $hasOwner) {
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
            } elseif ($hasContractor) {
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
            // Update contractors table only (verification fields moved here)
            $rejectPayload = [
                'verification_status' => 'rejected',
                'rejection_reason' => $reason,
            ];
            if (Schema::hasColumn('contractors', 'is_active')) {
                $rejectPayload['is_active'] = 0;
            }
            $rOwnerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
            $affectedContractors = DB::table('contractors')->where('owner_id', $rOwnerId)->update($rejectPayload);
            \Log::info('RejectVerification: contractors update', [
                'user_id' => $userId,
                'affected_rows' => $affectedContractors
            ]);
            // Also deactivate any contractor_staff records for this contractor
            try {
                $contractorId = DB::table('contractors')->where('owner_id', $rOwnerId)->value('contractor_id');
                if ($contractorId) {
                    DB::table('contractor_staff')
                        ->where('contractor_id', $contractorId)
                        ->update(['is_active' => 0]);
                }
            } catch (\Throwable $e) {
                \Log::warning('RejectVerification: failed to deactivate contractor_staff', ['user_id' => $userId, 'error' => $e->getMessage()]);
            }
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
            $ownerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
            return DB::table('contractors')
                ->where('owner_id', $ownerId)
                ->update([
                    'verification_status' => 'pending',
                    'rejection_reason' => null,
                    'verification_date' => null
                ]);
        }
        return DB::table('property_owners')
            ->where('user_id', $userId)
            ->update([
                'verification_status' => 'pending',
                'rejection_reason' => null,
                'verification_date' => null
            ]);
    }
}
