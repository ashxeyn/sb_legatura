<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/settings/notifications.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>


  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>


</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1">
      @include('admin.layouts.topnav', ['pageTitle' => 'Notifications'])

      <section class="px-8 py-8">
        <!-- Page Intro -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h2 class="text-xl font-semibold text-gray-800">Notification Preferences</h2>
            <p class="text-sm text-gray-500">Choose what to be notified about and how you want to receive it.</p>
          </div>
          <button id="resetDefaultsBtn" class="px-4 py-2 rounded-lg border-2 border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition">
            Reset to defaults
          </button>
        </div>

        <!-- Settings Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <!-- General -->

          <!-- User Activity -->
          <div class="setting-card bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b bg-gradient-to-r from-indigo-500 to-violet-600 text-white">
              <div class="flex items-center gap-2 font-semibold"><i class="fi fi-ss-users"></i><span>User Activity Notifications</span></div>
              <p class="text-xs opacity-80 mt-1">Account and security related updates</p>
            </div>
            <div class="p-6 space-y-4">
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">New User Registration</div>
                  <div class="text-xs text-gray-500">Get notified when new users sign up.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="user_registered">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Failed Login Attempt</div>
                  <div class="text-xs text-gray-500">Security alert for repeated failed attempts.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="failed_login_attempt">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Project Reported</div>
                  <div class="text-xs text-gray-500">Alert when a project is reported by users.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="project_reported">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Profile Updated</div>
                  <div class="text-xs text-gray-500">Notify when a user changes account details.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="profile_updated">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Password Reset Requested</div>
                  <div class="text-xs text-gray-500">Alert for password reset requests and completions.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="password_reset">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Email Verified</div>
                  <div class="text-xs text-gray-500">Notify when a user verifies their email address.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="email_verified">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Account Suspended/Unsuspended</div>
                  <div class="text-xs text-gray-500">Alert when moderation changes account status.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="account_status_changed">
                  <span class="slider"></span>
                </label>
              </div>
            </div>
          </div>

          <!-- Channels -->
          <div class="bg-transparent space-y-6">
            <div class="setting-card bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
              <div class="px-6 py-4 border-b bg-gradient-to-r from-emerald-500 to-teal-600 text-white">
                <div class="flex items-center gap-2 font-semibold"><i class="fi fi-ss-megaphone"></i><span>Notification Channels</span></div>
                <p class="text-xs opacity-80 mt-1">Choose how you receive alerts</p>
              </div>
              <div class="p-6 space-y-4">
                <div class="setting-row">
                  <div>
                    <div class="font-medium text-gray-800">Email Notifications</div>
                    <div class="text-xs text-gray-500">Receive important updates in your inbox.</div>
                  </div>
                  <label class="switch">
                    <input type="checkbox" class="setting-toggle" data-setting="channel_email">
                    <span class="slider"></span>
                  </label>
                </div>
                <div class="setting-row">
              </div>
            </div>
          </div>
        </div>

        <!-- Sticky Save Bar -->
        <div id="saveBar" class="save-bar hidden fixed left-1/2 -translate-x-1/2 bottom-6 z-40">
          <div class="bg-white border border-gray-200 rounded-xl shadow-2xl px-4 py-3 flex items-center gap-3">
            <span class="text-sm text-gray-700">You have unsaved changes</span>
            <button id="saveSettingsBtn" class="px-4 py-2 text-sm rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md">Save changes</button>
          </div>
        </div>
      </section>
    </main>


  <script src="{{ asset('js/admin/settings/notifications.js') }}" defer></script>

</body>

</html>
