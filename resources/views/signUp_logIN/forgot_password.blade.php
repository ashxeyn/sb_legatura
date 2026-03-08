<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Legatura | Forgot Password</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/signUp_logIN/logIn.css') }}">
    <link rel='stylesheet'
        href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <style>
        .forgot-steps {
            display: none;
        }

        .forgot-steps.active {
            display: block;
        }

        .otp-grid {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 20px 0;
        }

        .otp-digit {
            width: 48px;
            height: 56px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background: #f9fafb;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .otp-digit:focus {
            border-color: #f57c00;
            box-shadow: 0 0 0 3px rgba(245, 124, 0, 0.15);
            outline: none;
            background: #fff;
        }

        .otp-digit.success {
            border-color: #4caf50;
            background: #f0fdf4;
        }

        .step-indicator {
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            display: none;
            animation: slideInRight 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            max-width: 360px;
        }

        .toast-success {
            background: #4caf50;
        }

        .toast-error {
            background: #f44336;
        }

        .toast-info {
            background: #2196f3;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .password-requirements {
            display: none;
            margin-top: 8px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
            font-size: 12px;
        }

        .password-requirements p {
            margin: 4px 0;
            color: #ef4444;
            transition: color 0.2s;
        }

        .resend-link {
            color: #f57c00;
            cursor: pointer;
            text-decoration: underline;
            font-size: 13px;
        }

        .resend-link:hover {
            color: #e65100;
        }

        .resend-link[disabled] {
            color: #9ca3af;
            cursor: not-allowed;
            text-decoration: none;
        }

        .field-error-msg {
            color: #d32f2f;
            font-size: 12px;
            margin-top: 4px;
        }

        .back-to-login {
            color: #f57c00;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
            margin-bottom: 20px;
            justify-content: flex-start;
            align-self: flex-start;
            width: fit-content;
        }

        .back-to-login:hover {
            color: #e65100;
        }

        .send-reset-btn {
            margin-top: 24px;
        }
    </style>
</head>

<body class="min-h-screen">
    <div class="login-container">
        <div class="login-card">
            <!-- Back to Login Link -->
            <a href="/auth/gate/login" class="back-to-login">
                <i class="fi fi-rr-angle-small-left"></i>
                Back to Login
            </a>

            <div class="login-logo" aria-label="Legatura logo">
                <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="login-logo-img" loading="eager">
            </div>

            <!-- Toast -->
            <div id="toast" class="toast-notification"></div>

            <!-- ============ STEP 1: Enter Email ============ -->
            <div id="step-email" class="forgot-steps active">
                <div class="login-header">
                    <h1 class="login-title">Forgot Password?</h1>
                    <p class="login-subtitle">Enter your email to receive a reset code.</p>
                </div>

                <div class="form-group">
                    <label class="field">
                        <span class="field-icon" aria-hidden="true"><i class="fi fi-rr-envelope"></i></span>
                        <input type="email" id="resetEmail" placeholder="Your email address" required autofocus>
                    </label>
                    <p id="emailError" class="field-error-msg" style="display:none;"></p>
                </div>

                <button type="button" id="sendOtpBtn" class="btn btn-primary send-reset-btn">Send Reset Code</button>
            </div>

            <!-- ============ STEP 2: Enter OTP ============ -->
            <div id="step-otp" class="forgot-steps">
                <div class="login-header">
                    <h1 class="login-title">Enter Reset Code</h1>
                    <p class="login-subtitle" id="otpSubtitle">We sent a 6-digit code to your email.</p>
                </div>

                <div class="otp-grid" id="otpGrid">
                    <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]"
                        autocomplete="off">
                    <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]"
                        autocomplete="off">
                    <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]"
                        autocomplete="off">
                    <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]"
                        autocomplete="off">
                    <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]"
                        autocomplete="off">
                    <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]"
                        autocomplete="off">
                </div>
                <p id="otpError" class="field-error-msg" style="display:none;text-align:center;"></p>

                <button type="button" id="verifyOtpBtn" class="btn btn-primary" disabled>Verify Code</button>

                <div style="text-align:center;margin-top:12px;">
                    <span id="resendTimer" style="font-size:13px;color:#9ca3af;">Resend in <span
                            id="timerCount">60</span>s</span>
                    <a id="resendOtpBtn" class="resend-link" style="display:none;">Resend Code</a>
                </div>

                <div class="login-footer" style="margin-top:16px;">
                    <a href="#" id="backToEmail" class="link">← Change email</a>
                </div>
            </div>

            <!-- ============ STEP 3: New Password ============ -->
            <div id="step-password" class="forgot-steps">
                <div class="login-header">
                    <h1 class="login-title">Set New Password</h1>
                    <p class="login-subtitle">Choose a strong password for your account.</p>
                </div>

                <div class="form-group">
                    <label class="field">
                        <span class="field-icon" aria-hidden="true"><i class="fi fi-rr-lock"></i></span>
                        <input type="password" id="newPassword" placeholder="New Password" required>
                        <button type="button" class="toggle-visibility" id="toggleNew" aria-label="Show password">
                            <i class="fi fi-rr-eye eye-open"></i>
                            <i class="fi fi-rr-eye-crossed eye-closed" style="display:none"></i>
                        </button>
                    </label>
                    <div id="passwordRequirements" class="password-requirements">
                        <p data-req="min8">✕ At least 8 characters</p>
                        <p data-req="uppercase">✕ At least one uppercase letter</p>
                        <p data-req="number">✕ At least one number</p>
                        <p data-req="special">✕ At least one special character</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="field">
                        <span class="field-icon" aria-hidden="true"><i class="fi fi-rr-lock"></i></span>
                        <input type="password" id="confirmPassword" placeholder="Confirm Password" required>
                        <button type="button" class="toggle-visibility" id="toggleConfirm" aria-label="Show password">
                            <i class="fi fi-rr-eye eye-open"></i>
                            <i class="fi fi-rr-eye-crossed eye-closed" style="display:none"></i>
                        </button>
                    </label>
                    <p id="confirmError" class="field-error-msg" style="display:none;">Passwords do not match.</p>
                </div>

                <button type="button" id="resetPasswordBtn" class="btn btn-primary">Reset Password</button>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/signUp_logIN/forgot_password.js') }}"></script>
</body>

</html>
