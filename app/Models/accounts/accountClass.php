<?php

namespace App\Models\accounts;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class accountClass
{
    public function getContractorTypes()
    {
        try {
            return DB::table('contractor_types')
                ->orderByRaw("CASE WHEN LOWER(type_name) = 'others' THEN 1 ELSE 0 END, type_name ASC")
                ->get();
        } catch (\Exception $e) {
            Log::warning('getContractorTypes failed: ' . $e->getMessage());
            // Provide a small fallback list so UI can still render meaningful options
            $defaults = [
                (object)['id' => 1, 'type_name' => 'General Contractor'],
                (object)['id' => 2, 'type_name' => 'Electrical'],
                (object)['id' => 3, 'type_name' => 'Plumbing'],
                (object)['id' => 4, 'type_name' => 'Others']
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
        $userId = DB::table('users')->insertGetId([
            'profile_pic' => $data['profile_pic'] ?? null,
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
        ]);

        return $userId;
    }

    public function createContractor($data)
    {
        $contractorId = DB::table('contractors')->insertGetId([
            'user_id' => $data['user_id'],
            'company_name' => $data['company_name'],
            'years_of_experience' => $data['years_of_experience'],
            'type_id' => $data['type_id'],
            'contractor_type_other' => $data['contractor_type_other'] ?? null,
            'services_offered' => $data['services_offered'],
            'business_address' => $data['business_address'],
            'company_email' => $data['company_email'],
            'company_phone' => $data['company_phone'],
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
        ]);

        return $contractorId;
    }

    public function createContractorUser($data)
    {
        $contractorUserId = DB::table('contractor_users')->insertGetId([
            'contractor_id' => $data['contractor_id'],
            'user_id' => $data['user_id'],
            'authorized_rep_lname' => $data['last_name'],
            'authorized_rep_mname' => $data['middle_name'] ?? null,
            'authorized_rep_fname' => $data['first_name'],
            'phone_number' => $data['phone_number'] ?? '',
            'role' => 'owner',
            'is_active' => 0,
            'created_at' => now()
        ]);

        return $contractorUserId;
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

        $ownerId = DB::table('property_owners')->insertGetId([
            'user_id' => $data['user_id'],
            'last_name' => $data['last_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'first_name' => $data['first_name'],
            'phone_number' => $data['phone_number'],
            'valid_id_id' => $validIdId,
            'valid_id_photo' => $data['valid_id_photo'] ?? null,
            'valid_id_back_photo' => $data['valid_id_back_photo'] ?? null,
            'police_clearance' => $data['police_clearance'] ?? null,
            'date_of_birth' => $data['date_of_birth'],
            'age' => $data['age'],
            'occupation_id' => $data['occupation_id'],
            'occupation_other' => $data['occupation_other'] ?? null,
            'address' => $data['address'] ?? null,
            'verification_status' => 'pending',
            'verification_date' => null,
            'created_at' => now()
        ]);

        return $ownerId;
    }

    public function getUserById($userId)
    {
        return DB::table('users')->where('user_id', $userId)->first();
    }

    public function getContractorByUserId($userId)
    {
        return DB::table('contractors')->where('user_id', $userId)->first();
    }

    public function getPropertyOwnerByUserId($userId)
    {
        return DB::table('property_owners')->where('user_id', $userId)->first();
    }

    public function updateUserProfilePic($userId, $profilePicPath)
    {
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
