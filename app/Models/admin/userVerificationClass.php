<?php

namespace App\Models\admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\user;

class userVerificationClass
{
    /**
     * Get verification request details with profile data
     *
     * @param int $userId
     * @param string|null $type
     * @return array|null
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

        // If caller requested a specific profile type, prefer that. Otherwise try contractor first then owner.
        if ($type === 'contractor') {
            $profile = $this->findContractorByUserId($userId);
            if ($profile) {
                $this->normalizeContractorProfile($profile);
                $data['profile'] = $profile;
                $data['representative'] = null;
                try {
                    if (Schema::hasTable('contractor_users')) {
                        $data['representative'] = DB::table('contractor_users')->where('user_id', $userId)->first();
                    } else {
                        \Log::info("getVerificationDetails: 'contractor_users' table missing; skipping representative lookup", ['user_id' => $userId]);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('getVerificationDetails: failed fetching contractor representative', ['user_id' => $userId, 'error' => $e->getMessage()]);
                }
            } else {
                $data['profile'] = null;
            }
        } elseif ($type === 'property_owner') {
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
        } else {
            // Default behaviour: try contractor first then owner
            $profile = $this->findContractorByUserId($userId);
            if ($profile) {
                $this->normalizeContractorProfile($profile);
                $data['profile'] = $profile;
                $data['representative'] = null;
                try {
                    if (Schema::hasTable('contractor_users')) {
                        $data['representative'] = DB::table('contractor_users')->where('user_id', $userId)->first();
                    } else {
                        \Log::info("getVerificationDetails: 'contractor_users' table missing; skipping representative lookup", ['user_id' => $userId]);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('getVerificationDetails: failed fetching contractor representative', ['user_id' => $userId, 'error' => $e->getMessage()]);
                }
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
        }

        return $data;
    }

    /**
     * Normalize computed contractor fields
     */
    private function normalizeContractorProfile(&$profile)
    {
        $profile->pcab_license_number = $profile->pcab_number ?? $profile->picab_number ?? null;
        $profile->pcab_category = $profile->pcab_category ?? $profile->picab_category ?? null;
        $profile->pcab_validity = $profile->pcab_expiration_date ?? $profile->picab_expiration_date ?? null;
        $profile->tin_number = $profile->tin_business_reg_number ?? null;
        $profile->experience_years = $profile->years_of_experience ?? null;
        $profile->business_permit_validity = $profile->business_permit_expiration ?? null;
    }

    /**
     * Find a contractor profile related to a given user id.
     * Resolves via property_owners.owner_id first, then via contractor_users mapping.
     */
    private function findContractorByUserId($userId)
    {
        // Try owner -> contractor relation
        $ownerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
        if ($ownerId) {
            $contractor = DB::table('contractors')
                ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
                ->where('contractors.owner_id', $ownerId)
                ->select('contractors.*', 'contractor_types.type_name as contractor_type')
                ->first();

            if ($contractor) {
                return $contractor;
            }
        }

        // Next try contractor_users mapping (user is a representative/user for a contractor)
        $contractorId = null;
        try {
            if (Schema::hasTable('contractor_users')) {
                $contractorId = DB::table('contractor_users')->where('user_id', $userId)->value('contractor_id');
            } else {
                \Log::info("findContractorByUserId: 'contractor_users' table missing; skipping mapping lookup", ['user_id' => $userId]);
            }
        } catch (\Throwable $e) {
            \Log::warning('findContractorByUserId: failed reading contractor_users', ['user_id' => $userId, 'error' => $e->getMessage()]);
        }
        if ($contractorId) {
            $contractor = DB::table('contractors')
                ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
                ->where('contractors.contractor_id', $contractorId)
                ->select('contractors.*', 'contractor_types.type_name as contractor_type')
                ->first();

            if ($contractor) {
                return $contractor;
            }
        }

        return null;
    }

    /**
     * Return array of contractor_id values related to given user id.
     * Uses owner relation and contractor_users mapping.
     */
    private function getContractorIdsForUser($userId)
    {
        $ids = [];
        $ownerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
        if ($ownerId) {
            $rows = DB::table('contractors')->where('owner_id', $ownerId)->pluck('contractor_id')->toArray();
            if (!empty($rows)) {
                $ids = array_merge($ids, $rows);
            }
        }

        try {
            if (Schema::hasTable('contractor_users')) {
                $repIds = DB::table('contractor_users')->where('user_id', $userId)->pluck('contractor_id')->toArray();
                if (!empty($repIds)) {
                    $ids = array_merge($ids, $repIds);
                }
            } else {
                \Log::info("getContractorIdsForUser: 'contractor_users' table missing; skipping representative contractor lookup", ['user_id' => $userId]);
            }
        } catch (\Throwable $e) {
            \Log::warning('getContractorIdsForUser: failed reading contractor_users', ['user_id' => $userId, 'error' => $e->getMessage()]);
        }

        $ids = array_values(array_unique($ids));
        return $ids;
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

            // Resolve contractor rows related to this user (via owner or contractor_users)
            $contractorIds = $this->getContractorIdsForUser($userId);
            $affected = 0;
            if (!empty($contractorIds)) {
                $affected = DB::table('contractors')
                    ->whereIn('contractor_id', $contractorIds)
                    ->update($updatePayload);
            } else {
                \Log::warning("ApproveVerification: no contractor records found for user {$userId}");
            }

            \Log::info('ApproveVerification: contractors update', ['user_id' => $userId, 'affected_rows' => $affected]);

            // Also activate any contractor_users record that represents this user for the contractor(s)
            try {
                if (!empty($contractorIds) && Schema::hasTable('contractor_users')) {
                    DB::table('contractor_users')
                        ->whereIn('contractor_id', $contractorIds)
                        ->where('user_id', $userId)
                        ->update(['is_active' => 1, 'is_deleted' => 0]);
                }
            } catch (\Throwable $e) {
                \Log::warning('ApproveVerification: failed to activate contractor_users', ['user_id' => $userId, 'error' => $e->getMessage()]);
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
        } else {
            // No explicit targetRole: detect which profile rows exist and update them
            $contractorIds = $this->getContractorIdsForUser($userId);
            $hasContractor = !empty($contractorIds);
            $hasOwner = DB::table('property_owners')->where('user_id', $userId)->exists();

            if ($hasContractor) {
                $updatePayload = [
                    'verification_status' => 'approved',
                    'verification_date' => now(),
                ];
                if (Schema::hasColumn('contractors', 'is_active')) {
                    $updatePayload['is_active'] = 1;
                }
                $affected = 0;
                if (!empty($contractorIds)) {
                    $affected = DB::table('contractors')
                        ->whereIn('contractor_id', $contractorIds)
                        ->update($updatePayload);
                    \Log::info('ApproveVerification: contractors update', ['user_id' => $userId, 'affected_rows' => $affected]);
                }

                // Activate any contractor_users record linking this user to the contractor(s)
                try {
                    if (!empty($contractorIds) && Schema::hasTable('contractor_users')) {
                        DB::table('contractor_users')
                            ->whereIn('contractor_id', $contractorIds)
                            ->where('user_id', $userId)
                            ->update(['is_active' => 1, 'is_deleted' => 0]);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('ApproveVerification (auto): failed to activate contractor_users', ['user_id' => $userId, 'error' => $e->getMessage()]);
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
                DB::table('users')->where('user_id', $userId)->update(['user_type' => 'both']);

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

            $contractorIds = $this->getContractorIdsForUser($userId);
            $affectedContractors = 0;
            if (!empty($contractorIds)) {
                $affectedContractors = DB::table('contractors')->whereIn('contractor_id', $contractorIds)->update($rejectPayload);
            } else {
                \Log::warning("RejectVerification: no contractor rows found for user {$userId}");
            }
            \Log::info('RejectVerification: contractors update', [
                'user_id' => $userId,
                'affected_rows' => $affectedContractors
            ]);
            // Also deactivate any contractor_users records for this user under the contractor(s)
            try {
                if (!empty($contractorIds) && Schema::hasTable('contractor_users')) {
                    DB::table('contractor_users')
                        ->whereIn('contractor_id', $contractorIds)
                        ->where('user_id', $userId)
                        ->update(['is_active' => 0]);
                }
            } catch (\Throwable $e) {
                \Log::warning('RejectVerification: failed to deactivate contractor_users', ['user_id' => $userId, 'error' => $e->getMessage()]);
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
            $contractorIds = $this->getContractorIdsForUser($userId);
            if (!empty($contractorIds)) {
                return DB::table('contractors')
                    ->whereIn('contractor_id', $contractorIds)
                    ->update([
                        'verification_status' => 'pending',
                        'rejection_reason' => null,
                        'verification_date' => null
                    ]);
            }
            return 0;
        }

        return DB::table('property_owners')
            ->where('user_id', $userId)
            ->update([
                'verification_status' => 'pending', // Set back to pending
                'rejection_reason' => null,        // Clear the old reason
                'verification_date' => null         // Reset the date
            ]);
    }
}
