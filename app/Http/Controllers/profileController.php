<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Services\PsgcApiService;
use App\Services\UserActivityLogger;

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

        // After successful profile update, call:
        // UserActivityLogger::profileUpdated($userId, 'profile');
        // For email change: UserActivityLogger::profileUpdated($userId, 'email');
        // For profile photo: UserActivityLogger::profileUpdated($userId, 'profile_photo');

        \Log::info('profileController.update called', [
            'user_id' => $userId,
            'has_profile_pic' => $request->hasFile('profile_pic'),
            'has_cover_photo' => $request->hasFile('cover_photo')
        ]);

        // Determine which column links contractors -> users on this schema.
        // Some deployments use `user_id`, others use `owner_id` on contractors table.
        $contractorUserColumn = Schema::hasColumn('contractors', 'user_id')
            ? 'user_id'
            : (Schema::hasColumn('contractors', 'owner_id') ? 'owner_id' : 'user_id');

        try {
            DB::beginTransaction();

            // Handle profile picture upload if present
            if ($request->hasFile('profile_pic')) {
                $file = $request->file('profile_pic');
                $filename = time() . '_profile_' . $file->getClientOriginalName();
                $path = $file->storeAs('profiles', $filename, 'public');
                try {
                    if (Schema::hasColumn('users', 'profile_pic')) {
                        DB::table('users')->where('user_id', $userId)->update(['profile_pic' => $path]);
                    } elseif (Schema::hasColumn('property_owners', 'profile_pic')) {
                        DB::table('property_owners')->where('user_id', $userId)->update(['profile_pic' => $path]);
                    } else {
                        Log::warning('profileController.update: no profile_pic column found to update for user_id ' . $userId);
                    }
                } catch (\Throwable $e) {
                    Log::warning('profileController.update profile_pic update failed: ' . $e->getMessage());
                }
                \Log::info('profileController.update stored profile_pic', ['user_id' => $userId, 'path' => $path]);
            }
            // Handle cover photo upload if present
            if ($request->hasFile('cover_photo')) {
                $file = $request->file('cover_photo');
                $filename = time() . '_cover_' . $file->getClientOriginalName();
                $path = $file->storeAs('cover_photos', $filename, 'public');
                try {
                    if (Schema::hasColumn('users', 'cover_photo')) {
                        DB::table('users')->where('user_id', $userId)->update(['cover_photo' => $path]);
                    } elseif (Schema::hasColumn('property_owners', 'cover_photo')) {
                        DB::table('property_owners')->where('user_id', $userId)->update(['cover_photo' => $path]);
                    } else {
                        Log::warning('profileController.update: no cover_photo column found to update for user_id ' . $userId);
                    }
                } catch (\Throwable $e) {
                    Log::warning('profileController.update cover_photo update failed: ' . $e->getMessage());
                }
                \Log::info('profileController.update stored cover_photo', ['user_id' => $userId, 'path' => $path]);
            }

            // Ensure contractor row exists when company media is uploaded; create minimal row if missing
            $contractorRowForMedia = null;
            if (Schema::hasColumn('contractors', 'user_id')) {
                $contractorRowForMedia = DB::table('contractors')->where('user_id', $userId)->first();
            } elseif (Schema::hasColumn('contractors', 'owner_id')) {
                $contractorRowForMedia = DB::table('contractors as c')
                    ->join('property_owners as po', 'c.owner_id', '=', 'po.owner_id')
                    ->where('po.user_id', $userId)
                    ->select('c.*')
                    ->first();
            } else {
                $contractorRowForMedia = DB::table('contractors')->where('user_id', $userId)->first();
            }

            $needsContractor = !$contractorRowForMedia && ($request->hasFile('company_logo') || $request->hasFile('company_banner'));
            if ($needsContractor) {
                try {
                    $insertData = ['created_at' => now(), 'updated_at' => now()];
                    if (Schema::hasColumn('contractors', 'owner_id')) {
                        $po = DB::table('property_owners')->where('user_id', $userId)->first();
                        if ($po && isset($po->owner_id)) $insertData['owner_id'] = $po->owner_id;
                        elseif (Schema::hasColumn('contractors', 'user_id')) $insertData['user_id'] = $userId;
                    } else {
                        $insertData['user_id'] = $userId;
                    }

                    $newId = DB::table('contractors')->insertGetId($insertData);
                    $contractorRowForMedia = DB::table('contractors')->where('contractor_id', $newId)->first();
                    \Log::info('profileController.update created contractor row for media', ['user_id' => $userId, 'contractor_id' => $newId]);
                } catch (\Exception $e) {
                    \Log::warning('profileController.update failed to create contractor row: ' . $e->getMessage());
                    // fallback: attempt to resolve again using same logic
                    if (Schema::hasColumn('contractors', 'user_id')) {
                        $contractorRowForMedia = DB::table('contractors')->where('user_id', $userId)->first();
                    } elseif (Schema::hasColumn('contractors', 'owner_id')) {
                        $contractorRowForMedia = DB::table('contractors as c')
                            ->join('property_owners as po', 'c.owner_id', '=', 'po.owner_id')
                            ->where('po.user_id', $userId)
                            ->select('c.*')
                            ->first();
                    } else {
                        $contractorRowForMedia = DB::table('contractors')->where('user_id', $userId)->first();
                    }
                }
            }

            if ($contractorRowForMedia) {
                $contractorIdToUpdate = $contractorRowForMedia->contractor_id ?? null;
                if ($request->hasFile('company_logo')) {
                    $file = $request->file('company_logo');
                    $filename = time() . '_company_logo_' . $file->getClientOriginalName();
                    $path = $file->storeAs('profiles', $filename, 'public');
                    if ($contractorIdToUpdate) DB::table('contractors')->where('contractor_id', $contractorIdToUpdate)->update(['company_logo' => $path, 'updated_at' => now()]);
                    \Log::info('profileController.update stored contractors.company_logo', ['user_id' => $userId, 'path' => $path]);
                }

                if ($request->hasFile('company_banner')) {
                    $file = $request->file('company_banner');
                    $filename = time() . '_company_banner_' . $file->getClientOriginalName();
                    $path = $file->storeAs('cover_photos', $filename, 'public');
                    if ($contractorIdToUpdate) DB::table('contractors')->where('contractor_id', $contractorIdToUpdate)->update(['company_banner' => $path, 'updated_at' => now()]);
                    \Log::info('profileController.update stored contractors.company_banner', ['user_id' => $userId, 'path' => $path]);
                }
            }

            // Build users payload from allowed keys in request (only fields that exist on `users` table)
            $allowedUserKeys = [
                'username','email'
            ];

            // Include name fields on users if the schema supports them so frontend edits persist to users table
            if (Schema::hasColumn('users', 'first_name')) $allowedUserKeys[] = 'first_name';
            if (Schema::hasColumn('users', 'middle_name')) $allowedUserKeys[] = 'middle_name';
            if (Schema::hasColumn('users', 'last_name')) $allowedUserKeys[] = 'last_name';

            $userPayload = [];
            foreach ($allowedUserKeys as $k) {
                if ($request->exists($k)) {
                    $userPayload[$k] = $request->input($k);
                }
            }

            if (!empty($userPayload)) {
                DB::table('users')->where('user_id', $userId)->update($userPayload + ['updated_at' => now()]);
            }

            // Resolve active role from request so bio (and future role-specific fields) only go to the right table.
            // The frontend always sends active_role (e.g. 'owner' or 'contractor') via query param or request body.
            $activeRole = strtolower(trim((string)(
                $request->query('role') ??
                $request->input('active_role') ??
                session('preferred_role') ??
                ''
            )));
            $isContractorRole = str_contains($activeRole, 'contractor');
            $isOwnerRole = !$isContractorRole; // default to owner when role is unknown

            // If the frontend submitted a bio and the schema stores bio on `users`,
            // prefer saving property owner bio to `users` table (owner-specific behavior).
            if ($isOwnerRole && $request->exists('bio') && Schema::hasColumn('users', 'bio')) {
                try {
                    DB::table('users')->where('user_id', $userId)->update(['bio' => $request->input('bio'), 'updated_at' => now()]);
                } catch (\Throwable $e) {
                    Log::warning('profileController.update users.bio update failed: ' . $e->getMessage());
                }
            }

            // Personal/profile fields that belong to property_owners table
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();
            $ownerKeys = [
                'first_name','middle_name','last_name','phone','date_of_birth','occupation_id','occupation_other','occupation',
                'address_street','address_barangay','address_city','address_province','address_postal'
                // 'bio' is handled separately below (role-gated)
            ];

            $ownerPayload = [];
            foreach ($ownerKeys as $k) {
                if ($request->exists($k)) {
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
                if (!empty($ownerPayload['first_name']) && Schema::hasColumn('property_owners', 'first_name')) {
                    $updateOwner['first_name'] = $ownerPayload['first_name'];
                }
                // Allow explicit clearing of middle_name when frontend sends an empty value
                if (array_key_exists('middle_name', $ownerPayload) && Schema::hasColumn('property_owners', 'middle_name')) {
                    $updateOwner['middle_name'] = $ownerPayload['middle_name'];
                }
                if (!empty($ownerPayload['last_name']) && Schema::hasColumn('property_owners', 'last_name')) {
                    $updateOwner['last_name'] = $ownerPayload['last_name'];
                }
                if (!empty($ownerPayload['phone'])) {
                    if (Schema::hasColumn('property_owners', 'phone_number')) {
                        $updateOwner['phone_number'] = $ownerPayload['phone'];
                    } elseif (Schema::hasColumn('property_owners', 'phone')) {
                        $updateOwner['phone'] = $ownerPayload['phone'];
                    }
                }
                if (!empty($ownerPayload['date_of_birth']) && Schema::hasColumn('property_owners', 'date_of_birth')) {
                    $updateOwner['date_of_birth'] = $ownerPayload['date_of_birth'];
                }
                if (isset($ownerPayload['occupation_id']) && Schema::hasColumn('property_owners', 'occupation_id')) {
                    $updateOwner['occupation_id'] = $ownerPayload['occupation_id'];
                }
                // Map 'occupation' (text from frontend) to 'occupation_other' if column exists
                if (!empty($ownerPayload['occupation']) && Schema::hasColumn('property_owners', 'occupation_other')) {
                    $updateOwner['occupation_other'] = $ownerPayload['occupation'];
                }
                if (!empty($ownerPayload['occupation_other']) && Schema::hasColumn('property_owners', 'occupation_other')) {
                    $updateOwner['occupation_other'] = $ownerPayload['occupation_other'];
                }
                // Only write bio to property_owners when the active role is owner and column exists
                // but prefer `users.bio` when that column exists (fallback behavior)
                if ($isOwnerRole && $request->exists('bio') && Schema::hasColumn('property_owners', 'bio') && !Schema::hasColumn('users', 'bio')) {
                    $updateOwner['bio'] = $request->input('bio');
                }
                if (!empty($addressParts) && Schema::hasColumn('property_owners', 'address')) {
                    $updateOwner['address'] = implode(', ', $addressParts);
                }

                if (!empty($updateOwner)) {
                    // Ensure we do not force address verification or modify verification status here.
                    unset($updateOwner['address_requires_verification']);
                    unset($updateOwner['address_verification_pending']);
                    DB::table('property_owners')->where('user_id', $userId)->update($updateOwner + []);
                }
            }

            // If contractor-specific fields present and contractor exists, update contractor row
            // Resolve contractor in a schema-agnostic way to avoid direct `contractors.user_id` queries
            $contractor = $this->getContractorForUser($userId);
            $contractorKeys = [
                'company_name','company_phone','company_email','years_of_experience','type_id','contractor_type_other',
                'company_description','company_start_date',
                'services_offered','business_address','company_website','company_social_media',
                'picab_number','picab_category','picab_expiration_date','business_permit_number',
                'business_permit_city','business_permit_expiration','tin_business_reg_number'
                // 'bio' is handled separately below (role-gated)
            ];

            $hasContractorPayload = false;
            $contractorPayload = [];
            foreach ($contractorKeys as $k) {
                if ($request->exists($k)) {
                    $contractorPayload[$k] = $request->input($k);
                    $hasContractorPayload = true;
                }
            }

            // Build combined business_address from individual address fields (similar to owner)
            if ($request->exists('address_street') || $request->exists('address_barangay') || $request->exists('address_city') || $request->exists('address_province')) {
                $addressParts = [];
                if (!empty($request->input('address_street'))) $addressParts[] = $request->input('address_street');
                if (!empty($request->input('address_barangay'))) $addressParts[] = $request->input('address_barangay');
                if (!empty($request->input('address_city'))) $addressParts[] = $request->input('address_city');
                if (!empty($request->input('address_province'))) $addressParts[] = $request->input('address_province');
                if (!empty($request->input('address_postal'))) $addressParts[] = $request->input('address_postal');

                if (!empty($addressParts)) {
                    $contractorPayload['business_address'] = implode(', ', $addressParts);
                    $hasContractorPayload = true;
                }
            }

            if ($hasContractorPayload && $contractor) {
                // Only write bio to contractors when the active role is contractor
                if ($isContractorRole && $request->exists('bio')) {
                    if (Schema::hasColumn('contractors', 'bio')) {
                        $contractorPayload['bio'] = $request->input('bio');
                    } elseif (Schema::hasColumn('users', 'bio')) {
                        // Fallback: some schemas store generic bio on users table
                        try {
                            DB::table('users')->where('user_id', $userId)->update(['bio' => $request->input('bio'), 'updated_at' => now()]);
                        } catch (\Throwable $e) {
                            Log::warning('profileController.update users.bio fallback failed: ' . $e->getMessage());
                        }
                    }
                }
                // Apply contractor updates immediately; prefer updating by contractor_id to be schema-agnostic
                $contractorPayload['updated_at'] = now();
                $contractorIdToUpdate = $contractor->contractor_id ?? null;
                if ($contractorIdToUpdate) {
                    DB::table('contractors')->where('contractor_id', $contractorIdToUpdate)->update($contractorPayload);
                } else {
                    // Fallback: attempt to update using linking column if present (wrapped in try/catch)
                    try {
                        if (Schema::hasColumn('contractors', $contractorUserColumn)) {
                            DB::table('contractors')->where($contractorUserColumn, $userId)->update($contractorPayload);
                        } else {
                            Log::warning('profileController.update: cannot update contractor - no contractor_id and linking column missing for user_id ' . $userId);
                        }
                    } catch (\Throwable $e) {
                        Log::warning('profileController.update contractor update failed; attempting fallback: ' . $e->getMessage());
                        $c2 = $this->getContractorForUser($userId);
                        if ($c2 && !empty($c2->contractor_id)) {
                            DB::table('contractors')->where('contractor_id', $c2->contractor_id)->update($contractorPayload);
                        }
                    }
                }
            } elseif ($isContractorRole && $request->exists('bio') && $contractor) {
                // bio-only update for contractor (no other contractor fields were sent)
                $contractorIdToUpdate = $contractor->contractor_id ?? null;
                if ($contractorIdToUpdate) {
                    if (Schema::hasColumn('contractors', 'bio')) {
                        DB::table('contractors')->where('contractor_id', $contractorIdToUpdate)->update(['bio' => $request->input('bio'), 'updated_at' => now()]);
                    } elseif (Schema::hasColumn('users', 'bio')) {
                        try {
                            DB::table('users')->where('user_id', $userId)->update(['bio' => $request->input('bio'), 'updated_at' => now()]);
                        } catch (\Throwable $e) {
                            Log::warning('profileController.update users.bio fallback failed: ' . $e->getMessage());
                        }
                    }
                } else {
                    try {
                        if (Schema::hasColumn('contractors', $contractorUserColumn) && Schema::hasColumn('contractors', 'bio')) {
                            DB::table('contractors')->where($contractorUserColumn, $userId)->update(['bio' => $request->input('bio'), 'updated_at' => now()]);
                        } elseif (Schema::hasColumn('users', 'bio')) {
                            DB::table('users')->where('user_id', $userId)->update(['bio' => $request->input('bio'), 'updated_at' => now()]);
                        }
                    } catch (\Throwable $e) {
                        Log::warning('profileController.update contractor bio update failed: ' . $e->getMessage());
                        $c2 = $this->getContractorForUser($userId);
                        if ($c2 && !empty($c2->contractor_id) && Schema::hasColumn('contractors', 'bio')) {
                            DB::table('contractors')->where('contractor_id', $c2->contractor_id)->update(['bio' => $request->input('bio'), 'updated_at' => now()]);
                        } elseif (Schema::hasColumn('users', 'bio')) {
                            try {
                                DB::table('users')->where('user_id', $userId)->update(['bio' => $request->input('bio'), 'updated_at' => now()]);
                            } catch (\Throwable $e2) {
                                Log::warning('profileController.update users.bio fallback failed: ' . $e2->getMessage());
                            }
                        }
                    }
                }
            }

            DB::commit();

            \Log::info('profileController.update committed', ['user_id' => $userId]);

            // Log profile update activity
            UserActivityLogger::profileUpdated($userId, 'profile');

            // Return refreshed user row and contractor row if any
            $updatedUser = DB::table('users')->where('user_id', $userId)->first();
            // Refresh contractor via schema-agnostic resolver
            $updatedContractor = $this->getContractorForUser($userId);
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
     * Resolve a contractor record for a given user id in a schema-agnostic way.
     * - Prefer joining contractors -> property_owners (c.owner_id = po.owner_id) and matching po.user_id = $userId
     * - Fallback to contractor_users mapping for staff accounts
     * - Finally try resolving by property_owners.owner_id -> contractors.owner_id
     * This avoids direct queries against `contractors.user_id` which may not exist in some schemas.
     */
    protected function getContractorForUser($userId)
    {
        try {
            // Prefer join via owner_id -> property_owners
            if (Schema::hasColumn('contractors', 'owner_id')) {
                $c = DB::table('contractors as c')
                    ->join('property_owners as po', 'c.owner_id', '=', 'po.owner_id')
                    ->where('po.user_id', $userId)
                    ->select('c.*')
                    ->first();
                if ($c) return $c;
            }

            // Staff users may be linked via contractor_users
            $staffLink = DB::table('contractor_users')->where('user_id', $userId)->first();
            if ($staffLink && isset($staffLink->contractor_id)) {
                $c2 = DB::table('contractors')->where('contractor_id', $staffLink->contractor_id)->first();
                if ($c2) return $c2;
            }

            // Last-resort: resolve property_owner by user_id then find contractors by owner_id
            $po = DB::table('property_owners')->where('user_id', $userId)->first();
            if ($po && isset($po->owner_id)) {
                $c3 = DB::table('contractors')->where('owner_id', $po->owner_id)->first();
                if ($c3) return $c3;
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('getContractorForUser error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch base profile objects for a user and determine active role.
     * Returns array with keys: user, owner, contractor, role
     */
    protected function fetchProfileBase($userId, $requestedRole = null)
    {
        $user = DB::table('users')->where('user_id', $userId)->first();
        if (!$user) return null;

        $owner = DB::table('property_owners')->where('user_id', $userId)->first();

        // Resolve contractor without querying contractors.user_id directly
        $contractor = $this->getContractorForUser($userId);

        // Determine active role: request -> session -> user preferred -> user_type
        $requestedRole = strtolower(trim((string)($requestedRole ?? '')));
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

        // Normalize staff -> contractor, otherwise normalize to 'owner'|'contractor'
        if ($userType === 'staff') {
            $activeRole = 'contractor';
        } else {
            $activeRole = strpos($activeRole, 'contractor') !== false ? 'contractor' : 'owner';
        }

        return [
            'user' => $user,
            'owner' => $owner,
            'contractor' => $contractor,
            'role' => $activeRole,
        ];
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

        // Resolve contractor row in a schema-agnostic way (avoid direct contractors.user_id queries)
        $contractorRow = $this->getContractorForUser($userId);

        // Staff users are linked to a contractor via contractor_users, not directly in contractors.
            if (!$contractorRow && strtolower($user->user_type ?? '') === 'staff') {
            $staffLink = DB::table('contractor_users')->where('user_id', $userId)->first();
            if ($staffLink) {
                $contractorRow = DB::table('contractors')->where('contractor_id', $staffLink->contractor_id)->first();
                // Populate name fields on the user object from contractor_users
                if (empty($user->first_name)) $user->first_name = $staffLink->authorized_rep_fname ?? null;
                if (empty($user->middle_name)) $user->middle_name = $staffLink->authorized_rep_mname ?? null;
                if (empty($user->last_name)) $user->last_name = $staffLink->authorized_rep_lname ?? null;
            }
        }

        // Safely read user image properties (avoid undefined property notices)
        $userProfilePic = $user->profile_pic ?? null;
        $userCoverPhoto = $user->cover_photo ?? null;

        // If the users.profile_pic is empty but property_owners has a profile_pic, prefer that
        if (($userProfilePic === null || $userProfilePic === '') && $ownerRow && !empty($ownerRow->profile_pic)) {
            $user->profile_pic = $ownerRow->profile_pic;
            \Log::debug('profileController.apiGetProfile: populated user.profile_pic from property_owners', ['user_id' => $userId, 'profile_pic' => $user->profile_pic]);
            // keep the local var in sync
            $userProfilePic = $user->profile_pic;
        }
        // Similarly populate cover_photo from owner row if missing on users
        if ((empty($userCoverPhoto) || $userCoverPhoto === null) && $ownerRow && !empty($ownerRow->cover_photo)) {
            $user->cover_photo = $ownerRow->cover_photo;
            \Log::debug('profileController.apiGetProfile: populated user.cover_photo from property_owners', ['user_id' => $userId, 'cover_photo' => $user->cover_photo]);
            $userCoverPhoto = $user->cover_photo;
        }

        // If user images are still missing and contractor row exists, prefer contractor media
        // Skip this for staff — they have their own profile/cover, not the company logo.
        $isStaff = strtolower($user->user_type ?? '') === 'staff';
        if (!$isStaff && (($user->profile_pic ?? null) === null || ($user->profile_pic ?? '') === '') && $contractorRow && !empty($contractorRow->company_logo)) {
            $user->profile_pic = $contractorRow->company_logo;
            \Log::debug('profileController.apiGetProfile: populated user.profile_pic from contractors.company_logo', ['user_id' => $userId, 'company_logo' => $user->profile_pic]);
        }
        if (!$isStaff && (empty($user->cover_photo ?? null) || ($user->cover_photo ?? null) === null) && $contractorRow && !empty($contractorRow->company_banner)) {
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

        // Normalize — staff users belong to a contractor account
        if ($userType === 'staff') {
            $activeRole = 'contractor';
        } else {
            $activeRole = strpos($activeRole, 'contractor') !== false ? 'contractor' : 'owner';
        }

        // Role-aware rating and review count
        $rating = null;
        $totalReviews = 0;

        $statsQuery = DB::table('reviews as r')
            ->whereNotNull('r.rating')
            ->join('projects as p', 'r.project_id', '=', 'p.project_id');

        if ($activeRole === 'contractor') {
            // Get contractor - $contractorUserColumn is either 'user_id' (old) or 'owner_id' (new)
            $contractor = DB::table('contractors')->where($contractorUserColumn, $userId)->first();
            // For staff, resolve their parent contractor
            if (!$contractor && $userType === 'staff') {
                $staffLink = DB::table('contractor_staff')->where('user_id', $userId)->first();
                if ($staffLink) {
                    $contractor = DB::table('contractors')->where('contractor_id', $staffLink->contractor_id)->first();
                }
            }
            if ($contractor) {
                // Get the contractor's user_id through property_owners
                $contractorUserId = DB::table('property_owners')
                    ->where('owner_id', $contractor->owner_id)
                    ->value('user_id');
                
                $statsQuery->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                           ->where('pr.selected_contractor_id', $contractor->contractor_id)
                           ->where('r.reviewee_user_id', $contractorUserId);
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
                    ->leftJoin('contractor_types as ct', 'p.type_id', '=', 'ct.type_id')
                    ->join('property_owners as o', 'o.owner_id', '=', 'pr.owner_id')
                    ->join('users as u', 'u.user_id', '=', 'o.user_id')
                    ->where('u.user_id', $userId)
                    ->select('p.*', 'pfagg.files', 'pr.created_at as post_created_at', 'pr.bidding_due', 'ct.type_name as contractor_type_name')
                    ->orderBy('pr.created_at', 'desc')
                    ->get();

                // Add bids count for each project
                foreach ($projects as $project) {
                    $project->bids_count = DB::table('bids')
                        ->where('project_id', $project->project_id)
                        ->whereNotIn('bid_status', ['cancelled'])
                        ->count();
                }

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

            // Fetch files with file_type from project_files table for each project
            if ($projects && $projects->count()) {
                $projects = $projects->map(function ($p) {
                    $projectFiles = DB::table('project_files')
                        ->where('project_id', $p->project_id)
                        ->orderBy('uploaded_at', 'asc')
                        ->get();

                    $p->files = $projectFiles->map(function ($f) {
                        return [
                            'file_id' => $f->file_id,
                            'file_type' => $f->file_type,
                            'file_path' => $f->file_path,
                            'uploaded_at' => $f->uploaded_at
                        ];
                    })->toArray();

                    return $p;
                });
            }



            // Build human-readable address from owner->address if present
            $address_display = null;
            if ($owner && !empty($owner->address)) {
                try {
                    $psgc = new PsgcApiService();
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
            if ($owner) {
                $responseData['owner']->address_verification_pending = $owner->address_verification_pending ?? ($owner->address_requires_verification ?? null);
            }
        } else {
            // contractor (or staff linked to a contractor) - resolve without querying contractors.user_id
            $contractor = $this->getContractorForUser($userId);
            // Staff fallback already handled by getContractorForUser, keep compatibility if not found
            if (!$contractor && strtolower($user->user_type ?? '') === 'staff') {
                $staffLink = DB::table('contractor_users')->where('user_id', $userId)->first();
                if ($staffLink) {
                    $contractor = DB::table('contractors')->where('contractor_id', $staffLink->contractor_id)->first();
                }
            }
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
                    ->join('contractors as c', 'c.contractor_id', '=', 'pr.selected_contractor_id')
                    ->join('property_owners as po', 'c.owner_id', '=', 'po.owner_id')
                    ->join('users as u', 'po.user_id', '=', 'u.user_id')
                    ->where('u.user_id', $userId)
                    ->select('p.*', 'pfagg.files', 'pr.created_at as post_created_at')
                    ->orderBy('pr.created_at', 'desc')
                    ->get();

                $finished = DB::table('projects as p')
                    ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                    ->join('contractors as c', 'c.contractor_id', '=', 'pr.selected_contractor_id')
                    ->join('property_owners as po', 'c.owner_id', '=', 'po.owner_id')
                    ->join('users as u', 'po.user_id', '=', 'u.user_id')
                    ->where('u.user_id', $userId)
                    ->where('p.project_status', 'completed')
                    ->count();

                $ongoing = DB::table('projects as p')
                    ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                    ->join('contractors as c', 'c.contractor_id', '=', 'pr.selected_contractor_id')
                    ->join('property_owners as po', 'c.owner_id', '=', 'po.owner_id')
                    ->join('users as u', 'po.user_id', '=', 'u.user_id')
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
                if (Schema::hasTable('contractor_staff')) {
                    // New schema: contractor_staff links to property_owners (owner_id), which links to users
                    $repQuery = DB::table('contractor_staff as cs')
                        ->join('property_owners as rep_po', 'cs.owner_id', '=', 'rep_po.owner_id')
                        ->join('users as u', 'rep_po.user_id', '=', 'u.user_id')
                        ->where('cs.contractor_id', $contractor_id)
                        ->where('cs.company_role', 'representative')
                        ->where('cs.is_active', 1)
                        ->whereRaw("COALESCE(cs.deletion_reason, '') = ''");

                    $selects = [
                        'rep_po.profile_pic as profile_pic',
                        'u.email',
                        DB::raw("CONCAT(u.first_name, ' ', IFNULL(u.middle_name, ''), ' ', u.last_name) as full_name"),
                        DB::raw("cs.company_role as role")
                    ];

                    // Prefer phone stored on property_owners when available; otherwise fall back to users phone columns or empty string
                    if (Schema::hasColumn('property_owners', 'phone_number') || Schema::hasColumn('property_owners', 'phone')) {
                        $selects[] = DB::raw("COALESCE(rep_po.phone_number, rep_po.phone, u.phone_number, u.phone, '') as phone_number");
                    } elseif (Schema::hasColumn('users', 'phone_number') || Schema::hasColumn('users', 'phone')) {
                        $selects[] = DB::raw("COALESCE(u.phone_number, u.phone, '') as phone_number");
                    } else {
                        $selects[] = DB::raw("'' as phone_number");
                    }

                    $representatives = $repQuery->select($selects)->get();
                } elseif (Schema::hasTable('contractor_users')) {
                    // Legacy schema fallback
                    $representatives = DB::table('contractor_users as cu')
                        ->join('users as u', 'cu.user_id', '=', 'u.user_id')
                        ->leftJoin('property_owners as rep_po', 'u.user_id', '=', 'rep_po.user_id')
                        ->where('cu.contractor_id', $contractor_id)
                        ->where('cu.role', 'representative')
                        ->where('cu.is_deleted', 0)
                        ->where('cu.is_active', 1)
                        ->select(
                            'rep_po.profile_pic as profile_pic',
                            'u.email',
                            'cu.phone_number',
                            DB::raw("CONCAT(cu.authorized_rep_fname, ' ', IFNULL(cu.authorized_rep_mname, ''), ' ', cu.authorized_rep_lname) as full_name"),
                            'cu.role'
                        )
                        ->get();
                } else {
                    $representatives = collect();
                }

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

        Log::info('profileController.apiGetReviews called', [
            'project_id' => $projectId,
            'reviewee' => $reviewee,
            'query_role' => $request->query('role') ?? null,
        ]);

        try {
            $query = DB::table('reviews as r')
                ->leftJoin('users as ru', 'r.reviewer_user_id', '=', 'ru.user_id')
                ->leftJoin('property_owners as rpo', 'ru.user_id', '=', 'rpo.user_id')
                ->leftJoin('contractors as c', 'rpo.owner_id', '=', 'c.owner_id')
                ->where('r.is_deleted', 0)
                ->orderBy('r.created_at', 'desc');

            if ($projectId) {
                $query->where('r.project_id', $projectId);
            }

            if ($reviewee) {
                // Simplified behavior: return all reviews where reviewee_user_id matches the provided id
                // and are not deleted. This avoids complex role-based joins that can filter out
                // direct review rows that reference the reviewee_user_id.
                $query->where('r.reviewee_user_id', $reviewee);
            }

                // build a dedicated stats query (no ORDER/LIMIT) that mirrors the filters above
                $statsQuery = DB::table('reviews as r')
                    ->leftJoin('users as ru', 'r.reviewer_user_id', '=', 'ru.user_id')
                    ->leftJoin('property_owners as rpo', 'ru.user_id', '=', 'rpo.user_id')
                    ->leftJoin('contractors as c', 'rpo.owner_id', '=', 'c.owner_id');

                $statsQuery->where('r.is_deleted', 0);

                if ($projectId) {
                    $statsQuery->where('r.project_id', $projectId);
                }

                if ($reviewee) {
                    // Stats should mirror the simplified behavior: filter by reviewee_user_id
                    $statsQuery->where('r.reviewee_user_id', $reviewee);
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
                    'rpo.profile_pic as reviewer_profile_pic',
                    'ru.username as reviewer_username',
                    DB::raw("COALESCE(c.company_name, ru.username) as reviewer_name"),
                    'c.company_name as reviewer_company_name',
                    DB::raw("COALESCE(c.company_name, ru.username) as reviewer_display_name")
                )
                ->get();

            $stats = [
                'total_reviews' => $statsRow->total_reviews ? intval($statsRow->total_reviews) : 0,
                'avg_rating' => $statsRow->avg_rating !== null ? round(floatval($statsRow->avg_rating), 2) : null,
            ];

            // Log summary of results for debugging
            try {
                $reviewCount = is_object($reviews) && method_exists($reviews, 'count') ? $reviews->count() : (is_array($reviews) ? count($reviews) : 0);
                $reviewIds = [];
                if (is_object($reviews) && method_exists($reviews, 'pluck')) {
                    $reviewIds = $reviews->pluck('review_id')->toArray();
                } elseif (is_array($reviews)) {
                    foreach ($reviews as $r) { if (isset($r->review_id)) $reviewIds[] = $r->review_id; }
                }
                Log::info('profileController.apiGetReviews result', [
                    'project_id' => $projectId,
                    'reviewee' => $reviewee,
                    'role' => $request->query('role') ?? null,
                    'review_count' => $reviewCount,
                    'review_ids' => $reviewIds,
                    'stats' => $stats,
                ]);
            } catch (\Throwable $logEx) {
                Log::warning('profileController.apiGetReviews logging failed: ' . $logEx->getMessage());
            }

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
            // Build select list dynamically depending on available columns to support schema variations
            $selectCols = [
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
                'u.user_id as owner_user_id'
            ];

            // Prefer owner names from users table when available, fallback to property_owners
            if (Schema::hasColumn('users', 'first_name')) {
                $selectCols[] = 'u.first_name as first_name';
            } elseif (Schema::hasColumn('property_owners', 'first_name')) {
                $selectCols[] = 'po.first_name as first_name';
            }

            if (Schema::hasColumn('users', 'middle_name')) {
                $selectCols[] = 'u.middle_name as middle_name';
            } elseif (Schema::hasColumn('property_owners', 'middle_name')) {
                $selectCols[] = 'po.middle_name as middle_name';
            }

            if (Schema::hasColumn('users', 'last_name')) {
                $selectCols[] = 'u.last_name as last_name';
            } elseif (Schema::hasColumn('property_owners', 'last_name')) {
                $selectCols[] = 'po.last_name as last_name';
            }

            // Prefer property_owners.profile_pic when present, otherwise fall back to users.profile_pic
            if (Schema::hasColumn('property_owners', 'profile_pic')) {
                $selectCols[] = 'po.profile_pic as owner_profile_pic';
            } elseif (Schema::hasColumn('users', 'profile_pic')) {
                $selectCols[] = 'u.profile_pic as owner_profile_pic';
            }

            $project = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->leftJoin('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->leftJoin('users as u', 'po.user_id', '=', 'u.user_id')
                ->leftJoin('contractor_types as ct', 'p.type_id', '=', 'ct.type_id')
                ->select($selectCols)
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
