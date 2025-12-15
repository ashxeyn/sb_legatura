<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\authController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class userManagementController extends authController
{
    /**
     * Show property owners list
     */
    public function propertyOwners()
    {
        $propertyOwners = $this->getPropertyOwners();
        return view('admin.userManagement.propertyOwner', [
            'propertyOwners' => $propertyOwners
        ]);
    }

    /**
     * Show contractors list
     */
    public function contractors()
    {
        $contractors = $this->getContractors();
        return view('admin.userManagement.contractor', [
            'contractors' => $contractors
        ]);
    }

    /**
     * View details of a specific property owner
     */
    public function viewPropertyOwner($id)
    {
        $propertyOwner = DB::table('property_owners')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->leftJoin('valid_ids', 'property_owners.valid_id_id', '=', 'valid_ids.id')
            ->leftJoin('occupations', 'property_owners.occupation_id', '=', 'occupations.id')
            ->where('property_owners.owner_id', $id)
            ->select(
                'property_owners.*',
                'users.email',
                'users.username',
                'users.profile_pic',
                'valid_ids.valid_id_name',
                'occupations.occupation_name'
            )
            ->first();

        if (!$propertyOwner) {
            return redirect()->route('admin.userManagement.propertyOwner')
                ->with('error', 'Property Owner not found');
        }

        // Ensure occupation is populated
        $propertyOwner->occupation = $propertyOwner->occupation_name ?? $propertyOwner->occupation_other;

        return view('admin.userManagement.propertyOwner_Views', [
            'propertyOwner' => $propertyOwner
        ]);
    }

    /**
     * View details of a specific contractor
     */
    public function viewContractor(Request $request)
    {
        $contractorId = $request->query('id');

        $contractor = DB::table('contractors')
            ->join('users', 'contractors.user_id', '=', 'users.user_id')
            ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
            ->where('contractors.contractor_id', $contractorId)
            ->select('contractors.*', 'users.email', 'users.username', 'users.profile_pic', 'contractor_types.type_name')
            ->first();

        if (!$contractor) {
            return redirect()->route('admin.userManagement.contractor')
                ->with('error', 'Contractor not found');
        }

        return view('admin.userManagement.contractor_Views', [
            'contractor' => $contractor
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
            ->leftJoin('contractor_users', 'contractors.contractor_id', '=', 'contractor_users.contractor_id')
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
                'contractors_html' => view('admin.userManagement.partials.contractors_table', ['contractorRequests' => $contractorRequests, 'ownerRequests' => $ownerRequests])->render(),
                'owners_html' => view('admin.userManagement.partials.owners_table', ['ownerRequests' => $ownerRequests, 'contractorRequests' => $contractorRequests])->render(),
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
            DB::table('property_owners')->where('user_id', $id)->update(['verification_status' => 'verified']);
        }

        return response()->json(['success' => true, 'message' => 'User verified successfully']);
    }

    /**
     * Reject a verification request
     */
    public function rejectVerification(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $reason = $request->input('reason', 'Rejected by admin');

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
    private function getPropertyOwners($search = null, $status = null, $page = 1)
    {
        $query = DB::table('property_owners');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('verification_status', $status === 'verified' ? 'approved' : 'pending');
        }

        return $query->paginate(15, ['*'], 'page', $page);
    }

    /**
     * Get contractors with optional search and status filtering
     */
    private function getContractors($search = null, $status = null, $page = 1)
    {
        $query = DB::table('contractors');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('verification_status', $status === 'verified' ? 'approved' : 'pending');
        }

        return $query->paginate(15, ['*'], 'page', $page);
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

        $owners = $this->getPropertyOwners($search, $status, $page);

        return response()->json($owners);
    }

    /**
     * Get single property owner as JSON
     */
    public function getPropertyOwnerApi($id)
    {
        $owner = DB::table('property_owners')
            ->where('owner_id', $id)
            ->first();

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
    public function suspendPropertyOwner(Request $request, $id)
    {
        $reason = $request->input('reason', 'Suspended by admin');

        $updated = DB::table('property_owners')
            ->where('owner_id', $id)
            ->update([
                'verification_status' => 'rejected'
            ]);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Property owner suspended']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to suspend'], 400);
    }

    /**
     * Get contractors as JSON (for AJAX)
     */
    public function getContractorsApi(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $page = $request->input('page', 1);

        $contractors = $this->getContractors($search, $status, $page);

        return response()->json($contractors);
    }

    /**
     * Get single contractor as JSON
     */
    public function getContractorApi($id)
    {
        $contractor = DB::table('contractors')
            ->where('contractor_id', $id)
            ->first();

        if (!$contractor) {
            return response()->json(['error' => 'Contractor not found'], 404);
        }

        return response()->json($contractor);
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
