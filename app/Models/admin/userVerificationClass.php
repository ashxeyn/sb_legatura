<?php

namespace App\Models\admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\user;

class userVerificationClass
{
    private function resolveOwnerIdByUserId($userId)
    {
        return DB::table('property_owners')
            ->where('user_id', $userId)
            ->value('owner_id');
    }

    private function resolveContractorIdByUserId($userId)
    {
        return DB::table('contractors')
            ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
            ->where('property_owners.user_id', $userId)
            ->value('contractors.contractor_id');
    }

    private function fetchRepresentative($userId, $contractorId = null)
    {
        if (Schema::hasTable('contractor_users')) {
            $query = DB::table('contractor_users');
            if ($contractorId) {
                $query->where('contractor_id', $contractorId);
            }
            return $query->where('user_id', $userId)->first();
        }

        if (Schema::hasTable('contractor_staff') && $contractorId) {
            $ownerId = $this->resolveOwnerIdByUserId($userId);
            if ($ownerId) {
                return DB::table('contractor_staff')
                    ->where('contractor_id', $contractorId)
                    ->where('owner_id', $ownerId)
                    ->first();
            }
        }

        return null;
    }

    /**
     * Get verification request details with profile data
     */
    public function getVerificationDetails($userId, $type = null)
    {
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        $requestedType = in_array($type, ['contractor', 'property_owner'], true)
            ? $type
            : null;

        $data = [
            'user' => $user,
            'profile' => null,
            'representative' => null
        ];

        $contractorProfile = DB::table('contractors')
            ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
            ->where('property_owners.user_id', $userId)
            ->select('contractors.*', 'contractor_types.type_name as contractor_type')
            ->first();

        if ($contractorProfile) {
            $contractorProfile->pcab_license_number = $contractorProfile->picab_number ?? null;
            $contractorProfile->pcab_category = $contractorProfile->picab_category ?? null;
            $contractorProfile->pcab_validity = $contractorProfile->picab_expiration_date ?? null;
            $contractorProfile->tin_number = $contractorProfile->tin_business_reg_number ?? null;
            $contractorProfile->experience_years = $contractorProfile->years_of_experience ?? null;
            $contractorProfile->business_permit_validity = $contractorProfile->business_permit_expiration ?? null;
        }

        $ownerProfile = DB::table('property_owners')
            ->leftJoin('valid_ids', 'property_owners.valid_id_id', '=', 'valid_ids.id')
            ->leftJoin('occupations', 'property_owners.occupation_id', '=', 'occupations.id')
            ->where('property_owners.user_id', $userId)
            ->select('property_owners.*', 'valid_ids.valid_id_name as valid_id_type', 'occupations.occupation_name')
            ->first();

        if ($ownerProfile) {
            $ownerProfile->birthdate = $ownerProfile->date_of_birth ?? null;
            $ownerProfile->occupation = $ownerProfile->occupation_name ?? $ownerProfile->occupation_other ?? null;
            $ownerProfile->valid_id_number = 'N/A';
        }

        if ($requestedType === 'property_owner') {
            $data['profile'] = $ownerProfile ?? $contractorProfile;
            if (!$ownerProfile && $contractorProfile) {
                $data['representative'] = $this->fetchRepresentative($userId, $contractorProfile->contractor_id ?? null);
            }
        } else {
            // Default keeps previous behavior: prefer contractor, then fallback to owner.
            $data['profile'] = $contractorProfile ?? $ownerProfile;
            if ($contractorProfile) {
                $data['representative'] = $this->fetchRepresentative($userId, $contractorProfile->contractor_id ?? null);
            }
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

            if ($targetRole === 'contractor') {
                // Contractor is always linked through property_owners → contractors
                $contractorId = $this->resolveContractorIdByUserId($userId);
                if (!$contractorId) {
                    return ['success' => false, 'message' => 'Contractor profile not found'];
                }

                $updatePayload = ['verification_status' => 'approved', 'verification_date' => now()];
                if (Schema::hasColumn('contractors', 'is_active')) {
                    $updatePayload['is_active'] = 1;
                }

                $affected = DB::table('contractors')
                    ->where('contractor_id', $contractorId)
                    ->update($updatePayload);

                \Log::info('ApproveVerification: contractors update', [
                    'user_id' => $userId, 'contractor_id' => $contractorId, 'affected_rows' => $affected
                ]);

                // user_type → 'both' because contractor owners are always property_owners too.
                // DB enum is ('property_owner','both','owner_staff') — no standalone 'contractor'.
                if ($user->user_type !== 'both') {
                    DB::table('users')->where('user_id', $userId)->update(['user_type' => 'both']);
                }

            } elseif ($targetRole === 'property_owner') {
                $updatePayload = ['verification_status' => 'approved', 'verification_date' => now()];
                if (Schema::hasColumn('property_owners', 'is_active')) {
                    $updatePayload['is_active'] = 1;
                }

                DB::table('property_owners')->where('user_id', $userId)->update($updatePayload);

                // Only set to property_owner if not already 'both'
                if ($user->user_type !== 'both') {
                    DB::table('users')->where('user_id', $userId)->update(['user_type' => 'property_owner']);
                }

            } else {
                // No explicit targetRole: detect which profiles exist and update both
                $contractorId = $this->resolveContractorIdByUserId($userId);
                $hasContractor = !empty($contractorId);
                $hasOwner = DB::table('property_owners')->where('user_id', $userId)->exists();

                if ($hasContractor) {
                    $contractorPayload = ['verification_status' => 'approved', 'verification_date' => now()];
                    if (Schema::hasColumn('contractors', 'is_active')) {
                        $contractorPayload['is_active'] = 1;
                    }
                    DB::table('contractors')->where('contractor_id', $contractorId)->update($contractorPayload);
                    \Log::info('ApproveVerification (auto): contractors update', [
                        'user_id' => $userId, 'contractor_id' => $contractorId
                    ]);
                }

                if ($hasOwner) {
                    $ownerPayload = ['verification_status' => 'approved', 'verification_date' => now()];
                    if (Schema::hasColumn('property_owners', 'is_active')) {
                        $ownerPayload['is_active'] = 1;
                    }
                    DB::table('property_owners')->where('user_id', $userId)->update($ownerPayload);
                }

                // user_type logic:
                // - has contractor profile → 'both' (they are owner + contractor)
                // - owner only → 'property_owner'
                if ($hasContractor) {
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
            $contractorId = $this->resolveContractorIdByUserId($userId);
            if (!$contractorId) {
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
            $affectedContractors = DB::table('contractors')->where('contractor_id', $contractorId)->update($rejectPayload);
            \Log::info('RejectVerification: contractors update', [
                'user_id' => $userId,
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
            $contractorId = $this->resolveContractorIdByUserId($userId);
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
