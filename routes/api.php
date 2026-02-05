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

// At the very top of api.php
Log::info('=== INCOMING API REQUEST ===', [
    'url' => request()->fullUrl(),
    'method' => request()->method(),
    'has_auth_header' => request()->hasHeader('Authorization'),
    'auth_header' => request()->header('Authorization') ? 'Bearer ***' : 'missing'
]);

//role switch test endpoint moved outside middleware group
// Test endpoint first

Route::get('/role/switch-form', function() {
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
Route::get('/psgc/cities/{cityCode}/barangays', [authController::class, 'getBarangaysByCity']);

// Contractors endpoint for property owner feed
Route::get('/contractors', [projectsController::class, 'apiGetContractors']);

// Contractor types endpoint for project creation form
Route::get('/contractor-types', [projectsController::class, 'apiGetContractorTypes']);

// Owner endpoints - for owner dashboard/project management
Route::get('/owner/projects', [projectsController::class, 'apiGetOwnerProjects']);
Route::post('/owner/projects', [projectsController::class, 'apiCreateProject']);
Route::get('/owner/projects/{projectId}', [projectsController::class, 'apiGetProjectDetails']);
Route::get('/owner/projects/{projectId}/bids', [biddingController::class, 'getProjectBids']);
Route::post('/owner/projects/{projectId}/bids/{bidId}/accept', [projectsController::class, 'apiAcceptBid']);
Route::post('/owner/projects/{projectId}/bids/{bidId}/reject', [projectsController::class, 'apiRejectBid']);
Route::post('/owner/milestones/{milestoneId}/approve', [projectsController::class, 'apiApproveMilestone']);
Route::post('/owner/milestones/{milestoneId}/reject', [projectsController::class, 'apiRejectMilestone']);
Route::post('/owner/milestones/{milestoneId}/complete', [projectsController::class, 'apiSetMilestoneComplete']);
Route::post('/owner/milestone-items/{itemId}/complete', [projectsController::class, 'apiSetMilestoneItemComplete']);
Route::post('/owner/projects/{projectId}/complete', [projectsController::class, 'completeProject']);

// Owner payment upload routes for mobile app - controller handles auth manually
Route::post('/owner/payment/upload', [paymentUploadController::class, 'uploadPayment']);
Route::put('/owner/payment/{paymentId}', [paymentUploadController::class, 'updatePayment']);
Route::delete('/owner/payment/{paymentId}', [paymentUploadController::class, 'deletePayment']);
// Payment routes - controllers handle both session and token auth
Route::get('/projects/{projectId}/payments', [paymentUploadController::class, 'getPaymentsByProject']);
Route::get('/projects/{projectId}/downpayment-receipts', [paymentUploadController::class, 'getDownpaymentReceipts']);
Route::get('/milestone-items/{itemId}/payments', [paymentUploadController::class, 'getPaymentsByItem']);

// Progress files retrieval for mobile app (owners and contractors)
// These routes use optional Sanctum auth - controller handles both session and token auth
Route::get('/both/progress/files/{itemId}', [progressUploadController::class, 'getProgressFilesForBoth']);
Route::get('/contractor/progress/files/{itemId}', [progressUploadController::class, 'getProgressFiles']);
Route::post('/contractor/progress/upload', [progressUploadController::class, 'uploadProgress']);

// Contractor endpoints - for contractor feed
Route::get('/contractor/projects', [projectsController::class, 'apiGetApprovedProjects']);

// Contractor bidding endpoints
Route::post('/contractor/projects/{projectId}/bid', [\App\Http\Controllers\contractor\biddingController::class, 'apiSubmitBid']);
Route::get('/contractor/projects/{projectId}/my-bid', [\App\Http\Controllers\contractor\biddingController::class, 'apiGetMyBid']);
Route::get('/contractor/my-bids', [\App\Http\Controllers\contractor\biddingController::class, 'apiGetMyBids']);

// Contractor milestone setup endpoints
Route::get('/contractor/my-projects', [\App\Http\Controllers\contractor\cprocessController::class, 'apiGetContractorProjects']);
Route::get('/contractor/projects/{projectId}/milestone-form', [\App\Http\Controllers\contractor\cprocessController::class, 'apiGetMilestoneFormData']);
Route::post('/contractor/projects/{projectId}/milestones', [\App\Http\Controllers\contractor\cprocessController::class, 'apiSubmitMilestones']);
Route::put('/contractor/projects/{projectId}/milestones/{milestoneId}', [\App\Http\Controllers\contractor\cprocessController::class, 'apiUpdateMilestone']);

// Note: profile update registered below inside sanctum-protected group

// DEBUG TEST ENDPOINT - Remove after testing
Route::get('/test-auth', function (Request $request) {
    return response()->json([
        'request_user' => $request->user() ? 'EXISTS (ID: '.$request->user()->user_id.')' : 'NULL',
        'auth_check' => auth('sanctum')->check() ? 'TRUE' : 'FALSE',
        'auth_user' => auth('sanctum')->user() ? 'EXISTS (ID: '.auth('sanctum')->user()->user_id.')' : 'NULL',
        'has_bearer' => $request->bearerToken() ? 'YES ('.substr($request->bearerToken(), 0, 10).'...)' : 'NO',
    ]);
})->middleware('auth:sanctum');

// Lightweight debug ping (no auth) to verify server reachability from mobile
Route::get('/debug/ping', function (Request $request) {
    Log::debug('debug/ping called', ['ip' => $request->ip(), 'headers' => $request->headers->all()]);
    return response()->json(['success' => true, 'message' => 'pong', 'time' => now()]);
});

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

    $contractor = DB::table('contractors')->where('user_id', $userId)->first();
    $contractorUsers = DB::table('contractor_users')->where('user_id', $userId)->get();
    $allMembers = $contractor ? DB::table('contractor_users')
        ->where('contractor_id', $contractor->contractor_id)
        ->where('is_deleted', 0)
        ->get() : [];

    return response()->json([
        'contractor' => $contractor,
        'contractor_users_for_this_user' => $contractorUsers,
        'all_members' => $allMembers
    ]);
});

// Contractor members (staff) management - uses user_id from query/header
Route::get('/contractor/members', [\App\Http\Controllers\contractor\membersController::class, 'index']);
Route::post('/contractor/members', [\App\Http\Controllers\contractor\membersController::class, 'store']);
Route::put('/contractor/members/{id}', [\App\Http\Controllers\contractor\membersController::class, 'update']);
Route::delete('/contractor/members/{id}', [\App\Http\Controllers\contractor\membersController::class, 'delete']);
Route::patch('/contractor/members/{id}/toggle-active', [\App\Http\Controllers\contractor\membersController::class, 'toggleActive']);

// Debug: Check all member statuses for a contractor
Route::get('/debug/member-statuses', function (\Illuminate\Http\Request $request) {
    $userId = $request->query('user_id');
    if (!$userId) {
        return response()->json(['error' => 'user_id required']);
    }

    $contractor = DB::table('contractors')->where('user_id', $userId)->first();
    if (!$contractor) {
        return response()->json(['error' => 'Contractor not found for user_id: ' . $userId]);
    }

    $members = DB::table('contractor_users')
        ->join('users', 'contractor_users.user_id', '=', 'users.user_id')
        ->where('contractor_users.contractor_id', $contractor->contractor_id)
        ->select(
            'contractor_users.contractor_user_id',
            'contractor_users.user_id',
            'contractor_users.authorized_rep_fname as first_name',
            'contractor_users.authorized_rep_lname as last_name',
            'contractor_users.role',
            'contractor_users.is_active',
            'contractor_users.is_deleted',
            'contractor_users.deletion_reason',
            'users.email',
            'users.username'
        )
        ->get();

    return response()->json([
        'contractor_id' => $contractor->contractor_id,
        'contractor_name' => $contractor->company_name ?? 'N/A',
        'total_members' => count($members),
        'active_count' => $members->where('is_active', 1)->where('is_deleted', 0)->count(),
        'inactive_count' => $members->where('is_active', 0)->where('is_deleted', 0)->count(),
        'deleted_count' => $members->where('is_deleted', 1)->count(),
        'members' => $members
    ]);
});

// Protected routes (require authentication via Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // User information
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user(),
                'userType' => $request->user()->user_type ?? null
            ]
        ]);
    });

    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    });

    // Update profile (profile picture / cover photo)
    Route::post('/user/update-profile', [authController::class, 'updateProfile']);

    // Dashboard
    Route::get('/dashboard', [projectsController::class, 'showDashboard']);

    // Projects (Owner)
    Route::prefix('projects')->group(function () {
        Route::get('/', [projectsController::class, 'index']);
        Route::get('/{id}', [disputeController::class, 'showProjectDetails']);
        Route::post('/', [projectsController::class, 'store']);
        Route::put('/{id}', [projectsController::class, 'update']);
        Route::delete('/{id}', [projectsController::class, 'destroy']);

        // Project bids
        Route::get('/{id}/bids', [biddingController::class, 'getProjectBids']);
        Route::post('/{id}/bids', [biddingController::class, 'store']);
        Route::post('/{id}/bids/{bidId}/accept', [biddingController::class, 'acceptBid']);
    });

    // NOTE: pinned project endpoints removed

    // Milestones (Contractor)
    Route::prefix('milestones')->group(function () {
        Route::get('/', [cprocessController::class, 'getMilestones']);
        Route::get('/{id}', [cprocessController::class, 'getMilestoneDetails']);
        Route::post('/', [cprocessController::class, 'submitMilestone']);
        Route::put('/{id}', [cprocessController::class, 'updateMilestone']);
        Route::delete('/{id}', [cprocessController::class, 'deleteMilestone']);

        // Milestone approval (Owner)
        Route::post('/{id}/approve', [disputeController::class, 'approveMilestone']);
        Route::post('/{id}/reject', [disputeController::class, 'rejectMilestone']);
    });

    // Milestone setup (Contractor)
    Route::prefix('milestone')->group(function () {
        Route::get('/setup', [cprocessController::class, 'showMilestoneSetupForm']);
        Route::post('/setup/step1', [cprocessController::class, 'milestoneStepOne']);
        Route::post('/setup/step2', [cprocessController::class, 'milestoneStepTwo']);
        Route::post('/setup/submit', [cprocessController::class, 'submitMilestone']);
    });

    // Bids (Contractor)
    Route::prefix('bids')->group(function () {
        Route::get('/', [biddingController::class, 'index']);
        Route::get('/{id}', [biddingController::class, 'show']);
        Route::post('/', [biddingController::class, 'store']);
        Route::put('/{id}', [biddingController::class, 'update']);
        Route::post('/{id}/cancel', [biddingController::class, 'cancelBid']);
    });

    // Progress Reports (Contractor)
    Route::prefix('progress')->group(function () {
        Route::get('/', [progressUploadController::class, 'index']);
        Route::get('/{id}', [progressUploadController::class, 'show']);
        Route::post('/', [progressUploadController::class, 'store']);
        Route::put('/{id}', [progressUploadController::class, 'update']);
        Route::delete('/{id}', [progressUploadController::class, 'destroy']);
        // Progress files
        Route::post('/files', [progressUploadController::class, 'uploadFiles']);
        Route::delete('/files/{id}', [progressUploadController::class, 'deleteFile']);
    });

    // Payment Validations (Owner)
    Route::prefix('payments')->group(function () {
        Route::get('/', [paymentUploadController::class, 'index']);
        Route::get('/{id}', [paymentUploadController::class, 'show']);
        Route::post('/', [paymentUploadController::class, 'store']);
        Route::put('/{id}', [paymentUploadController::class, 'update']);
        Route::delete('/{id}', [paymentUploadController::class, 'destroy']);
    });

    // Disputes (Both)
    // Projects list (Both)
    Route::get('/projects', [disputeController::class, 'showProjectsPage']);
});

// Payment approve/reject routes - controller handles auth manually
Route::prefix('payments')->group(function () {
    Route::post('/{id}/approve', [disputeController::class, 'approvePayment']);
    Route::post('/{id}/reject', [disputeController::class, 'rejectPayment']);
});

// Disputes routes - controller handles auth manually
Route::prefix('disputes')->group(function () {
    Route::get('/', [disputeController::class, 'getDisputes']);
    Route::get('/{id}', [disputeController::class, 'getDisputeDetails']);
    Route::post('/', [disputeController::class, 'fileDispute']);
    Route::put('/{id}', [disputeController::class, 'updateDispute']);
    Route::delete('/{id}', [disputeController::class, 'cancelDispute']);
});

// Progress approve/reject routes - outside middleware group so controller can handle auth manually
Route::post('/progress/{id}/approve', [progressUploadController::class, 'approveProgress']);
Route::post('/progress/{id}/reject', [progressUploadController::class, 'rejectProgress']);
