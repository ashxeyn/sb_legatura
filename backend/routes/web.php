<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authController;
use App\Http\Controllers\contractor\cprocessController;
use App\Http\Controllers\both\disputeController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\admin\dashboardController;
use App\Http\Controllers\admin\analyticsController;
use App\Http\Controllers\admin\userManagementController;
use App\Http\Controllers\admin\globalManagementController;
use App\Http\Controllers\admin\ProjectAdminController;
use App\Http\Controllers\admin\projectManagementController;


Route::get('/', function () {
    return view('startPoint');
});

// Splash / introduction screen for owner signup/login
Route::get('/intro', function () {
    return view('signUp_logIN.introduction');
});

// Owner web login screen
Route::get('/login', function () {
    return view('signUp_logIN.logIn');
});

// Owner account type selection screen
Route::get('/account-type', function () {
    return view('signUp_logIN.accountType');
});
// Owner account setup screen
Route::match(['get', 'post'], '/propertyOwner/account-setup', function () {
    return view('signUp_logIN.propertyOwner_accountSetup');
})->name('owner.account-setup');

// Back-compat: keep old path working
Route::get('/account-setup', function () {
    return redirect('/propertyOwner/account-setup');
});

// Contractor account setup screen
Route::match(['get', 'post'], '/contractor/account-setup', function () {
    return view('signUp_logIN.contractor_accountSetup');
})->name('contractor.account-setup');

// OTP Verification screen
Route::get('/otp-verification', function () {
    return view('signUp_logIN.otp_Verification');
})->name('otp.verification');

// Add profile photo screen
Route::get('/add-profile-photo', function () {
    return view('signUp_logIN.add_Profilepicture');
})->name('profile.photo');

// Property Owner Homepage
Route::get('/owner/homepage', [\App\Http\Controllers\Owner\projectsController::class, 'showHomepage'])->name('owner.homepage');

// Property Owner Dashboard
Route::get('/owner/dashboard', [\App\Http\Controllers\Owner\projectsController::class, 'showOwnerDashboard'])->name('owner.dashboard');

// Property Owner All Projects
Route::get('/owner/projects', [\App\Http\Controllers\Owner\projectsController::class, 'showAllProjects'])->name('owner.projects');

// Property Owner Profile
Route::get('/owner/profile', [\App\Http\Controllers\Owner\projectsController::class, 'showProfile'])->name('owner.profile');

// Property Owner Finished Projects
Route::get('/owner/projects/finished', [\App\Http\Controllers\Owner\projectsController::class, 'showFinishedProjects'])->name('owner.projects.finished');

// Property Owner Milestone Report
Route::get('/owner/projects/milestone-report', [\App\Http\Controllers\Owner\projectsController::class, 'showMilestoneReport'])->name('owner.projects.milestone-report');

// Property Owner Milestone Progress Report
Route::get('/owner/projects/milestone-progress-report', [\App\Http\Controllers\Owner\projectsController::class, 'showMilestoneProgressReport'])->name('owner.projects.milestone-progress-report');

// Property Owner Messages
Route::get('/owner/messages', [\App\Http\Controllers\Owner\projectsController::class, 'showMessages'])->name('owner.messages');

// Contractor Homepage
Route::get('/contractor/homepage', [\App\Http\Controllers\contractor\cprocessController::class, 'showHomepage'])->name('contractor.homepage');

// Contractor Dashboard
Route::get('/contractor/dashboard', [\App\Http\Controllers\contractor\cprocessController::class, 'showDashboard'])->name('contractor.dashboard');

// Contractor My Projects
Route::get('/contractor/projects', [\App\Http\Controllers\contractor\cprocessController::class, 'showMyProjects'])->name('contractor.projects');
Route::get('/contractor/myprojects', [\App\Http\Controllers\contractor\cprocessController::class, 'showMyProjects'])->name('contractor.myprojects');

// Contractor My Bids
Route::get('/contractor/mybids', [\App\Http\Controllers\contractor\cprocessController::class, 'showMyBids'])->name('contractor.mybids');

// Contractor Milestone Report
Route::get('/contractor/projects/milestone-report', [\App\Http\Controllers\contractor\cprocessController::class, 'showMilestoneReport'])->name('contractor.projects.milestone-report');

// Contractor Milestone Progress Report
Route::get('/contractor/projects/milestone-progress-report', [\App\Http\Controllers\contractor\cprocessController::class, 'showMilestoneProgressReport'])->name('contractor.projects.milestone-progress-report');

// Contractor Messages
Route::get('/contractor/messages', [\App\Http\Controllers\contractor\cprocessController::class, 'showMessages'])->name('contractor.messages');

// Contractor Profile
Route::get('/contractor/profile', [\App\Http\Controllers\contractor\cprocessController::class, 'showProfile'])->name('contractor.profile');

// Authentication Routes
Route::get('/accounts/login', [authController::class, 'showLoginForm']);
Route::post('/accounts/login', [authController::class, 'login']);
Route::get('/accounts/signup', [authController::class, 'showSignupForm']);
Route::post('/accounts/logout', [authController::class, 'logout']);
Route::get('/accounts/logout', [authController::class, 'logout']);

// Admin Authentication Routes
Route::get('/admin/login', function() {
    return view('admin.logIn_signUp.logIn');
})->name('admin.login');
Route::post('/admin/login', [authController::class, 'login'])->name('admin.login.post');

Route::get('/admin/signup', function() {
    return view('admin.logIn_signUp.signUp');
})->name('admin.signup');
Route::post('/admin/signup', [authController::class, 'adminSignup'])->name('admin.signup.post');

Route::post('/admin/logout', [authController::class, 'logout'])->name('admin.logout');

// Contractor Signup Routes
Route::post('/accounts/signup/contractor/step1', [authController::class, 'contractorStep1']);
Route::post('/accounts/signup/contractor/step2', [authController::class, 'contractorStep2']);
Route::post('/accounts/signup/contractor/step3/verify-otp', [authController::class, 'contractorVerifyOtp']);
Route::post('/accounts/signup/contractor/step4', [authController::class, 'contractorStep4']);
Route::post('/accounts/signup/contractor/final', [authController::class, 'contractorFinalStep']);

// Property Owner Signup Routes
Route::post('/accounts/signup/owner/step1', [authController::class, 'propertyOwnerStep1']);
Route::post('/accounts/signup/owner/step2', [authController::class, 'propertyOwnerStep2']);
Route::post('/accounts/signup/owner/step3/verify-otp', [authController::class, 'propertyOwnerVerifyOtp']);
Route::post('/accounts/signup/owner/step4', [authController::class, 'propertyOwnerStep4']);
Route::post('/accounts/signup/owner/final', [authController::class, 'propertyOwnerFinalStep']);

// Role Switch Routes
Route::get('/accounts/switch', [authController::class, 'showSwitchForm']);
Route::post('/accounts/switch/contractor/step1', [authController::class, 'switchContractorStep1']);
Route::post('/accounts/switch/contractor/step2', [authController::class, 'switchContractorStep2']);
Route::post('/accounts/switch/contractor/final', [authController::class, 'switchContractorFinal']);
Route::post('/accounts/switch/owner/step1', [authController::class, 'switchOwnerStep1']);
Route::post('/accounts/switch/owner/step2', [authController::class, 'switchOwnerStep2']);
Route::post('/accounts/switch/owner/final', [authController::class, 'switchOwnerFinal']);

// PSGC API Routes
Route::get('/api/psgc/provinces', [authController::class, 'getProvinces']);
Route::get('/api/psgc/provinces/{provinceCode}/cities', [authController::class, 'getCitiesByProvince']);
Route::get('/api/psgc/cities/{cityCode}/barangays', [authController::class, 'getBarangaysByCity']);

// Dashboard Routes
Route::get('/admin/dashboard', function() {
    return view('admin.dashboard');
});

Route::get('/dashboard', [\App\Http\Controllers\Owner\projectsController::class, 'showDashboard']);

// Contractor Milestone Setup Routes
Route::get('/contractor/milestone/setup', [cprocessController::class, 'showMilestoneSetupForm']);
Route::post('/contractor/milestone/setup/step1', [cprocessController::class, 'milestoneStepOne']);
Route::post('/contractor/milestone/setup/step2', [cprocessController::class, 'milestoneStepTwo']);
Route::post('/contractor/milestone/setup/submit', [cprocessController::class, 'submitMilestone']);
Route::post('/contractor/milestone/{milestoneId}/delete', [cprocessController::class, 'deleteMilestone']);

// Role Management Routes for 'both' users
Route::post('/api/role/switch', [cprocessController::class, 'switchRole']);
Route::get('/api/role/current', [cprocessController::class, 'getCurrentRole']);

// Dispute Routes
Route::get('/both/disputes', [disputeController::class, 'showDisputePage']);
Route::post('/both/disputes/file', [disputeController::class, 'fileDispute']);
Route::get('/both/disputes/list', [disputeController::class, 'getDisputes']);
Route::get('/both/disputes/{disputeId}', [disputeController::class, 'getDisputeDetails']);
Route::put('/both/disputes/{disputeId}', [disputeController::class, 'updateDispute']);
Route::post('/both/disputes/{disputeId}/cancel', [disputeController::class, 'cancelDispute']);
Route::delete('/both/disputes/evidence/{fileId}', [disputeController::class, 'deleteEvidenceFile']);
Route::get('/both/disputes/milestones/{projectId}', [disputeController::class, 'getMilestones']);
Route::get('/both/disputes/milestone-items/{milestoneId}', [disputeController::class, 'getMilestoneItems']);
Route::post('/both/disputes/check-existing', [disputeController::class, 'checkExistingDispute']);

// Projects Routes
Route::get('/both/projects', [disputeController::class, 'showProjectsPage']);
Route::get('/both/projects/{projectId}', [disputeController::class, 'showProjectDetails']);

// Contractor Progress Upload Routes
Route::get('/contractor/progress/upload', [\App\Http\Controllers\contractor\progressUploadController::class, 'showUploadPage']);
Route::post('/contractor/progress/upload', [\App\Http\Controllers\contractor\progressUploadController::class, 'uploadProgress']);
Route::get('/contractor/progress/files/{itemId}', [\App\Http\Controllers\contractor\progressUploadController::class, 'getProgressFiles']);
Route::put('/contractor/progress/{progressId}', [\App\Http\Controllers\contractor\progressUploadController::class, 'updateProgress']);
Route::delete('/contractor/progress/{progressId}', [\App\Http\Controllers\contractor\progressUploadController::class, 'deleteProgress']);
// Owner payment validation routes
Route::post('/owner/payment/upload', [\App\Http\Controllers\owner\paymentUploadController::class, 'uploadPayment']);
Route::put('/owner/payment/{paymentId}', [\App\Http\Controllers\owner\paymentUploadController::class, 'updatePayment']);
Route::delete('/owner/payment/{paymentId}', [\App\Http\Controllers\owner\paymentUploadController::class, 'deletePayment']);
Route::post('/contractor/progress/approve/{progressId}', [\App\Http\Controllers\contractor\progressUploadController::class, 'approveProgress']);

// Owner Project Posting Routes
Route::get('/owner/projects/create', [\App\Http\Controllers\Owner\projectsController::class, 'showCreatePostPage']);
Route::post('/owner/projects', [\App\Http\Controllers\Owner\projectsController::class, 'store']);
Route::get('/owner/projects/{projectId}/edit', [\App\Http\Controllers\Owner\projectsController::class, 'showEditPostPage']);
Route::put('/owner/projects/{projectId}', [\App\Http\Controllers\Owner\projectsController::class, 'update']);
Route::delete('/owner/projects/{projectId}', [\App\Http\Controllers\Owner\projectsController::class, 'delete']);
Route::post('/owner/projects/{projectId}/bids/{bidId}/accept', [\App\Http\Controllers\Owner\projectsController::class, 'acceptBid']);
Route::post('/owner/milestones/{milestoneId}/approve', [disputeController::class, 'approveMilestone']);
Route::post('/owner/milestones/{milestoneId}/reject', [disputeController::class, 'rejectMilestone']);
Route::post('/contractor/payments/{paymentId}/approve', [disputeController::class, 'approvePayment']);

// Contractor Bidding Routes
Route::get('/contractor/projects/{projectId}', [\App\Http\Controllers\contractor\biddingController::class, 'showProjectOverview']);
Route::post('/contractor/bids', [\App\Http\Controllers\contractor\biddingController::class, 'store']);
Route::put('/contractor/bids/{bidId}', [\App\Http\Controllers\contractor\biddingController::class, 'update']);
Route::post('/contractor/bids/{bidId}/cancel', [\App\Http\Controllers\contractor\biddingController::class, 'cancel']);

// START HERE ADMIN

// Dashboard Routes
Route::get('/admin/dashboard', [dashboardController::class, 'index'])->name('admin.dashboard');
Route::get('/admin/dashboard/earnings', [dashboardController::class, 'getEarnings'])->name('admin.dashboard.earnings');

// Analytics Routes
Route::get('/admin/analytics', [analyticsController::class, 'analytics'])->name('admin.analytics');
Route::get('/admin/analytics/timeline', [analyticsController::class, 'getProjectsTimelineData'])->name('admin.analytics.timeline');
Route::get('/admin/analytics/subscription', [analyticsController::class, 'subscriptionAnalytics'])->name('admin.analytics.subscription');
Route::get('/admin/analytics/subscription/revenue', [analyticsController::class, 'subscriptionRevenue'])->name('admin.analytics.subscription.revenue');
Route::get('/admin/analytics/user-activity', [analyticsController::class, 'userActivityAnalytics'])->name('admin.analytics.userActivity');
Route::get('/admin/analytics/project-performance', [analyticsController::class, 'projectPerformanceAnalytics'])->name('admin.analytics.projectPerformance');
Route::get('/admin/analytics/bid-completion', [analyticsController::class, 'bidCompletionAnalytics'])->name('admin.analytics.bidCompletion');
Route::get('/admin/analytics/reports', [analyticsController::class, 'reportsAnalytics'])->name('admin.analytics.reports');

// User Management Routes
Route::get('/admin/user-management/property-owners', [userManagementController::class, 'propertyOwners'])->name('admin.userManagement.propertyOwner');
Route::post('/admin/user-management/property-owners/store', [userManagementController::class, 'addPropertyOwner'])->name('admin.userManagement.propertyOwner.store');
Route::get('/admin/user-management/property-owners/{id}/edit', [userManagementController::class, 'fetchPropertyOwner'])->name('admin.userManagement.propertyOwner.edit');
Route::put('/admin/user-management/property-owners/{id}', [userManagementController::class, 'updatePropertyOwner'])->name('admin.userManagement.propertyOwner.update');
Route::delete('/admin/user-management/property-owners/{id}', [userManagementController::class, 'deletePropertyOwner'])->name('admin.userManagement.propertyOwner.delete');
Route::get('/admin/user-management/property-owners/{id}', [userManagementController::class, 'viewPropertyOwner'])->name('admin.userManagement.propertyOwner.view');
Route::get('/admin/user-management/contractors', [userManagementController::class, 'contractors'])->name('admin.userManagement.contractor');
Route::post('/admin/user-management/contractors/store', [userManagementController::class, 'addContractor'])->name('admin.userManagement.contractor.store');
Route::get('/admin/user-management/contractors/{id}/edit', [userManagementController::class, 'fetchContractor'])->name('admin.userManagement.contractor.edit');
Route::put('/admin/user-management/contractors/update/{user_id}', [userManagementController::class, 'updateContractor'])->name('admin.userManagement.contractor.update');
Route::delete('/admin/user-management/contractors/{id}', [userManagementController::class, 'deleteContractor'])->name('admin.userManagement.contractor.delete');
Route::get('/admin/user-management/contractor/view', [userManagementController::class, 'viewContractor'])->name('admin.userManagement.contractor.view');
Route::post('/admin/user-management/contractor/team-member/store', [userManagementController::class, 'addContractorTeamMember'])->name('admin.userManagement.contractor.teamMember.store');
Route::get('/admin/user-management/contractor/team-member/{id}/edit', [userManagementController::class, 'fetchContractorTeamMember'])->name('admin.userManagement.contractor.teamMember.edit');
Route::put('/admin/user-management/contractor/team-member/update/{id}', [userManagementController::class, 'updateContractorTeamMember'])->name('admin.userManagement.contractor.teamMember.update');
Route::delete('/admin/user-management/contractor/team-member/deactivate/{id}', [userManagementController::class, 'deactivateContractorTeamMember'])->name('admin.userManagement.contractor.teamMember.deactivate');
Route::patch('/admin/user-management/contractor/team-member/reactivate/{id}', [userManagementController::class, 'reactivateContractorTeamMember'])->name('admin.userManagement.contractor.teamMember.reactivate');
Route::post('/admin/user-management/contractor/representative/change', [userManagementController::class, 'changeContractorRepresentative'])->name('admin.userManagement.contractor.representative.change');
Route::get('/admin/user-management/verification-requests', [userManagementController::class, 'verificationRequest'])->name('admin.userManagement.verificationRequest');
Route::get('/admin/user-management/suspended-accounts', [userManagementController::class, 'suspendedAccounts'])->name('admin.userManagement.suspendedAccounts');
Route::post('/admin/user-management/suspended-accounts/reactivate', [userManagementController::class, 'reactivateSuspendedUser'])->name('admin.userManagement.suspendedAccounts.reactivate');

// Global Management Routes
Route::get('/admin/global-management/bid-management', [globalManagementController::class, 'bidManagement'])->name('admin.globalManagement.bidManagement');
Route::get('/admin/global-management/proof-of-payments', [globalManagementController::class, 'proofOfPayments'])->name('admin.globalManagement.proofOfpayments');
Route::get('/admin/global-management/ai-management', [globalManagementController::class, 'aiManagement'])->name('admin.globalManagement.aiManagement');
Route::get('/admin/global-management/posting-management', [globalManagementController::class, 'postingManagement'])->name('admin.globalManagement.postingManagement');

// Project Management Routes
Route::get('/admin/project-management/list-of-projects', [ProjectAdminController::class, 'listOfProjects'])->name('admin.projectManagement.listOfprojects');
Route::get('/admin/project-management/subscriptions', [ProjectAdminController::class, 'subscriptions'])->name('admin.projectManagement.subscriptions');
Route::get('/admin/project-management/disputes-reports', [projectManagementController::class, 'disputesReports'])->name('admin.projectManagement.disputesReports');
Route::get('/admin/project-management/disputes/{id}/details', [projectManagementController::class, 'getDisputeDetails'])->name('admin.projectManagement.disputeDetails');
// Dispute actions: approve for review, reject (cancel), finalize resolution
Route::post('/admin/project-management/disputes/{id}/approve', [projectManagementController::class, 'approveForReview'])->name('admin.projectManagement.dispute.approve');
Route::post('/admin/project-management/disputes/{id}/reject', [projectManagementController::class, 'rejectDispute'])->name('admin.projectManagement.dispute.reject');
Route::post('/admin/project-management/disputes/{id}/finalize', [projectManagementController::class, 'finalizeResolution'])->name('admin.projectManagement.dispute.finalize');
Route::get('/admin/project-management/messages', [ProjectAdminController::class, 'messages'])->name('admin.projectManagement.messages');

// Settings Routes
Route::get('/admin/settings/notifications', function() {
    return view('admin.settings.notifications');
})->name('admin.settings.notifications');

Route::get('/admin/settings/security', function() {
    return view('admin.settings.security');
})->name('admin.settings.security');

// * REMOVED CONFLICTING ROUTE HERE *
// The route '/dashboard' was overwriting your Controller logic.

// Debug route
Route::get('/debug/check-projects', function() {
    $projects = DB::table('projects')
        ->where('project_status', 'open')
        ->whereNotNull('owner_id')
        ->select('project_id', 'project_title', 'project_status', 'owner_id', 'created_at')
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'count' => $projects->count(),
        'projects' => $projects
    ]);
})->name('debug.check.projects');

// =============================================
// ADMIN API ROUTES
// =============================================

Route::prefix('/api/admin/users')->group(function () {
    Route::get('/property-owners', [userManagementController::class, 'getPropertyOwnersApi'])->name('api.admin.propertyOwners');
    Route::get('/property-owners/{id}', [userManagementController::class, 'getPropertyOwnerApi'])->name('api.admin.propertyOwner');
    Route::post('/property-owners/{id}/verify', [userManagementController::class, 'verifyPropertyOwner'])->name('api.admin.propertyOwner.verify');
    Route::post('/property-owners/{id}/suspend', [userManagementController::class, 'suspendPropertyOwner'])->name('api.admin.propertyOwner.suspend');

    Route::get('/contractors', [userManagementController::class, 'getContractorsApi'])->name('api.admin.contractors');
    Route::get('/contractors/{id}', [userManagementController::class, 'getContractorApi'])->name('api.admin.contractor');
    Route::post('/contractors/{id}/verify', [userManagementController::class, 'verifyContractor'])->name('api.admin.contractor.verify');
    Route::post('/contractors/{id}/suspend', [userManagementController::class, 'suspendContractor'])->name('api.admin.contractor.suspend');

    Route::get('/verification-requests', [userManagementController::class, 'getVerificationRequestsApi'])->name('api.admin.verificationRequests');
    Route::get('/verification-requests/{id}', [userManagementController::class, 'getVerificationRequestDetails'])->name('api.admin.verificationRequest.details');
    Route::post('/verification-requests/{id}/approve', [userManagementController::class, 'approveVerification'])->name('api.admin.verificationRequest.approve');
    Route::post('/verification-requests/{id}/reject', [userManagementController::class, 'rejectVerification'])->name('api.admin.verificationRequest.reject');

    Route::get('/suspended', [userManagementController::class, 'getSuspendedAccountsApi'])->name('api.admin.suspendedAccounts');
    Route::post('/suspended/{id}/reactivate', [userManagementController::class, 'reactivateSuspendedAccount'])->name('api.admin.suspendedAccount.reactivate');
});

Route::prefix('/api/admin')->group(function () {
    Route::get('/contractors', [userManagementController::class, 'getContractorsApi'])->name('api.admin.contractors.short');
    Route::get('/contractors/{id}', [userManagementController::class, 'getContractorApi'])->name('api.admin.contractor.short');
    Route::post('/contractors/{id}/verify', [userManagementController::class, 'verifyContractor'])->name('api.admin.contractor.verify.short');
    Route::post('/contractors/{id}/suspend', [userManagementController::class, 'suspendContractor'])->name('api.admin.contractor.suspend.short');
});

Route::prefix('/api/admin/management')->group(function () {
    Route::get('/bids', [globalManagementController::class, 'getBidsApi'])->name('api.admin.bids');
    Route::post('/bids/{id}/approve', [globalManagementController::class, 'approveBid'])->name('api.admin.bid.approve');
    Route::post('/bids/{id}/reject', [globalManagementController::class, 'rejectBid'])->name('api.admin.bid.reject');

    Route::get('/payments', [globalManagementController::class, 'getPaymentsApi'])->name('api.admin.payments');
    Route::post('/payments/{id}/verify', [globalManagementController::class, 'verifyPayment'])->name('api.admin.payment.verify');
    Route::post('/payments/{id}/reject', [globalManagementController::class, 'rejectPayment'])->name('api.admin.payment.reject');

    Route::get('/postings', [globalManagementController::class, 'getPostingsApi'])->name('api.admin.postings');
    Route::get('/postings/{id}', [globalManagementController::class, 'getPostDetails'])->name('api.admin.posting.details');
    Route::post('/postings/{id}/approve', [globalManagementController::class, 'approvePosting'])->name('api.admin.posting.approve');
    Route::post('/postings/{id}/reject', [globalManagementController::class, 'rejectPosting'])->name('api.admin.posting.reject');

    Route::get('/ai-stats', [globalManagementController::class, 'getAiStatsApi'])->name('api.admin.aiStats');
});

Route::prefix('/api/admin/analytics')->group(function () {
    Route::get('/projects', [analyticsController::class, 'getProjectsAnalyticsApi'])->name('api.admin.analytics.projects');
    Route::get('/timeline', [analyticsController::class, 'getProjectsTimelineData'])->name('api.admin.analytics.timeline');
    Route::get('/subscription', [analyticsController::class, 'subscriptionAnalytics'])->name('api.admin.analytics.subscription');
    Route::get('/subscription/revenue', [analyticsController::class, 'subscriptionRevenue'])->name('api.admin.analytics.subscriptionRevenue');
    Route::get('/user-activity', [analyticsController::class, 'userActivityAnalytics'])->name('api.admin.analytics.userActivity');
    Route::get('/project-performance', [analyticsController::class, 'projectPerformanceAnalytics'])->name('api.admin.analytics.projectPerformance');
    Route::get('/bid-completion', [analyticsController::class, 'bidCompletionAnalytics'])->name('api.admin.analytics.bidCompletion');
});

Route::prefix('/api/admin/projects')->group(function () {
    Route::get('/', [ProjectAdminController::class, 'getProjectsApi'])->name('api.admin.projects');
    Route::post('/{id}/assign-contractor', [ProjectAdminController::class, 'assignContractor'])->name('api.admin.project.assignContractor');
    Route::post('/{id}/approve', [ProjectAdminController::class, 'approve'])->name('api.admin.project.approve');
    Route::post('/{id}/reject', [ProjectAdminController::class, 'reject'])->name('api.admin.project.reject');

    Route::get('/subscriptions', [ProjectAdminController::class, 'getSubscriptionsApi'])->name('api.admin.subscriptions');
    Route::get('/messages', [ProjectAdminController::class, 'getMessagesApi'])->name('api.admin.messages');
    Route::get('/disputes', [ProjectAdminController::class, 'getDisputesApi'])->name('api.admin.disputes');
});

// New admin resource API routes
Route::prefix('/api/admin')->group(function () {
    Route::apiResource('projects', App\Http\Controllers\Admin\ProjectController::class);
    Route::apiResource('bids', App\Http\Controllers\Admin\BidController::class);
    Route::apiResource('milestones', App\Http\Controllers\Admin\MilestoneController::class);
    Route::apiResource('payments', App\Http\Controllers\Admin\PaymentController::class);
});

Route::get('/storage/{path}', [authController::class, 'serve'])->where('path', '.*')->name('storage.serve');
