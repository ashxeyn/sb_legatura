<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authController;
use App\Http\Controllers\TestNotificationsController;
use App\Http\Controllers\subs\platformPaymentController;
use App\Http\Controllers\subs\payMongoController;
use App\Http\Controllers\contractor\cprocessController;
use App\Http\Controllers\contractor\membersController;
use App\Http\Controllers\contractor\aiController;
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
use App\Http\Controllers\Admin\projectAdminController;
use App\Http\Controllers\Admin\projectManagementController;
use App\Http\Controllers\message\broadcastAuthController;
use App\Http\Controllers\passwordController;
use App\Http\Controllers\AdminController;

Route::prefix('admin/notifications')
    ->middleware([\App\Http\Middleware\AdminAuthMiddleware::class])
    ->group(function () {

        // Page (already exists — keep your existing view route)
        // Route::get('/', [AdminController::class, 'notificationSettings']);
    
        // Users list for targeted send dropdown
        Route::get('/users', [\App\Http\Controllers\AdminController::class, 'getUsersForNotification']);

        // Send actions
        Route::post('/send-announcement', [\App\Http\Controllers\AdminController::class, 'sendAnnouncement'])
            ->name('admin.sendAnnouncement');
        Route::post('/send-targeted', [\App\Http\Controllers\AdminController::class, 'sendTargetedNotification'])
            ->name('admin.sendTargetedNotification');

        // Preferences
        Route::get('/preferences', [\App\Http\Controllers\AdminController::class, 'getPreferences']);
        Route::post('/preferences', [\App\Http\Controllers\AdminController::class, 'savePreferences'])
            ->name('admin.notifications.savePreferences');

        // Sent log
        Route::get('/sent-log', [\App\Http\Controllers\AdminController::class, 'getSentLog']);

        // ── NEW: User Activity Feed ──────────────────────────────────────────
        // Paginated list of user activity events (user_activity_logs table)
        Route::get('/activity', [\App\Http\Controllers\AdminController::class, 'getUserActivityLogs']);

        // Mark one, many, or all activity rows as read
        Route::post('/activity/mark-read', [\App\Http\Controllers\AdminController::class, 'markActivityRead'])
            ->name('admin.notifications.markActivityRead');
    });


Route::get('/admin/analytics/subscription', [analyticsController::class, 'subscriptionAnalytics'])->name('admin.analytics.subscription');
Route::get('/admin/analytics/subscription/revenue', [analyticsController::class, 'subscriptionRevenue'])->name('admin.analytics.subscription.revenue');
Route::get('/admin/analytics/subscription/subscribers', [analyticsController::class, 'getSubscribersJson'])->name('admin.analytics.subscription.subscribers');
Route::prefix('admin/settings/security')
    ->middleware([\App\Http\Middleware\AdminAuthMiddleware::class])
    ->group(function () {

        // ── VIEW ──────────────────────────────────────────────────────────────────
        Route::get('/', function () {
            return view('admin.settings.security');
        })->name('admin.settings.security');

        // ── JSON ENDPOINTS ────────────────────────────────────────────────────────
        Route::get('/data', [\App\Http\Controllers\Admin\accountController::class, 'data'])
            ->name('admin.settings.security.data');

        Route::post('/update', [\App\Http\Controllers\Admin\accountController::class, 'update'])
            ->name('admin.settings.security.update');

        Route::post('/change-password', [\App\Http\Controllers\Admin\accountController::class, 'changePassword'])
            ->name('admin.settings.security.changePassword');

        Route::post('/delete', [\App\Http\Controllers\Admin\accountController::class, 'delete'])
            ->name('admin.settings.security.delete');

        // ── ADMIN MEMBERS ─────────────────────────────────────────────────────────
        Route::get('/members', [\App\Http\Controllers\Admin\accountController::class, 'members'])
            ->name('admin.settings.security.members');

        Route::post('/members/create', [\App\Http\Controllers\Admin\accountController::class, 'createMember'])
            ->name('admin.settings.security.members.create');

        Route::get('/members/{id}/data', [\App\Http\Controllers\Admin\accountController::class, 'memberData'])
            ->name('admin.settings.security.members.data');

        Route::post('/members/{id}/update', [\App\Http\Controllers\Admin\accountController::class, 'updateMember'])
            ->name('admin.settings.security.members.update');

        Route::post('/members/{id}/delete', [\App\Http\Controllers\Admin\accountController::class, 'deleteMember'])
            ->name('admin.settings.security.members.delete');

        // ── GLOBAL TEAM ACTIVITY ─────────────────────────────────────────────────
        Route::get('/team-activity', [\App\Http\Controllers\Admin\accountController::class, 'teamActivity'])
            ->name('admin.settings.security.teamActivity');
    });

Route::post('/admin/global-management/ai-management/analyze/{id}', [globalManagementController::class, 'analyzeProject']);

// Mobile document viewer (served as HTML so WebView has same-origin access to files)
Route::get('/document-viewer', function () {
    $file = request()->query('file', '');
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $fileUrl = str_starts_with($file, 'http') ? $file : asset('storage/' . $file);

    // Convert DOCX to PDF server-side for proper page rendering
    if ($ext === 'docx' && !str_starts_with($file, 'http')) {
        $sourcePath = storage_path('app/public/' . $file);
        if (file_exists($sourcePath)) {
            $cacheDir = storage_path('app/public/doc_cache');
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            $hash = md5($file . filemtime($sourcePath));
            $pdfName = $hash . '.pdf';
            $pdfPath = $cacheDir . '/' . $pdfName;

            if (!file_exists($pdfPath)) {
                try {
                    // Load DOCX with PhpWord and export as HTML
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load($sourcePath, 'Word2007');
                    $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
                    ob_start();
                    $htmlWriter->save('php://output');
                    $html = ob_get_clean();

                    // Convert HTML to PDF with DomPDF for proper pagination
                    $dompdf = new \Dompdf\Dompdf([
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true,
                        'defaultFont' => 'Arial',
                    ]);
                    $dompdf->setPaper('letter', 'portrait');
                    $dompdf->loadHtml($html);
                    $dompdf->render();
                    file_put_contents($pdfPath, $dompdf->output());
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('DOCX→PDF conversion failed: ' . $e->getMessage());
                }
            }

            if (file_exists($pdfPath)) {
                $fileUrl = asset('storage/doc_cache/' . $pdfName);
                $ext = 'pdf';
            }
        }
    }

    return response(view('document-viewer', compact('fileUrl', 'ext')))
        ->header('Content-Type', 'text/html');
});

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

// Owner web login screen — guarded, direct access returns 404
Route::get('/login', function () {
    abort(404);
});

// Owner account type selection screen — guarded, direct access returns 404
Route::get('/account-type', function () {
    $token = session('auth_entry_token');
    $expiry = session('auth_entry_expiry', 0);

    if (!$token || time() > $expiry) {
        session()->forget(['auth_entry_token', 'auth_entry_expiry']);
        abort(404);
    }

    session()->forget(['auth_entry_token', 'auth_entry_expiry']);
    return view('signUp_logIN.accountType');
});
// Owner account setup screen — guarded, direct access returns 404
Route::get('/propertyOwner/account-setup', function () {
    $token = session('auth_entry_token');
    $expiry = session('auth_entry_expiry', 0);

    if (!$token || time() > $expiry) {
        session()->forget(['auth_entry_token', 'auth_entry_expiry']);
        abort(404);
    }

    session()->forget(['auth_entry_token', 'auth_entry_expiry']);
    return app(\App\Http\Controllers\authController::class)->showOwnerAccountSetup();
})->name('owner.account-setup');
Route::post('/propertyOwner/account-setup', [authController::class, 'showOwnerAccountSetup']);

// Back-compat: keep old path working (goes through gate)
Route::get('/account-setup', function () {
    return redirect('/auth/gate/owner-setup');
});

// Contractor account setup screen — guarded, direct access returns 404
Route::get('/contractor/account-setup', function () {
    $token = session('auth_entry_token');
    $expiry = session('auth_entry_expiry', 0);

    if (!$token || time() > $expiry) {
        session()->forget(['auth_entry_token', 'auth_entry_expiry']);
        abort(404);
    }

    session()->forget(['auth_entry_token', 'auth_entry_expiry']);
    return app(\App\Http\Controllers\authController::class)->showContractorSetup();
})->name('contractor.account-setup');
Route::post('/contractor/account-setup', [authController::class, 'showContractorSetup']);

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

// Property Owner Project Details (individual project)
// Property Owner Profile API (web session-based routes)
Route::get('/owner/profile/fetch', [\App\Http\Controllers\profileController::class, 'apiGetProfile'])->name('owner.profile.fetch');
Route::get('/owner/profile/reviews', [\App\Http\Controllers\profileController::class, 'apiGetReviews'])->name('owner.profile.reviews');
Route::post('/owner/profile/update', [\App\Http\Controllers\profileController::class, 'update'])->name('owner.profile.update');

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

// Property Owner Project Details (keep after static /owner/projects/* routes)
Route::get('/owner/projects/{projectId}', [\App\Http\Controllers\owner\projectsController::class, 'showProjectDetails'])->name('owner.projects.show');

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
Route::post('/security/change-otp/send', [\App\Http\Controllers\otpChangeController::class, 'sendOtp'])->name('security.otp.send');
Route::post('/security/change-otp/verify', [\App\Http\Controllers\otpChangeController::class, 'verifyOtp'])->name('security.otp.verify');

// Contractor AI Analytics
Route::get('/contractor/ai-analytics', [aiController::class, 'showAnalytics'])->name('contractor.ai-analytics');
Route::post('/contractor/ai-analytics/analyze/{id}', [aiController::class, 'analyzeProject'])->name('contractor.ai-analytics.analyze');
Route::get('/contractor/ai-analytics/stats', [aiController::class, 'getStats'])->name('contractor.ai-analytics.stats');

// PayMongo checkout endpoints (web, requires session auth)
Route::post('/subscribe/checkout', [payMongoController::class, 'createSubscriptionCheckout']);
Route::post('/subscribe/checkout', [payMongoController::class, 'createSubscriptionCheckout']);
Route::post('/subscribe/cancel', [payMongoController::class, 'cancelSubscription']);
Route::post('/boost/checkout', [payMongoController::class, 'createBoostCheckout']);
Route::get('/payment/callback', [payMongoController::class, 'handlePaymentSuccess'])->name('payment.callback');

// Subscription / Boosts modal JSON data (optional endpoint)
Route::get('/subs/modal-data', [platformPaymentController::class, 'modalData'])->name('subs.modal.data');

// ── Entry-gate setters ────────────────────────────────────────────────────
// These routes set a short-lived session token then redirect to the
// real destination. Only users who click a legitimate link/button will
// have this token; anyone who types the URL directly will get 404.
Route::get('/auth/gate/login', function () {
    session([
        'auth_entry_token' => true,
        'auth_entry_expiry' => time() + 30,  // 30-second window
    ]);
    return redirect('/accounts/login');
})->name('auth.gate.login');

Route::get('/auth/gate/signup', function () {
    session([
        'auth_entry_token' => true,
        'auth_entry_expiry' => time() + 30,
    ]);
    return redirect('/accounts/signup');
})->name('auth.gate.signup');

Route::get('/auth/gate/account-type', function () {
    session([
        'auth_entry_token' => true,
        'auth_entry_expiry' => time() + 30,
    ]);
    return redirect('/account-type');
})->name('auth.gate.account-type');

Route::get('/auth/gate/owner-setup', function () {
    session([
        'auth_entry_token' => true,
        'auth_entry_expiry' => time() + 30,
    ]);
    return redirect('/propertyOwner/account-setup');
})->name('auth.gate.owner-setup');

Route::get('/auth/gate/contractor-setup', function () {
    session([
        'auth_entry_token' => true,
        'auth_entry_expiry' => time() + 30,
    ]);
    return redirect('/contractor/account-setup');
})->name('auth.gate.contractor-setup');

Route::get('/auth/gate/forgot-password', function () {
    session([
        'auth_entry_token' => true,
        'auth_entry_expiry' => time() + 30,
    ]);
    return redirect('/accounts/forgot-password');
})->name('auth.gate.forgot-password');
// ──────────────────────────────────────────────────────────────────────────

// Authentication Routes
// GET pages are guarded: direct URL access returns 404
Route::get('/accounts/login', function () {
    $token = session('auth_entry_token');
    $expiry = session('auth_entry_expiry', 0);

    if (!$token || time() > $expiry) {
        session()->forget(['auth_entry_token', 'auth_entry_expiry']);
        abort(404);
    }

    session()->forget(['auth_entry_token', 'auth_entry_expiry']);
    return app(\App\Http\Controllers\authController::class)->showLoginForm();
});
Route::post('/accounts/login', [authController::class, 'login'])->middleware('throttle:5,1');

Route::get('/accounts/signup', function () {
    $token = session('auth_entry_token');
    $expiry = session('auth_entry_expiry', 0);

    if (!$token || time() > $expiry) {
        session()->forget(['auth_entry_token', 'auth_entry_expiry']);
        abort(404);
    }

    session()->forget(['auth_entry_token', 'auth_entry_expiry']);
    return app(\App\Http\Controllers\authController::class)->showSignupForm();
});

Route::get('/owner/signup', function () {
    return redirect('/auth/gate/account-type');
})->name('owner.signup');
Route::post('/accounts/signup/select-role', [authController::class, 'selectRole'])->middleware('throttle:10,1');
Route::post('/accounts/logout', [authController::class, 'logout']);
Route::get('/accounts/logout', [authController::class, 'logout']);

// Forgot Password Routes
Route::get('/accounts/forgot-password', function () {
    $token = session('auth_entry_token');
    $expiry = session('auth_entry_expiry', 0);

    if (!$token || time() > $expiry) {
        session()->forget(['auth_entry_token', 'auth_entry_expiry']);
        abort(404);
    }

    session()->forget(['auth_entry_token', 'auth_entry_expiry']);
    return app(\App\Http\Controllers\passwordController::class)->showForgotForm();
})->name('password.forgot');
Route::post('/accounts/password/send-otp', [passwordController::class, 'sendResetOtp'])->middleware('throttle:3,1')->name('password.send-otp');
Route::post('/accounts/password/verify-otp', [passwordController::class, 'verifyResetOtp'])->middleware('throttle:5,1')->name('password.verify-otp');
Route::post('/accounts/password/reset', [passwordController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.reset');

// Admin Authentication Routes
// GET pages abort(404) for direct URL access; the secret modal uses POST only
Route::get('/admin/login', function () {
    abort(404);
})->name('admin.login');
Route::post('/admin/login', [authController::class, 'login'])->middleware('throttle:3,1')->name('admin.login.post');

Route::get('/admin/signup', function () {
    abort(404);
})->name('admin.signup');
Route::post('/admin/signup', [authController::class, 'adminSignup'])->middleware('throttle:3,1')->name('admin.signup.post');

Route::post('/admin/logout', [authController::class, 'logout'])->name('admin.logout');

// Contractor Signup Routes - Protected from direct POST access
Route::post('/accounts/signup/contractor/step1', [authController::class, 'contractorStep1'])->middleware('throttle:10,1');
Route::post('/accounts/signup/contractor/step2', [authController::class, 'contractorStep2'])->middleware('throttle:5,1');
Route::post('/accounts/signup/contractor/step3/verify-otp', [authController::class, 'contractorVerifyOtp'])->middleware('throttle:5,1');
Route::post('/accounts/signup/contractor/step4', [authController::class, 'contractorStep4'])->middleware('throttle:10,1');
Route::post('/accounts/signup/contractor/final', [authController::class, 'contractorFinalStep'])->middleware('throttle:10,1');

// Property Owner Signup Routes - Protected from direct POST access
Route::post('/accounts/signup/owner/step1', [authController::class, 'propertyOwnerStep1'])->middleware('throttle:10,1');
Route::post('/accounts/signup/owner/step2', [authController::class, 'propertyOwnerStep2'])->middleware('throttle:5,1');
Route::post('/accounts/signup/owner/step3/verify-otp', [authController::class, 'propertyOwnerVerifyOtp'])->middleware('throttle:5,1');
Route::post('/accounts/signup/owner/step4', [authController::class, 'propertyOwnerStep4'])->middleware('throttle:10,1');
Route::post('/accounts/signup/owner/final', [authController::class, 'propertyOwnerFinalStep'])->middleware('throttle:10,1');

// Role Switch Routes
Route::get('/accounts/switch', [authController::class, 'showSwitchForm']);
Route::post('/accounts/switch/contractor/step1', [authController::class, 'switchContractorStep1'])->middleware('throttle:10,1');
Route::post('/accounts/switch/contractor/step2', [authController::class, 'switchContractorStep2'])->middleware('throttle:10,1');
Route::post('/accounts/switch/contractor/final', [authController::class, 'switchContractorFinal'])->middleware('throttle:10,1');
Route::post('/accounts/switch/owner/step1', [authController::class, 'switchOwnerStep1'])->middleware('throttle:10,1');
Route::post('/accounts/switch/owner/step2', [authController::class, 'switchOwnerStep2'])->middleware('throttle:10,1');
Route::post('/accounts/switch/owner/final', [authController::class, 'switchOwnerFinal'])->middleware('throttle:10,1');

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
// Owner milestone item completion
Route::post('/owner/milestone-items/{itemId}/complete', [\App\Http\Controllers\both\milestoneController::class, 'apiSetMilestoneItemComplete']);
// Owner milestone settlement due date
Route::post('/owner/milestone-items/{itemId}/settlement-due-date', [\App\Http\Controllers\both\milestoneController::class, 'setSettlementDueDateOwner']);
Route::post('/contractor/progress/approve/{progressId}', [\App\Http\Controllers\contractor\progressUploadController::class, 'approveProgress']);
Route::post('/contractor/progress/reject/{progressId}', [\App\Http\Controllers\contractor\progressUploadController::class, 'rejectProgress']);
// Owner-prefixed aliases for the same approve/reject methods (used by web modal)
Route::post('/owner/progress/{progressId}/approve', [\App\Http\Controllers\contractor\progressUploadController::class, 'approveProgress']);
Route::post('/owner/progress/{progressId}/reject', [\App\Http\Controllers\contractor\progressUploadController::class, 'rejectProgress']);

// Owner Project Posting Routes
Route::get('/owner/projects/create', [\App\Http\Controllers\owner\projectsController::class, 'showCreatePostPage']);
Route::post('/owner/projects', [\App\Http\Controllers\owner\projectsController::class, 'store']);
Route::get('/owner/projects/{projectId}/edit', [\App\Http\Controllers\owner\projectsController::class, 'showEditPostPage']);
Route::put('/owner/projects/{projectId}', [\App\Http\Controllers\owner\projectsController::class, 'update']);
Route::delete('/owner/projects/{projectId}', [\App\Http\Controllers\owner\projectsController::class, 'delete']);
Route::post('/owner/projects/{projectId}/bids/{bidId}/accept', [\App\Http\Controllers\owner\projectsController::class, 'acceptBid']);
Route::post('/owner/projects/{projectId}/bids/{bidId}/reject', [\App\Http\Controllers\owner\projectsController::class, 'rejectBid'])->name('owner.projects.bids.reject');
Route::post('/owner/milestones/{milestoneId}/approve', [milestoneController::class, 'webApproveMilestone']);
Route::post('/owner/milestones/{milestoneId}/reject', [milestoneController::class, 'webRejectMilestone']);
Route::post('/owner/projects/{projectId}/complete', [\App\Http\Controllers\owner\projectsController::class, 'completeProject']);
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
Route::get('/admin/dashboard/data', [dashboardController::class, 'getDashboardData'])->name('admin.dashboard.data');

// Analytics Routes
Route::get('/admin/analytics', [analyticsController::class, 'analytics'])->name('admin.analytics');
Route::get('/admin/analytics/timeline', [analyticsController::class, 'getProjectsTimelineData'])->name('admin.analytics.timeline');
Route::get('/admin/analytics/subscription', [analyticsController::class, 'subscriptionAnalytics'])->name('admin.analytics.subscription');
Route::get('/admin/analytics/subscription/revenue', [analyticsController::class, 'subscriptionRevenue'])->name('admin.analytics.subscription.revenue');
Route::get('/admin/analytics/user-activity', [analyticsController::class, 'userActivityAnalytics'])->name('admin.analytics.userActivity');
Route::get('/admin/analytics/project-performance', [analyticsController::class, 'projectPerformanceAnalytics'])->name('admin.analytics.projectPerformance');
Route::get('/admin/analytics/bid-completion', [analyticsController::class, 'bidCompletionAnalytics'])->name('admin.analytics.bidCompletion');
Route::get('/admin/analytics/reports', [analyticsController::class, 'reportsAnalytics'])->name('admin.analytics.reports');

// Analytics AJAX date-filter endpoints
Route::get('/admin/analytics/project-data', [analyticsController::class, 'getProjectAnalyticsData'])->name('admin.analytics.projectData');
Route::get('/admin/analytics/top-contractors-data', [analyticsController::class, 'getTopContractorsData'])->name('admin.analytics.topContractorsData');
Route::get('/admin/analytics/subscription-data', [analyticsController::class, 'getSubscriptionAnalyticsData'])->name('admin.analytics.subscriptionData');
Route::get('/admin/analytics/user-data', [analyticsController::class, 'getUserAnalyticsData'])->name('admin.analytics.userData');
Route::get('/admin/analytics/user-activity-feed', [analyticsController::class, 'getUserActivityFeedData'])->name('admin.analytics.userActivityFeed');
Route::get('/admin/analytics/bid-data', [analyticsController::class, 'getBidAnalyticsData'])->name('admin.analytics.bidData');

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
Route::get('/admin/user-management/contractor/available-owners', [userManagementController::class, 'getAvailablePropertyOwners'])->name('admin.userManagement.contractor.availableOwners');
Route::post('/admin/user-management/contractor/team-member/store', [userManagementController::class, 'addContractorTeamMember'])->name('admin.userManagement.contractor.teamMember.store');
Route::get('/admin/user-management/contractor/team-member/{id}/edit', [userManagementController::class, 'fetchContractorTeamMember'])->name('admin.userManagement.contractor.teamMember.edit');
Route::put('/admin/user-management/contractor/team-member/update/{id}', [userManagementController::class, 'updateContractorTeamMember'])->name('admin.userManagement.contractor.teamMember.update');
Route::post('/admin/user-management/contractor/team-member/{id}/suspend', [userManagementController::class, 'suspendContractorTeamMember'])->name('admin.userManagement.contractor.teamMember.suspend');
Route::delete('/admin/user-management/contractor/team-member/deactivate/{id}', [userManagementController::class, 'deactivateContractorTeamMember'])->name('admin.userManagement.contractor.teamMember.deactivate');
Route::patch('/admin/user-management/contractor/team-member/reactivate/{id}', [userManagementController::class, 'reactivateContractorTeamMember'])->name('admin.userManagement.contractor.teamMember.reactivate');
Route::post('/admin/user-management/contractor/team-member/{id}/cancel-invitation', [userManagementController::class, 'cancelInvitation'])->name('admin.userManagement.contractor.teamMember.cancelInvitation');
Route::post('/admin/user-management/contractor/team-member/{id}/reapply-invitation', [userManagementController::class, 'reapplyInvitation'])->name('admin.userManagement.contractor.teamMember.reapplyInvitation');
Route::post('/admin/user-management/contractor/representative/change', [userManagementController::class, 'changeContractorRepresentative'])->name('admin.userManagement.contractor.representative.change');
Route::get('/admin/user-management/verification-requests', [userManagementController::class, 'verificationRequest'])->name('admin.userManagement.verificationRequest');
Route::get('/admin/user-management/suspended-accounts', [userManagementController::class, 'suspendedAccounts'])->name('admin.userManagement.suspendedAccounts');
Route::post('/admin/user-management/suspended-accounts/reactivate', [userManagementController::class, 'reactivateSuspendedUser'])->name('admin.userManagement.suspendedAccounts.reactivate');

// Global Management Routes
Route::get('/admin/global-management/bid-management', [globalManagementController::class, 'bidManagement'])->name('admin.globalManagement.bidManagement');
Route::get('/admin/global-management/bid-management/files/{id}', [globalManagementController::class, 'getBidFiles'])->name('admin.globalManagement.bidFiles');
Route::put('/admin/global-management/bid-management/{id}', [globalManagementController::class, 'updateBid'])->name('admin.globalManagement.updateBid');
Route::delete('/admin/global-management/bid-management/{id}', [globalManagementController::class, 'deleteBid'])->name('admin.globalManagement.deleteBid');
Route::get('/admin/global-management/proof-of-payments', [globalManagementController::class, 'proofOfPayments'])->name('admin.globalManagement.proofOfpayments');
// Get single payment detail (AJAX – used by view modal)
Route::get('/admin/global-management/proof-of-payments/{id}', [globalManagementController::class, 'getPaymentDetail'])->name('admin.globalManagement.proofOfpayments.detail');
// Approve a payment
Route::post('/admin/global-management/proof-of-payments/{id}/verify', [globalManagementController::class, 'verifyPayment'])->name('admin.globalManagement.proofOfpayments.verify');
// Reject a payment (with reason)
Route::post('/admin/global-management/proof-of-payments/{id}/reject', [globalManagementController::class, 'rejectPayment'])->name('admin.globalManagement.proofOfpayments.reject');
// Soft-delete a payment (sets status = 'deleted')
Route::delete('/admin/global-management/proof-of-payments/{id}', [globalManagementController::class, 'deletePayment'])->name('admin.globalManagement.proofOfpayments.delete');
Route::put('/admin/global-management/proof-of-payments/{id}', [globalManagementController::class, 'updatePayment'])->name('admin.globalManagement.proofOfpayments.update');

Route::get('/admin/global-management/ai-management', [globalManagementController::class, 'aiManagement'])->name('admin.globalManagement.aiManagement');
Route::get('/admin/global-management/posting-management', [globalManagementController::class, 'postingManagement'])->name('admin.globalManagement.postingManagement');
Route::get('/admin/global-management/review-management', [globalManagementController::class, 'reviewManagement'])->name('admin.globalManagement.reviewManagement');
Route::post('/admin/global-management/review-management/{id}/delete', [globalManagementController::class, 'deleteReview'])->name('admin.globalManagement.deleteReview');

// Report Management
Route::get('/admin/global-management/report-management', [globalManagementController::class, 'reportManagement'])->name('admin.globalManagement.reportManagement');
Route::get('/admin/global-management/report-management/api', [globalManagementController::class, 'getReportsApi'])->name('admin.globalManagement.reportsApi');
Route::get('/admin/global-management/report-management/detail/{source}/{id}', [globalManagementController::class, 'getReportDetail'])->name('admin.globalManagement.reportDetail');
Route::get('/admin/global-management/report-management/user-profile/{userId}', [globalManagementController::class, 'getUserProfileCard'])->name('admin.globalManagement.userProfileCard');
Route::post('/admin/global-management/report-management/{source}/{id}/dismiss', [globalManagementController::class, 'dismissReport'])->name('admin.globalManagement.dismissReport');
Route::post('/admin/global-management/report-management/{source}/{id}/confirm', [globalManagementController::class, 'confirmReport'])->name('admin.globalManagement.confirmReport');
Route::post('/admin/global-management/report-management/{source}/{id}/status', [globalManagementController::class, 'updateReportStatus'])->name('admin.globalManagement.updateReportStatus');
Route::get('/admin/global-management/report-management/reporters', [globalManagementController::class, 'getReporterStatsApi'])->name('admin.globalManagement.reporterStatsApi');
Route::get('/admin/global-management/report-management/admin-search', [globalManagementController::class, 'adminSearch'])->name('admin.globalManagement.adminSearch');
Route::post('/admin/global-management/report-management/admin-action', [globalManagementController::class, 'adminDirectAction'])->name('admin.globalManagement.adminDirectAction');

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
Route::get('/admin/project-management/showcase-management', [projectManagementController::class, 'showcaseManagement'])->name('admin.projectManagement.showcaseManagement');
Route::get('/admin/project-management/showcase-management/{id}/details', [projectManagementController::class, 'getShowcaseDetails'])->name('admin.projectManagement.showcaseDetails');
Route::post('/admin/project-management/showcase-management/{id}/approve', [projectManagementController::class, 'approveShowcase'])->name('admin.projectManagement.approveShowcase');
Route::post('/admin/project-management/showcase-management/{id}/reject', [projectManagementController::class, 'rejectShowcase'])->name('admin.projectManagement.rejectShowcase');
Route::post('/admin/project-management/showcase-management/{id}/delete', [projectManagementController::class, 'deleteShowcase'])->name('admin.projectManagement.deleteShowcase');
Route::post('/admin/project-management/showcase-management/{id}/restore', [projectManagementController::class, 'restoreShowcase'])->name('admin.projectManagement.restoreShowcase');
Route::get('/admin/progress-feed', [\App\Http\Controllers\Admin\progressFeedController::class, 'index'])->name('admin.progressFeed');
Route::get('/admin/progress-feed/data', [\App\Http\Controllers\Admin\progressFeedController::class, 'fetch'])->name('admin.progressFeed.fetch');
Route::get('/admin/progress-feed/contractors', [\App\Http\Controllers\Admin\progressFeedController::class, 'contractors'])->name('admin.progressFeed.contractors');

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

// Timeline extension routes
Route::post('/admin/projects/{id}/extend-timeline', [projectManagementController::class, 'extendTimeline'])->name('admin.projects.extendTimeline');
Route::get('/admin/projects/{id}/affected-milestones', [projectManagementController::class, 'getAffectedMilestones'])->name('admin.projects.affectedMilestones');
Route::get('/admin/projects/{id}/pending-extensions', [projectManagementController::class, 'getPendingExtensions'])->name('admin.projects.pendingExtensions');
Route::post('/admin/projects/extensions/{extensionId}/approve', [projectManagementController::class, 'approveExtension'])->name('admin.projects.approveExtension');
Route::post('/admin/projects/extensions/{extensionId}/reject', [projectManagementController::class, 'rejectExtension'])->name('admin.projects.rejectExtension');
Route::post('/admin/projects/extensions/{extensionId}/request-revision', [projectManagementController::class, 'requestRevision'])->name('admin.projects.requestRevision');

// Bulk date adjustment routes
Route::post('/admin/projects/{id}/bulk-adjust-dates', [projectManagementController::class, 'bulkAdjustDates'])->name('admin.projects.bulkAdjustDates');
Route::get('/admin/projects/{id}/preview-bulk-adjustment', [projectManagementController::class, 'previewBulkAdjustment'])->name('admin.projects.previewBulkAdjustment');

// Payment history route
Route::get('/admin/projects/{id}/payment-history', [projectManagementController::class, 'getPaymentHistory'])->name('admin.projects.paymentHistory');

// Notification redirect — marks as read and 302s to the contextual page
Route::get('/notifications/{id}/redirect', [\App\Http\Controllers\both\notificationController::class, 'redirect'])->name('notifications.redirect');

// Settings Routes
Route::get('/admin/settings/notifications', function () {
    return view('admin.settings.notifications');
})->name('admin.settings.notifications');

// NOTE: The main admin/settings/security routes are defined earlier with AdminAuthMiddleware
// This is a fallback route - but we should NOT have it without the middleware
// Commenting this out to avoid duplicate route definitions without middleware
// Route::get('/admin/settings/security', function () {
//     return view('admin.settings.security');
// })->name('admin.settings.security');

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
    Route::get('/api/admin/management/payments/{id}', [globalManagementController::class, 'getPaymentDetail'])->name('api.admin.payment.detail');
    Route::delete('/api/admin/management/payments/{id}', [globalManagementController::class, 'deletePayment'])->name('api.admin.payment.delete');

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
    Route::get('admin/analytics/user-activity/feed', [analyticsController::class, 'getUserActivityFeed'])
        ->name('admin.analytics.userActivity.feed');
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

// Admin Notification Actions

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    Route::post('/admin/notifications/announcement', [AdminController::class, 'sendAnnouncement'])->name('admin.sendAnnouncement');
    Route::post('/admin/notifications/targeted', [AdminController::class, 'sendTargetedNotification'])->name('admin.sendTargetedNotification');
});

// ---------------------------------------------------------------
// DEV ONLY: Notification test panel (accessible without auth)
// ---------------------------------------------------------------
Route::get('/dev/test-notifications', [TestNotificationsController::class, 'index'])->name('test.notifications');
Route::post('/dev/test-notifications/run', [TestNotificationsController::class, 'run'])->name('test.notifications.run');
Route::post('/dev/test-notifications/clear-dedup', [TestNotificationsController::class, 'clearDedup'])->name('test.notifications.clear-dedup');

// ---------------------------------------------------------------
// DEV ONLY: Error page testing routes
// ---------------------------------------------------------------
Route::get('/dev/test-404', function () {
    abort(404);
})->name('test.404');

Route::get('/dev/test-403', function () {
    abort(403);
})->name('test.403');

Route::get('/dev/test-500', function () {
    abort(500);
})->name('test.500');



// ── Fallback Route - Catch all undefined routes ──────────────────
// This must be the LAST route defined
Route::fallback(function () {
    // Log suspicious access attempts for security monitoring
    \Log::warning('404 - Route not found', [
        'url' => request()->fullUrl(),
        'method' => request()->method(),
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent()
    ]);

    abort(404);
});
