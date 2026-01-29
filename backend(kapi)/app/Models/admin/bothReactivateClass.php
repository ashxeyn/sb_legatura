<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class bothReactivateClass extends Model
{
    /**
     * Get suspended contractors (owners with is_active = 0 and suspension data)
     * Only fetch owner role from contractor_users
     */
    public static function getSuspendedContractors($search = null, $dateFrom = null, $dateTo = null)
    {
        $query = DB::table('contractor_users as cu')
            ->join('contractors as c', 'cu.contractor_id', '=', 'c.contractor_id')
            ->join('users as u', 'cu.user_id', '=', 'u.user_id')
            ->where('cu.role', 'owner')
            ->where('cu.is_active', 0)
            ->whereNotNull('cu.suspension_reason')
            ->whereNotNull('cu.suspension_until')
            ->where('cu.is_deleted', 0);

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('c.company_name', 'like', "%{$search}%")
                  ->orWhere('u.email', 'like', "%{$search}%")
                  ->orWhere('cu.authorized_rep_fname', 'like', "%{$search}%")
                  ->orWhere('cu.authorized_rep_lname', 'like', "%{$search}%");
            });
        }

        // Apply date range filter
        if ($dateFrom) {
            $query->whereDate('cu.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('cu.created_at', '<=', $dateTo);
        }

        return $query->select(
                'cu.contractor_user_id',
                'cu.contractor_id',
                'cu.user_id',
                'c.company_name as name',
                'u.email',
                'cu.suspension_reason as reason',
                'cu.suspension_until',
                'cu.created_at as date_registered',
                'cu.created_at as updated_at',
                DB::raw("'contractor' as user_type"),
                DB::raw('(SELECT COUNT(*) FROM projects p INNER JOIN project_relationships pr ON p.relationship_id = pr.rel_id WHERE pr.selected_contractor_id = c.contractor_id) as total_projects')
            )
            ->orderBy('cu.created_at', 'desc')
            ->get();
    }

    /**
     * Get suspended property owners (is_active = 0 and suspension data)
     */
    public static function getSuspendedPropertyOwners($search = null, $dateFrom = null, $dateTo = null)
    {
        $query = DB::table('property_owners as po')
            ->join('users as u', 'po.user_id', '=', 'u.user_id')
            ->where('po.is_active', 0)
            ->whereNotNull('po.suspension_reason')
            ->whereNotNull('po.suspension_until')
            ->where('po.verification_status', '!=', 'deleted');

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('po.first_name', 'like', "%{$search}%")
                  ->orWhere('po.last_name', 'like', "%{$search}%")
                  ->orWhere('po.middle_name', 'like', "%{$search}%")
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
                DB::raw("CONCAT(po.first_name, ' ', COALESCE(po.middle_name, ''), ' ', po.last_name) as name"),
                'u.email',
                'po.suspension_reason as reason',
                'po.suspension_until',
                'po.created_at as date_registered',
                'po.created_at as updated_at',
                DB::raw("'property_owner' as user_type"),
                DB::raw('(SELECT COUNT(*) FROM projects p INNER JOIN project_relationships pr ON p.relationship_id = pr.rel_id WHERE pr.owner_id = po.owner_id) as total_projects')
            )
            ->orderBy('po.created_at', 'desc')
            ->get();
    }

    /**
     * Reactivate a suspended contractor (set is_active = 1 for owner)
     */
    public static function reactivateContractor($contractorUserId)
    {
        return DB::table('contractor_users')
            ->where('contractor_user_id', $contractorUserId)
            ->update([
                'is_active' => 1,
                'suspension_reason' => null,
                'suspension_until' => null
            ]);
    }

    /**
     * Reactivate a suspended property owner
     */
    public static function reactivatePropertyOwner($ownerId)
    {
        return DB::table('property_owners')
            ->where('owner_id', $ownerId)
            ->update([
                'is_active' => 1,
                'suspension_reason' => null,
                'suspension_until' => null
            ]);
    }
}
