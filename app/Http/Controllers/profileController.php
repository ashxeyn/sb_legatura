<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class profileController extends Controller
{
    /**
     * Update user profile (accepts FormData)
     * - Updates `users` table fields
     * - Updates existing `contractors` row when contractor fields provided (does not create contractor row)
     * - Handles `profile_pic` file upload
     */
    public function update(Request $request)
    {
        // Resolve authenticated user (session, sanctum, or bearer token fallback)
        $user = Session::get('user') ?: $request->user();
        if (!$user && $request->bearerToken()) {
            try {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
                if ($token && $token->tokenable) $user = $token->tokenable;
            } catch (\Throwable $e) {
                Log::warning('profileController bearer fallback failed: ' . $e->getMessage());
            }
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $userId = is_object($user) ? ($user->user_id ?? $user->id ?? null) : ($user['user_id'] ?? null);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Invalid user context'], 400);
        }

        try {
            DB::beginTransaction();

            // Handle profile picture upload if present
            if ($request->hasFile('profile_pic')) {
                $file = $request->file('profile_pic');
                $filename = time() . '_profile_' . $file->getClientOriginalName();
                $path = $file->storeAs('profile_pictures', $filename, 'public');
                DB::table('users')->where('user_id', $userId)->update(['profile_pic' => $path]);
            }

            // Build users payload from allowed keys in request
            $allowedUserKeys = [
                'username','email','first_name','middle_name','last_name','phone','occupation','date_of_birth',
                'address_street','address_barangay','address_city','address_province','address_postal'
            ];

            $userPayload = [];
            foreach ($allowedUserKeys as $k) {
                if ($request->has($k)) {
                    $userPayload[$k] = $request->input($k);
                }
            }

            if (!empty($userPayload)) {
                // Map address_* fields to stored column names if necessary
                $mapping = [
                    'address_street' => 'address_street',
                    'address_barangay' => 'address_barangay',
                    'address_city' => 'address_city',
                    'address_province' => 'address_province',
                    'address_postal' => 'address_postal',
                ];
                $updatePayload = [];
                foreach ($userPayload as $key => $val) {
                    $updateKey = $mapping[$key] ?? $key;
                    $updatePayload[$updateKey] = $val;
                }
                DB::table('users')->where('user_id', $userId)->update($updatePayload + ['updated_at' => now()]);
            }

            // If contractor-specific fields present and contractor exists, update contractor row
            $contractor = DB::table('contractors')->where('user_id', $userId)->first();
            $contractorKeys = [
                'company_name','company_phone','years_of_experience','type_id','contractor_type_other',
                'services_offered','business_address','company_website','company_social_media',
                'picab_number','picab_category','picab_expiration_date','business_permit_number',
                'business_permit_city','business_permit_expiration','tin_business_reg_number'
            ];

            $hasContractorPayload = false;
            $contractorPayload = [];
            foreach ($contractorKeys as $k) {
                if ($request->has($k)) {
                    $contractorPayload[$k] = $request->input($k);
                    $hasContractorPayload = true;
                }
            }

            if ($hasContractorPayload && $contractor) {
                // If any of the sensitive fields changed, set verification back to pending
                $sensitive = ['company_name','picab_number','business_permit_number','tin_business_reg_number'];
                foreach ($sensitive as $s) {
                    if (isset($contractorPayload[$s])) {
                        $contractorPayload['verification_status'] = 'pending';
                        break;
                    }
                }
                $contractorPayload['updated_at'] = now();
                DB::table('contractors')->where('user_id', $userId)->update($contractorPayload);
            }

            DB::commit();

            // Return refreshed user row
            $updatedUser = DB::table('users')->where('user_id', $userId)->first();
            return response()->json(['success' => true, 'data' => $updatedUser], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('profileController update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update profile', 'errors' => [$e->getMessage()]], 500);
        }
    }
}
