<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\authController;
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
            ->where('owner_id', $id)
            ->first();

        if (!$propertyOwner) {
            return redirect()->route('admin.userManagement.propertyOwner')
                ->with('error', 'Property Owner not found');
        }

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
            ->where('contractor_id', $contractorId)
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
    public function verificationRequest()
    {
        $pendingContractors = $this->getPendingVerificationContractors();
        $pendingOwners = $this->getPendingVerificationOwners();

        return view('admin.userManagement.verificationRequest', [
            'pendingContractors' => $pendingContractors,
            'pendingOwners' => $pendingOwners
        ]);
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
     * Approve a verification request
     */
    public function approveVerification(Request $request, $id)
    {
        $type = $request->input('type'); // 'contractor' or 'owner'

        $table = $type === 'contractor' ? 'contractors' : 'property_owners';
        $idField = $type === 'contractor' ? 'contractor_id' : 'owner_id';

        $updated = DB::table($table)
            ->where($idField, $id)
            ->update(['is_verified' => 1]);

        if ($updated) {
            return response()->json(['success' => true, 'message' => ucfirst($type) . ' verified successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to verify'], 400);
    }

    /**
     * Reject a verification request
     */
    public function rejectVerification(Request $request, $id)
    {
        $type = $request->input('type');
        $reason = $request->input('reason', 'Rejected by admin');

        $table = $type === 'contractor' ? 'contractors' : 'property_owners';
        $idField = $type === 'contractor' ? 'contractor_id' : 'owner_id';

        $updated = DB::table($table)
            ->where($idField, $id)
            ->update(['verification_rejected_reason' => $reason]);

        if ($updated) {
            return response()->json(['success' => true, 'message' => ucfirst($type) . ' rejected']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to reject'], 400);
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
