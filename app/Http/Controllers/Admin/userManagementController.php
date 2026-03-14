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

class userManagementController extends authController
{
    /**
     * Show property owners list
     */
    public function propertyOwners(Request $request)
    {
        $search = $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $page = $request->query('page', 1);

        $propertyOwners = $this->getPropertyOwners($search, null, $dateFrom, $dateTo, $page);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.userManagement.partials.ownerTable', ['propertyOwners' => $propertyOwners])->render(),
            ]);
        }

        $accountModel = new accountClass();
        $psgcService = new PsgcApiService();

        $occupations = $accountModel->getOccupations();
        $validIds = $accountModel->getValidIds();
        $provinces = $psgcService->getProvinces();

        return view('admin.userManagement.propertyOwner', [
            'propertyOwners' => $propertyOwners,
            'occupations' => $occupations,
            'validIds' => $validIds,
            'provinces' => $provinces
        ]);
    }

    public function addPropertyOwner(propertyOwnerRequest $request)
    {
        $validated = $request->validated();

        try {
            // Handle File Uploads
            $profilePicPath = null;
            if ($request->hasFile('profile_pic')) {
                $profilePicPath = $request->file('profile_pic')->store('profiles', 'public');
            }

            $validIdFrontPath = $request->file('valid_id_photo')->store('validID/front', 'public');
            $validIdBackPath = $request->file('valid_id_back_photo')->store('validID/back', 'public');
            $policeClearancePath = $request->file('police_clearance')->store('policeClearance', 'public');

            // Calculate Age
            $dob = new \DateTime($validated['date_of_birth']);
            $now = new \DateTime();
            $age = $now->diff($dob)->y;

            // Construct Address
            $address = $validated['street_address'] . ', ' . $request->input('barangay_name') . ', ' . $request->input('city_name') . ', ' . $request->input('province_name') . ' ' . $validated['zip_code'];

            // Prepare Data for Model
            $data = [
                'profile_pic' => $profilePicPath,
                'email' => $validated['email'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'],
                'first_name' => $validated['first_name'],
                'valid_id_id' => $validated['valid_id_id'],
                'valid_id_photo' => $validIdFrontPath,
                'valid_id_back_photo' => $validIdBackPath,
                'police_clearance' => $policeClearancePath,
                'date_of_birth' => $validated['date_of_birth'],
                'age' => $age,
                'occupation_id' => $validated['occupation_id'] === 'others' ? null : $validated['occupation_id'],
                'occupation_other' => $validated['occupation_id'] === 'others' ? $validated['occupation_other'] : null,
                'address' => $address
            ];

            // Call Model to Create User and Property Owner
            $propertyOwnerModel = new propertyOwnerClass();
            $result = $propertyOwnerModel->addPropertyOwner($data);

            // Send Email
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Your account is successfully created by the admin.\n\n" .
                    "Login with:\n" .
                    "Username: " . $result['username'] . "\n" .
                    "Password: owner123@!\n\n" .
                    "Please change your password after logging in.",
                    function ($message) use ($result) {
                        $message->to($result['email'])
                            ->subject('Account Created - Legatura');
                    }
                );
            } catch (\Exception $e) {
                // Log email error but don't fail the request
                \Illuminate\Support\Facades\Log::error('Failed to send account creation email: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Property Owner added successfully']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function addContractor(contractorRequest $request)
    {
        $validated = $request->validated();

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
    }

    public function updateContractor(contractorRequest $request, $id)
    {
        $validated = $request->validated();

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
    }

    public function deletePropertyOwner(Request $request, $id)
    {
        $request->validate([
            'deletion_reason' => 'required|string|max:500',
        ]);

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
    }

    public function deleteContractor(Request $request, $id)
    {
        $request->validate([
            'deletion_reason' => 'required|string|max:500',
        ]);

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

        try {
            // Prepare Data for Model
            $data = [
                'owner_id' => $validated['owner_id'], // Now we receive owner_id from the form
                'role' => $validated['role'],
                'role_other' => $validated['role_other'] ?? null,
                'contractor_id' => $validated['contractor_id']
            ];

            // Call Model to Link Property Owner as Team Member
            $contractorModel = new contractorClass();
            $result = $contractorModel->addTeamMember($data);

            // Get the property owner details for the response
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
    }

    public function cancelInvitation(Request $request, $staffId)
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string|max:500'
            ]);

            $contractorModel = new contractorClass();
            $result = $contractorModel->cancelInvitation($staffId, $validated['reason']);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invitation canceled successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel invitation'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reapplyInvitation(Request $request, $staffId)
    {
        try {
            $contractorModel = new contractorClass();
            $result = $contractorModel->reapplyInvitation($staffId);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invitation reapplied successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reapply invitation'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change contractor representative
     */
    public function changeContractorRepresentative(changeContractorRepresentativeRequest $request)
    {
        $validated = $request->validated();

        try {
            $contractorModel = new contractorClass();
            $result = $contractorModel->changeRepresentative(
                $validated['contractor_id'],
                $validated['new_representative_id']
            );

            // Get updated representative details for notification
            $newRep = DB::table('contractor_staff')
                ->join('property_owners', 'contractor_staff.owner_id', '=', 'property_owners.owner_id')
                ->join('users', 'property_owners.user_id', '=', 'users.user_id')
                ->where('contractor_staff.staff_id', $validated['new_representative_id'])
                ->select(
                    'contractor_staff.*',
                    'users.email',
                    'users.username',
                    'users.first_name',
                    'users.last_name'
                )
                ->first();

            // Send notification email to new representative
            try {
                Mail::raw(
                    "You have been assigned as the Company Representative.\n\n" .
                    "This role gives you authorization to represent the company in all official matters.\n\n" .
                    "If you have any questions, please contact the administrator.",
                    function ($message) use ($newRep) {
                        $message->to($newRep->email)
                            ->subject('Company Representative Assignment - Legatura');
                    }
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send representative change email: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Company representative changed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
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
        try {
            $validated = $request->validated();

            // Get the contractor_staff record to get owner_id
            $staffMember = DB::table('contractor_staff')
                ->where('staff_id', $validated['staff_id'])
                ->first();

            if (!$staffMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team member not found'
                ], 404);
            }

            // Prepare contractor_staff table update data - only update role
            $staffData = [
                'company_role' => $validated['role'],
                'company_role_before' => $staffMember->company_role  // Save the previous role
            ];

            // Handle role "others" - store custom role in role_if_others column
            if ($validated['role'] === 'others' && isset($validated['role_other'])) {
                $staffData['role_if_others'] = $validated['role_other'];
            } else {
                // Clear role_if_others if role is not "others"
                $staffData['role_if_others'] = null;
            }

            // Update contractor_staff table
            DB::table('contractor_staff')
                ->where('staff_id', $validated['staff_id'])
                ->update($staffData);

            return response()->json([
                'success' => true,
                'message' => 'Team member updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suspend contractor team member
     */
    public function suspendContractorTeamMember(Request $request, $staffId)
    {
        try {
            $validated = $request->validate([
                'suspension_reason' => 'required|string|max:500',
                'duration' => 'required|in:temporary,permanent',
                'suspension_until' => 'required_if:duration,temporary|date|after:today'
            ]);

            // Determine suspension_until date
            $suspensionUntil = null;
            if ($validated['duration'] === 'permanent') {
                $suspensionUntil = '9999-12-31';
            } else {
                $suspensionUntil = $validated['suspension_until'];
            }

            // Update contractor_staff table to suspend the member
            DB::table('contractor_staff')
                ->where('staff_id', $staffId)
                ->update([
                    'is_active' => 0,
                    'is_suspended' => 1,
                    'suspension_reason' => $validated['suspension_reason'],
                    'suspension_until' => $suspensionUntil
                ]);

            // Send email notification
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
                        "Reason: {$validated['suspension_reason']}\n" .
                        "Duration: {$durationText}\n\n" .
                        "Please contact the company administrator or support for more information.\n\n" .
                        "Best regards,\nThe Legatura Team",
                        function ($mailMsg) use ($staff) {
                            $mailMsg->to($staff->email)
                                ->subject('Legatura - Staff Membership Suspended');
                        }
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send staff suspension email: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Team member suspended successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate contractor team member (soft delete)
     */
    public function deactivateContractorTeamMember(deactivateContractorTeamMemberRequest $request)
    {
        try {
            $validated = $request->validated();

            // Update contractor_staff table to set is_active = 0 and save reason
            DB::table('contractor_staff')
                ->where('staff_id', $validated['staff_id'])
                ->update([
                    'is_active' => 0,
                    'deletion_reason' => $validated['deletion_reason']
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Team member deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reactivate contractor team member
     */
    public function reactivateContractorTeamMember(reactivateContractorTeamMemberRequest $request)
    {
        try {
            $validated = $request->validated();

            // Check if the contractor company is active
            $staff = DB::table('contractor_staff')
                ->join('contractors', 'contractor_staff.contractor_id', '=', 'contractors.contractor_id')
                ->where('contractor_staff.staff_id', $validated['staff_id'])
                ->select('contractors.is_active as contractor_active', 'contractors.company_name')
                ->first();

            if (!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff member not found'
                ], 404);
            }

            if ($staff->contractor_active == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reactivate staff member. The contractor company "' . $staff->company_name . '" is currently suspended or inactive. Please reactivate the company first.'
                ], 400);
            }

            // Update contractor_staff table to reactivate the member
            DB::table('contractor_staff')
                ->where('staff_id', $validated['staff_id'])
                ->update([
                    'is_active' => 1,
                    'is_suspended' => 0,
                    'deletion_reason' => null,
                    'suspension_reason' => null,
                    'suspension_until' => null
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Team member reactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
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
            'contractors.company_name'
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
            'users.last_name'
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
                DB::table('users')->where('user_id', $id)->update(['user_type' => 'contractor']);
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
            }

            // In-app notification
            NotificationService::create(
                (int) $id,
                'project_update',
                'Account Verified',
                "Your {$roleLabel} account has been verified and approved. You can now access all platform features.",
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
    }

    /**
     * Reject a verification request
     */
    public function rejectVerification(rejectVerificationRequest $request, $id)
    {
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
                'project_update',
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

        $table = $validated['entityType'] === 'contractor' ? 'contractors' : 'property_owners';
        $idField = $validated['entityType'] === 'contractor' ? 'contractor_id' : 'owner_id';

        // Update the account status (set back to pending/approved)
        $updated = DB::table($table)
            ->where($idField, $validated['id'])
            ->update(['verification_status' => 'pending']);

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => ucfirst($validated['entityType']) . ' account reactivated successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to reactivate account'
        ], 400);
    }

    /**
     * Get all property owners with optional filters
     */
    private function getPropertyOwners($search = null, $status = null, $dateFrom = null, $dateTo = null, $page = 1, $onlyEligible = false)
    {
        $model = new propertyOwnerClass();
        return $model->getPropertyOwners($search, $status, $dateFrom, $dateTo, 10, $page, $onlyEligible);
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

        $onlyEligible = $request->input('eligible') ? true : false;

        $owners = $this->getPropertyOwners($search, $status, null, null, $page, $onlyEligible);

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

        $reason = $validated['reason'];
        $duration = $validated['duration'];
        $suspensionUntil = $validated['suspension_until'] ?? null;

        if ($duration === 'permanent') {
            $suspensionUntil = '9999-12-31';
        }

        $propertyOwnerModel = new propertyOwnerClass();
        $owner = $propertyOwnerModel->suspendOwner($id, $reason, $duration, $suspensionUntil);

        if ($owner) {
            // Get user email
            $user = User::where('user_id', $owner->user_id)->first();

            if ($user) {
                // Send email notification
                try {
                    $emailData = [
                        'name' => $owner->first_name,
                        'reason' => $reason,
                        'duration' => $duration,
                        'until' => $suspensionUntil
                    ];

                    Mail::raw("Dear {$owner->first_name},\n\nYour account has been suspended.\n\nReason: {$reason}\nDuration: " . ucfirst($duration) . "\nSuspension Until: {$suspensionUntil}\n\nPlease contact support for more information.", function ($message) use ($user) {
                        $message->to($user->email)
                            ->subject('Account Suspension Notification');
                    });
                } catch (\Exception $e) {
                    // Log email error but don't fail the request
                    // Log::error('Failed to send suspension email: ' . $e->getMessage());
                }
            }

            return response()->json(['success' => true, 'message' => 'Property owner suspended successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to suspend property owner'], 400);
    }

    /**
     * Suspend contractor (reusing property owner suspension logic)
     */
    public function suspendContractor(contractorRequest $request, $id)
    {
        $validated = $request->validated();

        $reason = $validated['reason'];
        $duration = $validated['duration'];
        $suspensionUntil = $validated['suspension_until'] ?? null;

        if ($duration === 'permanent') {
            $suspensionUntil = '9999-12-31';
        }

        $contractorModel = new contractorClass();
        $contractor = $contractorModel->suspendContractor($id, $reason, $duration, $suspensionUntil);

        if ($contractor) {
            // Get user email through property_owners table
            $owner = DB::table('property_owners')
                ->where('owner_id', $contractor->owner_id)
                ->first();

            if ($owner) {
                $user = User::where('user_id', $owner->user_id)->first();

                if ($user) {
                    // Send email notification
                    try {
                        $emailData = [
                            'name' => $contractor->company_name,
                            'reason' => $reason,
                            'duration' => $duration,
                            'until' => $suspensionUntil
                        ];

                        Mail::raw("Dear {$contractor->company_name},\n\nYour contractor account has been suspended.\n\nReason: {$reason}\nDuration: " . ucfirst($duration) . "\nSuspension Until: {$suspensionUntil}\n\nPlease contact support for more information.", function ($message) use ($user) {
                            $message->to($user->email)
                                ->subject('Contractor Account Suspension Notification');
                        });
                    } catch (\Exception $e) {
                        // Log email error but don't fail the request
                        \Illuminate\Support\Facades\Log::error('Failed to send suspension email: ' . $e->getMessage());
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Contractor suspended successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to suspend contractor'], 400);
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

        // Only set `is_active` if the column exists in the contractors table
        if (Schema::hasColumn('contractors', 'is_active')) {
            $updatePayload['is_active'] = 1;
        } else {
            \Log::warning("approveContractorVerification: 'is_active' column not present on contractors table; skipping setting is_active for user_id {$id}");
        }

        $updated = DB::table('contractors')
            ->where('user_id', $id)
            ->update($updatePayload);

        if ($updated) {
            \Log::info("Contractor verification approved for user_id: {$id}");

            // Ensure users.user_type reflects both roles if a property_owner profile exists
            try {
                $hasOwner = DB::table('property_owners')->where('user_id', $id)->exists();
                if ($hasOwner) {
                    $currentUser = DB::table('users')->where('user_id', $id)->first();
                    $updateData = ['user_type' => 'both'];
                    // Preserve the user's current active role so approval doesn't auto-switch
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
                    // Only contractor profile exists; set to contractor unless already both
                    $currentType = DB::table('users')->where('user_id', $id)->value('user_type');
                    if ($currentType !== 'both' && $currentType !== 'contractor') {
                        DB::table('users')->where('user_id', $id)->update(['user_type' => 'contractor']);
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('approveContractorVerification: failed to update users.user_type', ['user_id' => $id, 'error' => $e->getMessage()]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Contractor verification approved successfully'
            ]);
        }

        \Log::warning("No contractor record found for user_id: {$id}");
        return response()->json(['success' => false, 'message' => 'Contractor not found'], 404);
    }

    /**
     * Reject contractor verification request
     */
    public function rejectContractorVerification(VerificationRequest $request, $id)
    {
        \Log::info("Rejecting contractor verification for user_id: {$id}");

        $validated = $request->validated();
        $user = User::find($id);

        if (!$user) {
            \Log::error("User not found for verification rejection: {$id}");
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $updated = DB::table('contractors')
            ->where('user_id', $id)
            ->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $validated['reason'],
                'verification_date' => now()
            ]);

        if ($updated) {
            \Log::info("Contractor verification rejected for user_id: {$id}");
            return response()->json([
                'success' => true,
                'message' => 'Contractor verification rejected successfully'
            ]);
        }

        \Log::warning("No contractor record found for user_id: {$id}");
        return response()->json(['success' => false, 'message' => 'Contractor not found'], 404);
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
}

