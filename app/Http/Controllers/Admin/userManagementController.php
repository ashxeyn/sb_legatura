<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\authController;
use App\Models\user;
use App\Models\admin\propertyOwnerClass;
use App\Models\admin\contractorClass;
use App\Models\admin\userVerificationClass;
use App\Models\accounts\accountClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\admin\rejectVerificationRequest;
use App\Http\Requests\admin\propertyOwnerRequest;
use App\Http\Requests\admin\contractorRequest;
use App\Services\NotificationService;
use App\Http\Requests\admin\contractorTeamMemberRequest;
use App\Http\Requests\admin\updateContractorTeamMemberRequest;
use App\Http\Requests\admin\changeContractorRepresentativeRequest;
use App\Http\Requests\admin\deactivateContractorTeamMemberRequest;
use App\Http\Requests\admin\reactivateContractorTeamMemberRequest;
use App\Services\PsgcApiService;
use Illuminate\Support\Facades\Mail;
use App\Traits\WithAtomicLock;

class userManagementController extends authController
{
    use WithAtomicLock;
    /**
     * Show property owners list
     */
    public function getPropertyOwners($search = null, $status = null, $dateFrom = null, $dateTo = null, $perPage = 15, $page = null, $onlyEligible = false)
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

        // If requesting only eligible owners for contractor creation,
        // exclude owners who already have a contractor company or are listed as contractor staff.
        if ($onlyEligible) {
            $query->leftJoin('contractors', function($join) {
                $join->on('property_owners.owner_id', '=', 'contractors.owner_id')
                     ->where('contractors.verification_status', '!=', 'deleted')
                     ->where('contractors.is_active', 1);
            });

            $query->leftJoin('contractor_staff', function($join) {
                $join->on('property_owners.owner_id', '=', 'contractor_staff.owner_id')
                     ->whereNull('contractor_staff.deletion_reason')
                     ->where('contractor_staff.is_active', 1);
            });

            $query->whereNull('contractors.contractor_id')
                  ->whereNull('contractor_staff.staff_id');
        }

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
                ->whereNotNull('project_relationships.selected_contractor_id')
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

        // Only pure property owners, not 'both' or owner staff
        $query->where('users.user_type', 'property_owner');

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

    public function addContractor(contractorRequest $request)
    {
        $validated = $request->validated();
        return $this->withLock("admin_create_contractor_" . auth()->id(), function () use ($request, $validated) {
        try {
            // Handle File Uploads
            $dtiSecPath = $request->file('dti_sec_registration_photo')->store('DTI_SEC', 'public');

            $profilePicPath = null;
            if ($request->hasFile('profile_pic')) {
                $profilePicPath = $request->file('profile_pic')->store('profile_pics', 'public');
            }

            // Construct Address
            $address = $validated['business_address_street'] . ', ' .
                $validated['business_address_barangay'] . ', ' .
                $validated['business_address_city'] . ', ' .
                $validated['business_address_province'] . ' ' .
                $validated['business_address_postal'];

            // Calculate Years of Experience
            $startDate = new \DateTime($validated['company_start_date']);
            $now = new \DateTime();
            $yearsOfExperience = $now->diff($startDate)->y;

            // Prepare Data for Model
            // Normalize optional fields to null when empty
            $servicesOffered = isset($validated['services_offered']) && strlen(trim($validated['services_offered'])) ? $validated['services_offered'] : null;
            $companyWebsite = isset($validated['company_website']) && strlen(trim($validated['company_website'])) ? $validated['company_website'] : null;
            $companySocialMedia = isset($validated['company_social_media']) && strlen(trim($validated['company_social_media'])) ? $validated['company_social_media'] : null;

            $data = [
                // Company Info
                'company_logo' => $profilePicPath,
                'company_name' => $validated['company_name'],
                'company_start_date' => $validated['company_start_date'],
                'years_of_experience' => $yearsOfExperience,
                'type_id' => $validated['contractor_type_id'],
                'contractor_type_other' => $validated['contractor_type_id'] == 9 ? $validated['contractor_type_other_text'] : null,
                'services_offered' => $servicesOffered,
                'company_website' => $companyWebsite,
                'company_social_media' => $companySocialMedia,

                // Address
                'business_address' => $address,
                'business_address_street' => $validated['business_address_street'],
                'business_address_barangay' => $validated['business_address_barangay'],
                'business_address_city' => $validated['business_address_city'],
                'business_address_province' => $validated['business_address_province'],
                'business_address_postal' => $validated['business_address_postal'],

                // Representative
                // Representative fields removed from Add Contractor form; owner selection is used instead.
                'company_email' => $validated['company_email'],

                // Optional: link to an existing property owner
                'owner_id' => $validated['owner_id'] ?? null,

                // Legal Docs
                'dti_sec_registration_photo' => $dtiSecPath,
                'picab_number' => $validated['picab_number'],
                'picab_category' => $validated['picab_category'],
                'picab_expiration_date' => $validated['picab_expiration_date'],
                'business_permit_number' => $validated['business_permit_number'],
                'business_permit_city' => $validated['business_permit_city'],
                'business_permit_expiration' => $validated['business_permit_expiration'],
                'tin_business_reg_number' => $validated['tin_business_reg_number'],
            ];

            // Call Model to create contractor linked to existing owner
            $contractorModel = new contractorClass();
            $result = $contractorModel->addContractor($data);

            // Send credentials email
            try {
                $toEmail = $result['email'] ?? ($data['company_email'] ?? null);
                $username = $result['username'] ?? null;
                if ($toEmail) {
                    \Illuminate\Support\Facades\Mail::raw(
                        "Your contractor account has been successfully created by the admin.\n\n" .
                        "Login with:\n" .
                        "Username: " . ($username ?? 'N/A') . "\n" .
                        "Password: owner123@!\n\n" .
                        "Please change your password after logging in.\n\n" .
                        "Best regards,\nLegatura",
                        function ($message) use ($toEmail) {
                            $message->to($toEmail)->subject('Contractor Account Created - Legatura');
                        }
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send contractor creation email: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Contractor added successfully']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
        }); // end withLock
    }

    public function updateContractor(contractorRequest $request, $id)
    {
        $validated = $request->validated();
        return $this->withLock("admin_update_contractor_{$id}", function () use ($request, $validated, $id) {
        // Debug: Log owner_id
        \Log::info('=== Update Contractor Debug ===');
        \Log::info('Contractor ID:', ['id' => $id]);
        \Log::info('Request has owner_id:', ['has' => $request->has('owner_id')]);
        \Log::info('Request owner_id value:', ['value' => $request->input('owner_id')]);
        \Log::info('Validated has owner_id:', ['has' => array_key_exists('owner_id', $validated)]);
        \Log::info('Validated owner_id value:', ['value' => $validated['owner_id'] ?? 'NOT SET']);
        \Log::info('All validated keys:', ['keys' => array_keys($validated)]);
        \Log::info('==============================');

        try {
            // Merge with existing contractor so updates can be partial
            $contractorModel = new contractorClass();
            $existing = $contractorModel->getContractorById($id);

            if (!$existing) {
                return response()->json(['success' => false, 'message' => 'Contractor not found'], 404);
            }

            // Handle File Uploads (store and overwrite validated keys)
            if ($request->hasFile('dti_sec_registration_photo')) {
                $validated['dti_sec_registration_photo'] = $request->file('dti_sec_registration_photo')->store('DTI_SEC', 'public');
            }

            if ($request->hasFile('profile_pic')) {
                $validated['profile_pic'] = $request->file('profile_pic')->store('profile_pics', 'public');
            }

            // Parse existing address into components
            $addrParts = array_map('trim', explode(', ', $existing->business_address ?? ''));
            $existingStreet = $addrParts[0] ?? '';
            $existingBarangay = $addrParts[1] ?? '';
            $existingCity = $addrParts[2] ?? '';
            $existingProvinceZip = $addrParts[3] ?? '';

            // If postal is not provided separately, try to extract from existing province field
            $existingZip = '';
            $existingProvince = $existingProvinceZip;
            if (preg_match('/(.*)\s+(\d+)$/', $existingProvinceZip, $m)) {
                $existingProvince = $m[1];
                $existingZip = $m[2];
            }

            // Build address using provided fields when present, otherwise fall back to existing
            $street = $validated['business_address_street'] ?? $existingStreet;
            $barangay = $validated['business_address_barangay'] ?? $existingBarangay;
            $city = $validated['business_address_city'] ?? $existingCity;
            $province = $validated['business_address_province'] ?? $existingProvince;
            $postal = $validated['business_address_postal'] ?? $existingZip;

            $addressParts = array_filter([trim($street), trim($barangay), trim($city), trim($province)]);
            $address = implode(', ', $addressParts) . ($postal ? ' ' . trim($postal) : '');

            // Determine company start date and years of experience
            $companyStart = $validated['company_start_date'] ?? ($existing->company_start_date ?? null);
            $yearsOfExperience = $existing->years_of_experience ?? 0;
            if ($companyStart) {
                try {
                    $startDate = new \DateTime($companyStart);
                    $now = new \DateTime();
                    $yearsOfExperience = $now->diff($startDate)->y;
                } catch (\Exception $e) {
                    // If parsing fails, keep existing years_of_experience
                }
            }

            // Contractor type
            $typeId = $validated['contractor_type_id'] ?? ($existing->type_id ?? null);
            $typeOther = ($typeId == 9) ? ($validated['contractor_type_other_text'] ?? ($existing->contractor_type_other ?? null)) : null;
            // Normalize optional fields to null when explicitly provided as empty strings
            $servicesOffered = array_key_exists('services_offered', $validated) ? (strlen(trim($validated['services_offered'])) ? $validated['services_offered'] : null) : ($existing->services_offered ?? null);
            $companyWebsite = array_key_exists('company_website', $validated) ? (strlen(trim($validated['company_website'])) ? $validated['company_website'] : null) : ($existing->company_website ?? null);
            $companySocialMedia = array_key_exists('company_social_media', $validated) ? (strlen(trim($validated['company_social_media'])) ? $validated['company_social_media'] : null) : ($existing->company_social_media ?? null);

            // Prepare merged data array — only overwrite fields that were provided or computed
            $data = [
                'updated_at' => now(),

                'company_name' => $validated['company_name'] ?? ($existing->company_name ?? null),
                'company_start_date' => $companyStart,
                'years_of_experience' => $yearsOfExperience,
                'type_id' => $typeId,
                'contractor_type_other' => $typeOther,
                'services_offered' => $servicesOffered,
                'business_address' => $address ?: ($existing->business_address ?? null),
                'company_website' => $companyWebsite,
                'company_social_media' => $companySocialMedia,
                'picab_number' => $validated['picab_number'] ?? ($existing->picab_number ?? null),
                'picab_category' => $validated['picab_category'] ?? ($existing->picab_category ?? null),
                'picab_expiration_date' => $validated['picab_expiration_date'] ?? ($existing->picab_expiration_date ?? null),
                'business_permit_number' => $validated['business_permit_number'] ?? ($existing->business_permit_number ?? null),
                'business_permit_city' => $validated['business_permit_city'] ?? ($existing->business_permit_city ?? null),
                'business_permit_expiration' => $validated['business_permit_expiration'] ?? ($existing->business_permit_expiration ?? null),
                'tin_business_reg_number' => $validated['tin_business_reg_number'] ?? ($existing->tin_business_reg_number ?? null),
            ];

            // Update owner_id if provided (allow changing the linked property owner)
            if (array_key_exists('owner_id', $validated)) {
                $data['owner_id'] = $validated['owner_id'] ?: null;
            }

            // Files / logos
            if (isset($validated['profile_pic'])) {
                $data['profile_pic'] = $validated['profile_pic'];
                $data['company_logo'] = $validated['profile_pic'];
            }
            if (isset($validated['dti_sec_registration_photo'])) {
                $data['dti_sec_registration_photo'] = $validated['dti_sec_registration_photo'];
            }

            // Update user email only when provided
            if (!empty($validated['company_email'])) {
                $data['company_email'] = $validated['company_email'];
            }

            if (!empty($request->input('password'))) {
                $data['password_hash'] = bcrypt($request->input('password'));
            }

            // Call Model
            $contractorModel->editContractor($id, $data);

            // Notify contractor of profile update
            try {
                $user = DB::table('users')->where('user_id', $id)->first();
                $companyName = $data['company_name'] ?? ($existing->company_name ?? 'your company');
                if ($user && !empty($user->email)) {
                    \Illuminate\Support\Facades\Mail::raw(
                        "Dear {$companyName},\n\n" .
                        "Your contractor profile has been updated by the admin.\n\n" .
                        "If you did not expect this change, please contact our support team.\n\n" .
                        "Best regards,\nLegatura",
                        function ($message) use ($user) {
                            $message->to($user->email)->subject('Contractor Profile Updated - Legatura');
                        }
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send contractor update email: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Contractor updated successfully']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
        }); // end withLock
    }

    public function fetchContractor($id)
    {
        $model = new contractorClass();
        $contractor = $model->getContractorById($id);

        if (!$contractor) {
            return response()->json(['error' => 'Contractor not found'], 404);
        }

        // Parse Address
        // Assuming format: Street, Barangay, City, Province Zip
        // Note: The address might contain PSGC codes or names depending on when it was created
        $addressParts = explode(', ', $contractor->business_address);
        $street = $addressParts[0] ?? '';
        $barangay = $addressParts[1] ?? '';
        $city = $addressParts[2] ?? '';
        $provinceZip = $addressParts[3] ?? '';

        // Extract Zip from Province
        $zip = '';
        $province = $provinceZip;
        if (preg_match('/(.*)\s+(\d+)$/', $provinceZip, $matches)) {
            $province = $matches[1];
            $zip = $matches[2];
        }

        // Check if province is a code (numeric) and try to convert to name if needed
        // For now, we'll return what we have since the frontend should handle codes
        // But if it's a pure numeric code without a name, we need to look it up
        // This is a workaround for data that might have been saved with codes

        $contractor->business_address_street = $street;
        $contractor->business_address_barangay = $barangay;
        $contractor->business_address_city = $city;
        $contractor->business_address_province = $province;
        $contractor->business_address_postal = $zip;

        return response()->json([
            'success' => true,
            'data' => $contractor
        ]);
    }

    /**
     * Add a new property owner
     */
    public function addPropertyOwner(propertyOwnerRequest $request)
    {
        $validated = $request->validated();
        return $this->withLock("admin_create_owner_" . auth()->id(), function () use ($request, $validated) {
        // Debug: Log validated data
        \Log::info('=== Add Property Owner - Validated Data ===');
        \Log::info('Address fields:', [
            'street_address' => $validated['street_address'] ?? 'NOT SET',
            'barangay' => $validated['barangay'] ?? 'NOT SET',
            'barangay_name' => $validated['barangay_name'] ?? 'NOT SET',
            'city' => $validated['city'] ?? 'NOT SET',
            'city_name' => $validated['city_name'] ?? 'NOT SET',
            'province' => $validated['province'] ?? 'NOT SET',
            'province_name' => $validated['province_name'] ?? 'NOT SET',
            'zip_code' => $validated['zip_code'] ?? 'NOT SET',
        ]);
        \Log::info('Valid ID field:', [
            'valid_id_id' => $validated['valid_id_id'] ?? 'NOT SET',
        ]);
        \Log::info('==========================================');

        try {
            $profilePicPath = null;
            if ($request->hasFile('profile_pic')) {
                $profilePicPath = $request->file('profile_pic')->store('profile_pics', 'public');
            }

            $validIdPath = null;
            if ($request->hasFile('valid_id_photo')) {
                $validIdPath = $request->file('valid_id_photo')->store('valid_ids', 'public');
            }

            $validIdBackPath = null;
            if ($request->hasFile('valid_id_back_photo')) {
                $validIdBackPath = $request->file('valid_id_back_photo')->store('valid_ids', 'public');
            }

            $policeClearancePath = null;
            if ($request->hasFile('police_clearance')) {
                $policeClearancePath = $request->file('police_clearance')->store('police_clearance', 'public');
            }

            // Construct Address from form fields
            $addressParts = [];
            if (!empty($validated['street_address'])) {
                $addressParts[] = $validated['street_address'];
            }
            if (!empty($validated['barangay_name'])) {
                $addressParts[] = $validated['barangay_name'];
            }
            if (!empty($validated['city_name'])) {
                $addressParts[] = $validated['city_name'];
            }
            if (!empty($validated['province_name'])) {
                $addressParts[] = $validated['province_name'];
            }
            if (!empty($validated['zip_code'])) {
                $addressParts[] = $validated['zip_code'];
            }
            $address = implode(', ', $addressParts);

            // Debug: Log constructed address
            \Log::info('Constructed address:', ['address' => $address, 'parts' => $addressParts]);

            // Calculate age from birthdate
            $age = null;
            if (isset($validated['date_of_birth'])) {
                $birthDate = new \DateTime($validated['date_of_birth']);
                $today = new \DateTime();
                $age = $today->diff($birthDate)->y;
            }

            // Handle occupation - if occupation_id is 'others' or non-numeric, find the 'Others' occupation ID
            $occupationId = $validated['occupation_id'] ?? null;
            if ($occupationId && !is_numeric($occupationId)) {
                // Find the 'Others' occupation ID from the database
                $othersOccupation = DB::table('occupations')
                    ->where('occupation_name', 'Others')
                    ->first();
                $occupationId = $othersOccupation ? $othersOccupation->id : null;
            }

            // Prepare Data for Model
            $data = [
                'profile_pic' => $profilePicPath,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'valid_id_id' => $validated['valid_id_id'] ?? null,
                'valid_id_photo' => $validIdPath,
                'valid_id_back_photo' => $validIdBackPath,
                'police_clearance' => $policeClearancePath,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'age' => $age,
                'occupation_id' => $occupationId,
                'occupation_other' => $validated['occupation_other'] ?? null,
                'address' => $address,
            ];

            // Call Model to create property owner
            $ownerModel = new propertyOwnerClass();
            $result = $ownerModel->addPropertyOwner($data);

            if ($result && isset($result['owner_id'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Property owner added successfully!',
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add property owner'
                ], 422);
            }
        } catch (\Exception $e) {
            \Log::error('Add Property Owner Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
        }); // end withLock
    }

    public function fetchPropertyOwner($id)
    {
        $model = new propertyOwnerClass();
        $propertyOwner = $model->getPropertyOwnerById($id);

        if (!$propertyOwner) {
            return response()->json(['error' => 'Property Owner not found'], 404);
        }

        // Parse Address securely from the end
        $addressStr = trim($propertyOwner->address);
        $zip = '';

        // Extract 3 to 5 digit zip code at the end
        if (preg_match('/[\s,]+(\d{3,5})$/', $addressStr, $matches)) {
            $zip = $matches[1];
            // Remove the zip code and trailing spaces/commas from the address
            $addressStr = preg_replace('/[\s,]+(\d{3,5})$/', '', $addressStr);
        }

        // Now split the remaining by comma and trim
        $addressParts = array_map('trim', explode(',', $addressStr));

        $province = array_pop($addressParts) ?? '';
        $city = array_pop($addressParts) ?? '';
        $barangay = array_pop($addressParts) ?? '';
        $street = implode(', ', $addressParts);

        $propertyOwner->street_address = $street;
        $propertyOwner->barangay = $barangay;
        $propertyOwner->city = $city;
        $propertyOwner->province = $province;
        $propertyOwner->zip_code = $zip;

        return response()->json([
            'user' => [
                'id' => $propertyOwner->user_id,
                'email' => $propertyOwner->email,
                'username' => $propertyOwner->username,
                'profile_pic' => $propertyOwner->profile_pic
            ],
            'owner' => $propertyOwner
        ]);
    }

    public function updatePropertyOwner(propertyOwnerRequest $request, $id)
    {
        $validated = $request->validated();
        return $this->withLock("admin_update_owner_{$id}", function () use ($request, $validated, $id) {
        try {
            // Handle File Uploads
            $profilePicPath = null;
            if ($request->hasFile('profile_pic')) {
                $profilePicPath = $request->file('profile_pic')->store('profiles', 'public');
                $validated['profile_pic'] = $profilePicPath;
            }

            if ($request->hasFile('valid_id_photo')) {
                $validated['valid_id_photo'] = $request->file('valid_id_photo')->store('validID/front', 'public');
            }

            if ($request->hasFile('valid_id_back_photo')) {
                $validated['valid_id_back_photo'] = $request->file('valid_id_back_photo')->store('validID/back', 'public');
            }

            if ($request->hasFile('police_clearance')) {
                $validated['police_clearance'] = $request->file('police_clearance')->store('policeClearance', 'public');
            }

            // Calculate Age
            $dob = new \DateTime($validated['date_of_birth']);
            $now = new \DateTime();
            $age = $now->diff($dob)->y;
            $validated['age'] = $age;

            // Construct Address
            $address = $validated['street_address'] . ', ' . $request->input('barangay_name') . ', ' . $request->input('city_name') . ', ' . $request->input('province_name') . ' ' . $validated['zip_code'];
            $validated['address'] = $address;

            // Handle Occupation
            if ($validated['occupation_id'] === 'others') {
                $validated['occupation_id'] = null;
            } else {
                $validated['occupation_other'] = null;
            }

            // Call Model to Update
            $propertyOwnerModel = new propertyOwnerClass();
            $propertyOwnerModel->editPropertyOwner($validated['user_id'], $validated);

            // Notify user of profile update
            try {
                $user = DB::table('users')->where('user_id', $validated['user_id'])->first();
                if ($user && !empty($user->email)) {
                    \Illuminate\Support\Facades\Mail::raw(
                        "Dear {$validated['first_name']},\n\n" .
                        "Your property owner profile has been updated by the admin.\n\n" .
                        "If you did not expect this change, please contact our support team.\n\n" .
                        "Best regards,\nLegatura",
                        function ($message) use ($user) {
                            $message->to($user->email)->subject('Profile Updated - Legatura');
                        }
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send profile update email: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Property Owner updated successfully']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
        }); // end withLock
    }

    public function deletePropertyOwner(Request $request, $id)
    {
        $request->validate([
            'deletion_reason' => 'required|string|max:500',
        ]);
        return $this->withLock("admin_delete_owner_{$id}", function () use ($request, $id) {

        try {
            // Get owner info before deletion
            $owner = DB::table('property_owners')
                ->join('users', 'property_owners.user_id', '=', 'users.user_id')
                ->where('property_owners.owner_id', $id)
                ->select('users.email', 'users.first_name', 'users.last_name')
                ->first();

            $model = new propertyOwnerClass();
            $model->deleteOwner($id, $request->input('deletion_reason'));

            // Send email notification
            if ($owner && $owner->email) {
                try {
                    \Mail::raw(
                        "Dear {$owner->first_name} {$owner->last_name},\n\n" .
                        "Your account has been deactivated.\n\n" .
                        "Reason: {$request->input('deletion_reason')}\n\n" .
                        "If you believe this is an error or have questions, please contact our support team.\n\n" .
                        "Best regards,\nThe Legatura Team",
                        function ($mailMsg) use ($owner) {
                            $mailMsg->to($owner->email)
                                ->subject('Legatura - Account Deactivated');
                        }
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send deletion email: ' . $e->getMessage());
                }
            }

            return response()->json(['success' => true, 'message' => 'Property Owner deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
        }); // end withLock
    }

    public function deleteContractor(Request $request, $id)
    {
        $request->validate([
            'deletion_reason' => 'required|string|max:500',
        ]);
        return $this->withLock("admin_delete_contractor_{$id}", function () use ($request, $id) {
        try {
            // Get contractor info before deletion
            $contractor = DB::table('contractors')
                ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                ->join('users', 'property_owners.user_id', '=', 'users.user_id')
                ->where('contractors.contractor_id', $id)
                ->select('contractors.company_name', 'users.email')
                ->first();

            $model = new contractorClass();
            $model->deleteContractor($id, $request->input('deletion_reason'));

            // Send email notification
            if ($contractor && $contractor->email) {
                try {
                    \Mail::raw(
                        "Dear {$contractor->company_name},\n\n" .
                        "Your contractor account has been deactivated.\n\n" .
                        "Reason: {$request->input('deletion_reason')}\n\n" .
                        "If you believe this is an error or have questions, please contact our support team.\n\n" .
                        "Best regards,\nThe Legatura Team",
                        function ($mailMsg) use ($contractor) {
                            $mailMsg->to($contractor->email)
                                ->subject('Legatura - Contractor Account Deactivated');
                        }
                    );
                } catch (\Exception $e) {
                    \Log::error('Failed to send contractor deletion email: ' . $e->getMessage());
                }
            }

            return response()->json(['success' => true, 'message' => 'Contractor deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
        }); // end withLock
    }

    /**
     * Show property owners list page
     */
    public function propertyOwners(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $propertyOwners = $this->getPropertyOwners($search, $status, $dateFrom, $dateTo, 10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.userManagement.partials.ownerTable', compact('propertyOwners'))->render()
            ]);
        }

        $accountModel = new accountClass();
        $psgcService = new PsgcApiService();

        $occupations = $accountModel->getOccupations();
        $validIds = $accountModel->getValidIds();
        $provinces = $psgcService->getProvinces();
        $allCities = $psgcService->getAllCities();

        return view('admin.userManagement.propertyOwner', [
            'propertyOwners' => $propertyOwners,
            'occupations' => $occupations,
            'validIds' => $validIds,
            'provinces' => $provinces,
            'allCities' => $allCities
        ]);
    }

    /**
     * Show contractors list
     */
    public function contractors(Request $request)
    {
        $search = $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $contractorModel = new contractorClass();
        $contractors = $contractorModel->getContractors($search, null, $dateFrom, $dateTo, 10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.userManagement.partials.contractorTable', compact('contractors'))->render()
            ]);
        }

        $accountModel = new accountClass();
        $psgcService = new PsgcApiService();

        $occupations = $accountModel->getOccupations();
        $validIds = $accountModel->getValidIds();
        $picabCategories = $accountModel->getPicabCategories();
        $provinces = $psgcService->getProvinces();
        $allCities = $psgcService->getAllCities();
        $contractorTypes = DB::table('contractor_types')->get();

        return view('admin.userManagement.contractor', [
            'contractors' => $contractors,
            'occupations' => $occupations,
            'validIds' => $validIds,
            'picabCategories' => $picabCategories,
            'provinces' => $provinces,
            'allCities' => $allCities,
            'contractorTypes' => $contractorTypes
        ]);
    }

    /**
     * Get available property owners for adding as team members
     * Returns property owners who are:
     * - Verified (approved)
     * - Active
     * - Not linked to any contractor (user_type = 'property_owner', not 'both')
     */
    public function getAvailablePropertyOwners(Request $request)
    {
        $search = $request->query('search', '');

        $query = DB::table('property_owners')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->where('property_owners.verification_status', 'approved')
            ->where('property_owners.is_active', 1)
            ->where('users.user_type', 'property_owner') // Only pure property owners, not 'both'
            ->select(
                'property_owners.owner_id',
                'users.user_id',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'users.email',
                'users.username',
                'property_owners.profile_pic'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.first_name', 'like', "%{$search}%")
                    ->orWhere('users.last_name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%");
            });
        }

        $owners = $query->orderBy('users.first_name')
            ->orderBy('users.last_name')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $owners
        ]);
    }

    /**
     * View details of a specific property owner
     */
    public function viewPropertyOwner($id)
    {
        $model = new propertyOwnerClass();
        $propertyOwner = $model->fetchOwnerView($id);

        if (!$propertyOwner) {
            return redirect()->route('admin.userManagement.propertyOwner')
                ->with('error', 'Property Owner not found');
        }

        $accountModel = new accountClass();
        $psgcService = new PsgcApiService();

        $occupations = $accountModel->getOccupations();
        $validIds = $accountModel->getValidIds();
        $provinces = $psgcService->getProvinces();

        return view('admin.userManagement.propertyOwner_Views', [
            'propertyOwner' => $propertyOwner,
            'occupations' => $occupations,
            'validIds' => $validIds,
            'provinces' => $provinces
        ]);
    }

    /**
     * View details of a specific contractor
     */
    public function viewContractor(Request $request)
    {
        $contractorId = $request->query('id');

        $contractorModel = new contractorClass();
        $contractor = $contractorModel->fetchContractorView($contractorId);

        if (!$contractor) {
            return redirect()->route('admin.userManagement.contractor')
                ->with('error', 'Contractor not found');
        }

        // Handle AJAX requests
        if ($request->ajax()) {
            // If modal parameter is set, return representative modal list
            if ($request->query('modal') === 'representative') {
                return response()->json([
                    'modal_html' => view('admin.userManagement.partials.representativeModalList', ['contractor' => $contractor])->render(),
                ]);
            }

            // Default: return team members table
            return response()->json([
                'html' => view('admin.userManagement.partials.teamMembersTable', ['contractor' => $contractor])->render(),
            ]);
        }

        $accountModel = new accountClass();
        $psgcService = new PsgcApiService();

        $picabCategories = $accountModel->getPicabCategories();
        $provinces = $psgcService->getProvinces();
        $allCities = $psgcService->getAllCities();
        $contractorTypes = DB::table('contractor_types')->get();

        return view('admin.userManagement.contractor_Views', [
            'contractor' => $contractor,
            'picabCategories' => $picabCategories,
            'provinces' => $provinces,
            'allCities' => $allCities,
            'contractorTypes' => $contractorTypes
        ]);
    }

    /**
     * Add a team member to a contractor
     */
    public function addContractorTeamMember(contractorTeamMemberRequest $request)
    {
        $validated = $request->validated();
        return $this->withLock("admin_add_team_member_" . $validated['contractor_id'], function () use ($validated) {
            try {
                $data = [
                    'owner_id' => $validated['owner_id'],
                    'role' => $validated['role'],
                    'role_other' => $validated['role_other'] ?? null,
                    'contractor_id' => $validated['contractor_id']
                ];

                $contractorModel = new contractorClass();
                $result = $contractorModel->addTeamMember($data);

                $owner = DB::table('property_owners')
                    ->join('users', 'property_owners.user_id', '=', 'users.user_id')
                    ->where('property_owners.owner_id', $validated['owner_id'])
                    ->select('users.first_name', 'users.last_name', 'users.email')
                    ->first();

                return response()->json([
                    'success' => true,
                    'message' => 'Team member added successfully',
                    'data' => [
                        'name' => ($owner->first_name ?? '') . ' ' . ($owner->last_name ?? ''),
                        'email' => $owner->email ?? ''
                    ]
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }
        }); // end withLock
    }

    public function cancelInvitation(Request $request, $staffId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);
        return $this->withLock("admin_cancel_invitation_{$staffId}", function () use ($validated, $staffId) {
            try {
                $contractorModel = new contractorClass();
                $result = $contractorModel->cancelInvitation($staffId, $validated['reason']);

                if ($result) {
                    return response()->json(['success' => true, 'message' => 'Invitation canceled successfully']);
                }
                return response()->json(['success' => false, 'message' => 'Failed to cancel invitation'], 400);

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
        }); // end withLock
    }

    public function reapplyInvitation(Request $request, $staffId)
    {
        return $this->withLock("admin_reapply_invitation_{$staffId}", function () use ($staffId) {
            try {
                $contractorModel = new contractorClass();
                $result = $contractorModel->reapplyInvitation($staffId);

                if ($result) {
                    return response()->json(['success' => true, 'message' => 'Invitation reapplied successfully']);
                }
                return response()->json(['success' => false, 'message' => 'Failed to reapply invitation'], 400);

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
        }); // end withLock
    }

    /**
     * Change contractor representative
     */
    public function changeContractorRepresentative(changeContractorRepresentativeRequest $request)
    {
        $validated = $request->validated();
        return $this->withLock("admin_change_representative_" . $validated['contractor_id'], function () use ($validated) {
            try {
                $contractorModel = new contractorClass();
                $result = $contractorModel->changeRepresentative(
                    $validated['contractor_id'],
                    $validated['new_representative_id']
                );

                $newRep = DB::table('contractor_staff')
                    ->join('property_owners', 'contractor_staff.owner_id', '=', 'property_owners.owner_id')
                    ->join('users', 'property_owners.user_id', '=', 'users.user_id')
                    ->where('contractor_staff.staff_id', $validated['new_representative_id'])
                    ->select('contractor_staff.*', 'users.email', 'users.username', 'users.first_name', 'users.last_name')
                    ->first();

                try {
                    Mail::raw(
                        "You have been assigned as the Company Representative.\n\n" .
                        "This role gives you authorization to represent the company in all official matters.\n\n" .
                        "If you have any questions, please contact the administrator.",
                        function ($message) use ($newRep) {
                            $message->to($newRep->email)->subject('Company Representative Assignment - Legatura');
                        }
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send representative change email: ' . $e->getMessage());
                }

                return response()->json(['success' => true, 'message' => 'Company representative changed successfully', 'data' => $result]);

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
        }); // end withLock
    }

    /**
     * Fetch contractor team member data for editing
     */
    public function fetchContractorTeamMember($id)
    {
        try {
            // First, try to get the staff member with all joins
            $member = DB::table('contractor_staff')
                ->join('property_owners', 'contractor_staff.owner_id', '=', 'property_owners.owner_id')
                ->join('users', 'property_owners.user_id', '=', 'users.user_id')
                ->where('contractor_staff.staff_id', $id)
                ->select(
                    'contractor_staff.*',
                    'users.username',
                    'users.email',
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name',
                    'property_owners.profile_pic'
                )
                ->first();

            // If not found with joins, try to get just the staff member
            if (!$member) {
                $member = DB::table('contractor_staff')
                    ->where('staff_id', $id)
                    ->first();

                if (!$member) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Team member not found'
                    ], 404);
                }

                // Try to get user info separately
                $owner = DB::table('property_owners')
                    ->where('owner_id', $member->owner_id)
                    ->first();

                if ($owner) {
                    $user = DB::table('users')
                        ->where('user_id', $owner->user_id)
                        ->first();

                    if ($user) {
                        $member->username = $user->username;
                        $member->email = $user->email;
                        $member->first_name = $user->first_name;
                        $member->middle_name = $user->middle_name;
                        $member->last_name = $user->last_name;
                        $member->profile_pic = $owner->profile_pic;
                    }
                }
            }

            // Map database column names to expected frontend field names
            $memberData = [
                'staff_id' => $member->staff_id,
                'first_name' => $member->first_name ?? '-',
                'middle_name' => $member->middle_name ?? '-',
                'last_name' => $member->last_name ?? '-',
                'company_role' => $member->company_role,
                'role_if_others' => $member->role_if_others,
                'username' => $member->username ?? '-',
                'email' => $member->email ?? '-',
                'profile_pic' => $member->profile_pic ?? null
            ];

            // Check if there's an active representative for this contractor
            // (excluding the current member being edited)
            $hasActiveRepresentative = DB::table('contractor_staff')
                ->where('contractor_id', $member->contractor_id)
                ->where('staff_id', '!=', $member->staff_id)
                ->where('company_role', 'representative')
                ->where('is_active', 1)
                ->exists();

            return response()->json([
                'success' => true,
                'data' => $memberData,
                'has_active_representative' => $hasActiveRepresentative
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update contractor team member
     */
    public function updateContractorTeamMember(updateContractorTeamMemberRequest $request)
    {
        $validated = $request->validated();
        return $this->withLock("admin_update_team_member_" . $validated['staff_id'], function () use ($validated) {
            try {
                $staffMember = DB::table('contractor_staff')->where('staff_id', $validated['staff_id'])->first();

                if (!$staffMember) {
                    return response()->json(['success' => false, 'message' => 'Team member not found'], 404);
                }

                $staffData = [
                    'company_role' => $validated['role'],
                    'company_role_before' => $staffMember->company_role
                ];

                if ($validated['role'] === 'others' && isset($validated['role_other'])) {
                    $staffData['role_if_others'] = $validated['role_other'];
                } else {
                    $staffData['role_if_others'] = null;
                }

                DB::table('contractor_staff')->where('staff_id', $validated['staff_id'])->update($staffData);

                return response()->json(['success' => true, 'message' => 'Team member updated successfully']);

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
        }); // end withLock
    }

    /**
     * Suspend contractor team member
     */
    public function suspendContractorTeamMember(Request $request, $staffId)
    {
        $validated = $request->validate([
            'suspension_reason' => 'required|string|max:500',
            'duration' => 'required|in:temporary,permanent',
            'suspension_until' => 'required_if:duration,temporary|date|after:today'
        ]);
        return $this->withLock("admin_suspend_team_member_{$staffId}", function () use ($validated, $staffId) {
            try {
                $suspensionUntil = $validated['duration'] === 'permanent' ? '9999-12-31' : ($validated['suspension_until'] ?? null);

                DB::table('contractor_staff')->where('staff_id', $staffId)->update([
                    'is_active' => 0,
                    'is_suspended' => 1,
                    'suspension_reason' => $validated['suspension_reason'],
                    'suspension_until' => $suspensionUntil
                ]);

                try {
                    $staff = DB::table('contractor_staff')
                        ->join('property_owners', 'contractor_staff.owner_id', '=', 'property_owners.owner_id')
                        ->join('users', 'property_owners.user_id', '=', 'users.user_id')
                        ->join('contractors', 'contractor_staff.contractor_id', '=', 'contractors.contractor_id')
                        ->where('contractor_staff.staff_id', $staffId)
                        ->select('users.email', 'users.first_name', 'users.last_name', 'contractors.company_name')
                        ->first();

                    if ($staff && $staff->email) {
                        $durationText = $validated['duration'] === 'permanent' ? 'Permanent' : 'Until ' . date('F d, Y', strtotime($suspensionUntil));
                        \Mail::raw(
                            "Dear {$staff->first_name} {$staff->last_name},\n\n" .
                            "Your staff membership at {$staff->company_name} has been suspended.\n\n" .
                            "Reason: {$validated['suspension_reason']}\nDuration: {$durationText}\n\n" .
                            "Please contact the company administrator or support for more information.\n\nBest regards,\nThe Legatura Team",
                            function ($mailMsg) use ($staff) {
                                $mailMsg->to($staff->email)->subject('Legatura - Staff Membership Suspended');
                            }
                        );
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send staff suspension email: ' . $e->getMessage());
                }

                return response()->json(['success' => true, 'message' => 'Team member suspended successfully']);

            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
        }); // end withLock
    }

    /**
     * Deactivate contractor team member (soft delete)
     */
    public function deactivateContractorTeamMember(deactivateContractorTeamMemberRequest $request)
    {
        $validated = $request->validated();
        return $this->withLock("admin_deactivate_team_member_" . $validated['staff_id'], function () use ($validated) {
            try {
                DB::table('contractor_staff')->where('staff_id', $validated['staff_id'])->update([
                    'is_active' => 0,
                    'deletion_reason' => $validated['deletion_reason']
                ]);
                return response()->json(['success' => true, 'message' => 'Team member deactivated successfully']);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
        }); // end withLock
    }

    /**
     * Reactivate contractor team member
     */
    public function reactivateContractorTeamMember(reactivateContractorTeamMemberRequest $request)
    {
        $validated = $request->validated();
        return $this->withLock("admin_reactivate_team_member_" . $validated['staff_id'], function () use ($validated) {
            try {
                $staff = DB::table('contractor_staff')
                    ->join('contractors', 'contractor_staff.contractor_id', '=', 'contractors.contractor_id')
                    ->where('contractor_staff.staff_id', $validated['staff_id'])
                    ->select('contractors.is_active as contractor_active', 'contractors.company_name')
                    ->first();

                if (!$staff) {
                    return response()->json(['success' => false, 'message' => 'Staff member not found'], 404);
                }

                if ($staff->contractor_active == 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot reactivate staff member. The contractor company "' . $staff->company_name . '" is currently suspended or inactive. Please reactivate the company first.'
                    ], 400);
                }

                DB::table('contractor_staff')->where('staff_id', $validated['staff_id'])->update([
                    'is_active' => 1,
                    'is_suspended' => 0,
                    'deletion_reason' => null,
                    'suspension_reason' => null,
                    'suspension_until' => null
                ]);

                return response()->json(['success' => true, 'message' => 'Team member reactivated successfully']);

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
        }); // end withLock
    }

    /**
     * Show verification requests for contractors and property owners
     */
    public function verificationRequest(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $search = $request->query('search');

        // Fetch pending contractors
        $contractorQuery = DB::table('contractors')
            ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->where('contractors.verification_status', 'pending');

        if ($dateFrom) {
            $contractorQuery->whereDate('contractors.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $contractorQuery->whereDate('contractors.created_at', '<=', $dateTo);
        }
        if ($search) {
            $contractorQuery->where(function ($q) use ($search) {
                $q->where('users.username', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('contractors.company_name', 'like', "%{$search}%")
                    ->orWhere('users.first_name', 'like', "%{$search}%")
                    ->orWhere('users.last_name', 'like', "%{$search}%");
            });
        }

        $contractorRequests = $contractorQuery->select(
            'users.user_id',
            'users.username',
            'users.email',
            'users.first_name',
            'users.last_name',
            'contractors.contractor_id',
            'contractors.verification_status',
            'contractors.created_at as request_date',
            'contractors.company_name',
            'contractors.company_logo'
        )
            ->orderBy('contractors.created_at', 'desc')
            ->orderBy('contractors.contractor_id', 'desc')
            ->paginate(10, ['*'], 'contractors_page');

        // Fetch pending property owners
        $ownerQuery = DB::table('property_owners')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->where('property_owners.verification_status', 'pending');

        if ($dateFrom) {
            $ownerQuery->whereDate('property_owners.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $ownerQuery->whereDate('property_owners.created_at', '<=', $dateTo);
        }
        if ($search) {
            $ownerQuery->where(function ($q) use ($search) {
                $q->where('users.username', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('users.first_name', 'like', "%{$search}%")
                    ->orWhere('users.last_name', 'like', "%{$search}%");
            });
        }

        $ownerRequests = $ownerQuery->select(
            'users.user_id',
            'users.username',
            'users.email',
            'property_owners.verification_status',
            'property_owners.created_at as request_date',
            'users.first_name',
            'users.last_name',
            'property_owners.profile_pic'
        )
            ->orderBy('property_owners.created_at', 'desc')
            ->orderBy('property_owners.owner_id', 'desc')
            ->paginate(10, ['*'], 'owners_page');

        if ($request->ajax()) {
            return response()->json([
                'contractors_html' => view('admin.userManagement.partials.vercontractorTable', ['contractorRequests' => $contractorRequests, 'ownerRequests' => $ownerRequests])->render(),
                'owners_html' => view('admin.userManagement.partials.verownerTable', ['ownerRequests' => $ownerRequests, 'contractorRequests' => $contractorRequests])->render(),
            ]);
        }

        return view('admin.userManagement.verificationRequest', [
            'contractorRequests' => $contractorRequests,
            'ownerRequests' => $ownerRequests
        ]);
    }

    /**
     * Get details of a verification request (User + Profile)
     */
    public function getVerificationRequestDetails(Request $request, $id)
    {
        $type = $request->query('type', 'property_owner'); // Default to property_owner

        $verificationModel = new userVerificationClass();
        $data = $verificationModel->getVerificationDetails($id, $type);

        if (!$data) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($data);
    }

    /**
     * Approve a verification request
     */
    public function approveVerification(Request $request, $id)
    {
        return $this->withLock("admin_approve_verification_{$id}", function () use ($request, $id) {
            $verificationModel = new userVerificationClass();
        // Allow client to specify which role to approve (contractor | property_owner)
        $targetRole = $request->input('targetRole') ?? null;
        $result = $verificationModel->approveVerification($id, $targetRole);

        if (!$result['success']) {
            return response()->json($result, 404);
        }

        // Recalculate and ensure users.user_type reflects current profiles (contractor/owner/both)
        try {
            $hasContractor = DB::table('contractors')->where('user_id', $id)->exists();
            $hasOwner = DB::table('property_owners')->where('user_id', $id)->exists();
            $currentUser = DB::table('users')->where('user_id', $id)->first();
            $oldUserType = $currentUser->user_type ?? null;

            if ($hasContractor && $hasOwner) {
                $updateData = ['user_type' => 'both'];

                // Preserve the user's current active role so they are NOT auto-switched
                // when user_type transitions to 'both'. Only set preferred_role if it
                // hasn't been explicitly chosen yet.
                if (empty($currentUser->preferred_role)) {
                    $preservedRole = $oldUserType;
                    // Normalize 'property_owner' → 'owner' for the preferred_role column
                    if ($preservedRole === 'property_owner') {
                        $preservedRole = 'owner';
                    }
                    if (in_array($preservedRole, ['contractor', 'owner'])) {
                        $updateData['preferred_role'] = $preservedRole;
                    }
                }

                DB::table('users')->where('user_id', $id)->update($updateData);
            } elseif ($hasContractor) {
                // Note: 'contractor' is not a valid ENUM value for user_type
                // Contractors without owner role should remain as their current type or use 'staff'
                // Only update if they also have owner role (which would be 'both')
                // For pure contractors, the contractor record itself indicates the role
                \Log::info('User has contractor role only, not updating user_type to avoid ENUM error', ['user_id' => $id]);
            } elseif ($hasOwner) {
                DB::table('users')->where('user_id', $id)->update(['user_type' => 'property_owner']);
            }
        } catch (\Throwable $e) {
            \Log::warning('approveVerification: failed to reconcile users.user_type', ['user_id' => $id, 'error' => $e->getMessage()]);
        }

        // Send notification + email to user
        try {
            $user = DB::table('users')->where('user_id', $id)->first();
            $roleLabel = $targetRole === 'contractor' ? 'contractor' : 'property owner';

            // Determine first name from the relevant profile table
            $firstName = '';
            if ($targetRole === 'contractor') {
                $profile = DB::table('contractors')->where('user_id', $id)->first();
                $firstName = $profile->first_name ?? ($profile->company_name ?? '');
            } else {
                $profile = DB::table('property_owners')->where('user_id', $id)->first();
                $firstName = $profile->first_name ?? '';
                $lastName  = $profile->last_name ?? '';
            }

            if ($targetRole === 'contractor') {
                // Contractors are companies — use company name
                $contractorProfile = DB::table('contractors')->where('user_id', $id)->first();
                $displayName = $contractorProfile->company_name ?? $firstName;
            } else {
                $displayName = trim($firstName . ' ' . ($lastName ?? ''));
                if ($displayName === '') $displayName = 'there';
            }

            // In-app welcome notification
            NotificationService::create(
                (int) $id,
                'general',
                "Welcome to Legatura, {$displayName}!",
                "Your {$roleLabel} account has been verified and approved. You can now access all platform features. Welcome aboard!",
                'high',
                null,
                null,
                ['screen' => 'Home', 'params' => []]
            );

            // Email notification
            if (!empty($user->email)) {
                $emailMessage = "Dear {$firstName},\n\n";
                $emailMessage .= "Great news! Your {$roleLabel} account on Legatura has been verified and approved.\n\n";
                $emailMessage .= "You can now log in and start using all platform features.\n\n";
                $emailMessage .= "Thank you for choosing Legatura!\n\n";
                $emailMessage .= "Best regards,\nThe Legatura Team";

                Mail::raw($emailMessage, function ($mailMsg) use ($user) {
                    $mailMsg->to($user->email)
                        ->subject('Legatura - Account Approved');
                });
            }
        } catch (\Throwable $e) {
            \Log::warning('approveVerification: failed to send notification/email', ['user_id' => $id, 'error' => $e->getMessage()]);
        }

        return response()->json($result);
        }); // end withLock
    }

    /**
     * Reject a verification request
     */
    public function rejectVerification(rejectVerificationRequest $request, $id)
    {
        return $this->withLock("admin_reject_verification_{$id}", function () use ($request, $id) {
        $validated = $request->validated();
        // Safely obtain targetRole: prefer validated value, fall back to raw input
        $targetRole = $validated['targetRole'] ?? $request->input('targetRole') ?? null;

        if (!$targetRole) {
            return response()->json([
                'success' => false,
                'message' => 'The role being rejected is required.'
            ], 400);
        }

        $verificationModel = new userVerificationClass();
        $result = $verificationModel->rejectVerification($id, $validated['reason'], $targetRole);

        if (!$result['success']) {
            return response()->json($result, 404);
        }

        // Send notification + email to user
        try {
            $user = DB::table('users')->where('user_id', $id)->first();
            $roleLabel = $targetRole === 'contractor' ? 'contractor' : 'property owner';

            $firstName = '';
            if ($targetRole === 'contractor') {
                $profile = DB::table('contractors')->where('user_id', $id)->first();
                $firstName = $profile->first_name ?? ($profile->company_name ?? '');
            } else {
                $profile = DB::table('property_owners')->where('user_id', $id)->first();
                $firstName = $profile->first_name ?? '';
            }

            $reason = $validated['reason'];
            $isResubmission = str_starts_with($reason, 'RESUBMISSION:');
            $cleanReason = $isResubmission ? trim(substr($reason, strlen('RESUBMISSION:'))) : $reason;

            // In-app notification
            NotificationService::create(
                (int) $id,
                'general',
                $isResubmission ? 'Account Verification — Resubmission Required' : 'Account Verification Rejected',
                $isResubmission
                    ? "Your {$roleLabel} account requires resubmission of documents. Reason: {$cleanReason}. Please log in and resubmit."
                    : "Your {$roleLabel} account verification has been rejected. Reason: {$cleanReason}",
                'high',
                null,
                null,
                ['screen' => 'Home', 'params' => []]
            );

            // Email notification
            if (!empty($user->email)) {
                if ($isResubmission) {
                    $emailMessage = "Dear {$firstName},\n\n";
                    $emailMessage .= "Upon reviewing your signup documents, your {$roleLabel} account is subjected to resubmission of documents.\n\n";
                    $emailMessage .= "Reason: {$cleanReason}\n\n";
                    $emailMessage .= "Please log in to the app and resubmit your documents.\n\n";
                    $emailMessage .= "Best regards,\nLegatura";
                    $emailSubject = 'Legatura - Resubmission of Documents Required';
                } else {
                    $emailMessage = "Dear {$firstName},\n\n";
                    $emailMessage .= "Sorry, your {$roleLabel} account verification on Legatura has been rejected.\n\n";
                    $emailMessage .= "Reason: {$cleanReason}\n\n";
                    $emailMessage .= "Best regards,\nLegatura";
                    $emailSubject = 'Legatura - Account Verification Rejected';
                }

                Mail::raw($emailMessage, function ($mailMsg) use ($user, $emailSubject) {
                    $mailMsg->to($user->email)->subject($emailSubject);
                });
            }
        } catch (\Throwable $e) {
            \Log::warning('rejectVerification: failed to send notification/email', ['user_id' => $id, 'error' => $e->getMessage()]);
        }

        return response()->json($result);
        }); // end withLock
    }

    /**
     * Show suspended accounts
     */
    public function suspendedAccounts(Request $request)
    {
        $search = $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $contractorId = $request->query('contractor_id');

        $suspendedContractors = \App\Models\admin\bothReactivateClass::getSuspendedContractors($search, $dateFrom, $dateTo);
        $suspendedOwners = \App\Models\admin\bothReactivateClass::getSuspendedPropertyOwners($search, $dateFrom, $dateTo);
        $suspendedStaff = \App\Models\admin\bothReactivateClass::getSuspendedStaff($search, $dateFrom, $dateTo, $contractorId);

        // Get all contractors for the filter dropdown (all statuses except deleted)
        $allContractors = DB::table('contractors')
            ->whereNull('deletion_reason')
            ->orderBy('company_name', 'asc')
            ->select('contractor_id', 'company_name')
            ->get();

        // If AJAX request, return JSON with filtered data
        if ($request->ajax()) {
            $contractorsHtml = view('admin.userManagement.partials.suspendedContractorsTable', [
                'suspendedContractors' => $suspendedContractors
            ])->render();

            $ownersHtml = view('admin.userManagement.partials.suspendedOwnersTable', [
                'suspendedOwners' => $suspendedOwners
            ])->render();

            $staffHtml = view('admin.userManagement.partials.suspendedStaffTable', [
                'suspendedStaff' => $suspendedStaff
            ])->render();

            return response()->json([
                'contractors_html' => $contractorsHtml,
                'owners_html' => $ownersHtml,
                'staff_html' => $staffHtml
            ]);
        }

        return view('admin.userManagement.suspendedAccounts', [
            'suspendedContractors' => $suspendedContractors,
            'suspendedOwners' => $suspendedOwners,
            'suspendedStaff' => $suspendedStaff,
            'allContractors' => $allContractors
        ]);
    }

    /**
     * Reactivate a suspended contractor or property owner
     */
    public function reactivateSuspendedUser(\App\Http\Requests\admin\reactivateContractorTeamMemberRequest $request)
    {
        return $this->withLock("admin_reactivate_user_" . $request->input('contractor_user_id'), function () use ($request) {
        try {
            $userType = $request->input('user_type');

            if ($userType === 'contractor') {
                $contractorId = $request->input('contractor_user_id'); // This is actually contractor_id now
                $result = \App\Models\admin\bothReactivateClass::reactivateContractor($contractorId);
                $message = 'Contractor reactivated successfully!';

                // Send email notification
                if ($result) {
                    try {
                        $contractor = DB::table('contractors')
                            ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
                            ->where('contractors.contractor_id', $contractorId)
                            ->select('contractors.company_name', 'users.email', 'users.first_name')
                            ->first();

                        if ($contractor && $contractor->email) {
                            \Mail::raw(
                                "Dear {$contractor->company_name},\n\n" .
                                "Good news! Your contractor account has been reactivated.\n\n" .
                                "You can now access all platform features and resume your business activities.\n\n" .
                                "Thank you for your patience.\n\n" .
                                "Best regards,\nThe Legatura Team",
                                function ($mailMsg) use ($contractor) {
                                    $mailMsg->to($contractor->email)
                                        ->subject('Legatura - Account Reactivated');
                                }
                            );
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to send reactivation email: ' . $e->getMessage());
                    }
                }
            } elseif ($userType === 'property_owner') {
                $ownerId = $request->input('contractor_user_id'); // For owner, this is owner_id
                $result = \App\Models\admin\bothReactivateClass::reactivatePropertyOwner($ownerId);
                $message = 'Property owner reactivated successfully!';

                // Send email notification
                if ($result) {
                    try {
                        $owner = DB::table('property_owners')
                            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
                            ->where('property_owners.owner_id', $ownerId)
                            ->select('users.email', 'users.first_name', 'users.last_name')
                            ->first();

                        if ($owner && $owner->email) {
                            \Mail::raw(
                                "Dear {$owner->first_name} {$owner->last_name},\n\n" .
                                "Good news! Your account has been reactivated.\n\n" .
                                "You can now access all platform features and resume your activities.\n\n" .
                                "Thank you for your patience.\n\n" .
                                "Best regards,\nThe Legatura Team",
                                function ($mailMsg) use ($owner) {
                                    $mailMsg->to($owner->email)
                                        ->subject('Legatura - Account Reactivated');
                                }
                            );
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to send reactivation email: ' . $e->getMessage());
                    }
                }
            } elseif ($userType === 'staff') {
                $staffId = $request->input('contractor_user_id'); // For staff, this is staff_id
                $result = \App\Models\admin\bothReactivateClass::reactivateStaff($staffId);
                $message = 'Staff member reactivated successfully!';

                // Send email notification
                if ($result) {
                    try {
                        $staff = DB::table('contractor_staff')
                            ->join('property_owners', 'contractor_staff.owner_id', '=', 'property_owners.owner_id')
                            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
                            ->join('contractors', 'contractor_staff.contractor_id', '=', 'contractors.contractor_id')
                            ->where('contractor_staff.staff_id', $staffId)
                            ->select('users.email', 'users.first_name', 'users.last_name', 'contractors.company_name')
                            ->first();

                        if ($staff && $staff->email) {
                            \Mail::raw(
                                "Dear {$staff->first_name} {$staff->last_name},\n\n" .
                                "Good news! Your staff membership at {$staff->company_name} has been reactivated.\n\n" .
                                "You can now access all platform features and resume your work.\n\n" .
                                "Thank you for your patience.\n\n" .
                                "Best regards,\nThe Legatura Team",
                                function ($mailMsg) use ($staff) {
                                    $mailMsg->to($staff->email)
                                        ->subject('Legatura - Staff Membership Reactivated');
                                }
                            );
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to send staff reactivation email: ' . $e->getMessage());
                    }
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user type'
                ], 400);
            }

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to reactivate user'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
        }); // end withLock
    }

    /**
     * Reactivate a suspended account (old method - keeping for compatibility)
     */
    public function reactivateSuspendedAccount(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string',
            'entityType' => 'required|in:contractor,owner',
            'mode' => 'required|in:keep,edit',
        ]);
        return $this->withLock("admin_reactivate_account_" . $validated['entityType'] . "_" . $validated['id'], function () use ($validated) {
            $table = $validated['entityType'] === 'contractor' ? 'contractors' : 'property_owners';
            $idField = $validated['entityType'] === 'contractor' ? 'contractor_id' : 'owner_id';

            $updated = DB::table($table)->where($idField, $validated['id'])->update(['verification_status' => 'pending']);

            if ($updated) {
                return response()->json(['success' => true, 'message' => ucfirst($validated['entityType']) . ' account reactivated successfully.']);
            }
            return response()->json(['success' => false, 'message' => 'Failed to reactivate account'], 400);
        }); // end withLock
    }

    /**
     * Get pending verification contractors
     */
    private function getPendingVerificationContractors()
    {
        return DB::table('contractors')
            ->where('verification_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->orderBy('contractor_id', 'desc')
            ->get();
    }

    /**
     * Get pending verification property owners
     */
    private function getPendingVerificationOwners()
    {
        return DB::table('property_owners')
            ->where('verification_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->orderBy('owner_id', 'desc')
            ->get();
    }

    /**
     * Get suspended contractors (use rejected status as proxy)
     */
    private function getSuspendedContractors()
    {
        return DB::table('contractors')
            ->where('verification_status', 'rejected')
            ->get();
    }

    /**
     * Get suspended property owners (use rejected status as proxy)
     */
    private function getSuspendedOwners()
    {
        return DB::table('property_owners')
            ->where('verification_status', 'rejected')
            ->get();
    }

    // =============================================
    // API METHODS FOR AJAX CALLS
    // =============================================

    /**
     * Get property owners as JSON (for AJAX)
     */
    public function getPropertyOwnersApi(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $onlyEligible = $request->input('eligible') ? true : false;

        $owners = $this->getPropertyOwners($search, $status, null, null, $perPage, $page, $onlyEligible);

        return response()->json($owners);
    }

    /**
     * Get single property owner as JSON
     */
    public function getPropertyOwnerApi($id)
    {
        $model = new propertyOwnerClass();
        $owner = $model->getPropertyOwnerById($id);

        if (!$owner) {
            return response()->json(['error' => 'Property Owner not found'], 404);
        }

        return response()->json($owner);
    }

    /**
     * Verify a property owner
     */
    public function verifyPropertyOwner($id)
    {
        $updated = DB::table('property_owners')
            ->where('owner_id', $id)
            ->update(['verification_status' => 'verified']);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Property owner verified']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to verify'], 400);
    }

    /**
     * Suspend a property owner
     */
    public function suspendPropertyOwner(propertyOwnerRequest $request, $id)
    {
        $validated = $request->validated();
        return $this->withLock("admin_suspend_owner_{$id}", function () use ($validated, $id) {
            $reason = $validated['reason'];
            $duration = $validated['duration'];
            $suspensionUntil = $validated['suspension_until'] ?? null;

            if ($duration === 'permanent') {
                $suspensionUntil = '9999-12-31';
            }

            $propertyOwnerModel = new propertyOwnerClass();
            $owner = $propertyOwnerModel->suspendOwner($id, $reason, $duration, $suspensionUntil);

            if ($owner) {
                $user = User::where('user_id', $owner->user_id)->first();
                if ($user) {
                    try {
                        Mail::raw("Dear {$owner->first_name},\n\nYour account has been suspended.\n\nReason: {$reason}\nDuration: " . ucfirst($duration) . "\nSuspension Until: {$suspensionUntil}\n\nPlease contact support for more information.", function ($message) use ($user) {
                            $message->to($user->email)->subject('Account Suspension Notification');
                        });
                    } catch (\Exception $e) {}
                }
                return response()->json(['success' => true, 'message' => 'Property owner suspended successfully']);
            }

            return response()->json(['success' => false, 'message' => 'Failed to suspend property owner'], 400);
        }); // end withLock
    }

    /**
     * Suspend contractor (reusing property owner suspension logic)
     */
    public function suspendContractor(contractorRequest $request, $id)
    {
        $validated = $request->validated();
        return $this->withLock("admin_suspend_contractor_{$id}", function () use ($validated, $id) {
            $reason = $validated['reason'];
            $duration = $validated['duration'];
            $suspensionUntil = $validated['suspension_until'] ?? null;

            if ($duration === 'permanent') {
                $suspensionUntil = '9999-12-31';
            }

            $contractorModel = new contractorClass();
            $contractor = $contractorModel->suspendContractor($id, $reason, $duration, $suspensionUntil);

            if ($contractor) {
                $owner = DB::table('property_owners')->where('owner_id', $contractor->owner_id)->first();

            if ($owner) {
                $user = User::where('user_id', $owner->user_id)->first();

                if ($user) {
                    // Send email notification
                    try {
                        Mail::raw("Dear {$contractor->company_name},\n\nYour contractor account has been suspended.\n\nReason: {$reason}\nDuration: " . ucfirst($duration) . "\nSuspension Until: {$suspensionUntil}\n\nPlease contact support for more information.", function ($message) use ($user) {
                            $message->to($user->email)
                                ->subject('Contractor Account Suspension Notification');
                        });
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Failed to send suspension email: ' . $e->getMessage());
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Contractor suspended successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to suspend contractor'], 400);
        }); // end withLock
    }

    /**
     * Get contractors as JSON (for AJAX)
     */
    public function getContractorsApi(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $contractorModel = new contractorClass();
        $contractors = $contractorModel->getContractors($search, $status, null, null, 10);

        return response()->json($contractors);
    }

    /**
     * Get single contractor as JSON
     */
    public function getContractorApi($id)
    {
        $contractor = \App\Models\admin\contractorClass::with('user')
            ->where('contractor_id', $id)
            ->first();

        if (!$contractor) {
            return response()->json(['error' => 'Contractor not found'], 404);
        }

        // Flatten the response to include user details at the top level if needed,
        // or just return the nested structure. The JS expects nested or I can map it.
        // The JS expects: email, username (from user)

        $response = $contractor->toArray();
        if ($contractor->user) {
            $response['email'] = $contractor->user->email;
            $response['username'] = $contractor->user->username;
            $response['contact_number'] = $contractor->user->phone_number; // Assuming phone is in user
        }

        return response()->json($response);
    }

    /**
     * Verify a contractor
     */
    /**
     * Approve contractor verification request
     */
    public function approveContractorVerification($id)
    {
        return $this->withLock("admin_approve_contractor_verification_{$id}", function () use ($id) {
            \Log::info("Approving contractor verification for user_id: {$id}");

            $user = User::find($id);

            if (!$user) {
                \Log::error("User not found for verification approval: {$id}");
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            $updatePayload = [
                'verification_status' => 'approved',
                'verification_date' => now(),
            ];

            if (Schema::hasColumn('contractors', 'is_active')) {
                $updatePayload['is_active'] = 1;
            } else {
                \Log::warning("approveContractorVerification: 'is_active' column not present on contractors table; skipping setting is_active for user_id {$id}");
            }

            $updated = DB::table('contractors')->where('user_id', $id)->update($updatePayload);

            if ($updated) {
                \Log::info("Contractor verification approved for user_id: {$id}");

                try {
                    $hasOwner = DB::table('property_owners')->where('user_id', $id)->exists();
                    if ($hasOwner) {
                        $currentUser = DB::table('users')->where('user_id', $id)->first();
                        $updateData = ['user_type' => 'both'];
                        if ($currentUser && empty($currentUser->preferred_role)) {
                            $preservedRole = $currentUser->user_type;
                            if ($preservedRole === 'property_owner') {
                                $preservedRole = 'owner';
                            }
                            if (in_array($preservedRole, ['contractor', 'owner'])) {
                                $updateData['preferred_role'] = $preservedRole;
                            }
                        }
                        DB::table('users')->where('user_id', $id)->update($updateData);
                    } else {
                        \Log::info('User has contractor role only, not updating user_type to avoid ENUM error', ['user_id' => $id]);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('approveContractorVerification: failed to update users.user_type', ['user_id' => $id, 'error' => $e->getMessage()]);
                }
                return response()->json(['success' => true, 'message' => 'Contractor verification approved successfully']);
            }

            \Log::warning("No contractor record found for user_id: {$id}");
            return response()->json(['success' => false, 'message' => 'Contractor not found'], 404);
        }); // end withLock
    }

    /**
     * Reject contractor verification request
     */
    public function rejectContractorVerification(VerificationRequest $request, $id)
    {
        $validated = $request->validated();
        return $this->withLock("admin_reject_contractor_verification_{$id}", function () use ($validated, $id) {
            \Log::info("Rejecting contractor verification for user_id: {$id}");

            $user = User::find($id);

            if (!$user) {
                \Log::error("User not found for verification rejection: {$id}");
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            $updated = DB::table('contractors')->where('user_id', $id)->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $validated['reason'],
                'verification_date' => now()
            ]);

            if ($updated) {
                \Log::info("Contractor verification rejected for user_id: {$id}");
                return response()->json(['success' => true, 'message' => 'Contractor verification rejected successfully']);
            }

            \Log::warning("No contractor record found for user_id: {$id}");
            return response()->json(['success' => false, 'message' => 'Contractor not found'], 404);
        }); // end withLock
    }

    /**
     * Legacy method - kept for backward compatibility
     */
    public function verifyContractor($id)
    {
        $updated = DB::table('contractors')
            ->where('contractor_id', $id)
            ->update(['verification_status' => 'approved']);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Contractor verified']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to verify'], 400);
    }

    /**
     * Suspend a contractor
     */
    /**
     * Get verification requests as JSON
     */
    public function getVerificationRequestsApi(Request $request)
    {
        $type = $request->input('type'); // 'contractor' or 'owner'

        if ($type === 'contractor') {
            $requests = $this->getPendingVerificationContractors();
        } elseif ($type === 'owner') {
            $requests = $this->getPendingVerificationOwners();
        } else {
            $contractors = $this->getPendingVerificationContractors();
            $owners = $this->getPendingVerificationOwners();
            $requests = collect($contractors)->concat($owners);
        }

        return response()->json(['data' => $requests]);
    }

    /**
     * Get suspended accounts as JSON
     */
    public function getSuspendedAccountsApi(Request $request)
    {
        $type = $request->input('type'); // 'contractor' or 'owner'

        if ($type === 'contractor') {
            $accounts = $this->getSuspendedContractors();
        } elseif ($type === 'owner') {
            $accounts = $this->getSuspendedOwners();
        } else {
            $contractors = $this->getSuspendedContractors();
            $owners = $this->getSuspendedOwners();
            $accounts = collect($contractors)->concat($owners);
        }

        return response()->json(['data' => $accounts]);
    }

    /**
     * Update contractor logo
     */
    public function updateContractorLogo(Request $request, $id)
    {
        try {
            if (!$request->hasFile('company_logo')) {
                return response()->json(['success' => false, 'message' => 'No logo file provided'], 400);
            }

            $path = $request->file('company_logo')->store('contractors/logos', 'public');
            
            DB::table('contractors')
                ->where('contractor_id', $id)
                ->update([
                    'company_logo' => $path,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true, 
                'message' => 'Company logo updated successfully',
                'path' => asset('storage/' . $path)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update contractor banner
     */
    public function updateContractorBanner(Request $request, $id)
    {
        try {
            if (!$request->hasFile('company_banner')) {
                return response()->json(['success' => false, 'message' => 'No banner file provided'], 400);
            }

            $path = $request->file('company_banner')->store('contractors/banners', 'public');
            
            DB::table('contractors')
                ->where('contractor_id', $id)
                ->update([
                    'company_banner' => $path,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true, 
                'message' => 'Company banner updated successfully',
                'path' => asset('storage/' . $path)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update property owner profile picture
     */
    public function updatePropertyOwnerProfilePic(Request $request, $id)
    {
        try {
            if (!$request->hasFile('profile_pic')) {
                return response()->json(['success' => false, 'message' => 'No profile picture provided'], 400);
            }

            $path = $request->file('profile_pic')->store('owners/profiles', 'public');
            
            DB::table('property_owners')
                ->where('owner_id', $id)
                ->update([
                    'profile_pic' => $path
                ]);


            return response()->json([
                'success' => true, 
                'message' => 'Profile picture updated successfully',
                'path' => asset('storage/' . $path)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update property owner cover photo
     */
    public function updatePropertyOwnerCoverPhoto(Request $request, $id)
    {
        try {
            if (!$request->hasFile('cover_photo')) {
                return response()->json(['success' => false, 'message' => 'No cover photo provided'], 400);
            }

            $path = $request->file('cover_photo')->store('owners/covers', 'public');
            
            DB::table('property_owners')
                ->where('owner_id', $id)
                ->update([
                    'cover_photo' => $path
                ]);


            return response()->json([
                'success' => true, 
                'message' => 'Cover photo updated successfully',
                'path' => asset('storage/' . $path)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}


