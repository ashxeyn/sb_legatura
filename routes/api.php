<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authController;
use App\Http\Controllers\owner\projectsController;
use App\Http\Controllers\contractor\cprocessController;
use App\Http\Controllers\contractor\biddingController;
use App\Http\Controllers\contractor\progressUploadController;
use App\Http\Controllers\owner\paymentUploadController;
use App\Http\Controllers\projectPosting\projectPostingController;
use App\Http\Controllers\both\disputeController;
use App\Http\Controllers\both\notificationController;
use App\Http\Controllers\passwordController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\both\milestoneController;
use App\Http\Controllers\both\projectUpdateController;
use App\Http\Controllers\subs\payMongoController;
use App\Http\Controllers\owner\downpaymentController;
use App\Http\Controllers\verificationResubmitController;


//role switch test endpoint moved outside middleware group
// Test endpoint first

Route::get('/role/switch-form', function () {
    return response()->json(['success' => true, 'message' => 'This is a test']);
});
// Role management
Route::post('/role/switch', [cprocessController::class, 'switchRole']);
Route::get('/role/current', [cprocessController::class, 'getCurrentRole']);
// Switch form data (prefill + dropdowns)
Route::get('/role/switch-form', [authController::class, 'showSwitchForm']);
// Current contractor profile (for mobile profile screen)
Route::get('/contractor/me', [cprocessController::class, 'apiGetMyContractorProfile']);


Route::get('/role/switch-form', [authController::class, 'showSwitchForm']); // Get form data for adding role

// Add role endpoints (for users with single role to add another role)
Route::post('/role/add/contractor/step1', [authController::class, 'switchContractorStep1']);
Route::post('/role/add/contractor/step2', [authController::class, 'switchContractorStep2']);
Route::post('/role/add/contractor/final', [authController::class, 'switchContractorFinal']);
Route::post('/role/add/owner/step1', [authController::class, 'switchOwnerStep1']);
Route::post('/role/add/owner/step2', [authController::class, 'switchOwnerStep2']);
Route::post('/role/add/owner/final', [authController::class, 'switchOwnerFinal']);


// Update profile (profile picture / cover photo and general profile FormData)
// Route requests to profileController->update so file uploads and owner fields
// are handled consistently by the dedicated controller.
Route::post('/user/update-profile', [profileController::class, 'update']);
// Keep both `/profile` and `/user/profile` aliases for backward compatibility
Route::post('/profile', [profileController::class, 'update']);
Route::post('/user/profile', [profileController::class, 'update']);
// Fetch profile data (owner profile + stats) for mobile About tab
Route::get('/profile/fetch', [profileController::class, 'apiGetProfile']);
// Fetch reviews for a user (mobile Reviews tab)
Route::get('/profile/reviews', [profileController::class, 'apiGetReviews']);

// Test endpoint for mobile app
Route::get('/test', [authController::class, 'apiTest']);

// File serving endpoint for mobile app (bypasses Apache symlink issues)
// For viewing files inline (images, PDFs in browser)
Route::get('/files/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        return response()->json(['error' => 'File not found'], 404);
    }

    $mimeType = mime_content_type($fullPath);
    $fileName = basename($path);

    // For images and PDFs, display inline; for others, suggest download
    $disposition = 'inline';
    if (preg_match('/\.(doc|docx|xls|xlsx|ppt|pptx|zip|rar)$/i', $fileName)) {
        $disposition = 'attachment';
    }

    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => $disposition . '; filename="' . $fileName . '"',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET',
        'Access-Control-Allow-Headers' => '*',
    ]);
})->where('path', '.*');

// File download endpoint (forces download instead of inline view)
Route::get('/download/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        return response()->json(['error' => 'File not found'], 404);
    }

    $fileName = basename($path);

    return response()->download($fullPath, $fileName, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET',
        'Access-Control-Allow-Headers' => '*',
    ]);
})->where('path', '.*');

// Signup form data endpoint for mobile app
Route::get('/signup-form', [authController::class, 'showSignupForm']);

// Public routes (no authentication required)
Route::post('/login', [authController::class, 'apiLogin']);
Route::post('/register', [authController::class, 'apiRegister']);
Route::post('/force-change-password', [passwordController::class, 'apiForceChangePassword']);

// Forgot Password API routes (stateless, for mobile)
Route::post('/forgot-password/send-otp', [passwordController::class, 'sendResetOtp']);
Route::post('/forgot-password/verify-otp', [passwordController::class, 'verifyResetOtp']);
Route::post('/forgot-password/reset', [passwordController::class, 'resetPassword']);

// Change OTP endpoints (public; controller will resolve user via bearer token if provided)
Route::post('/change-otp/send', [\App\Http\Controllers\otpChangeController::class, 'sendOtp']);
Route::post('/change-otp/verify', [\App\Http\Controllers\otpChangeController::class, 'verifyOtp']);

// Mobile API signup routes (mirror web signup but stateless API paths for mobile clients)
Route::post('/signup/contractor/step1', [authController::class, 'contractorStep1']);
Route::post('/signup/contractor/step2', [authController::class, 'contractorStep2']);
Route::post('/signup/contractor/step3/verify-otp', [authController::class, 'contractorVerifyOtp']);
Route::post('/signup/contractor/step4', [authController::class, 'contractorStep4']);
Route::post('/signup/contractor/final', [authController::class, 'contractorFinalStep']);

// Temporary debug route to lookup users by email (do not expose in production)
Route::get('/debug/user', [authController::class, 'debugGetUserByEmail']);

Route::post('/signup/owner/step1', [authController::class, 'propertyOwnerStep1']);
Route::post('/signup/owner/step2', [authController::class, 'propertyOwnerStep2']);
Route::post('/signup/owner/step3/verify-otp', [authController::class, 'propertyOwnerVerifyOtp']);
// New route alias for property-owner (explicit naming for mobile frontend)
Route::post('/signup/property-owner/step3/verify-otp', [authController::class, 'propertyOwnerVerifyOtp']);
Route::post('/signup/owner/step4', [authController::class, 'propertyOwnerStep4']);
Route::post('/signup/owner/final', [authController::class, 'propertyOwnerFinalStep']);

// PSGC API Routes (public)
Route::get('/psgc/provinces', [authController::class, 'getProvinces']);
Route::get('/psgc/provinces/{provinceCode}/cities', [authController::class, 'getCitiesByProvince']);
Route::get('/psgc/cities', [authController::class, 'getAllCities']);
Route::get('/psgc/cities/{cityCode}/barangays', [authController::class, 'getBarangaysByCity']);

// Contractors endpoint for property owner feed
Route::get('/contractors', [\App\Http\Controllers\both\homepageController::class, 'apiGetContractors']);

// Contractor types endpoint for project creation form / filter chips
Route::get('/contractor-types', [\App\Http\Controllers\both\homepageController::class, 'apiGetContractorTypes']);

// Search & filter options for mobile search/filter UI
Route::get('/search/filter-options', [\App\Http\Controllers\both\homepageController::class, 'apiGetFilterOptions']);

// Combined user search (contractors + property owners)
Route::get('/search/users', [\App\Http\Controllers\both\homepageController::class, 'apiSearchUsers']);

// Owner endpoints - for owner dashboard/project management
Route::get('/owner/projects', [projectsController::class, 'apiGetOwnerProjects']);
Route::post('/owner/projects', [projectsController::class, 'apiCreateProject']);

// Public project details (non-owner) - returns limited public info
Route::get('/projects/{projectId}/public', [profileController::class, 'apiGetProjectPublic']);

Route::get('/owner/projects/{projectId}', [projectsController::class, 'apiGetProjectDetails']);
Route::get('/owner/projects/{projectId}/bids', [biddingController::class, 'getProjectBids']);
Route::post('/owner/projects/{projectId}/bids/{bidId}/accept', [projectsController::class, 'apiAcceptBid']);
Route::post('/owner/projects/{projectId}/bids/{bidId}/reject', [projectsController::class, 'apiRejectBid']);
Route::post('/owner/milestones/{milestoneId}/approve', [milestoneController::class, 'apiApproveMilestone']);
Route::post('/owner/milestones/{milestoneId}/reject', [milestoneController::class, 'apiRejectMilestone']);
Route::post('/owner/milestones/{milestoneId}/complete', [milestoneController::class, 'apiSetMilestoneComplete']);
Route::post('/owner/milestone-items/{itemId}/complete', [milestoneController::class, 'apiSetMilestoneItemComplete']);
Route::post('/owner/milestone-items/{itemId}/settlement-due-date', [milestoneController::class, 'setSettlementDueDateOwner']);
Route::post('/owner/projects/{projectId}/complete', [projectsController::class, 'completeProject']);

// Owner payment upload routes for mobile app - controller handles auth manually
Route::post('/owner/payment/upload', [paymentUploadController::class, 'uploadPayment']);
Route::put('/owner/payment/{paymentId}', [paymentUploadController::class, 'updatePayment']);
Route::delete('/owner/payment/{paymentId}', [paymentUploadController::class, 'deletePayment']);
// Payment routes - controllers handle both session and token auth
Route::get('/projects/{projectId}/payments', [paymentUploadController::class, 'getPaymentsByProject']);
Route::get('/projects/{projectId}/downpayment-receipts', [paymentUploadController::class, 'getDownpaymentReceipts']);
Route::get('/milestone-items/{itemId}/payments', [paymentUploadController::class, 'getPaymentsByItem']);

// Dedicated downpayment routes — separate from milestone payment flow
Route::post('/downpayment/upload', [downpaymentController::class, 'upload']);
Route::get('/projects/{projectId}/downpayment-payments', [downpaymentController::class, 'list']);
Route::post('/downpayment/{id}/approve', [downpaymentController::class, 'approve']);
Route::post('/downpayment/{id}/reject', [downpaymentController::class, 'reject']);

// Progress files retrieval for mobile app (owners and contractors)
// These routes use optional Sanctum auth - controller handles both session and token auth
Route::get('/both/progress/files/{itemId}', [progressUploadController::class, 'getProgressFilesForBoth']);
Route::get('/contractor/progress/files/{itemId}', [progressUploadController::class, 'getProgressFiles']);
Route::post('/contractor/progress/upload', [progressUploadController::class, 'uploadProgress'])->middleware('auth:sanctum');

// Contractor endpoints - for contractor feed
Route::get('/contractor/projects', [\App\Http\Controllers\both\homepageController::class, 'apiGetApprovedProjects']);

// Contractor bidding endpoints
Route::get('/contractor/bid-eligibility', [\App\Http\Controllers\contractor\biddingController::class, 'apiBidEligibility']);
Route::post('/contractor/projects/{projectId}/bid', [\App\Http\Controllers\contractor\biddingController::class, 'apiSubmitBid']);
Route::get('/contractor/projects/{projectId}/my-bid', [\App\Http\Controllers\contractor\biddingController::class, 'apiGetMyBid']);
Route::get('/contractor/my-bids', [\App\Http\Controllers\contractor\biddingController::class, 'apiGetMyBids']);
Route::put('/contractor/bids/{id}', [\App\Http\Controllers\contractor\biddingController::class, 'update']);
Route::post('/contractor/bids/{id}', [\App\Http\Controllers\contractor\biddingController::class, 'update']); // POST with _method=PUT for FormData
Route::post('/contractor/bids/{id}/cancel', [\App\Http\Controllers\contractor\biddingController::class, 'apiCancelBid']);

// Contractor milestone setup endpoints
Route::get('/contractor/my-projects', [\App\Http\Controllers\contractor\cprocessController::class, 'apiGetContractorProjects']);
Route::get('/contractor/projects/{projectId}/milestone-form', [\App\Http\Controllers\contractor\cprocessController::class, 'apiGetMilestoneFormData']);
Route::post('/contractor/projects/{projectId}/milestones', [milestoneController::class, 'apiSubmitMilestones']);
Route::put('/contractor/projects/{projectId}/milestones/{milestoneId}', [milestoneController::class, 'apiUpdateMilestone']);

// Contractor — settlement due date management
Route::post('/contractor/milestone-items/{itemId}/settlement-due-date', [milestoneController::class, 'setSettlementDueDate']);

// Contractor AI Analytics endpoints (mobile) — controller handles auth via X-User-Id / Bearer token
Route::get('/contractor/ai-analytics', [\App\Http\Controllers\contractor\aiController::class, 'apiGetAnalytics']);
Route::post('/contractor/ai-analytics/analyze/{id}', [\App\Http\Controllers\contractor\aiController::class, 'apiAnalyzeProject']);
Route::get('/contractor/ai-analytics/stats', [\App\Http\Controllers\contractor\aiController::class, 'apiGetStats']);

// Notification endpoints - controller handles both session and token auth
Route::get('/notifications', [notificationController::class , 'index']);
Route::get('/notifications/unread-count', [notificationController::class , 'unreadCount']);
Route::post('/notifications/{id}/read', [notificationController::class , 'markAsRead']);
Route::post('/notifications/read-all', [notificationController::class , 'markAllAsRead']);
Route::get('/notifications/{id}/redirect', [notificationController::class , 'apiResolveRedirect']);

// Note: profile update registered below inside sanctum-protected group

// DEBUG TEST ENDPOINT - Remove after testing
Route::get('/test-auth', function (Request $request) {
    return response()->json([
        'request_user' => $request->user() ? 'EXISTS (ID: ' . $request->user()->user_id . ')' : 'NULL',
        'auth_check' => auth('sanctum')->check() ? 'TRUE' : 'FALSE',
        'auth_user' => auth('sanctum')->user() ? 'EXISTS (ID: ' . auth('sanctum')->user()->user_id . ')' : 'NULL',
        'has_bearer' => $request->bearerToken() ? 'YES (' . substr($request->bearerToken(), 0, 10) . '...)' : 'NO',
    ]);
})->middleware('auth:sanctum');

// Lightweight debug ping (no auth) to verify server reachability from mobile
Route::get('/debug/ping', function (Request $request) {
    Log::debug('debug/ping called', ['ip' => $request->ip(), 'headers' => $request->headers->all()]);
    return response()->json(['success' => true, 'message' => 'pong', 'time' => now()]);
});

// PayMongo webhook endpoint (API routes bypass CSRF)
Route::post('/paymongo/webhook', [payMongoController::class, 'handleWebhook']);

// Subscription checkout for mobile clients (supports X-User-Id and return_url)
Route::post('/subscribe/checkout', [payMongoController::class, 'createSubscriptionCheckout']);

// Cancel subscription (mobile API)
Route::post('/subscribe/cancel', [payMongoController::class, 'cancelSubscription']);

// Verify a boost payment by checking PayMongo directly (used by mobile deep-link)
Route::post('/boost/verify', [payMongoController::class, 'verifyBoostPayment']);

// Debug: Check token validity (no auth required)
Route::get('/debug/token-check', function (Request $request) {
    $token = $request->bearerToken();
    if (!$token) {
        return response()->json(['error' => 'No bearer token provided']);
    }

    // Check if token exists in database
    $tokenRecord = DB::table('personal_access_tokens')
        ->where('token', hash('sha256', $token))
        ->first();

    if (!$tokenRecord) {
        return response()->json(['error' => 'Token not found in database']);
    }

    // Try to get the user
    $user = DB::table('users')->where('user_id', $tokenRecord->tokenable_id)->first();

    return response()->json([
        'token_found' => true,
        'tokenable_type' => $tokenRecord->tokenable_type,
        'tokenable_id' => $tokenRecord->tokenable_id,
        'user_found' => $user ? true : false,
        'user_id' => $user ? $user->user_id : null,
        'username' => $user ? $user->username : null,
        'user_type' => $user ? $user->user_type : null,
        'token_name' => $tokenRecord->name,
        'token_last_used' => $tokenRecord->last_used_at,
    ]);
});

// Debug: Check contractor status
Route::get('/debug/contractor-status', function (Request $request) {
    $userId = $request->query('user_id');
    if (!$userId) {
        return response()->json(['error' => 'user_id required']);
    }

    $ownerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
    $contractor = $ownerId ? DB::table('contractors')->where('owner_id', $ownerId)->first() : null;
    $staffRecord = $ownerId ? DB::table('contractor_staff')->where('owner_id', $ownerId)->whereNull('deletion_reason')->first() : null;
    $allMembers = $contractor ? DB::table('contractor_staff')
        ->where('contractor_id', $contractor->contractor_id)
        ->whereNull('deletion_reason')
        ->get() : [];

    return response()->json([
        'contractor' => $contractor,
        'staff_record_for_user' => $staffRecord,
        'all_members' => $allMembers
    ]);
});

// Contractor members (staff) management — uses user_id from query param or X-User-Id header
Route::get('/contractor/members/search-owners', [\App\Http\Controllers\contractor\membersController::class, 'searchVerifiedOwners']);
Route::get('/contractor/members', [\App\Http\Controllers\contractor\membersController::class, 'index']);
Route::post('/contractor/members', [\App\Http\Controllers\contractor\membersController::class, 'store']);
Route::put('/contractor/members/{id}', [\App\Http\Controllers\contractor\membersController::class, 'update']);
Route::delete('/contractor/members/{id}', [\App\Http\Controllers\contractor\membersController::class, 'delete']);
Route::patch('/contractor/members/{id}/suspend', [\App\Http\Controllers\contractor\membersController::class, 'suspend']);
Route::patch('/contractor/members/{id}/unsuspend', [\App\Http\Controllers\contractor\membersController::class, 'unsuspend']);
Route::patch('/contractor/members/{id}/accept', [\App\Http\Controllers\contractor\membersController::class, 'acceptInvitation']);
Route::patch('/contractor/members/{id}/decline', [\App\Http\Controllers\contractor\membersController::class, 'declineInvitation']);
Route::patch('/contractor/members/{id}/cancel-invitation', [\App\Http\Controllers\contractor\membersController::class, 'cancelInvitation']);
Route::post('/contractor/members/change-representative', [\App\Http\Controllers\contractor\membersController::class, 'changeRepresentative']);
// Backward-compat alias for old toggle-active route
Route::patch('/contractor/members/{id}/toggle-active', [\App\Http\Controllers\contractor\membersController::class, 'toggleActive']);

// Debug: Check all member statuses for a contractor
Route::get('/debug/member-statuses', function (\Illuminate\Http\Request $request) {
    $userId = $request->query('user_id');
    if (!$userId) {
        return response()->json(['error' => 'user_id required']);
    }

    $ownerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
    $contractor = $ownerId ? DB::table('contractors')->where('owner_id', $ownerId)->first() : null;
    if (!$contractor) {
        return response()->json(['error' => 'Contractor not found for user_id: ' . $userId]);
    }

    $members = DB::table('contractor_staff')
        ->join('property_owners', 'contractor_staff.owner_id', '=', 'property_owners.owner_id')
        ->join('users', 'property_owners.user_id', '=', 'users.user_id')
        ->where('contractor_staff.contractor_id', $contractor->contractor_id)
        ->select(
            'contractor_staff.staff_id',
            'users.user_id',
            'users.first_name',
            'users.last_name',
            'contractor_staff.company_role as role',
            'contractor_staff.is_active',
            'contractor_staff.is_suspended',
            'contractor_staff.deletion_reason',
            'users.email',
            'users.username'
        )
        ->get();

    return response()->json([
        'contractor_id'   => $contractor->contractor_id,
        'contractor_name' => $contractor->company_name ?? 'N/A',
        'total_members'   => count($members),
        'active_count'    => $members->where('is_active', 1)->whereNull('deletion_reason')->count(),
        'inactive_count'  => $members->where('is_active', 0)->whereNull('deletion_reason')->count(),
        'deleted_count'   => $members->whereNotNull('deletion_reason')->count(),
        'members'         => $members
    ]);
});

// Debug: Check a user's login data by username
Route::get('/debug/check-user-login', function (\Illuminate\Http\Request $request) {
    $username = $request->query('username');
    if (!$username) {
        return response()->json(['error' => 'username required']);
    }

    $user = DB::table('users')
        ->where('username', $username)
        ->orWhere('email', $username)
        ->first();

    if (!$user) {
        return response()->json(['error' => 'User not found: ' . $username]);
    }

    $propertyOwner = DB::table('property_owners')->where('user_id', $user->user_id)->first();
    $ownerId       = $propertyOwner ? $propertyOwner->owner_id : null;

    $ownedContractor = $ownerId ? DB::table('contractors')->where('owner_id', $ownerId)->first() : null;

    // Check if this owner is staff in a contractor company
    $staffRecord = $ownerId ? DB::table('contractor_staff')
        ->where('owner_id', $ownerId)
        ->whereNull('deletion_reason')
        ->first() : null;
    $staffContractor = $staffRecord ? DB::table('contractors')->where('contractor_id', $staffRecord->contractor_id)->first() : null;

    return response()->json([
        'user' => [
            'user_id'   => $user->user_id,
            'username'  => $user->username,
            'email'     => $user->email,
            'user_type' => $user->user_type,
        ],
        'property_owner' => $propertyOwner ? [
            'owner_id'            => $propertyOwner->owner_id,
            'verification_status' => $propertyOwner->verification_status,
            'is_active'           => $propertyOwner->is_active,
        ] : null,
        'owned_contractor' => $ownedContractor ? [
            'contractor_id'       => $ownedContractor->contractor_id,
            'company_name'        => $ownedContractor->company_name ?? null,
            'verification_status' => $ownedContractor->verification_status,
            'role'                => 'owner',
        ] : null,
        'staff_record' => $staffRecord ? [
            'staff_id'          => $staffRecord->staff_id,
            'contractor_id'     => $staffRecord->contractor_id,
            'company_name'      => $staffContractor->company_name ?? null,
            'role'              => $staffRecord->company_role,
            'is_active'         => $staffRecord->is_active,
            'is_suspended'      => $staffRecord->is_suspended,
        ] : null,
    ]);
});

// Messages & Chat - controller handles auth manually via getAuthUserId()
// Placed outside auth:sanctum to avoid PHP dev server crash on Windows
Route::prefix('messages')->group(function () {
    Route::get('/', [\App\Http\Controllers\message\messageController::class, 'index']); // Get inbox
    Route::get('/stats', [\App\Http\Controllers\message\messageController::class, 'getStats']); // Dashboard stats
    Route::get('/users', [\App\Http\Controllers\message\messageController::class, 'getAvailableUsers']); // Users list
    Route::get('/search', [\App\Http\Controllers\message\messageController::class, 'search']); // Search messages
    Route::get('/{conversationId}', [\App\Http\Controllers\message\messageController::class, 'show']); // Conversation history
    Route::post('/', [\App\Http\Controllers\message\messageController::class, 'store']); // Send message
    Route::post('/{conversationId}/read', [\App\Http\Controllers\message\messageController::class, 'markRead']); // Mark as read
    Route::post('/report', [\App\Http\Controllers\message\messageController::class, 'report']); // Report message
    Route::post('/{messageId}/flag', [\App\Http\Controllers\message\messageController::class, 'flag']); // Flag message
    Route::post('/{messageId}/unflag', [\App\Http\Controllers\message\messageController::class, 'unflag']); // Unflag message
    Route::post('/conversation/{conversationId}/suspend', [\App\Http\Controllers\message\messageController::class, 'suspend']); // Suspend
    Route::post('/conversation/{conversationId}/restore', [\App\Http\Controllers\message\messageController::class, 'restore']); // Restore
    Route::post('/typing', [\App\Http\Controllers\message\messageController::class, 'typing']); // Typing indicator
});

// Pusher Broadcasting Auth - controller handles auth manually (Sanctum + session fallback)
Route::post('/broadcasting/auth', [\App\Http\Controllers\message\broadcastAuthController::class, 'authorize']);

// Protected routes (require authentication via Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // User information
    Route::get(
        '/user',
        function (Request $request) {
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $request->user(),
                    'userType' => $request->user()->user_type ?? null
                ]
            ]);
        }
    );

    Route::post(
        '/logout',
        function (Request $request) {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        }
    );

    // Verification resubmission (rejected users re-uploading documents)
    Route::post('/verification/resubmit', [verificationResubmitController::class, 'resubmit']);

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\both\dashboardController::class, 'apiDashboard']);
    Route::get('/dashboard/owner-stats', [\App\Http\Controllers\both\dashboardController::class, 'apiOwnerStats']);
    Route::get('/dashboard/contractor-stats', [\App\Http\Controllers\both\dashboardController::class, 'apiContractorStats']);

    // Projects (Owner)
    Route::prefix('projects')->group(
        function () {
            Route::get('/', [projectsController::class, 'index']);
            Route::get('/{id}', [disputeController::class, 'showProjectDetails']);
            Route::post('/', [projectsController::class, 'store']);
            Route::put('/{id}', [projectsController::class, 'update']);
            Route::delete('/{id}', [projectsController::class, 'destroy']);

            // Project bids
            Route::get('/{id}/bids', [biddingController::class, 'getProjectBids']);
            Route::post('/{id}/bids', [biddingController::class, 'store']);
            Route::post('/{id}/bids/{bidId}/accept', [biddingController::class, 'acceptBid']);
        }
    );

    // NOTE: pinned project endpoints removed

    // Milestones (Contractor)
    Route::prefix('milestones')->group(
        function () {
            Route::get('/', [cprocessController::class, 'getMilestones']);
            Route::get('/{id}', [cprocessController::class, 'getMilestoneDetails']);
            Route::post('/', [cprocessController::class, 'submitMilestone']);
            Route::put('/{id}', [cprocessController::class, 'updateMilestone']);
            Route::delete('/{id}', [milestoneController::class, 'deleteMilestone']);

            // Milestone approval (Owner) — now handled by milestoneController
            Route::post('/{id}/approve', [milestoneController::class, 'apiApproveMilestone']);
            Route::post('/{id}/reject', [milestoneController::class, 'apiRejectMilestone']);
        }
    );

    // Bids (Contractor)
    Route::prefix('bids')->group(
        function () {
            Route::get('/', [biddingController::class, 'index']);
            Route::get('/{id}', [biddingController::class, 'show']);
            Route::post('/', [biddingController::class, 'store']);
            Route::put('/{id}', [biddingController::class, 'update']);
            Route::post('/{id}/cancel', [biddingController::class, 'cancelBid']);
        }
    );

    // Progress Reports (Contractor)
    Route::prefix('progress')->group(
        function () {
            Route::get('/', [progressUploadController::class, 'index']);
            Route::get('/{id}', [progressUploadController::class, 'show']);
            Route::post('/', [progressUploadController::class, 'store']);
            Route::put('/{id}', [progressUploadController::class, 'update']);
            Route::delete('/{id}', [progressUploadController::class, 'destroy']);
            // Progress files
            Route::post('/files', [progressUploadController::class, 'uploadFiles']);
            Route::delete('/files/{id}', [progressUploadController::class, 'deleteFile']);
        }
    );

    // Payment Validations (Owner)
    Route::prefix('payments')->group(
        function () {
            Route::get('/', [paymentUploadController::class, 'index']);
            Route::get('/{id}', [paymentUploadController::class, 'show']);
            Route::post('/', [paymentUploadController::class, 'store']);
            Route::put('/{id}', [paymentUploadController::class, 'update']);
            Route::delete('/{id}', [paymentUploadController::class, 'destroy']);
        }
    );

    // Disputes (Both)
    // Projects list (Both)
    Route::get('/projects', [disputeController::class, 'showProjectsPage']);
});

// Payment approve/reject routes - controller handles auth manually
Route::prefix('payments')->group(function () {
    Route::post('/{id}/approve', [milestoneController::class, 'apiApprovePayment']);
    Route::post('/{id}/reject', [milestoneController::class, 'apiRejectPayment']);
});

// Payment summary per milestone item - controller handles auth manually
Route::get('/milestone-items/{itemId}/payment-summary', [milestoneController::class, 'apiGetItemPaymentSummary']);

// Milestone item date extension history
Route::get('/milestone-items/{itemId}/date-history', [milestoneController::class, 'getDateHistory']);

// Summary reports - controller handles auth manually
Route::get('/projects/{projectId}/summary', [\App\Http\Controllers\both\summaryController::class, 'projectSummary']);
Route::get('/projects/{projectId}/milestones/{itemId}/summary', [\App\Http\Controllers\both\summaryController::class, 'milestoneSummary']);

// Disputes routes - controller handles auth manually
Route::prefix('disputes')->group(function () {
    Route::get('/', [disputeController::class, 'getDisputes']);
    Route::get('/{id}', [disputeController::class, 'getDisputeDetails']);
    Route::post('/', [disputeController::class, 'fileDispute']);
    Route::put('/{id}', [disputeController::class, 'updateDispute']);
    Route::delete('/{id}', [disputeController::class, 'cancelDispute']);
});

// Progress approve/reject routes — auth:sanctum enforced; role read from X-Current-Role header
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/progress/{id}/approve', [progressUploadController::class, 'approveProgress']);
    Route::post('/progress/{id}/reject', [progressUploadController::class, 'rejectProgress']);
});

// Accessible at http://192.168.100.27:8000/api/boost/checkout
// No CSRF required (because it's in api.php)
// No Authentication required (middleware removed)
// routes/api.php
// In routes/api.php
// routes/api.php

// This version is clean, single-prefixed, and authenticated
Route::post('/boost/checkout', [\App\Http\Controllers\subs\payMongoController::class, 'createBoostCheckout']);

// ── Project Update ──────────────────────────────────────────────────────
// Shared (both roles)
Route::get('/projects/{projectId}/update/context', [projectUpdateController::class, 'context']);
Route::get('/projects/{projectId}/update/milestone-items', [projectUpdateController::class, 'milestoneItems']);
Route::get('/projects/{projectId}/updates', [projectUpdateController::class, 'index']);
// Contractor
Route::post('/projects/{projectId}/update/preview', [projectUpdateController::class, 'preview']);
Route::post('/projects/{projectId}/update', [projectUpdateController::class, 'store']);
Route::post('/projects/{projectId}/updates/{extensionId}/withdraw', [projectUpdateController::class, 'withdraw']);
// Owner
Route::post('/projects/{projectId}/updates/{extensionId}/approve', [projectUpdateController::class, 'approve']);
Route::post('/projects/{projectId}/updates/{extensionId}/reject', [projectUpdateController::class, 'reject']);
Route::post('/projects/{projectId}/updates/{extensionId}/request-changes', [projectUpdateController::class, 'requestChanges']);

// ═══════════════════════════════════════════════════════════════════════════
// FEATURE ROUTES: Reviews, Highlights, Profile (aggregated), Posts, Feed
// ═══════════════════════════════════════════════════════════════════════════

use App\Http\Controllers\reviewController;
use App\Http\Controllers\both\highlightController;
use App\Http\Controllers\profileApiController;
use App\Http\Controllers\both\postController;
use App\Http\Controllers\both\reportController;

// ── Reviews ─────────────────────────────────────────────────────────────────
Route::post('/reviews', [reviewController::class, 'store']);
Route::get('/reviews/user/{userId}', [reviewController::class, 'forUser']);
Route::get('/reviews/project/{projectId}', [reviewController::class, 'forProject']);
Route::get('/reviews/can-review', [reviewController::class, 'canReview']);
Route::get('/reviews/stats/{userId}', [reviewController::class, 'stats']);

// ── Highlights (Pinned Posts) ───────────────────────────────────────────
Route::post('/posts/{postId}/highlight', [highlightController::class, 'highlightPost']);
Route::delete('/posts/{postId}/highlight', [highlightController::class, 'unhighlightPost']);
Route::get('/posts/highlights', [highlightController::class, 'getHighlights']);
Route::post('/projects/{projectId}/highlight', [highlightController::class, 'highlightProject']);
Route::delete('/projects/{projectId}/highlight', [highlightController::class, 'unhighlightProject']);

// ── Profile (aggregated: header, posts, highlights, reviews, about) ─────
Route::get('/profile/view/{userId}', [profileApiController::class, 'show']);

// ── Project Posts (Facebook-style CRUD) ─────────────────────────────────
Route::post('/posts', [postController::class, 'store']);
Route::put('/posts/{id}', [postController::class, 'update']);
Route::delete('/posts/{id}', [postController::class, 'destroy']);
Route::get('/posts/{id}', [postController::class, 'show'])->where('id', '[0-9]+');
Route::get('/posts/user/{userId}', [postController::class, 'forUser']);
Route::get('/posts/completed-projects', [postController::class, 'completedProjects']);

// ── Social Feed (scored ordering) ───────────────────────────────────────
Route::get('/feed', [postController::class, 'feed']);

// ── Unified Feed (bidding projects + showcase posts merged) ─────────────
Route::get('/unified-feed', [postController::class, 'unifiedFeed']);
Route::get('/unified-feed/search', [postController::class, 'searchUnifiedFeed']);

// ── Showcase Moderation (Admin) ───────────────────────────────────────
Route::get('/admin/showcases', [postController::class, 'adminShowcases']);
Route::post('/admin/showcases/{id}/approve', [postController::class, 'adminApproveShowcase']);
Route::post('/admin/showcases/{id}/reject', [postController::class, 'adminRejectShowcase']);

// ── Content Reports (Projects + Showcases) ────────────────────────────
Route::post('/reports', [reportController::class, 'store']);
Route::get('/reports/mine', [reportController::class, 'mine']);
Route::get('/admin/reports', [reportController::class, 'adminIndex']);
Route::get('/admin/reports/{reportId}', [reportController::class, 'adminShow']);
Route::post('/admin/reports/{reportId}/review', [reportController::class, 'adminReview']);

// ── Review Reports (separate table) ──────────────────────────────────
Route::post('/review-reports', [reportController::class, 'storeReviewReport']);
Route::get('/review-reports/mine', [reportController::class, 'myReviewReports']);
Route::get('/admin/review-reports', [reportController::class, 'adminReviewReportsIndex']);
Route::post('/admin/review-reports/{reportId}/review', [reportController::class, 'adminReviewReportAction']);

// ── User Reports (users reporting other users)
Route::post('/user-reports', [\App\Http\Controllers\both\userReportController::class, 'store']);
Route::get('/user-reports/mine', [\App\Http\Controllers\both\userReportController::class, 'mine']);

// NOTE: change-otp endpoints are registered publicly (do not rely on Sanctum middleware)
