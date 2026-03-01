<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authController;
use App\Http\Controllers\subs\platformPaymentController;
use App\Http\Controllers\subs\payMongoController;
use App\Http\Controllers\contractor\cprocessController;
use App\Http\Controllers\contractor\membersController;
use App\Http\Controllers\contractor\AiController;
use App\Http\Controllers\both\disputeController;
use App\Http\Controllers\both\milestoneController;
use App\Http\Controllers\both\homepageController;
use App\Http\Controllers\both\dashboardController as BothDashboardController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Admin\dashboardController;
use App\Http\Controllers\Admin\analyticsController;
use App\Http\Controllers\Admin\userManagementController;
use App\Http\Controllers\Admin\globalManagementController;
use App\Http\Controllers\Admin\ProjectAdminController;
use App\Http\Controllers\Admin\projectManagementController;
use App\Http\Controllers\message\broadcastAuthController;

Route::post('/admin/global-management/ai-management/analyze/{id}', [globalManagementController::class, 'analyzeProject']);

Route::get('/', function () {
    return view('signUp_logIN.landingPage');
});

// Landing page
Route::get('/landing', function () {
    return view('signUp_logIN.landingPage');
})->name('landing');

// Custom Pusher Broadcasting Auth (Session-based for web dashboard)
Route::post('/broadcasting/auth', [broadcastAuthController::class, 'authorize'])
    ->name('broadcasting.auth.custom');

// Splash / introduction screen for owner signup/login
Route::get('/intro', function () {
    return view('signUp_logIN.introduction');
});

// Owner web login screen
Route::get('/login', function () {
    return view('accounts.login');
});

// Owner account type selection screen
Route::get('/account-type', function () {
    return view('signUp_logIN.accountType');
});
// Owner account setup screen
Route::match(['get', 'post'], '/propertyOwner/account-setup', [authController::class, 'showOwnerAccountSetup'])
    ->name('owner.account-setup');

// Back-compat: keep old path working
Route::get('/account-setup', function () {
    return redirect('/propertyOwner/account-setup');
});

// Contractor account setup screen
Route::match(['get', 'post'], '/contractor/account-setup', [authController::class, 'showContractorSetup'])->name('contractor.account-setup');

// OTP Verification screen
Route::get('/otp-verification', function () {
    return view('signUp_logIN.otp_Verification');
})->name('otp.verification');

// OTP Verification for contractor (after Step 2)
Route::get('/otp-verify', function () {
    return view('signUp_logIN.otp_Verification');
})->name('otp.verify');

// Generic OTP verification endpoint (handles contractor and owner)
Route::post('/verify-otp', [authController::class, 'verifyOtp'])->name('verify.otp');

// Add profile photo screen
Route::get('/add-profile-photo', function () {
    return view('signUp_logIN.add_Profilepicture');
})->name('profile.photo');

// Property Owner Homepage
Route::get('/owner/homepage', [homepageController::class, 'ownerHomepage'])->name('owner.homepage');

// Property Owner Dashboard
Route::get('/owner/dashboard', [BothDashboardController::class, 'ownerDashboard'])->name('owner.dashboard');

// Property Owner All Projects
Route::get('/owner/projects', [\App\Http\Controllers\owner\projectsController::class, 'showAllProjects'])->name('owner.projects');

// Property Owner Profile
Route::get('/owner/profile', [\App\Http\Controllers\owner\projectsController::class, 'showProfile'])->name('owner.profile');

// Property Owner Finished Projects
Route::get('/owner/projects/finished', [\App\Http\Controllers\owner\projectsController::class, 'showFinishedProjects'])->name('owner.projects.finished');

// Property Owner — Bids for a project (JSON, web session auth)
Route::get('/owner/projects/{projectId}/bids', [\App\Http\Controllers\owner\projectsController::class, 'getProjectBids'])->name('owner.projects.bids');

// Property Owner Milestone Session Setter
Route::post('/owner/projects/set-milestone', [\App\Http\Controllers\owner\projectsController::class, 'setMilestoneSession'])->name('owner.projects.set-milestone');
Route::post('/owner/projects/set-milestone-item', [\App\Http\Controllers\owner\projectsController::class, 'setMilestoneItemSession'])->name('owner.projects.set-milestone-item');

// Property Owner Milestone Report
Route::get('/owner/projects/milestone-report', [\App\Http\Controllers\owner\projectsController::class, 'showMilestoneReport'])->name('owner.projects.milestone-report');

// Property Owner Milestone Progress Report
Route::get('/owner/projects/milestone-progress-report', [\App\Http\Controllers\owner\projectsController::class, 'showMilestoneProgressReport'])->name('owner.projects.milestone-progress-report');

// Property Owner Messages
Route::get('/owner/messages', [\App\Http\Controllers\owner\projectsController::class, 'showMessages'])->name('owner.messages');

// Property Owner Messages API (Session-based for web)
Route::prefix('owner/messages')->group(function () {
    Route::get('/api', [\App\Http\Controllers\message\messageController::class, 'index'])->name('owner.messages.index');
    Route::get('/api/stats', [\App\Http\Controllers\message\messageController::class, 'getStats'])->name('owner.messages.stats');
    Route::get('/api/users', [\App\Http\Controllers\message\messageController::class, 'getAvailableUsers'])->name('owner.messages.users');
    Route::get('/api/search', [\App\Http\Controllers\message\messageController::class, 'search'])->name('owner.messages.search');
    Route::get('/api/{conversationId}', [\App\Http\Controllers\message\messageController::class, 'show'])->name('owner.messages.show');
    Route::post('/api', [\App\Http\Controllers\message\messageController::class, 'store'])->name('owner.messages.store');
    Route::post('/api/report', [\App\Http\Controllers\message\messageController::class, 'report'])->name('owner.messages.report');
});

// Contractor Homepage
Route::get('/contractor/homepage', [homepageController::class, 'contractorHomepage'])->name('contractor.homepage');

// Contractor Dashboard
Route::get('/contractor/dashboard', [BothDashboardController::class, 'contractorDashboard'])->name('contractor.dashboard');

// Contractor My Projects
Route::get('/contractor/projects', [\App\Http\Controllers\contractor\cprocessController::class, 'showMyProjects'])->name('contractor.projects');
Route::get('/contractor/myprojects', [\App\Http\Controllers\contractor\cprocessController::class, 'showMyProjects'])->name('contractor.myprojects');

// Contractor My Bids
Route::get('/contractor/mybids', [\App\Http\Controllers\contractor\cprocessController::class, 'showMyBids'])->name('contractor.mybids');

// Contractor Milestone Session Setter
Route::post('/contractor/projects/set-milestone', [\App\Http\Controllers\contractor\cprocessController::class, 'setMilestoneSession'])->name('contractor.projects.set-milestone');
Route::post('/contractor/projects/set-milestone-item', [\App\Http\Controllers\contractor\cprocessController::class, 'setMilestoneItemSession'])->name('contractor.projects.set-milestone-item');

// Contractor Milestone Report
Route::get('/contractor/projects/milestone-report', [\App\Http\Controllers\contractor\cprocessController::class, 'showMilestoneReport'])->name('contractor.projects.milestone-report');

// Contractor Milestone Progress Report
Route::get('/contractor/projects/milestone-progress-report', [\App\Http\Controllers\contractor\cprocessController::class, 'showMilestoneProgressReport'])->name('contractor.projects.milestone-progress-report');

// Contractor Messages
Route::get('/contractor/messages', [\App\Http\Controllers\contractor\cprocessController::class, 'showMessages'])->name('contractor.messages');

// Contractor Messages API (Session-based for web)
Route::prefix('contractor/messages')->group(function () {
    Route::get('/api', [\App\Http\Controllers\message\messageController::class, 'index'])->name('contractor.messages.index');
    Route::get('/api/stats', [\App\Http\Controllers\message\messageController::class, 'getStats'])->name('contractor.messages.stats');
    Route::get('/api/users', [\App\Http\Controllers\message\messageController::class, 'getAvailableUsers'])->name('contractor.messages.users');
    Route::get('/api/search', [\App\Http\Controllers\message\messageController::class, 'search'])->name('contractor.messages.search');
    Route::get('/api/{conversationId}', [\App\Http\Controllers\message\messageController::class, 'show'])->name('contractor.messages.show');
    Route::post('/api', [\App\Http\Controllers\message\messageController::class, 'store'])->name('contractor.messages.store');
    Route::post('/api/report', [\App\Http\Controllers\message\messageController::class, 'report'])->name('contractor.messages.report');
});

// Contractor Profile
Route::get('/contractor/profile', [\App\Http\Controllers\contractor\cprocessController::class, 'showProfile'])->name('contractor.profile');

// Contractor Profile API (web session-based routes)
Route::get('/contractor/profile/fetch', [\App\Http\Controllers\profileController::class, 'apiGetProfile'])->name('contractor.profile.fetch');
Route::get('/contractor/profile/reviews', [\App\Http\Controllers\profileController::class, 'apiGetReviews'])->name('contractor.profile.reviews');
Route::post('/contractor/profile/update', [\App\Http\Controllers\profileController::class, 'update'])->name('contractor.profile.update');

// Security Settings – OTP change endpoints (web session-based)
Route::post('/security/change-otp/send', [\App\Http\Controllers\OTPChangeController::class, 'sendOtp'])->name('security.otp.send');
Route::post('/security/change-otp/verify', [\App\Http\Controllers\OTPChangeController::class, 'verifyOtp'])->name('security.otp.verify');

// Contractor AI Analytics
Route::get('/contractor/ai-analytics', [AiController::class, 'showAnalytics'])->name('contractor.ai-analytics');
Route::post('/contractor/ai-analytics/analyze/{id}', [AiController::class, 'analyzeProject'])->name('contractor.ai-analytics.analyze');
Route::get('/contractor/ai-analytics/stats', [AiController::class, 'getStats'])->name('contractor.ai-analytics.stats');

// PayMongo checkout endpoints (web, requires session auth)
Route::post('/subscribe/checkout', [payMongoController::class, 'createSubscriptionCheckout']);
Route::post('/subscribe/checkout', [payMongoController::class, 'createSubscriptionCheckout']);
Route::post('/subscribe/cancel', [payMongoController::class, 'cancelSubscription']);
Route::post('/boost/checkout', [payMongoController::class, 'createBoostCheckout']);
Route::get('/payment/callback', [payMongoController::class, 'handlePaymentSuccess'])->name('payment.callback');

// Subscription / Boosts modal JSON data (optional endpoint)
Route::get('/subs/modal-data', [platformPaymentController::class, 'modalData'])->name('subs.modal.data');

// Authentication Routes
Route::get('/accounts/login', [authController::class, 'showLoginForm']);
Route::post('/accounts/login', [authController::class, 'login']);
Route::get('/accounts/signup', [authController::class, 'showSignupForm']);
Route::get('/owner/signup', function () {
    return redirect('/account-type');
})->name('owner.signup');
Route::post('/accounts/signup/select-role', [authController::class, 'selectRole']);
Route::post('/accounts/logout', [authController::class, 'logout']);
Route::get('/accounts/logout', [authController::class, 'logout']);

// Admin Authentication Routes
Route::get('/admin/login', function () {
    return view('accounts.login');
})->name('admin.login');
Route::post('/admin/login', [authController::class, 'login'])->name('admin.login.post');

Route::get('/admin/signup', function () {
    return view('accounts.login');
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

// Web POST endpoint for role switching (session-aware) so frontend can switch roles without bearer token
Route::post('/accounts/switch-role', [cprocessController::class, 'switchRole'])->name('accounts.switch.role');

// PSGC API Routes
Route::get('/api/psgc/provinces', [authController::class, 'getProvinces']);
Route::get('/api/psgc/provinces/{provinceCode}/cities', [authController::class, 'getCitiesByProvince']);
Route::get('/api/psgc/cities', [authController::class, 'getAllCities']);
Route::get('/api/psgc/cities/{cityCode}/barangays', [authController::class, 'getBarangaysByCity']);

// Contractor Setup Form Data
Route::get('/api/contractor/setup-data', [authController::class, 'getContractorSetupData']);

// NOTE: Contractor members API routes are in routes/api.php (not here)
// Mobile app uses /api/contractor/members endpoints with Bearer token auth

// Dashboard Routes
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
});

Route::get('/dashboard', [BothDashboardController::class, 'unifiedDashboard']);

// Contractor Milestone Setup Routes
Route::get('/contractor/milestone/setup', [cprocessController::class, 'showMilestoneSetupForm']);
Route::post('/contractor/milestone/setup/step1', [cprocessController::class, 'milestoneStepOne']);
Route::post('/contractor/milestone/setup/step2', [cprocessController::class, 'milestoneStepTwo']);
Route::post('/contractor/milestone/setup/submit', [cprocessController::class, 'submitMilestone']);
Route::post('/contractor/milestone/{milestoneId}/delete', [cprocessController::class, 'deleteMilestone']);

// Role Management Routes for 'both' users
// NOTE: API versions of these routes live in routes/api.php.
// Removing duplicates here avoids CSRF errors for mobile clients.

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
Route::get('/contractor/progress/document/view', [\App\Http\Controllers\contractor\progressUploadController::class, 'viewProgressDocument'])->name('contractor.progress.document.view');
Route::put('/contractor/progress/{progressId}', [\App\Http\Controllers\contractor\progressUploadController::class, 'updateProgress']);
Route::delete('/contractor/progress/{progressId}', [\App\Http\Controllers\contractor\progressUploadController::class, 'deleteProgress']);
// Owner payment validation routes
Route::post('/owner/payment/upload', [\App\Http\Controllers\owner\paymentUploadController::class, 'uploadPayment']);
Route::put('/owner/payment/{paymentId}', [\App\Http\Controllers\owner\paymentUploadController::class, 'updatePayment']);
Route::delete('/owner/payment/{paymentId}', [\App\Http\Controllers\owner\paymentUploadController::class, 'deletePayment']);
Route::post('/contractor/progress/approve/{progressId}', [\App\Http\Controllers\contractor\progressUploadController::class, 'approveProgress']);
Route::post('/contractor/progress/reject/{progressId}', [\App\Http\Controllers\contractor\progressUploadController::class, 'rejectProgress']);

// Owner Project Posting Routes
Route::get('/owner/projects/create', [\App\Http\Controllers\owner\projectsController::class, 'showCreatePostPage']);
Route::post('/owner/projects', [\App\Http\Controllers\owner\projectsController::class, 'store']);
Route::get('/owner/projects/{projectId}/edit', [\App\Http\Controllers\owner\projectsController::class, 'showEditPostPage']);
Route::put('/owner/projects/{projectId}', [\App\Http\Controllers\owner\projectsController::class, 'update']);
Route::delete('/owner/projects/{projectId}', [\App\Http\Controllers\owner\projectsController::class, 'delete']);
Route::post('/owner/projects/{projectId}/bids/{bidId}/accept', [\App\Http\Controllers\owner\projectsController::class, 'acceptBid']);
Route::post('/owner/projects/{projectId}/bids/{bidId}/reject', [\App\Http\Controllers\owner\projectsController::class , 'rejectBid'])->name('owner.projects.bids.reject');
Route::post('/owner/milestones/{milestoneId}/approve', [milestoneController::class, 'webApproveMilestone']);
Route::post('/owner/milestones/{milestoneId}/reject', [milestoneController::class, 'webRejectMilestone']);
Route::post('/contractor/payments/{paymentId}/approve', [milestoneController::class, 'apiApprovePayment']);

// Protected Document Viewer (for important documents with watermark)
Route::get('/contractor/document/view', [\App\Http\Controllers\contractor\documentViewController::class, 'viewProtectedDocument'])
    ->name('contractor.document.view');

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
// Specific routes first (to avoid conflict with {id} parameter)
Route::get('/admin/project-management/list-of-projects', [projectManagementController::class, 'listOfProjects'])->name('admin.projectManagement.listOfProjects');
Route::get('/admin/project-management/subscriptions', [projectManagementController::class, 'subscriptions'])->name('admin.projectManagement.subscriptions');
Route::post('/admin/project-management/subscriptions/plans', [projectManagementController::class, 'addSubscriptionPlan'])->name('admin.projectManagement.addSubscriptionPlan');
Route::put('/admin/project-management/subscriptions/plans/{id}', [projectManagementController::class, 'updateSubscriptionPlan'])->name('admin.projectManagement.updateSubscriptionPlan');
Route::delete('/admin/project-management/subscriptions/plans/{id}', [projectManagementController::class, 'deleteSubscriptionPlan'])->name('admin.projectManagement.deleteSubscriptionPlan');
Route::post('/admin/project-management/subscriptions/{id}/deactivate', [projectManagementController::class, 'deactivateSubscription'])->name('admin.projectManagement.deactivateSubscription');
Route::post('/admin/project-management/subscriptions/{id}/reactivate', [projectManagementController::class, 'reactivateSubscription'])->name('admin.projectManagement.reactivateSubscription');
Route::get('/admin/project-management/disputes-reports', [projectManagementController::class, 'disputesReports'])->name('admin.projectManagement.disputesReports');
Route::get('/admin/project-management/messages', [ProjectAdminController::class, 'messages'])->name('admin.projectManagement.messages');

// Admin Messages API (Session-based for web dashboard)
Route::prefix('admin/messages')->group(function () {
    Route::get('/', [\App\Http\Controllers\message\messageController::class, 'index'])->name('admin.messages.index');
    Route::get('/stats', [\App\Http\Controllers\message\messageController::class, 'getStats'])->name('admin.messages.stats');
    Route::get('/flagged', [\App\Http\Controllers\message\messageController::class, 'getFlaggedConversations'])->name('admin.messages.flagged');
    Route::get('/suspended', [\App\Http\Controllers\message\messageController::class, 'getSuspendedConversations'])->name('admin.messages.suspended');
    Route::get('/users', [\App\Http\Controllers\message\messageController::class, 'getAvailableUsers'])->name('admin.messages.users');
    Route::get('/search', [\App\Http\Controllers\message\messageController::class, 'search'])->name('admin.messages.search');
    Route::get('/{conversationId}', [\App\Http\Controllers\message\messageController::class, 'show'])->name('admin.messages.show');
    Route::post('/', [\App\Http\Controllers\message\messageController::class, 'store'])->name('admin.messages.store');
    Route::post('/report', [\App\Http\Controllers\message\messageController::class, 'report'])->name('admin.messages.report');
    Route::post('/conversation/{conversationId}/suspend', [\App\Http\Controllers\message\messageController::class, 'suspend'])->name('admin.messages.suspend');
    Route::post('/conversation/{conversationId}/restore', [\App\Http\Controllers\message\messageController::class, 'restore'])->name('admin.messages.restore');
    Route::post('/conversation/{conversationId}/flag', [\App\Http\Controllers\message\messageController::class, 'flagConversation'])->name('admin.messages.conversation.flag');
    Route::post('/conversation/{conversationId}/unflag', [\App\Http\Controllers\message\messageController::class, 'unflagConversation'])->name('admin.messages.conversation.unflag');
});

// Bid routes
Route::get('/admin/project-management/bids/{bid_id}/details', [projectManagementController::class, 'getBidDetails'])->name('admin.projectManagement.bidDetails');
Route::get('/admin/project-management/bids/{bid_id}/accept-summary', [projectManagementController::class, 'getAcceptBidSummary'])->name('admin.projectManagement.acceptBidSummary');
Route::post('/admin/project-management/bids/{bid_id}/accept', [projectManagementController::class, 'acceptBid'])->name('admin.projectManagement.acceptBid');
Route::get('/admin/project-management/bids/{bid_id}/reject-summary', [projectManagementController::class, 'getRejectBidSummary'])->name('admin.projectManagement.rejectBidSummary');
Route::post('/admin/project-management/bids/{bid_id}/reject', [projectManagementController::class, 'rejectBid'])->name('admin.projectManagement.rejectBid');

// Dispute routes
Route::get('/admin/project-management/disputes/{id}/details', [projectManagementController::class, 'getDisputeDetails'])->name('admin.projectManagement.disputeDetails');
Route::post('/admin/project-management/disputes/{id}/approve', [projectManagementController::class, 'approveForReview'])->name('admin.projectManagement.approveDispute');
Route::post('/admin/project-management/disputes/{id}/reject', [projectManagementController::class, 'rejectDispute'])->name('admin.projectManagement.rejectDispute');
Route::post('/admin/project-management/disputes/{id}/finalize', [projectManagementController::class, 'finalizeResolution'])->name('admin.projectManagement.finalizeDispute');

// Project detail routes (specific paths before {id})
Route::get('/admin/project-management/{id}/details', [projectManagementController::class, 'getProjectDetails'])->name('admin.projectManagement.projectDetails');
Route::get('/admin/project-management/{id}/completed-details', [projectManagementController::class, 'getCompletedDetails'])->name('admin.projectManagement.completedDetails');
Route::get('/admin/project-management/{id}/completion-details', [projectManagementController::class, 'getCompletionDetails'])->name('admin.projectManagement.completionDetails');
Route::get('/admin/project-management/{id}/ongoing-details', [projectManagementController::class, 'getOngoingDetails'])->name('admin.projectManagement.ongoingDetails');
Route::get('/admin/project-management/{id}/open-details', [projectManagementController::class, 'getOpenDetails'])->name('admin.projectManagement.openDetails');
Route::get('/admin/project-management/{id}/terminated-details', [projectManagementController::class, 'getTerminatedDetails'])->name('admin.projectManagement.terminatedDetails');
Route::get('/admin/project-management/{id}/halted-details', [projectManagementController::class, 'getHaltedDetails'])->name('admin.projectManagement.haltedDetails');
Route::get('/admin/project-management/{id}/halt-details', [projectManagementController::class, 'getHaltDetails'])->name('admin.projectManagement.haltDetails');
Route::get('/admin/project-management/{id}/edit', [projectManagementController::class, 'getEditProject'])->name('admin.projectManagement.editProject');
Route::get('/admin/project-management/{id}/delete-summary', [projectManagementController::class, 'getDeleteSummary'])->name('admin.projectManagement.deleteSummary');
Route::get('/admin/project-management/{id}/restore-summary', [projectManagementController::class, 'getRestoreSummary'])->name('admin.projectManagement.restoreSummary');
Route::get('/admin/project-management/{id}/halt-summary', [projectManagementController::class, 'getHaltSummary'])->name('admin.projectManagement.haltSummary');
Route::get('/admin/project-management/milestone-item/{itemId}/edit', [projectManagementController::class, 'getMilestoneItemForEdit'])->name('admin.projectManagement.editMilestoneItem');

// Project action routes
Route::post('/admin/project-management/{id}/cancel-halt', [projectManagementController::class, 'cancelHalt'])->name('admin.projectManagement.cancelHalt');
Route::post('/admin/project-management/{id}/resume-halt', [projectManagementController::class, 'resumeHalt'])->name('admin.projectManagement.resumeHalt');
Route::post('/admin/project-management/{id}/halt', [projectManagementController::class, 'haltProject'])->name('admin.projectManagement.haltProject');
Route::post('/admin/project-management/{id}/resume', [projectManagementController::class, 'resumeProject'])->name('admin.projectManagement.resumeProject');
Route::put('/admin/project-management/{id}', [projectManagementController::class, 'updateProject'])->name('admin.projectManagement.updateProject');
Route::delete('/admin/project-management/{id}', [projectManagementController::class, 'deleteProject'])->name('admin.projectManagement.deleteProject');
Route::post('/admin/project-management/{id}/restore', [projectManagementController::class, 'restoreProject'])->name('admin.projectManagement.restoreProject');
Route::put('/admin/project-management/milestone-item/{itemId}', [projectManagementController::class, 'updateMilestoneItem'])->name('admin.projectManagement.updateMilestoneItem');

// Notification redirect — marks as read and 302s to the contextual page
Route::get('/notifications/{id}/redirect', [\App\Http\Controllers\both\NotificationController::class, 'redirect'])->name('notifications.redirect');

// Settings Routes
Route::get('/admin/settings/notifications', function () {
    return view('admin.settings.notifications');
})->name('admin.settings.notifications');

Route::get('/admin/settings/security', function () {
    return view('admin.settings.security');
})->name('admin.settings.security');

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
    // Contractor members management (admin)
    Route::post('/contractors/{id}/members', [userManagementController::class, 'addContractorMember'])->name('api.admin.contractor.addMember');
    Route::put('/contractors/{id}/members/{memberId}', [userManagementController::class, 'updateContractorMember'])->name('api.admin.contractor.updateMember');
    Route::delete('/contractors/{id}/members/{memberId}', [userManagementController::class, 'deleteContractorMember'])->name('api.admin.contractor.deleteMember');

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
    Route::get('/disputes', [ProjectAdminController::class, 'getDisputesApi'])->name('api.admin.disputes');
});

// New admin resource API routes
Route::prefix('/api/admin')->group(function () {
    Route::apiResource('projects', App\Http\Controllers\Admin\projectController::class);
    Route::apiResource('bids', App\Http\Controllers\Admin\bidController::class);
    Route::apiResource('milestones', App\Http\Controllers\Admin\milestoneController::class);
    Route::apiResource('payments', App\Http\Controllers\Admin\paymentController::class);
});

// Windows/XAMPP storage fallback route - serves files from storage/app/public/
Route::get('/storage/{path}', function ($path) {
    // Remove query parameters from path (e.g., ?t=timestamp)
    $cleanPath = strtok($path, '?');
    $fullPath = storage_path('app/public/' . $cleanPath);

    \Log::info('Storage serve request', [
        'raw_path' => $path,
        'clean_path' => $cleanPath,
        'full_path' => $fullPath,
        'exists' => file_exists($fullPath),
        'is_readable' => file_exists($fullPath) ? is_readable($fullPath) : false
    ]);

    if (!file_exists($fullPath)) {
        \Log::warning('File not found in storage', ['path' => $fullPath]);
        abort(404, 'File not found');
    }

    $mimeType = mime_content_type($fullPath);

    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*')->name('storage.serve');
