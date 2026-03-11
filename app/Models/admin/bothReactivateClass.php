<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class bothReactivateClass extends Model
{
    /**
     * Get suspended contractors (owners with is_active = 0 and suspension data)
     * Fetch suspended contractors
     */
    public static function getSuspendedContractors($search = null, $dateFrom = null, $dateTo = null)
    {
        $query = DB::table('contractors as c')
            ->join('property_owners as po', 'c.owner_id', '=', 'po.owner_id')
            ->join('users as u', 'po.user_id', '=', 'u.user_id')
            ->where('c.is_active', 0)
            ->whereNotNull('c.suspension_reason')
            ->whereNotNull('c.suspension_until');

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
                'c.contractor_id as contractor_user_id',
                'po.owner_id',
                'po.user_id',
                'c.company_name as name',
                'u.email',
                'c.suspension_reason as reason',
                'c.suspension_until',
                'c.created_at as date_registered',
                'c.created_at as updated_at',
                DB::raw("'contractor' as user_type"),
                DB::raw('(SELECT COUNT(*) FROM projects p INNER JOIN project_relationships pr ON p.relationship_id = pr.rel_id WHERE pr.selected_contractor_id = c.contractor_id) as total_projects')
            )
            ->orderBy('c.created_at', 'desc')
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
            ->get();
    }

    /**
     * Reactivate a suspended contractor (set is_active = 1 for owner)
     */
    public static function reactivateContractor($contractorUserId)
    {
        return DB::table('contractors')
            ->where('contractor_id', $contractorUserId)
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
