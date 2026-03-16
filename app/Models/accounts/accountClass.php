<?php

namespace App\Models\accounts;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class accountClass
{
    public function getContractorTypes()
    {
        try {
            return DB::table('contractor_types')
                ->select('type_id', 'type_name')
                ->orderByRaw("CASE WHEN LOWER(type_name) = 'others' THEN 1 ELSE 0 END, type_name ASC")
                ->get();
        } catch (\Exception $e) {
            Log::warning('getContractorTypes failed: ' . $e->getMessage());
            // Provide complete fallback list matching database
            $defaults = [
                (object)['type_id' => 1, 'type_name' => 'General Contractor'],
                (object)['type_id' => 2, 'type_name' => 'Electrical Contractor'],
                (object)['type_id' => 3, 'type_name' => 'Pool Contractor'],
                (object)['type_id' => 4, 'type_name' => 'Mechanical Contractor'],
                (object)['type_id' => 5, 'type_name' => 'Civil Works Contractor'],
                (object)['type_id' => 6, 'type_name' => 'Architectural Contractor'],
                (object)['type_id' => 7, 'type_name' => 'Interior Fit-out Contractor'],
                (object)['type_id' => 8, 'type_name' => 'Landscaping Contractor'],
                (object)['type_id' => 9, 'type_name' => 'Others']
            ];
            return collect($defaults);
        }
    }

    public function getOccupations()
    {
        try {
            return DB::table('occupations')
                ->orderByRaw("CASE WHEN LOWER(occupation_name) = 'others' THEN 1 ELSE 0 END, occupation_name ASC")
                ->get();
        } catch (\Exception $e) {
            Log::warning('getOccupations failed: ' . $e->getMessage());
            $defaults = [
                (object)['id' => 1, 'occupation_name' => 'Engineer'],
                (object)['id' => 2, 'occupation_name' => 'Architect'],
                (object)['id' => 3, 'occupation_name' => 'Foreman'],
                (object)['id' => 4, 'occupation_name' => 'Others']
            ];
            return collect($defaults);
        }
    }

    public function getValidIds()
    {
        try {
            return DB::table('valid_ids')->orderBy('valid_id_name', 'asc')->get();
        } catch (\Exception $e) {
            Log::warning('getValidIds failed: ' . $e->getMessage());
            $defaults = [
                (object)['id' => 1, 'valid_id_name' => 'Passport'],
                (object)['id' => 2, 'valid_id_name' => 'Driver License'],
                (object)['id' => 3, 'valid_id_name' => 'National ID']
            ];
            return collect($defaults);
        }
    }

    public function getPicabCategories()
    {
        try {
            $result = DB::select("SHOW COLUMNS FROM contractors WHERE Field = 'picab_category'");
            if (empty($result)) {
                return [];
            }

            $type = $result[0]->Type;
            preg_match('/^enum\((.*)\)$/', $type, $matches);

            if (empty($matches[1])) {
                return [];
            }

            $values = str_getcsv($matches[1], ',', "'");
            return $values;
        } catch (\Exception $e) {
            Log::warning('getPicabCategories failed: ' . $e->getMessage());
            return [];
        }
    }

    public function usernameExists($username)
    {
        try {
            $userExists = DB::table('users')->where('username', $username)->exists();
            $adminExists = DB::table('admin_users')->where('username', $username)->exists();
            return $userExists || $adminExists;
        } catch (\Exception $e) {
            Log::warning('usernameExists failed: ' . $e->getMessage());
            return false;
        }
    }

    public function emailExists($email)
    {
        try {
            $userExists = DB::table('users')->where('email', $email)->exists();
            $adminExists = DB::table('admin_users')->where('email', $email)->exists();
            return $userExists || $adminExists;
        } catch (\Exception $e) {
            Log::warning('emailExists failed: ' . $e->getMessage());
            return false;
        }
    }

    public function companyEmailExists($companyEmail)
    {
        try {
            return DB::table('contractors')->where('company_email', $companyEmail)->exists();
        } catch (\Exception $e) {
            Log::warning('companyEmailExists failed: ' . $e->getMessage());
            return false;
        }
    }

    public function createUser($data)
    {
        $payload = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'OTP_hash' => $data['OTP_hash'],
            // Default to 'property_owner' for mobile/web registrations when not provided.
            // The `users.user_type` column uses an ENUM; using an invalid value like 'user'
            // causes SQL truncation warnings. Use a valid enum member to avoid errors.
            'user_type' => $data['user_type'] ?? 'property_owner',
            'created_at' => now(),
            'updated_at' => now()
        ];

        try {
            if (Schema::hasColumn('users', 'profile_pic')) {
                $payload['profile_pic'] = $data['profile_pic'] ?? null;
            }
            if (Schema::hasColumn('users', 'cover_photo')) {
                $payload['cover_photo'] = $data['cover_photo'] ?? null;
            }
            // Ensure name fields are present when the schema requires them.
            if (Schema::hasColumn('users', 'first_name')) {
                $payload['first_name'] = $data['first_name'] ?? '';
            }
            if (Schema::hasColumn('users', 'middle_name')) {
                $payload['middle_name'] = $data['middle_name'] ?? null;
            }
            if (Schema::hasColumn('users', 'last_name')) {
                $payload['last_name'] = $data['last_name'] ?? '';
            }
        } catch (\Throwable $e) {
            Log::warning('Schema check failed when creating user: ' . $e->getMessage());
        }

        $userId = DB::table('users')->insertGetId($payload);

        return $userId;
    }

    public function createContractor($data)
    {
        // Resolve owner_id from property_owners for this user
        $userId = $data['user_id'] ?? null;
        $ownerId = null;
        if ($userId) {
            $ownerRow = DB::table('property_owners')->where('user_id', $userId)->first();
            if ($ownerRow) $ownerId = $ownerRow->owner_id ?? null;
        }

        $payload = [
            'owner_id' => $ownerId,
            'company_logo' => $data['company_logo'] ?? null,
            'company_banner' => $data['company_banner'] ?? null,
            'company_name' => $data['company_name'],
            'years_of_experience' => $data['years_of_experience'],
            'type_id' => $data['type_id'],
            'contractor_type_other' => $data['contractor_type_other'] ?? null,
            'services_offered' => $data['services_offered'],
            'business_address' => $data['business_address'],
            'company_email' => $data['company_email'],
            'company_website' => $data['company_website'] ?? null,
            'company_social_media' => $data['company_social_media'] ?? null,
            'picab_number' => $data['picab_number'],
            'picab_category' => $data['picab_category'],
            'picab_expiration_date' => $data['picab_expiration_date'],
            'business_permit_number' => $data['business_permit_number'],
            'business_permit_city' => $data['business_permit_city'],
            'business_permit_expiration' => $data['business_permit_expiration'],
            'tin_business_reg_number' => $data['tin_business_reg_number'],
            'dti_sec_registration_photo' => $data['dti_sec_registration_photo'],
            'verification_status' => 'pending',
            'verification_date' => null,
            'created_at' => now(),
            'updated_at' => now()
        ];

        $contractorId = DB::table('contractors')->insertGetId($payload);

        return $contractorId;
    }

    public function createContractorUser($data)
    {
        try {
            if (!Schema::hasTable('contractor_staff')) {
                Log::warning('createContractorUser skipped: contractor_staff table does not exist');
                return null;
            }

            // Get owner_id from property_owners via user_id
            $ownerId = DB::table('property_owners')->where('user_id', $data['user_id'])->value('owner_id');
            if (!$ownerId) {
                Log::warning('createContractorUser skipped: no property_owners record for user_id ' . $data['user_id']);
                return null;
            }

            $staffId = DB::table('contractor_staff')->insertGetId([
                'contractor_id' => $data['contractor_id'],
                'owner_id' => $ownerId,
                'company_role' => 'owner',
                'is_active' => 0,
                'created_at' => now()
            ]);

            return $staffId;
        } catch (\Throwable $e) {
            Log::warning('createContractorUser failed: ' . $e->getMessage());
            return null;
        }
    }

    public function createPropertyOwner($data)
    {
        // Ensure provided valid_id_id exists in `valid_ids` table to avoid FK constraint failures.
        $validIdId = null;
        if (!empty($data['valid_id_id'])) {
            $exists = DB::table('valid_ids')->where('id', $data['valid_id_id'])->exists();
            if ($exists) {
                $validIdId = $data['valid_id_id'];
            }
        }

        $payload = [
            'user_id' => $data['user_id'],
            'verification_status' => 'pending',
            'verification_date' => null,
            'created_at' => now()
        ];

        try {
            if (Schema::hasColumn('property_owners', 'last_name')) {
                $payload['last_name'] = $data['last_name'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'middle_name')) {
                $payload['middle_name'] = $data['middle_name'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'first_name')) {
                $payload['first_name'] = $data['first_name'] ?? null;
            }

            // Address components (preferred). If the DB has dedicated columns, store them.
            if (Schema::hasColumn('property_owners', 'province')) {
                $payload['province'] = $data['province'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'city')) {
                $payload['city'] = $data['city'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'barangay')) {
                $payload['barangay'] = $data['barangay'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'street')) {
                $payload['street'] = $data['street'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'postal_code')) {
                $payload['postal_code'] = $data['postal_code'] ?? null;
            }

            // Backwards-compatible: if the table still uses a single `address` field,
            // prefer an explicitly provided `address`, otherwise construct one from components.
            if (Schema::hasColumn('property_owners', 'address')) {
                if (!empty($data['address'])) {
                    $payload['address'] = $data['address'];
                } else {
                    $constructed = [];
                    if (!empty($data['street'])) {
                        $constructed[] = $data['street'];
                    }
                    if (!empty($data['barangay'])) {
                        $constructed[] = $data['barangay'];
                    }
                    if (!empty($data['city'])) {
                        $constructed[] = $data['city'];
                    }
                    if (!empty($data['province'])) {
                        $constructed[] = $data['province'];
                    }
                    if (!empty($data['postal_code'])) {
                        $constructed[] = $data['postal_code'];
                    }

                    if (!empty($constructed)) {
                        $payload['address'] = implode(', ', $constructed);
                    }
                }
            }

            if (Schema::hasColumn('property_owners', 'date_of_birth')) {
                $payload['date_of_birth'] = $data['date_of_birth'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'age')) {
                $payload['age'] = $data['age'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'occupation_id')) {
                $payload['occupation_id'] = $data['occupation_id'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'occupation_other')) {
                $payload['occupation_other'] = $data['occupation_other'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'valid_id_id')) {
                $payload['valid_id_id'] = $validIdId;
            }
            if (Schema::hasColumn('property_owners', 'valid_id_photo')) {
                $payload['valid_id_photo'] = $data['valid_id_photo'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'valid_id_back_photo')) {
                $payload['valid_id_back_photo'] = $data['valid_id_back_photo'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'profile_pic')) {
                $payload['profile_pic'] = $data['profile_pic'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'cover_photo')) {
                $payload['cover_photo'] = $data['cover_photo'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'police_clearance')) {
                $payload['police_clearance'] = $data['police_clearance'] ?? null;
            }
            if (Schema::hasColumn('property_owners', 'is_active')) {
                $payload['is_active'] = $data['is_active'] ?? 0;
            }
        } catch (\Throwable $e) {
            Log::warning('Schema check failed when creating property owner: ' . $e->getMessage());
        }

        $ownerId = DB::table('property_owners')->insertGetId($payload);

        return $ownerId;
    }

    public function getUserById($userId)
    {
        return DB::table('users')->where('user_id', $userId)->first();
    }

    public function getContractorByUserId($userId)
    {
        return (new \App\Services\ProfileService())->getContractorByUserId($userId);
    }

    public function getPropertyOwnerByUserId($userId)
    {
        return DB::table('property_owners')->where('user_id', $userId)->first();
    }

    public function updateUserProfilePic($userId, $profilePicPath)
    {
        try {
            if (!Schema::hasColumn('users', 'profile_pic')) {
                Log::warning('updateUserProfilePic called but users.profile_pic column is missing');
                return 0;
            }
        } catch (\Throwable $e) {
            Log::warning('Schema check failed in updateUserProfilePic: ' . $e->getMessage());
        }

        return DB::table('users')
            ->where('user_id', $userId)
            ->update(['profile_pic' => $profilePicPath]);
    }

    public function updateOtpHash($userId, $otpHash)
    {
        return DB::table('users')
            ->where('user_id', $userId)
            ->update(['OTP_hash' => $otpHash]);
    }

    // public function verifyUser($userId)
    // {
    //     return DB::table('users')
    //         ->where('user_id', $userId)
    //         ->update(['is_verified' => 1]);
    // }

    public function createAdminUser($data)
    {
        $adminId = DB::table('admin_users')->insertGetId([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'last_name' => $data['last_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'first_name' => $data['first_name'],
            'is_active' => 0,
            'created_at' => now()
        ]);

        return $adminId;
    }
}
