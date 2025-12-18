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
// Test endpoint for mobile app
Route::get('/test', [authController::class, 'apiTest']);

// File serving endpoint for mobile app (bypasses Apache symlink issues)
Route::get('/files/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    
    if (!file_exists($fullPath)) {
        return response()->json(['error' => 'File not found'], 404);
    }
    
    $mimeType = mime_content_type($fullPath);
    
    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
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
Route::post('/owner/projects/{projectId}/complete', [projectsController::class, 'completeProject'])->middleware('auth:sanctum');

// Owner payment upload routes for mobile app
Route::post('/owner/payment/upload', [paymentUploadController::class, 'uploadPayment'])->middleware('auth:sanctum');
Route::put('/owner/payment/{paymentId}', [paymentUploadController::class, 'updatePayment'])->middleware('auth:sanctum');
Route::delete('/owner/payment/{paymentId}', [paymentUploadController::class, 'deletePayment'])->middleware('auth:sanctum');
Route::get('/projects/{projectId}/payments', [paymentUploadController::class, 'getPaymentsByProject'])->middleware('auth:sanctum');
Route::get('/milestone-items/{itemId}/payments', [paymentUploadController::class, 'getPaymentsByItem'])->middleware('auth:sanctum');

// Progress files retrieval for mobile app (owners and contractors)
// These routes use optional Sanctum auth - controller handles both session and token auth
Route::get('/both/progress/files/{itemId}', [progressUploadController::class, 'getProgressFilesForBoth'])->middleware('auth:sanctum');
Route::get('/contractor/progress/files/{itemId}', [progressUploadController::class, 'getProgressFiles'])->middleware('auth:sanctum');
Route::post('/contractor/progress/upload', [progressUploadController::class, 'uploadProgress'])->middleware('auth:sanctum');

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

// Note: profile update registered below inside sanctum-protected group

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

    // Role management
    Route::post('/role/switch', [cprocessController::class, 'switchRole']);
    Route::get('/role/current', [cprocessController::class, 'getCurrentRole']);

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
        Route::post('/{id}/approve', [progressUploadController::class, 'approveProgress']);
        Route::post('/{id}/reject', [progressUploadController::class, 'rejectProgress']);

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
        Route::post('/{id}/approve', [disputeController::class, 'approvePayment'])->middleware('auth:sanctum');
        Route::post('/{id}/reject', [disputeController::class, 'rejectPayment'])->middleware('auth:sanctum');
    });

    // Disputes (Both)
    Route::prefix('disputes')->group(function () {
        Route::get('/', [disputeController::class, 'getDisputes'])->middleware('auth:sanctum');
        Route::get('/{id}', [disputeController::class, 'getDisputeDetails'])->middleware('auth:sanctum');
        Route::post('/', [disputeController::class, 'fileDispute'])->middleware('auth:sanctum');
        Route::put('/{id}', [disputeController::class, 'updateDispute'])->middleware('auth:sanctum');
        Route::delete('/{id}', [disputeController::class, 'cancelDispute'])->middleware('auth:sanctum');
    });

    // Projects list (Both)
    Route::get('/projects', [disputeController::class, 'showProjectsPage']);
});
