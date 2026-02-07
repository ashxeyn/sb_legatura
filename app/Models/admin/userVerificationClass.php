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

        if ($user->user_type === 'contractor' || $user->user_type === 'both') {
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
            }
            $data['profile'] = $profile;
            $data['representative'] = DB::table('contractor_users')->where('user_id', $userId)->first();
        } elseif ($user->user_type === 'property_owner') {
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

        // Update Profile table based on user type
        if ($user->user_type === 'contractor' || $user->user_type === 'both') {
            // Start a transaction to ensure both updates happen together
            DB::transaction(function () use ($userId) {

                // 1. Update the contractors table
                DB::table('contractors')
                    ->where('user_id', $userId)
                    ->update([
                        'verification_status' => 'approved',
                        'verification_date' => now()
                    ]);

                // 2. Update the users table
                DB::table('contractor_users')
                    ->where('id', $userId)
                    ->update([
                        'is_active' => 1
                    ]);
            });
        }

        if ($user->user_type === 'property_owner' || $user->user_type === 'both') {
            DB::table('property_owners')
                ->where('user_id', $userId)
                ->update([
                    'verification_status' => 'approved',
                    'verification_date' => now(),
                    'is_active' => 1
                ]);
        }

        return ['success' => true, 'message' => 'User verified successfully'];
    }

    /**
     * Reject a verification request
     */
    public function rejectVerification($userId, $reason)
    {
        $user = User::find($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Update Profile table with rejection reason
        if ($user->user_type === 'contractor' || $user->user_type === 'both') {
            DB::table('contractors')
                ->where('user_id', $userId)
                ->update([
                    'verification_status' => 'rejected',
                    'rejection_reason' => $reason,
                    'verification_date' => now()
                ]);
        }

        if ($user->user_type === 'property_owner' || $user->user_type === 'both') {
            DB::table('property_owners')
                ->where('user_id', $userId)
                ->update([
                    'verification_status' => 'rejected',
                    'rejection_reason' => $reason,
                    'verification_date' => now()
                ]);
        }

        return ['success' => true, 'message' => 'Verification rejected'];
    }
}
