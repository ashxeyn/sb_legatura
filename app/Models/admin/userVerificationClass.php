<?php

namespace App\Models\admin;

use Illuminate\Support\Facades\DB;
use App\Models\User;

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
            ->where('contractors.user_id', $userId)
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
            $data['representative'] = DB::table('contractor_users')->where('user_id', $userId)->first();
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
            DB::transaction(function () use ($userId) {
                DB::table('contractors')
                    ->where('user_id', $userId)
                    ->update([
                        'verification_status' => 'approved',
                        'verification_date' => now()
                    ]);

                DB::table('contractor_users')
                    ->where('user_id', $userId)
                    ->update([
                        'is_active' => 1
                    ]);
            });

            // Ensure users.user_type reflects contractor (unless both)
            if ($user->user_type !== 'both' && $user->user_type !== 'contractor') {
                DB::table('users')->where('user_id', $userId)->update(['user_type' => 'contractor']);
            }
        } elseif ($targetRole === 'property_owner') {
            DB::table('property_owners')
                ->where('user_id', $userId)
                ->update([
                    'verification_status' => 'approved',
                    'verification_date' => now()
                ]);

            if ($user->user_type !== 'both' && $user->user_type !== 'property_owner') {
                DB::table('users')->where('user_id', $userId)->update(['user_type' => 'property_owner']);
            }
        } else {
            // No explicit targetRole: detect which profile rows exist and update them
            $hasContractor = DB::table('contractors')->where('user_id', $userId)->exists();
            $hasOwner = DB::table('property_owners')->where('user_id', $userId)->exists();

            if ($hasContractor) {
                DB::transaction(function () use ($userId) {
                    DB::table('contractors')
                        ->where('user_id', $userId)
                        ->update([
                            'verification_status' => 'approved',
                            'verification_date' => now()
                        ]);

                    DB::table('contractor_users')
                        ->where('user_id', $userId)
                        ->update([
                            'is_active' => 1
                        ]);
                });
            }

            if ($hasOwner) {
                DB::table('property_owners')
                    ->where('user_id', $userId)
                    ->update([
                        'verification_status' => 'approved',
                        'verification_date' => now()
                    ]);
            }

            // Update users.user_type to reflect available profiles
            if ($hasContractor && $hasOwner) {
                DB::table('users')->where('user_id', $userId)->update(['user_type' => 'both']);
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
            // Update contractors table
            $affectedContractors = DB::table('contractors')->where('user_id', $userId)->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $reason
            ]);
            \Log::info('RejectVerification: contractors update', [
                'user_id' => $userId,
                'affected_rows' => $affectedContractors
            ]);

            // Update contractor_users table
            $affectedContractorUsers = DB::table('contractor_users')->where('user_id', $userId)->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $reason,
                'is_active' => 0 // Deactivate them on rejection
            ]);
            \Log::info('RejectVerification: contractor_users update', [
                'user_id' => $userId,
                'affected_rows' => $affectedContractorUsers
            ]);
        }
        // If target is owner, ONLY update property_owners table
        elseif ($targetRole === 'property_owner') {
            DB::table('property_owners')
                ->where('user_id', $userId)
                ->update([
                    'verification_status' => 'rejected',
                    'rejection_reason' => $reason,
                    'verification_date' => now()
                ]);
            $affectedOwners = DB::table('property_owners')->where('user_id', $userId)->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $reason
            ]);
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
        $table = ($role === 'contractor') ? 'contractors' : 'property_owners';
        return DB::table($table)
            ->where('user_id', $userId)
            ->update([
                'verification_status' => 'pending', // Set back to pending
                'rejection_reason' => null,        // Clear the old reason
                'verification_date' => null         // Reset the date
            ]);
    }
}
