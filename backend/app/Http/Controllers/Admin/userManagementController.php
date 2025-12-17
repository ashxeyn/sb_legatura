<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\authController;
use App\Models\User;
use App\Models\admin\propertyOwnerClass;
use App\Models\admin\contractorClass;
use App\Models\accounts\accountClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Admin\rejectVerificationRequest;
use App\Http\Requests\admin\propertyOwnerRequest;
use App\Http\Requests\admin\contractorRequest;
use App\Services\psgcApiService;
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
        $psgcService = new psgcApiService();

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
                'phone_number' => $validated['phone_number'],
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
            $data = [
                // Company Info
                'profile_pic' => $profilePicPath,
                'company_name' => $validated['company_name'],
                'company_phone' => $validated['company_phone'],
                'company_start_date' => $validated['company_start_date'],
                'years_of_experience' => $yearsOfExperience,
                'type_id' => $validated['contractor_type_id'],
                'contractor_type_other' => $validated['contractor_type_id'] == 9 ? $validated['contractor_type_other_text'] : null,
                'services_offered' => $validated['services_offered'],
                'company_website' => $validated['company_website'],
                'company_social_media' => $validated['company_social_media'],

                // Address
                'business_address' => $address,
                'business_address_street' => $validated['business_address_street'],
                'business_address_barangay' => $validated['business_address_barangay'],
                'business_address_city' => $validated['business_address_city'],
                'business_address_province' => $validated['business_address_province'],
                'business_address_postal' => $validated['business_address_postal'],

                // Representative
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'company_email' => $validated['company_email'],

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

            // Call Model to Create User and Contractor
            $contractorModel = new contractorClass();
            $result = $contractorModel->addContractor($data);

            // Send Email
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Your contractor account is successfully created by the admin.\n\n" .
                    "Login with:\n" .
                    "Username: " . $result['username'] . "\n" .
                    "Password: contractor123@!\n\n" .
                    "Please change your password after logging in.",
                    function ($message) use ($result) {
                        $message->to($result['email'])
                                ->subject('Contractor Account Created - Legatura');
                    }
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send contractor account creation email: ' . $e->getMessage());
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
            // Handle File Uploads
            if ($request->hasFile('dti_sec_registration_photo')) {
                $validated['dti_sec_registration_photo'] = $request->file('dti_sec_registration_photo')->store('DTI_SEC', 'public');
            }

            if ($request->hasFile('profile_pic')) {
                $validated['profile_pic'] = $request->file('profile_pic')->store('profile_pics', 'public');
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

            // Handle Contractor Type
            $typeId = $validated['contractor_type_id'];
            $typeOther = ($typeId == 9) ? ($validated['contractor_type_other_text'] ?? null) : null;

            // Prepare Data Array
            $data = [
                // Users Table Fields
                'company_email' => $validated['company_email'],
                'updated_at' => now(),

                // Contractors Table Fields
                'company_name' => $validated['company_name'],
                'company_start_date' => $validated['company_start_date'],
                'years_of_experience' => $yearsOfExperience,
                'type_id' => $typeId,
                'contractor_type_other' => $typeOther,
                'services_offered' => $validated['services_offered'] ?? null,
                'business_address' => $address,
                'company_phone' => $validated['company_phone'],
                'company_website' => $validated['company_website'] ?? null,
                'company_social_media' => $validated['company_social_media'] ?? null,
                'picab_number' => $validated['picab_number'],
                'picab_category' => $validated['picab_category'],
                'picab_expiration_date' => $validated['picab_expiration_date'],
                'business_permit_number' => $validated['business_permit_number'],
                'business_permit_city' => $validated['business_permit_city'],
                'business_permit_expiration' => $validated['business_permit_expiration'],
                'tin_business_reg_number' => $validated['tin_business_reg_number'],

                // Contractor Users (Representative) Fields
                'authorized_rep_fname' => $validated['first_name'],
                'authorized_rep_lname' => $validated['last_name'],
                'authorized_rep_mname' => $validated['middle_name'] ?? null,
                'phone_number' => $validated['company_phone'],
            ];

            // Add optional fields if present
            if (isset($validated['profile_pic'])) {
                $data['profile_pic'] = $validated['profile_pic'];
            }
            if (isset($validated['dti_sec_registration_photo'])) {
                $data['dti_sec_registration_photo'] = $validated['dti_sec_registration_photo'];
            }
            if (!empty($request->input('password'))) {
                $data['password_hash'] = bcrypt($request->input('password'));
            }

            // Call Model
            $contractorModel = new contractorClass();
            $contractorModel->editContractor($id, $data);

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

        // Parse Address
        $addressParts = explode(', ', $propertyOwner->address);
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
            $model = new propertyOwnerClass();
            $model->deleteOwner($id, $request->input('deletion_reason'));

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
            $model = new contractorClass();
            $model->deleteContractor($id, $request->input('deletion_reason'));

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
        $contractors = $contractorModel->getContractors($search, null, $dateFrom, $dateTo);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.userManagement.partials.contractorTable', compact('contractors'))->render()
            ]);
        }

        $accountModel = new accountClass();
        $psgcService = new psgcApiService();

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
        $psgcService = new psgcApiService();

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

        $accountModel = new accountClass();
        $psgcService = new psgcApiService();

        $picabCategories = $accountModel->getPicabCategories();
        $provinces = $psgcService->getProvinces();
        $contractorTypes = DB::table('contractor_types')->get();

        return view('admin.userManagement.contractor_Views', [
            'contractor' => $contractor,
            'picabCategories' => $picabCategories,
            'provinces' => $provinces,
            'contractorTypes' => $contractorTypes
        ]);
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
            ->join('users', 'contractors.user_id', '=', 'users.user_id')
            ->leftJoin('contractor_users', function($join) {
                $join->on('contractors.contractor_id', '=', 'contractor_users.contractor_id')
                     ->where('contractor_users.role', '=', 'owner');
            })
            ->where('contractors.verification_status', 'pending');

        if ($dateFrom) {
            $contractorQuery->whereDate('contractors.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $contractorQuery->whereDate('contractors.created_at', '<=', $dateTo);
        }
        if ($search) {
            $contractorQuery->where(function($q) use ($search) {
                $q->where('users.username', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('contractors.company_name', 'like', "%{$search}%")
                  ->orWhere('contractor_users.authorized_rep_fname', 'like', "%{$search}%")
                  ->orWhere('contractor_users.authorized_rep_lname', 'like', "%{$search}%");
            });
        }

        $contractorRequests = $contractorQuery->select(
                'users.user_id',
                'users.username',
                'users.email',
                'contractors.verification_status',
                'contractors.created_at as request_date',
                'contractors.company_name',
                'contractor_users.authorized_rep_fname',
                'contractor_users.authorized_rep_lname'
            )
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
            $ownerQuery->where(function($q) use ($search) {
                $q->where('users.username', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('property_owners.first_name', 'like', "%{$search}%")
                  ->orWhere('property_owners.last_name', 'like', "%{$search}%");
            });
        }

        $ownerRequests = $ownerQuery->select(
                'users.user_id',
                'users.username',
                'users.email',
                'property_owners.verification_status',
                'property_owners.created_at as request_date',
                'property_owners.first_name',
                'property_owners.last_name'
            )
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
    public function getVerificationRequestDetails($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $data = [
            'user' => $user,
            'profile' => null,
            'representative' => null
        ];

        if ($user->user_type === 'contractor') {
            $profile = DB::table('contractors')
                ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
                ->where('contractors.user_id', $id)
                ->select('contractors.*', 'contractor_types.type_name as contractor_type')
                ->first();

            if ($profile) {
                // Map DB columns to expected frontend keys
                $profile->pcab_license_number = $profile->picab_number;
                $profile->pcab_category = $profile->picab_category;
                $profile->pcab_validity = $profile->picab_expiration_date;
                $profile->tin_number = $profile->tin_business_reg_number;
                $profile->experience_years = $profile->years_of_experience;
                $profile->business_permit_validity = $profile->business_permit_expiration;
            }
            $data['profile'] = $profile;
            $data['representative'] = DB::table('contractor_users')->where('user_id', $id)->first();
        } elseif ($user->user_type === 'property_owner') {
            $profile = DB::table('property_owners')
                ->leftJoin('valid_ids', 'property_owners.valid_id_id', '=', 'valid_ids.id')
                ->leftJoin('occupations', 'property_owners.occupation_id', '=', 'occupations.id')
                ->where('property_owners.user_id', $id)
                ->select('property_owners.*', 'valid_ids.valid_id_name as valid_id_type', 'occupations.occupation_name')
                ->first();

            if ($profile) {
                $profile->birthdate = $profile->date_of_birth;
                $profile->occupation = $profile->occupation_name ?? $profile->occupation_other;
                $profile->valid_id_number = 'N/A'; // Column missing in DB
            }
            $data['profile'] = $profile;
        }

        return response()->json($data);
    }

    /**
     * Approve a verification request
     */
    public function approveVerification(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        // Update User table
        // $user->is_verified = true;
        // $user->is_active = true;
        // $user->save();

        // Update Profile table (optional, if there's a status field there too)
        if ($user->user_type === 'contractor') {
            DB::table('contractors')->where('user_id', $id)->update(['verification_status' => 'approved']);
        } elseif ($user->user_type === 'property_owner') {
            DB::table('property_owners')->where('user_id', $id)->update(['verification_status' => 'approved']);
        }

        return response()->json(['success' => true, 'message' => 'User verified successfully']);
    }

    /**
     * Reject a verification request
     */
    public function rejectVerification(rejectVerificationRequest $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $reason = $request->validated()['reason'];

        // Update Profile table with rejection reason
        if ($user->user_type === 'contractor') {
            DB::table('contractors')->where('user_id', $id)->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $reason
            ]);
        } elseif ($user->user_type === 'property_owner') {
            DB::table('property_owners')->where('user_id', $id)->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $reason
            ]);
        }

        // Note: We keep is_verified=false and is_active=false in users table

        return response()->json(['success' => true, 'message' => 'Verification rejected']);
    }

    /**
     * Show suspended accounts
     */
    public function suspendedAccounts()
    {
        $suspendedContractors = $this->getSuspendedContractors();
        $suspendedOwners = $this->getSuspendedOwners();

        return view('admin.userManagement.suspendedAccounts', [
            'suspendedContractors' => $suspendedContractors,
            'suspendedOwners' => $suspendedOwners
        ]);
    }

    /**
     * Reactivate a suspended account
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
    private function getPropertyOwners($search = null, $status = null, $dateFrom = null, $dateTo = null, $page = 1)
    {
        $model = new propertyOwnerClass();
        return $model->getPropertyOwners($search, $status, $dateFrom, $dateTo, 15, $page);
    }



    /**
     * Get pending verification contractors
     */
    private function getPendingVerificationContractors()
    {
        return DB::table('contractors')
            ->where('verification_status', 'pending')
            ->get();
    }

    /**
     * Get pending verification property owners
     */
    private function getPendingVerificationOwners()
    {
        return DB::table('property_owners')
            ->where('verification_status', 'pending')
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

        $owners = $this->getPropertyOwners($search, $status, null, null, $page);

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
     * Get contractors as JSON (for AJAX)
     */
    public function getContractorsApi(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $contractorModel = new contractorClass();
        $contractors = $contractorModel->getContractors($search, $status);

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
    public function suspendContractor(Request $request, $id)
    {
        $reason = $request->input('reason', 'Suspended by admin');

        $updated = DB::table('contractors')
            ->where('contractor_id', $id)
            ->update([
                'verification_status' => 'rejected'
            ]);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Contractor suspended']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to suspend'], 400);
    }

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
