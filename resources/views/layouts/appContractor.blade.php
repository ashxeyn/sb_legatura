<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Legatura')</title>
    <!-- Tailwind CSS 4 (local) -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- Navbar CSS -->
    <link rel="stylesheet" href="{{ asset('css/partials/navbar_Contractor.css') }}">
    <!-- Footer CSS -->
    <link rel="stylesheet" href="{{ asset('css/partials/footer.css') }}">
    <!-- Edit Profile Modal CSS -->
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorAccountsettings_Modal.css') }}?v={{ time() }}">
    <!-- Help & Support Modal CSS -->
    <link rel="stylesheet" href="{{ asset('css/partials/helpSupport_Modal.css') }}?v={{ time() }}">
    <!-- Contact Us Modal CSS -->
    <link rel="stylesheet" href="{{ asset('css/partials/contactUs_Modal.css') }}?v={{ time() }}">
    <!-- Privacy Policy Modal CSS -->
    <link rel="stylesheet" href="{{ asset('css/partials/privacyPolicy_Modal.css') }}?v={{ time() }}">
    <!-- Switch Account Modal CSS -->
    <link rel="stylesheet" href="{{ asset('css/partials/switchAccount_Modal.css') }}?v={{ time() }}">
    <!-- Switch to Contractor Modal CSS -->
    <link rel="stylesheet" href="{{ asset('css/partials/switchAccount_CONTRACTOR_Modal.css') }}?v={{ time() }}">
    <!-- Switch to Owner Modal CSS -->
    <link rel="stylesheet" href="{{ asset('css/partials/switchAccount_OWNER_Modal.css') }}?v={{ time() }}">
    <!-- Security Modal CSS -->
    <link rel="stylesheet" href="{{ asset('css/partials/security_Modal.css') }}?v={{ time() }}">
    <!-- Settings Modal CSS -->
    <link rel="stylesheet" href="{{ asset('css/partials/settings_Modal.css') }}?v={{ time() }}">
    <!-- Subscription Modal CSS -->
    <link rel="stylesheet" href="{{ asset('css/partials/subscription_Modal.css') }}?v={{ time() }}">
    <!-- Flaticon Icons -->
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    @yield('extra_css')
</head>
<body class="min-h-screen flex flex-col">

    @include('partials.navbar_Contractor')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer')

    <!-- Edit Profile Information Modal (Available on all pages) -->
    @include('contractor.contractor_Modals.contractorAccountsettings_Modal')
    
    <!-- Help & Support Modal (Available on all pages) -->
    @include('partials.helpSupport_Modal')
    
    <!-- Contact Us Modal (Available on all pages) -->
    @include('partials.contactUs_Modal')
    
    <!-- Privacy Policy Modal (Available on all pages) -->
    @include('partials.privacyPolicy_Modal')
    
    <!-- Switch Account Modal (Available on all pages) -->
    @include('partials.switchAccount_Modal')
    
    <!-- Switch to Contractor Form Modal -->
    @include('partials.switchAccount_CONTRACTOR_Modal')
    
    <!-- Switch to Property Owner Form Modal -->
    @include('partials.switchAccount_OWNER_Modal')
    
    <!-- Security Modal (Available on all pages) -->
    @include('partials.security_Modal')
    
    <!-- Settings Modal (Available on all pages) -->
    @include('partials.settings_Modal')
    
    <!-- Subscription Modal (Available on all pages) -->
    @include('partials.subscription_Modal')

    <!-- Navbar JS -->
    <script src="{{ asset('js/partials/navbar_Contractor.js') }}"></script>
    <!-- Edit Profile Modal JS -->
    <script src="{{ asset('js/contractor/contractor_Modals/contractorAccountsettings_Modal.js') }}?v={{ time() }}"></script>
    <!-- Help & Support Modal JS -->
    <script src="{{ asset('js/partials/helpSupport_Modal.js') }}?v={{ time() }}"></script>
    <!-- Contact Us Modal JS -->
    <script src="{{ asset('js/partials/contactUs_Modal.js') }}?v={{ time() }}"></script>
    <!-- Privacy Policy Modal JS -->
    <script src="{{ asset('js/partials/privacyPolicy_Modal.js') }}?v={{ time() }}"></script>
    <!-- Switch Account Modal JS -->
    <script src="{{ asset('js/partials/switchAccount_Modal.js') }}?v={{ time() }}"></script>
    <!-- Switch to Contractor Modal JS -->
    <script src="{{ asset('js/partials/switchAccount_CONTRACTOR_Modal.js') }}?v={{ time() }}"></script>
    <!-- Switch to Owner Modal JS -->
    <script src="{{ asset('js/partials/switchAccount_OWNER_Modal.js') }}?v={{ time() }}"></script>
    <!-- Security Modal JS -->
    <script src="{{ asset('js/partials/security_Modal.js') }}?v={{ time() }}"></script>
    <!-- Settings Modal JS -->
    <script src="{{ asset('js/partials/settings_Modal.js') }}?v={{ time() }}"></script>
    <!-- Subscription Modal JS -->
    <script src="{{ asset('js/partials/subscription_Modal.js') }}?v={{ time() }}"></script>
    @yield('extra_js')
</body>
</html>