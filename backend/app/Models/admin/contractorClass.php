<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class contractorClass extends Model
{
    protected $table = 'contractors';
    protected $primaryKey = 'contractor_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'company_name',
        'years_of_experience',
        'type_id',
        'contractor_type_other',
        'verification_status'
    ];

    public function bids(): HasMany
    {
        return $this->hasMany(bid::class, 'contractor_id', 'contractor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get contractors with filters
     */
    public function getContractors($search = null, $status = null, $dateFrom = null, $dateTo = null, $perPage = 15)
    {
        $query = DB::table('contractors')
            ->leftJoin('users', 'contractors.user_id', '=', 'users.user_id')
            ->leftJoin('contractor_users', function($join) {
                $join->on('contractors.contractor_id', '=', 'contractor_users.contractor_id')
                     ->where('contractor_users.role', '=', 'owner');
            })
            ->leftJoin('bids', 'contractors.contractor_id', '=', 'bids.contractor_id')
            ->select(
                'contractors.*',
                'users.email',
                'users.username',
                'users.profile_pic',
                'contractor_users.authorized_rep_fname',
                'contractor_users.authorized_rep_lname',
                'contractor_users.authorized_rep_mname',
                DB::raw('COUNT(bids.bid_id) as bids_count')
            )
            ->groupBy(
                'contractors.contractor_id',
                'contractors.user_id',
                'contractors.company_name',
                'contractors.company_start_date',
                'contractors.years_of_experience',
                'contractors.type_id',
                'contractors.contractor_type_other',
                'contractors.services_offered',
                'contractors.business_address',
                'contractors.company_email',
                'contractors.company_phone',
                'contractors.company_website',
                'contractors.company_social_media',
                'contractors.company_description',
                'contractors.picab_number',
                'contractors.picab_category',
                'contractors.picab_expiration_date',
                'contractors.business_permit_number',
                'contractors.business_permit_city',
                'contractors.business_permit_expiration',
                'contractors.tin_business_reg_number',
                'contractors.dti_sec_registration_photo',
                'contractors.verification_status',
                'contractors.verification_date',
                'contractors.rejection_reason',
                'contractors.completed_projects',
                'contractors.created_at',
                'contractors.updated_at',
                'users.email',
                'users.username',
                'users.profile_pic',
                'contractor_users.authorized_rep_fname',
                'contractor_users.authorized_rep_lname',
                'contractor_users.authorized_rep_mname'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('contractors.company_name', 'like', "%{$search}%")
                  ->orWhere('contractor_users.authorized_rep_fname', 'like', "%{$search}%")
                  ->orWhere('contractor_users.authorized_rep_lname', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('contractors.verification_status', $status === 'verified' ? 'approved' : 'pending');
        } else {
            $query->where('contractors.verification_status', 'approved');
        }

        if ($dateFrom) {
            $query->whereDate('contractors.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('contractors.created_at', '<=', $dateTo);
        }

        return $query->orderBy('contractors.created_at', 'desc')->paginate($perPage);
    }

    /**
     * Add a new contractor
     */
    public function addContractor($data)
    {
        return DB::transaction(function () use ($data) {
            // Create User
            $user = User::create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password_hash' => Hash::make($data['password']),
                'OTP_hash' => '',
                'user_type' => 'contractor',
                'is_active' => 1,
                'is_verified' => 1
            ]);

            // Create Contractor Profile
            $contractor = self::create([
                'user_id' => $user->user_id,
                'company_name' => $data['company_name'],
                'years_of_experience' => $data['years_of_experience'],
                'type_id' => $data['type_id'],
                'contractor_type_other' => $data['contractor_type_other'] ?? null,
                'verification_status' => 'approved'
            ]);

            // Create Contractor User (Representative)
            DB::table('contractor_users')->insert([
                'contractor_id' => $contractor->contractor_id,
                'user_id' => $user->user_id,
                'authorized_rep_fname' => $data['first_name'],
                'authorized_rep_lname' => $data['last_name'],
                'authorized_rep_mname' => $data['middle_name'] ?? null,
                'phone_number' => $data['contact_number'],
                'role' => 'owner',
                'is_active' => 1,
                'created_at' => now()
            ]);

            return $contractor;
        });
    }
}
