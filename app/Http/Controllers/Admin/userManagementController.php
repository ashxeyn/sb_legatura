<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\authController;
use App\Models\User;
use App\Models\admin\propertyOwnerClass;
use App\Models\admin\contractorClass;
use App\Models\admin\userVerificationClass;
use App\Models\accounts\accountClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\admin\rejectVerificationRequest;
use App\Http\Requests\admin\propertyOwnerRequest;
use App\Http\Requests\admin\contractorRequest;
use App\Http\Requests\admin\contractorTeamMemberRequest;
use App\Http\Requests\admin\updateContractorTeamMemberRequest;
use App\Http\Requests\admin\changeContractorRepresentativeRequest;
use App\Http\Requests\admin\deactivateContractorTeamMemberRequest;
use App\Http\Requests\admin\reactivateContractorTeamMemberRequest;
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
        $psgcService = new psgcApiService();

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
            // Handle Profile Picture Upload
            $profilePicPath = null;
            if ($request->hasFile('profile_pic')) {
                $profilePicPath = $request->file('profile_pic')->store('team_members', 'public');
            }

            // Prepare Data for Model
            $data = [
                'profile_pic' => $profilePicPath,
                'email' => $validated['email'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'phone_number' => $validated['phone_number'],
                'role' => $validated['role'],
                'role_other' => $validated['role_other'] ?? null,
                'contractor_id' => $validated['contractor_id']
            ];

            // Call Model to Create User and Team Member
            $contractorModel = new contractorClass();
            $result = $contractorModel->addTeamMember($data);

            // Send Email Notification
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "You have been added as a team member by the admin.\n\n" .
                    "Login Credentials:\n" .
                    "Username: " . $result['username'] . "\n" .
                    "Password: teammember123@!\n\n" .
                    "Note: Username and Password are automatically generated.\n" .
                    "Please change your password after logging in for security.",
                    function ($message) use ($result) {
                        $message->to($result['email'])
                                ->subject('Team Member Account Created - Legatura');
                    }
                );
            } catch (\Exception $e) {
                // Log email error but don't fail the request
                \Illuminate\Support\Facades\Log::error('Failed to send team member creation email: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Team member added successfully',
                'data' => [
                    'username' => $result['username'],
                    'email' => $result['email']
                ]
            ]);

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
            $newRep = DB::table('contractor_users')
                ->join('users', 'contractor_users.user_id', '=', 'users.user_id')
                ->where('contractor_user_id', $validated['new_representative_id'])
                ->select(
                    'contractor_users.*',
                    'users.email',
                    'users.username'
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
            $member = DB::table('contractor_users')
                ->join('users', 'contractor_users.user_id', '=', 'users.user_id')
                ->where('contractor_users.contractor_user_id', $id)
                ->select(
                    'contractor_users.*',
                    'users.username',
                    'users.email',
                    'users.profile_pic'
                )
                ->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team member not found'
                ], 404);
            }

            // Map database column names to expected frontend field names
            $memberData = [
                'contractor_user_id' => $member->contractor_user_id,
                'first_name' => $member->authorized_rep_fname,
                'middle_name' => $member->authorized_rep_mname,
                'last_name' => $member->authorized_rep_lname,
                'phone_number' => $member->phone_number,
                'role' => $member->role,
                'if_others' => $member->if_others,
                'username' => $member->username,
                'email' => $member->email,
                'profile_pic' => $member->profile_pic
            ];

            return response()->json([
                'success' => true,
                'data' => $memberData
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

            // Get the contractor_user record to get user_id
            $contractorUser = DB::table('contractor_users')
                ->where('contractor_user_id', $validated['contractor_user_id'])
                ->first();

            if (!$contractorUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team member not found'
                ], 404);
            }

            $userId = $contractorUser->user_id;

            // Handle profile picture upload if present
            $profilePicPath = null;
            if ($request->hasFile('profile_pic')) {
                $file = $request->file('profile_pic');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('img/profile_pics'), $filename);
                $profilePicPath = 'img/profile_pics/' . $filename;
            }

            // Prepare user table update data
            $userData = [
                'username' => $validated['username'],
                'email' => $validated['email']
            ];

            // Only update password if provided
            if (!empty($validated['password'])) {
                $userData['password_hash'] = password_hash($validated['password'], PASSWORD_DEFAULT);
            }

            // Only update profile pic if new one uploaded
            if ($profilePicPath) {
                $userData['profile_pic'] = $profilePicPath;
            }

            // Update users table
            DB::table('users')
                ->where('user_id', $userId)
                ->update($userData);

            // Prepare contractor_users table update data
            $contractorUserData = [
                'authorized_rep_fname' => $validated['first_name'],
                'authorized_rep_lname' => $validated['last_name'],
                'phone_number' => $validated['phone_number'],
                'role' => $validated['role']
            ];

            // Add optional middle name if provided
            if (isset($validated['middle_name'])) {
                $contractorUserData['authorized_rep_mname'] = $validated['middle_name'];
            }

            // Handle role "others" - store custom role in if_others column
            if ($validated['role'] === 'others' && isset($validated['role_other'])) {
                $contractorUserData['if_others'] = $validated['role_other'];
            } else {
                // Clear if_others if role is not "others"
                $contractorUserData['if_others'] = null;
            }

            // Update contractor_users table
            DB::table('contractor_users')
                ->where('contractor_user_id', $validated['contractor_user_id'])
                ->update($contractorUserData);

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
     * Deactivate contractor team member (soft delete)
     */
    public function deactivateContractorTeamMember(deactivateContractorTeamMemberRequest $request)
    {
        try {
            $validated = $request->validated();

            // Update contractor_users table to set is_active = 0, is_deleted = 1, and save reason
            DB::table('contractor_users')
                ->where('contractor_user_id', $validated['contractor_user_id'])
                ->update([
                    'is_active' => 0,
                    'is_deleted' => 1,
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

            // Update contractor_users table to reactivate the member
            DB::table('contractor_users')
                ->where('contractor_user_id', $validated['contractor_user_id'])
                ->update([
                    'is_active' => 1,
                    'is_deleted' => 0,
                    'deletion_reason' => null
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
        $verificationModel = new userVerificationClass();
        $data = $verificationModel->getVerificationDetails($id);

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

        $suspendedContractors = \App\Models\admin\bothReactivateClass::getSuspendedContractors($search, $dateFrom, $dateTo);
        $suspendedOwners = \App\Models\admin\bothReactivateClass::getSuspendedPropertyOwners($search, $dateFrom, $dateTo);

        // If AJAX request, return JSON with filtered data
        if ($request->ajax()) {
            $contractorsHtml = view('admin.userManagement.partials.suspendedContractorsTable', [
                'suspendedContractors' => $suspendedContractors
            ])->render();

            $ownersHtml = view('admin.userManagement.partials.suspendedOwnersTable', [
                'suspendedOwners' => $suspendedOwners
            ])->render();

            return response()->json([
                'contractors_html' => $contractorsHtml,
                'owners_html' => $ownersHtml
            ]);
        }

        return view('admin.userManagement.suspendedAccounts', [
            'suspendedContractors' => $suspendedContractors,
            'suspendedOwners' => $suspendedOwners
        ]);
    }

    /**
     * Reactivate a suspended contractor or property owner
     */
    public function reactivateSuspendedUser(\App\Http\Requests\admin\reactivateContractorTeamMemberRequest $request)
    {
        try {
            $userType = $request->input('user_type');
            $userId = $request->input('contractor_user_id'); // For contractor, this is contractor_user_id; for owner, it's owner_id

            if ($userType === 'contractor') {
                $result = \App\Models\admin\bothReactivateClass::reactivateContractor($userId);
                $message = 'Contractor reactivated successfully!';
            } elseif ($userType === 'property_owner') {
                $result = \App\Models\admin\bothReactivateClass::reactivatePropertyOwner($userId);
                $message = 'Property owner reactivated successfully!';
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
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
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
            // Get user email
            $user = User::where('user_id', $contractor->user_id)->first();

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
                    // Log::error('Failed to send suspension email: ' . $e->getMessage());
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

        $updated = DB::table('contractors')
            ->where('user_id', $id)
            ->update([
                'verification_status' => 'approved',
                'verification_date' => now(),
                'is_active' => 1
            ]);

        if ($updated) {
            \Log::info("Contractor verification approved for user_id: {$id}");
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
