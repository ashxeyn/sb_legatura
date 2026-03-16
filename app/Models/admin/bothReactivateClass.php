<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class bothReactivateClass extends Model
{
    /**
     * Get suspended contractors (contractors with is_active = 0 and suspension data)
     */
    public static function getSuspendedContractors($search = null, $dateFrom = null, $dateTo = null, $pageName = 'contractors_page')
    {
        $query = DB::table('contractors as c')
            ->join('property_owners as po', 'c.owner_id', '=', 'po.owner_id')
            ->join('users as u', 'po.user_id', '=', 'u.user_id')
            ->where('c.is_active', 0)
            ->whereNotNull('c.suspension_reason')
            ->whereNotNull('c.suspension_until')
            ->where('c.verification_status', '!=', 'deleted');

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('c.company_name', 'like', "%{$search}%")
                  ->orWhere('u.email', 'like', "%{$search}%")
                  ->orWhere('u.first_name', 'like', "%{$search}%")
                  ->orWhere('u.last_name', 'like', "%{$search}%");
            });
        }

        // Apply date range filter
        if ($dateFrom) {
            $query->whereDate('c.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('c.created_at', '<=', $dateTo);
        }

        return $query->select(
                'c.contractor_id',
                'po.owner_id',
                'u.user_id',
                'c.company_name as name',
                'c.company_logo',
                'u.email',
                'c.suspension_reason as reason',
                'c.suspension_until',
                'c.created_at as date_registered',
                'c.updated_at',
                DB::raw("'contractor' as user_type"),
                DB::raw('(SELECT COUNT(*) FROM projects p INNER JOIN project_relationships pr ON p.relationship_id = pr.rel_id WHERE pr.selected_contractor_id = c.contractor_id) as total_projects')
            )
            ->orderBy('c.created_at', 'desc')
            ->paginate(10, ['*'], $pageName)
            ->withQueryString();
    }

    /**
     * Get suspended property owners (is_active = 0 and suspension data)
     */
    public static function getSuspendedPropertyOwners($search = null, $dateFrom = null, $dateTo = null, $pageName = 'owners_page')
    {
        $query = DB::table('property_owners as po')
            ->join('users as u', 'po.user_id', '=', 'u.user_id')
            ->where('po.is_active', 0)
            ->whereNotNull('po.suspension_reason')
            ->whereNotNull('po.suspension_until')
            ->where('po.verification_status', '!=', 'deleted')
            ->whereIn('u.user_type', ['property_owner', 'both']);

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('u.first_name', 'like', "%{$search}%")
                  ->orWhere('u.last_name', 'like', "%{$search}%")
                  ->orWhere('u.middle_name', 'like', "%{$search}%")
                  ->orWhere('u.email', 'like', "%{$search}%");
            });
        }

        // Apply date range filter
        if ($dateFrom) {
            $query->whereDate('po.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('po.created_at', '<=', $dateTo);
        }

        return $query->select(
                'po.owner_id',
                'po.user_id',
                'po.profile_pic',
                DB::raw("CONCAT(u.first_name, ' ', COALESCE(u.middle_name, ''), ' ', u.last_name) as name"),
                'u.email',
                'po.suspension_reason as reason',
                'po.suspension_until',
                'po.created_at as date_registered',
                'po.created_at as updated_at',
                DB::raw("'property_owner' as user_type"),
                DB::raw('(SELECT COUNT(*) FROM projects p INNER JOIN project_relationships pr ON p.relationship_id = pr.rel_id WHERE pr.owner_id = po.owner_id) as total_projects')
            )
            ->orderBy('po.created_at', 'desc')
                ->paginate(10, ['*'], $pageName)
                ->withQueryString();
    }

    /**
     * Get suspended contractor staff (is_active = 0 and suspension data)
     */
    public static function getSuspendedStaff($search = null, $dateFrom = null, $dateTo = null, $contractorId = null, $pageName = 'staff_page')
    {
        $query = DB::table('contractor_staff as cs')
            ->join('contractors as c', 'cs.contractor_id', '=', 'c.contractor_id')
            ->join('property_owners as po', 'cs.owner_id', '=', 'po.owner_id')
            ->join('users as u', 'po.user_id', '=', 'u.user_id')
            ->where('cs.is_active', 0)
            ->where('cs.is_suspended', 1)
            ->whereNotNull('cs.suspension_reason')
            ->whereNotNull('cs.suspension_until')
            ->whereNull('cs.deletion_reason');

        // Apply contractor filter
        if ($contractorId) {
            $query->where('cs.contractor_id', $contractorId);
        }

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('u.first_name', 'like', "%{$search}%")
                  ->orWhere('u.last_name', 'like', "%{$search}%")
                  ->orWhere('u.email', 'like', "%{$search}%")
                  ->orWhere('c.company_name', 'like', "%{$search}%");
            });
        }

        // Apply date range filter
        if ($dateFrom) {
            $query->whereDate('cs.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('cs.created_at', '<=', $dateTo);
        }

        return $query->select(
                'cs.staff_id',
                'cs.contractor_id',
                'cs.owner_id',
                'u.user_id',
                DB::raw("CONCAT(u.first_name, ' ', COALESCE(u.middle_name, ''), ' ', u.last_name) as name"),
                'u.email',
                'c.company_name',
                DB::raw("COALESCE(cs.company_role, cs.role_if_others, 'N/A') as role"),
                'cs.suspension_reason as reason',
                'cs.suspension_until',
                'cs.created_at as date_registered',
                DB::raw("'staff' as user_type")
            )
            ->orderBy('cs.created_at', 'desc')
            ->paginate(10, ['*'], $pageName)
            ->withQueryString();
    }

    /**
     * Reactivate a suspended contractor (set is_active = 1)
     */
    public static function reactivateContractor($contractorId)
    {
        return DB::transaction(function () use ($contractorId) {
            // 1. Reactivate the contractor
            $result = DB::table('contractors')
                ->where('contractor_id', $contractorId)
                ->update([
                    'is_active' => 1,
                    'suspension_reason' => null,
                    'suspension_until' => null
                ]);

            // 2. Reactivate staff members that were suspended due to contractor suspension
            // Only reactivate staff whose suspension reason indicates they were suspended due to contractor suspension
            DB::table('contractor_staff')
                ->where('contractor_id', $contractorId)
                ->where('is_active', 0)
                ->where('is_suspended', 1)
                ->where(function($query) {
                    $query->where('suspension_reason', 'like', 'Contractor company suspended:%')
                          ->orWhere('suspension_reason', 'like', 'Property owner account suspended:%');
                })
                ->update([
                    'is_active' => 1,
                    'is_suspended' => 0,
                    'suspension_reason' => null,
                    'suspension_until' => null
                ]);

            // 3. Restore halted projects that were halted due to this contractor's suspension
            DB::table('projects')
                ->where('selected_contractor_id', $contractorId)
                ->where('project_status', 'halt')
                ->update([
                    'project_status' => 'in_progress'
                ]);

            return $result;
        });
    }

    /**
     * Reactivate a suspended property owner
     */
    public static function reactivatePropertyOwner($ownerId)
    {
        return DB::transaction(function () use ($ownerId) {
            // 1. Reactivate the property owner
            $result = DB::table('property_owners')
                ->where('owner_id', $ownerId)
                ->update([
                    'is_active' => 1,
                    'suspension_reason' => null,
                    'suspension_until' => null
                ]);

            // 2. Reactivate contractor companies that were suspended due to owner suspension
            // Only reactivate contractors whose suspension reason indicates they were suspended due to owner suspension
            $contractors = DB::table('contractors')
                ->where('owner_id', $ownerId)
                ->where('is_active', 0)
                ->where('suspension_reason', 'like', 'Property owner account suspended:%')
                ->get();

            foreach ($contractors as $contractor) {
                // Reactivate the contractor
                DB::table('contractors')
                    ->where('contractor_id', $contractor->contractor_id)
                    ->update([
                        'is_active' => 1,
                        'suspension_reason' => null,
                        'suspension_until' => null
                    ]);

                // Reactivate staff members that were suspended due to owner suspension
                DB::table('contractor_staff')
                    ->where('contractor_id', $contractor->contractor_id)
                    ->where('is_active', 0)
                    ->where('is_suspended', 1)
                    ->where('suspension_reason', 'like', 'Property owner account suspended:%')
                    ->update([
                        'is_active' => 1,
                        'is_suspended' => 0,
                        'suspension_reason' => null,
                        'suspension_until' => null
                    ]);

                // Note: Bids and projects are NOT automatically restored
                // They require manual admin review and restoration
            }

            return $result;
        });
    }

    /**
     * Reactivate a suspended staff member
     */
    public static function reactivateStaff($staffId)
    {
        // Check if the contractor company is active
        $staff = DB::table('contractor_staff')
            ->join('contractors', 'contractor_staff.contractor_id', '=', 'contractors.contractor_id')
            ->where('contractor_staff.staff_id', $staffId)
            ->select('contractors.is_active as contractor_active', 'contractor_staff.contractor_id')
            ->first();
        
        if (!$staff) {
            return false; // Staff not found
        }
        
        if ($staff->contractor_active == 0) {
            // Contractor is suspended/inactive, cannot reactivate staff
            throw new \Exception('Cannot reactivate staff member. The contractor company is currently suspended or inactive.');
        }
        
        // Contractor is active, proceed with reactivation
        DB::table('contractor_staff')
            ->where('staff_id', $staffId)
            ->update([
                'is_active' => 1,
                'is_suspended' => 0,
                'suspension_reason' => null,
                'suspension_until' => null
            ]);

        return true;
    }
}
