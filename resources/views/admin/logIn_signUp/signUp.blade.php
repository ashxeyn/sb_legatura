<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Signup - Legatura</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/logIn_signUp/signUp.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="background: linear-gradient(135deg, #ffffff 0%, #f3f4f6 50%, #e5e7eb 100%);">
    
    <!-- Background Pattern -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 left-0 w-96 h-96 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob" style="background-color: #3b82f6;"></div>
        <div class="absolute top-0 right-0 w-96 h-96 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob animation-delay-2000" style="background-color: #6366f1;"></div>
        <div class="absolute bottom-0 left-1/2 w-96 h-96 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob animation-delay-4000" style="background-color: #8b5cf6;"></div>
    </div>

    <!-- Signup Container -->
    <div class="relative w-full max-w-6xl mx-auto" style="min-height: 600px;">
        <div class="grid md:grid-cols-2 gap-0 bg-white/10 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden border border-white/20">
            
            <!-- Left Side - Signup Form -->
            <div class="p-8 md:p-12 bg-white">
                <div class="max-w-md mx-auto">
                    <!-- Mobile Logo -->
                    <div class="md:hidden mb-8 text-center">
                        <div class="inline-flex items-center space-x-2 mb-2">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shield-alt text-white text-xl"></i>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-800">Legatura Admin</h1>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">Create Account</h2>
                        <p class="text-gray-600">Join the Legatura administrative team</p>
                    </div>

                    <!-- Alert Messages -->
                    <div id="alert-container" class="mb-6"></div>

                    <!-- Signup Form -->
                    <form id="signupForm" class="space-y-4">
                        @csrf

                        <!-- Full Name -->
                        <div>
                            <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-user mr-2 text-blue-600"></i>Full Name
                            </label>
                            <input 
                                type="text" 
                                id="full_name" 
                                name="full_name" 
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 outline-none"
                                placeholder="John Doe"
                                required
                            >
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-envelope mr-2 text-blue-600"></i>Email Address
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 outline-none"
                                placeholder="admin@legatura.com"
                                required
                            >
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-lock mr-2 text-blue-600"></i>Password
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 outline-none pr-12"
                                    placeholder="Create a strong password"
                                    required
                                >
                                <button type="button" class="password-toggle-button" id="togglePassword">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-lock mr-2 text-blue-600"></i>Confirm Password
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 outline-none pr-12"
                                    placeholder="Confirm your password"
                                    required
                                >
                                <button type="button" class="password-toggle-button" id="togglePasswordConfirm">
                                    <i class="fas fa-eye" id="eyeIconConfirm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div>
                            <label class="flex items-start space-x-3 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    id="terms" 
                                    name="terms" 
                                    class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5"
                                    required
                                >
                                <span class="text-sm text-gray-700">
                                    I agree to the <a href="#" class="text-blue-600 hover:text-blue-700 font-medium">Terms & Conditions</a> and <a href="#" class="text-blue-600 hover:text-blue-700 font-medium">Privacy Policy</a>
                                </span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            id="signupBtn"
                            class="w-full text-white font-semibold py-2.5 px-6 rounded-lg transition-all duration-200 shadow-lg create-account-button"
                        >
                            <span>CREATE ACCOUNT</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Side - Branding -->
            <div class="hidden md:flex flex-col justify-center items-center relative overflow-hidden branding-section">
                <div class="branding-container">
                    <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura Logo" class="logo-image">
                    <img src="{{ asset('img/Legatura.svg') }}" alt="Legatura" class="name-image">
                    <p class="tagline-text">
                        Connect with Skilled Professionals and Trusted Experts for Efficient and Successful Project Delivery
                    </p>
                    <a href="{{ url('/admin/login') }}" class="login-button">
                        <span>LOGIN</span>
                    </a>
                </div>
            </div>

        </div>
    </div>

    <script src="{{ asset('js/admin/logIn_signUp/signUp.js') }}"></script>
</body>
</html>
