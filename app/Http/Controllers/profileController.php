<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Services\psgcApiService;

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

        \Log::info('profileController.update called', [
            'user_id' => $userId,
            'has_profile_pic' => $request->hasFile('profile_pic'),
            'has_cover_photo' => $request->hasFile('cover_photo')
        ]);

        try {
            DB::beginTransaction();

            // Handle profile picture upload if present
            if ($request->hasFile('profile_pic')) {
                $file = $request->file('profile_pic');
                $filename = time() . '_profile_' . $file->getClientOriginalName();
                $path = $file->storeAs('profiles', $filename, 'public');
                DB::table('users')->where('user_id', $userId)->update(['profile_pic' => $path]);
                \Log::info('profileController.update stored profile_pic', ['user_id' => $userId, 'path' => $path]);
            }
            // Handle cover photo upload if present
            if ($request->hasFile('cover_photo')) {
                $file = $request->file('cover_photo');
                $filename = time() . '_cover_' . $file->getClientOriginalName();
                $path = $file->storeAs('cover_photos', $filename, 'public');
                DB::table('users')->where('user_id', $userId)->update(['cover_photo' => $path]);
                \Log::info('profileController.update stored cover_photo', ['user_id' => $userId, 'path' => $path]);
            }

            // Ensure contractor row exists when company media is uploaded; create minimal row if missing
            $contractorRowForMedia = DB::table('contractors')->where('user_id', $userId)->first();
            $needsContractor = !$contractorRowForMedia && ($request->hasFile('company_logo') || $request->hasFile('company_banner'));
            if ($needsContractor) {
                try {
                    $newId = DB::table('contractors')->insertGetId([
                        'user_id' => $userId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $contractorRowForMedia = DB::table('contractors')->where('contractor_id', $newId)->first();
                    \Log::info('profileController.update created contractor row for media', ['user_id' => $userId, 'contractor_id' => $newId]);
                } catch (\Exception $e) {
                    \Log::warning('profileController.update failed to create contractor row: ' . $e->getMessage());
                    $contractorRowForMedia = DB::table('contractors')->where('user_id', $userId)->first();
                }
            }

            if ($contractorRowForMedia) {
                if ($request->hasFile('company_logo')) {
                    $file = $request->file('company_logo');
                    $filename = time() . '_company_logo_' . $file->getClientOriginalName();
                    $path = $file->storeAs('profiles', $filename, 'public');
                    DB::table('contractors')->where('user_id', $userId)->update(['company_logo' => $path, 'updated_at' => now()]);
                    \Log::info('profileController.update stored contractors.company_logo', ['user_id' => $userId, 'path' => $path]);
                }

                if ($request->hasFile('company_banner')) {
                    $file = $request->file('company_banner');
                    $filename = time() . '_company_banner_' . $file->getClientOriginalName();
                    $path = $file->storeAs('cover_photos', $filename, 'public');
                    DB::table('contractors')->where('user_id', $userId)->update(['company_banner' => $path, 'updated_at' => now()]);
                    \Log::info('profileController.update stored contractors.company_banner', ['user_id' => $userId, 'path' => $path]);
                }
            }

            // Build users payload from allowed keys in request (only fields that exist on `users` table)
            $allowedUserKeys = [
                'username','email'
            ];

            $userPayload = [];
            foreach ($allowedUserKeys as $k) {
                if ($request->has($k)) {
                    $userPayload[$k] = $request->input($k);
                }
            }

            if (!empty($userPayload)) {
                DB::table('users')->where('user_id', $userId)->update($userPayload + ['updated_at' => now()]);
            }

            // Personal/profile fields that belong to property_owners table
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();
            $ownerKeys = [
                'first_name','middle_name','last_name','phone','date_of_birth','occupation_id','occupation_other',
                'address_street','address_barangay','address_city','address_province','address_postal'
            ];

            $ownerPayload = [];
            foreach ($ownerKeys as $k) {
                if ($request->has($k)) {
                    $ownerPayload[$k] = $request->input($k);
                }
            }

            if (!empty($ownerPayload) && $owner) {
                // Map frontend address_* fields into single `address` column used by property_owners
                $addressParts = [];
                if (!empty($ownerPayload['address_street'])) $addressParts[] = $ownerPayload['address_street'];
                if (!empty($ownerPayload['address_barangay'])) $addressParts[] = $ownerPayload['address_barangay'];
                if (!empty($ownerPayload['address_city'])) $addressParts[] = $ownerPayload['address_city'];
                if (!empty($ownerPayload['address_province'])) $addressParts[] = $ownerPayload['address_province'];
                if (!empty($ownerPayload['address_postal'])) $addressParts[] = $ownerPayload['address_postal'];

                $updateOwner = [];
                if (!empty($ownerPayload['first_name'])) $updateOwner['first_name'] = $ownerPayload['first_name'];
                if (!empty($ownerPayload['middle_name'])) $updateOwner['middle_name'] = $ownerPayload['middle_name'];
                if (!empty($ownerPayload['last_name'])) $updateOwner['last_name'] = $ownerPayload['last_name'];
                if (!empty($ownerPayload['phone'])) $updateOwner['phone_number'] = $ownerPayload['phone'];
                if (!empty($ownerPayload['date_of_birth'])) $updateOwner['date_of_birth'] = $ownerPayload['date_of_birth'];
                if (isset($ownerPayload['occupation_id'])) $updateOwner['occupation_id'] = $ownerPayload['occupation_id'];
                if (!empty($ownerPayload['occupation_other'])) $updateOwner['occupation_other'] = $ownerPayload['occupation_other'];
                if (!empty($addressParts)) $updateOwner['address'] = implode(', ', $addressParts);

                if (!empty($updateOwner)) {
                    // Ensure we do not force address verification or modify verification status here.
                    unset($updateOwner['address_requires_verification']);
                    unset($updateOwner['address_verification_pending']);
                    DB::table('property_owners')->where('user_id', $userId)->update($updateOwner + []);
                }
            }

            // If contractor-specific fields present and contractor exists, update contractor row
            $contractor = DB::table('contractors')->where('user_id', $userId)->first();
            $contractorKeys = [
                'company_name','company_phone','company_email','years_of_experience','type_id','contractor_type_other',
                'bio','company_description','company_start_date',
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
                // Apply contractor updates immediately; do not force verification status changes here.
                $contractorPayload['updated_at'] = now();
                DB::table('contractors')->where('user_id', $userId)->update($contractorPayload);
            }

            DB::commit();

            \Log::info('profileController.update committed', ['user_id' => $userId]);

            // Return refreshed user row and contractor row if any
            $updatedUser = DB::table('users')->where('user_id', $userId)->first();
            $updatedContractor = DB::table('contractors')->where('user_id', $userId)->first();
            $responsePayload = ['user' => $updatedUser];
            if ($updatedContractor) $responsePayload['contractor'] = $updatedContractor;
            return response()->json(['success' => true, 'data' => $responsePayload], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('profileController update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update profile', 'errors' => [$e->getMessage()]], 500);
        }
    }

    /**
     * API to fetch owner profile for About tab
     * Accepts query param `user_id` or `username`
     */
    public function apiGetProfile(Request $request)
    {
        $userId = $request->query('user_id');
        $username = $request->query('username');

        // If no user identifier provided, attempt to resolve authenticated user (session, sanctum or bearer token)
        if (!$userId && !$username) {
            $authUser = Session::get('user') ?: $request->user();
            if (!$authUser && $request->bearerToken()) {
                try {
                    $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
                    if ($token && $token->tokenable) $authUser = $token->tokenable;
                } catch (\Throwable $e) {
                    Log::warning('profileController apiGetProfile bearer fallback failed: ' . $e->getMessage());
                }
            }

            if ($authUser) {
                $userId = is_object($authUser) ? ($authUser->user_id ?? $authUser->id ?? null) : ($authUser['user_id'] ?? null);
            }
        }

        if (!$userId && $username) {
            $user = DB::table('users')->where('username', $username)->orWhere('email', $username)->first();
            if (!$user) return response()->json(['success' => false, 'message' => 'User not found'], 404);
            $userId = $user->user_id;
        }

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'user_id or username required'], 400);
        }

        // Get user data
        $user = DB::table('users')->where('user_id', $userId)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        // Preload owner and contractor rows and include curated payloads so frontend
        // can always access both shapes for `user_type = both` users.
        $ownerRow = DB::table('property_owners')->where('user_id', $userId)->first();
        $contractorRow = DB::table('contractors')->where('user_id', $userId)->first();

        // If the users.profile_pic is empty but property_owners has a profile_pic, prefer that
        if (($user->profile_pic === null || $user->profile_pic === '') && $ownerRow && !empty($ownerRow->profile_pic)) {
            $user->profile_pic = $ownerRow->profile_pic;
            \Log::debug('profileController.apiGetProfile: populated user.profile_pic from property_owners', ['user_id' => $userId, 'profile_pic' => $user->profile_pic]);
        }
        // Similarly populate cover_photo from owner row if missing on users
        if ((empty($user->cover_photo) || $user->cover_photo === null) && $ownerRow && !empty($ownerRow->cover_photo)) {
            $user->cover_photo = $ownerRow->cover_photo;
            \Log::debug('profileController.apiGetProfile: populated user.cover_photo from property_owners', ['user_id' => $userId, 'cover_photo' => $user->cover_photo]);
        }

        // If user images are still missing and contractor row exists, prefer contractor media
        if (($user->profile_pic === null || $user->profile_pic === '') && $contractorRow && !empty($contractorRow->company_logo)) {
            $user->profile_pic = $contractorRow->company_logo;
            \Log::debug('profileController.apiGetProfile: populated user.profile_pic from contractors.company_logo', ['user_id' => $userId, 'company_logo' => $user->profile_pic]);
        }
        if ((empty($user->cover_photo) || $user->cover_photo === null) && $contractorRow && !empty($contractorRow->company_banner)) {
            $user->cover_photo = $contractorRow->company_banner;
            \Log::debug('profileController.apiGetProfile: populated user.cover_photo from contractors.company_banner', ['user_id' => $userId, 'company_banner' => $user->cover_photo]);
        }

        $ownerKeys = [
            'first_name','middle_name','last_name','phone_number','date_of_birth','occupation_id','occupation_other',
            'address','address_verification_pending','bio','profile_pic','cover_photo','email','owner_id'
        ];

        $contractorKeys = [
            'contractor_id','company_name','company_description','bio','company_website','company_email','company_phone',
            'company_social_media','services_offered','business_address','picab_number','dti_sec_registration_photo','tin_business_reg_number',
            'company_start_date','years_of_experience','type_id','contractor_type_other','completed_projects',
            'verification_status','verification_date','rejection_reason','picab_category','business_permit_number',
            // include media fields so frontend receives company logo/banner
            'company_logo','company_banner',
            'business_permit_city','business_permit_expiration'
        ];

        if ($ownerRow) {
            $ownerPayload = [];
            foreach ($ownerKeys as $k) {
                $ownerPayload[$k] = $ownerRow->$k ?? null;
            }
            $responseData['owner'] = (object)$ownerPayload;
        }

        if ($contractorRow) {
            $contractorPayload = [];
            foreach ($contractorKeys as $k) {
                $contractorPayload[$k] = $contractorRow->$k ?? null;
            }
            $responseData['contractor'] = (object)$contractorPayload;
        }

        // Determine active role: request param 'role' -> session preferred_role -> user.preferred_role -> user_type
        $requestedRole = strtolower(trim((string)($request->query('role') ?? '')));
        $sessionRole = strtolower(trim((string)Session::get('preferred_role', '')));
        $userPreferred = strtolower(trim((string)($user->preferred_role ?? '')));
        $userType = strtolower(trim((string)($user->user_type ?? '')));

        if ($userType === 'both') {
            if (!empty($requestedRole)) {
                $activeRole = $requestedRole;
            } elseif (!empty($sessionRole)) {
                $activeRole = $sessionRole;
            } elseif (!empty($userPreferred)) {
                $activeRole = $userPreferred;
            } else {
                $activeRole = 'owner';
            }
        } else {
            $activeRole = $userType ?: 'owner';
        }

        // Normalize
        $activeRole = strpos($activeRole, 'contractor') !== false ? 'contractor' : 'owner';

        // Role-aware rating and review count
        $rating = null;
        $totalReviews = 0;

        $statsQuery = DB::table('reviews as r')
            ->whereNotNull('r.rating')
            ->join('projects as p', 'r.project_id', '=', 'p.project_id');

        if ($activeRole === 'contractor') {
            $contractor = DB::table('contractors')->where('user_id', $userId)->first();
            if ($contractor) {
                $statsQuery->where('p.selected_contractor_id', $contractor->contractor_id)
                           ->where('r.reviewee_user_id', $userId);
            } else {
                // no contractor profile -> no reviews
                $statsQuery->whereRaw('1=0');
            }
        } else {
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();
            if ($owner) {
                $statsQuery->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                           ->where('pr.owner_id', $owner->owner_id)
                           ->where('r.reviewee_user_id', $userId);
            } else {
                // no owner profile -> no reviews
                $statsQuery->whereRaw('1=0');
            }
        }

        $stats = $statsQuery
            ->select(
                DB::raw('COUNT(r.review_id) as total_reviews'),
                DB::raw('ROUND(AVG(r.rating), 1) as avg_rating')
            )
            ->first();

        if ($stats) {
            $totalReviews = $stats->total_reviews ? intval($stats->total_reviews) : 0;
            $rating = $stats->avg_rating !== null ? round(floatval($stats->avg_rating), 1) : null;
        }

        $responseData = [
            'user' => $user,
            'role' => $activeRole,
            'rating' => $rating,
            'total_reviews' => $totalReviews,
        ];

        if ($activeRole === 'owner') {
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();
            $occupationName = null;
            if ($owner && !empty($owner->occupation_id)) {
                $occupation = DB::table('occupations')->where('id', $owner->occupation_id)->first();
                $occupationName = $occupation ? $occupation->occupation_name : ($owner->occupation_other ?? null);
            } elseif ($owner) {
                $occupationName = $owner->occupation_other ?? null;
            }

            // Projects created by owner (projects.owner_id = property_owners.owner_id)
            $projects = [];
            $finished = 0;
            $ongoing = 0;
            if ($owner) {
                // Select projects where the owner.user_id matches.
                // Use a subquery to aggregate project_files per project_id to avoid GROUP BY on p.*
                $pfSub = '(SELECT project_id, GROUP_CONCAT(file_path SEPARATOR "||") as files FROM project_files GROUP BY project_id) pfagg';
                $projects = DB::table('projects as p')
                    ->leftJoin(DB::raw($pfSub), 'p.project_id', '=', 'pfagg.project_id')
                    ->leftJoin('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                    ->join('property_owners as o', 'o.owner_id', '=', 'pr.owner_id')
                    ->join('users as u', 'u.user_id', '=', 'o.user_id')
                    ->where('u.user_id', $userId)
                    ->select('p.*', 'pfagg.files', 'pr.created_at as post_created_at')
                    ->orderBy('pr.created_at', 'desc')
                    ->get();

                $finished = DB::table('projects as p')
                    ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                    ->join('property_owners as o', 'o.owner_id', '=', 'pr.owner_id')
                    ->join('users as u', 'u.user_id', '=', 'o.user_id')
                    ->where('u.user_id', $userId)
                    ->where('p.project_status', 'completed')
                    ->count();

                $ongoing = DB::table('projects as p')
                    ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                    ->join('property_owners as o', 'o.owner_id', '=', 'pr.owner_id')
                    ->join('users as u', 'u.user_id', '=', 'o.user_id')
                    ->where('u.user_id', $userId)
                    ->whereIn('p.project_status', ['in_progress', 'open', 'bidding_closed'])
                    ->count();
            }

            // Normalize concatenated files into arrays (GROUP_CONCAT uses '||' separator)
            if ($projects && $projects->count()) {
                $projects = $projects->map(function ($p) {
                    $filesArr = [];
                    if (!empty($p->files)) {
                        $parts = explode('||', $p->files);
                        $parts = array_map('trim', $parts);
                        $parts = array_filter($parts, function ($v) { return $v !== '' && $v !== null; });
                        $filesArr = array_values($parts);
                    }
                    $p->files = $filesArr;
                    return $p;
                });
            }



            // Build human-readable address from owner->address if present
            $address_display = null;
            if ($owner && !empty($owner->address)) {
                try {
                    $psgc = new psgcApiService();
                    $parts = array_map('trim', explode(',', $owner->address));
                    $street = $parts[0] ?? null;
                    $barangayCode = $parts[1] ?? null;
                    $cityCode = $parts[2] ?? null;
                    $provinceCode = $parts[3] ?? null;
                    $postal = $parts[4] ?? null;

                    $barangayName = null;
                    $cityName = null;
                    $provinceName = null;

                    if ($cityCode) {
                        $allBarangays = $psgc->getBarangaysByCity($cityCode);
                        foreach ($allBarangays as $b) {
                            if ((string)($b['code'] ?? '') === (string)$barangayCode) {
                                $barangayName = $b['name'];
                                break;
                            }
                        }

                        // attempt to resolve city name from all cities
                        $allCities = $psgc->getAllCities();
                        foreach ($allCities as $c) {
                            if ((string)($c['code'] ?? '') === (string)$cityCode) {
                                $cityName = $c['name'];
                                break;
                            }
                        }
                    }

                    if ($provinceCode) {
                        $provinces = $psgc->getProvinces();
                        foreach ($provinces as $prov) {
                            if ((string)($prov['code'] ?? '') === (string)$provinceCode) {
                                $provinceName = $prov['name'];
                                break;
                            }
                        }
                    }

                    $addrParts = [];
                    if ($street) $addrParts[] = $street;
                    if ($barangayName) $addrParts[] = $barangayName;
                    if ($cityName) $addrParts[] = $cityName;
                    if ($provinceName) $addrParts[] = $provinceName;
                    if ($postal) $addrParts[] = $postal;

                    $address_display = count($addrParts) ? implode(', ', $addrParts) : $owner->address;
                } catch (\Throwable $e) {
                    Log::warning('Failed to resolve PSGC names: ' . $e->getMessage());
                    $address_display = $owner->address;
                }
            }

            $responseData['owner'] = $owner;
            $responseData['occupation_name'] = $occupationName;
            $responseData['projects'] = $projects;
            $responseData['projects_done'] = $finished;
            $responseData['ongoing_projects'] = $ongoing;
            $responseData['address_display'] = $address_display;
            // expose address verification flag if present for frontend
            $responseData['owner']->address_verification_pending = $owner->address_verification_pending ?? ($owner->address_requires_verification ?? null);
        } else {
            // contractor
            $contractor = DB::table('contractors')->where('user_id', $userId)->first();
            $projects = [];
            $finished = 0;
            $ongoing = 0;
            $occupationName = null;

            if ($contractor) {
                // Projects where this contractor was selected (selected_contractor_id)
                $pfSub = '(SELECT project_id, GROUP_CONCAT(file_path SEPARATOR "||") as files FROM project_files GROUP BY project_id) pfagg';
                $projects = DB::table('projects as p')
                    ->leftJoin(DB::raw($pfSub), 'p.project_id', '=', 'pfagg.project_id')
                    ->leftJoin('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                    ->join('contractors as c', 'c.contractor_id', '=', 'p.selected_contractor_id')
                    ->join('users as u', 'u.user_id', '=', 'c.user_id')
                    ->where('u.user_id', $userId)
                    ->select('p.*', 'pfagg.files', 'pr.created_at as post_created_at')
                    ->orderBy('pr.created_at', 'desc')
                    ->get();

                $finished = DB::table('projects as p')
                    ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                    ->join('contractors as c', 'c.contractor_id', '=', 'p.selected_contractor_id')
                    ->join('users as u', 'u.user_id', '=', 'c.user_id')
                    ->where('u.user_id', $userId)
                    ->where('p.project_status', 'completed')
                    ->count();

                $ongoing = DB::table('projects as p')
                    ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                    ->join('contractors as c', 'c.contractor_id', '=', 'p.selected_contractor_id')
                    ->join('users as u', 'u.user_id', '=', 'c.user_id')
                    ->where('u.user_id', $userId)
                    ->whereIn('p.project_status', ['in_progress', 'open', 'bidding_closed'])
                    ->count();

                if (!empty($contractor->occupation_id)) {
                    $occupation = DB::table('occupations')->where('id', $contractor->occupation_id)->first();
                    $occupationName = $occupation ? $occupation->occupation_name : ($contractor->occupation_other ?? null);
                } else {
                    $occupationName = $contractor->occupation_other ?? null;
                }
            }

            // Normalize concatenated files into arrays (GROUP_CONCAT uses '||' separator)
            if ($projects && $projects->count()) {
                $projects = $projects->map(function ($p) {
                    $filesArr = [];
                    if (!empty($p->files)) {
                        $parts = explode('||', $p->files);
                        $parts = array_map('trim', $parts);
                        $parts = array_filter($parts, function ($v) { return $v !== '' && $v !== null; });
                        $filesArr = array_values($parts);
                    }
                    $p->files = $filesArr;
                    return $p;
                });
            }

            // Ensure representatives variables exist even if no contractor found
            $representatives = [];
            $representative = null;
            if ($contractor && !empty($contractor->contractor_id)) {
                $contractor_id = $contractor->contractor_id;
                $representatives = DB::table('contractor_users as cu')
                    ->join('users as u', 'cu.user_id', '=', 'u.user_id')
                    ->where('cu.contractor_id', $contractor_id)
                    ->where('cu.role', 'representative')
                    ->where('cu.is_deleted', 0)
                    ->where('cu.is_active', 1)
                    ->select(
                        'u.profile_pic',
                        'u.email',
                        'cu.phone_number',
                        DB::raw("CONCAT(cu.authorized_rep_fname, ' ', IFNULL(cu.authorized_rep_mname, ''), ' ', cu.authorized_rep_lname) as full_name"),
                        'cu.role'
                    )
                    ->get();

                $representative = $representatives->first() ?? null;
            }

            $responseData['contractor'] = $contractor;
            $responseData['occupation_name'] = $occupationName;
            $responseData['projects'] = $projects;
            $responseData['projects_done'] = $finished;
            $responseData['ongoing_projects'] = $ongoing;
            $responseData['representative'] = $representative;
            $responseData['representatives'] = $representatives;
            // ensure frontend fields exist for edit form
            if ($contractor) {
                $responseData['contractor']->completed_projects = $finished;
                $responseData['contractor']->years_of_experience = $contractor->years_of_experience ?? 0;
            }
        }

        return response()->json(['success' => true, 'data' => $responseData]);
    }

    /**
     * API to fetch reviews for a given user (reviewee)
     * Query params: reviewee_user_id or user_id
     */
    public function apiGetReviews(Request $request)
    {
        $projectId = $request->query('project_id');
        $reviewee = $request->query('reviewee_user_id') ?? $request->query('user_id');

        if (!$projectId && !$reviewee) {
            return response()->json(['success' => false, 'message' => 'project_id or reviewee_user_id (or user_id) is required'], 400);
        }



        try {
            $query = DB::table('reviews as r')
                ->leftJoin('users as ru', 'r.reviewer_user_id', '=', 'ru.user_id')
                ->leftJoin('contractors as c', 'ru.user_id', '=', 'c.user_id')
                ->orderBy('r.created_at', 'desc');

            if ($projectId) {
                $query->where('r.project_id', $projectId);
            }

            if ($reviewee) {
                // Determine role preference: request param -> session -> user's user_type
                $roleParam = $request->query('role') ?? session('preferred_role') ?? null;
                if (!$roleParam) {
                    $u = DB::table('users')->where('user_id', $reviewee)->first();
                    $roleParam = $u->user_type ?? null;
                }
                $role = $roleParam ? strtolower(str_replace(' ', '_', $roleParam)) : null;

                if ($role === 'contractor') {
                    $contractor = DB::table('contractors')->where('user_id', $reviewee)->first();
                    if (!$contractor) {
                        return response()->json(['success' => true, 'data' => []], 200);
                    }
                    $query->join('projects as p', 'r.project_id', '=', 'p.project_id')
                          ->where('p.selected_contractor_id', $contractor->contractor_id)
                          ->where('r.reviewee_user_id', $reviewee);
                } elseif ($role === 'property_owner' || $role === 'owner') {
                    $owner = DB::table('property_owners')->where('user_id', $reviewee)->first();
                    if (!$owner) {
                        return response()->json(['success' => true, 'data' => []], 200);
                    }
                    // Some schemas don't have projects.owner_id; use project_relationships mapping instead
                    $query->join('projects as p', 'r.project_id', '=', 'p.project_id')
                          ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                          ->where('pr.owner_id', $owner->owner_id)
                          ->where('r.reviewee_user_id', $reviewee);
                } else {
                    $query->where('r.reviewee_user_id', $reviewee);
                }
            }

                // build a dedicated stats query (no ORDER/LIMIT) that mirrors the filters above
                $statsQuery = DB::table('reviews as r')
                    ->leftJoin('users as ru', 'r.reviewer_user_id', '=', 'ru.user_id')
                    ->leftJoin('contractors as c', 'ru.user_id', '=', 'c.user_id');

                if ($projectId) {
                    $statsQuery->where('r.project_id', $projectId);
                }

                if ($reviewee) {
                    if ($role === 'contractor') {
                        // contractor filter
                        $contractor = DB::table('contractors')->where('user_id', $reviewee)->first();
                        if (!$contractor) {
                            return response()->json(['success' => true, 'data' => []], 200);
                        }
                        $statsQuery->join('projects as p', 'r.project_id', '=', 'p.project_id')
                            ->where('p.selected_contractor_id', $contractor->contractor_id)
                            ->where('r.reviewee_user_id', $reviewee);
                    } elseif ($role === 'property_owner' || $role === 'owner') {
                        $owner = DB::table('property_owners')->where('user_id', $reviewee)->first();
                        if (!$owner) {
                            return response()->json(['success' => true, 'data' => []], 200);
                        }
                        $statsQuery->join('projects as p', 'r.project_id', '=', 'p.project_id')
                            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                            ->where('pr.owner_id', $owner->owner_id)
                            ->where('r.reviewee_user_id', $reviewee);
                    } else {
                        $statsQuery->where('r.reviewee_user_id', $reviewee);
                    }
                }

                $statsRow = $statsQuery->select(DB::raw('COUNT(r.review_id) as total_reviews'), DB::raw('ROUND(AVG(r.rating), 1) as avg_rating'))->first();

                $reviews = $query->select(
                    'r.review_id',
                    'r.project_id',
                    'r.reviewer_user_id',
                    'r.reviewee_user_id',
                    'r.rating',
                    'r.comment',
                    'r.created_at',
                    'ru.profile_pic as reviewer_profile_pic',
                    'ru.username as reviewer_username',
                    'ru.username as reviewer_name',
                    'c.company_name as reviewer_company_name',
                    DB::raw("ru.username as reviewer_display_name")
                )
                ->get();

            $stats = [
                'total_reviews' => $statsRow->total_reviews ? intval($statsRow->total_reviews) : 0,
                'avg_rating' => $statsRow->avg_rating !== null ? round(floatval($statsRow->avg_rating), 2) : null,
            ];

            return response()->json(['success' => true, 'data' => ['reviews' => $reviews, 'stats' => $stats]], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch reviews: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Public API endpoint to get project details for mobile/web clients.
     * If requester is owner (identified by ?user_id=...), returns full details (same as owner-only endpoint).
     * Otherwise returns a limited public view with owner name/pic, post_created_at and files.
     */
    public function apiGetProjectPublic(Request $request, $projectId)
    {
        try {
            // Fetch basic project + owner info via relationships
            $project = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->leftJoin('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->leftJoin('users as u', 'po.user_id', '=', 'u.user_id')
                ->leftJoin('contractor_types as ct', 'p.type_id', '=', 'ct.type_id')
                ->select(
                    'p.project_id',
                    'p.project_title',
                    'p.project_description',
                    'p.project_location',
                    'p.budget_range_min',
                    'p.budget_range_max',
                    'p.lot_size',
                    'p.floor_area',
                    'p.property_type',
                    'p.type_id',
                    'ct.type_name',
                    DB::raw('pr.created_at as post_created_at'),
                    'pr.owner_id',
                    'po.first_name',
                    'po.middle_name',
                    'po.last_name',
                    'u.user_id as owner_user_id',
                    'u.profile_pic as owner_profile_pic'
                )
                ->where('p.project_id', $projectId)
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            // Attach files (thumbnails/gallery)
            $files = DB::table('project_files')
                ->where('project_id', $projectId)
                ->orderBy('uploaded_at', 'asc')
                ->get();

            // Build owner full name
            $ownerFullName = trim((($project->first_name ?? '') . ' ' . ($project->middle_name ?? '') . ' ' . ($project->last_name ?? '')));

            // If requester provided user_id and is the owner, return full details
            $requesterId = $request->query('user_id');
            if ($requesterId && $project->owner_user_id && (int)$requesterId === (int)$project->owner_user_id) {
                $bidsCount = DB::table('bids')
                    ->where('project_id', $projectId)
                    ->whereNotIn('bid_status', ['cancelled'])
                    ->count();

                $project->bids_count = $bidsCount;
                $project->files = $files;

                return response()->json([
                    'success' => true,
                    'message' => 'Project details retrieved (owner)',
                    'data' => $project
                ], 200);
            }

            // Non-owner: limited public view
            $public = (object) [
                'project_id' => $project->project_id,
                'project_title' => $project->project_title,
                'project_description' => $project->project_description,
                'project_location' => $project->project_location,
                'budget_range_min' => $project->budget_range_min,
                'budget_range_max' => $project->budget_range_max,
                'lot_size' => $project->lot_size,
                'floor_area' => $project->floor_area,
                'property_type' => $project->property_type,
                'type_id' => $project->type_id,
                'type_name' => $project->type_name ?? null,
                'post_created_at' => $project->post_created_at ?? null,
                'owner_full_name' => $ownerFullName ?: null,
                'owner_profile_pic' => $project->owner_profile_pic ?? null,
                'files' => $files
            ];

            return response()->json([
                'success' => true,
                'message' => 'Project public details retrieved',
                'data' => $public
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project public details: ' . $e->getMessage()
            ], 500);
        }
    }
}
