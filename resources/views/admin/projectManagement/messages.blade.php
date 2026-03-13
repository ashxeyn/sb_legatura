<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @php
    $currentUser = session('user') ?? auth()->user();
    // Admin users: admin_id is VARCHAR 'ADMIN-1' - extract numeric part for Pusher subscription
    // Regular users: user_id or id
    $userId = null;
    if ($currentUser && isset($currentUser->admin_id)) {
        $userId = (int) preg_replace('/[^0-9]/', '', $currentUser->admin_id);
    } else {
        $userId = $currentUser->user_id ?? ($currentUser->id ?? null);
    }
    $apiToken = '';
    if ($currentUser && method_exists($currentUser, 'currentAccessToken')) {
        $token = $currentUser->currentAccessToken();
        $apiToken = $token ? $token->plainTextToken : '';
    }
  @endphp
  <meta name="api-token" content="{{ $apiToken }}">
  <meta name="user-id" content="{{ $userId ?? '' }}">
  <title>Messages - Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

  <!-- Laravel Echo & Pusher for Real-time Chat -->
  <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/projectManagement/messages.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>

  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>

  @if(env('PUSHER_APP_KEY'))
  <script>
    // Initialize Laravel Echo with Pusher (Session-based authentication for web dashboard)
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ env("PUSHER_APP_KEY") }}',
        cluster: '{{ env("PUSHER_APP_CLUSTER", 'mt1') }}',
        forceTLS: true,
        encrypted: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        },
        // Enable cookies for session-based authentication
        authorizer: (channel, options) => {
            return {
                authorize: (socketId, callback) => {
                    fetch(options.authEndpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Socket-ID': socketId
                        },
                        credentials: 'same-origin', // Important: sends cookies
                        body: JSON.stringify({
                            socket_id: socketId,
                            channel_name: channel.name
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Authorization failed: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        callback(null, data);
                    })
                    .catch(error => {
                        // console.warn('Pusher auth failed (fallback to polling):', error);
                        callback(error, null);
                    });
                }
            };
        }
    });
  </script>
  @endif
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1">
      @include('admin.layouts.topnav', [
          'pageTitle' => 'Messages',
          'hideSearch' => true,
          'afterNotifications' => '<button id="composeBtn" class="hidden md:inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold text-sm shadow-md hover:shadow-lg transition-all"><i class="fi fi-rr-edit"></i><span>Compose</span></button>',
      ])

      <!-- Content -->
      <section class="px-8 py-8 space-y-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Total Suspended Chats -->
          <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Total Suspended Chats</p>
                <h3 id="totalSuspended" class="text-4xl font-bold text-gray-800">0</h3>
              </div>
              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-red-100 to-rose-200 flex items-center justify-center">
                <i class="fi fi-sr-comment-slash text-2xl text-red-600"></i>
              </div>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="flex items-center gap-1 text-emerald-600 font-semibold">
                <i class="fi fi-rr-arrow-trend-up"></i>
                <span>15%</span>
              </span>
              <span class="text-gray-400">vs last month</span>
            </div>
          </div>

          <!-- Active Conversations -->
          <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Active Conversations</p>
                <h3 id="activeConversations" class="text-4xl font-bold text-gray-800">0</h3>
              </div>
              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 flex items-center justify-center">
                <i class="fi fi-sr-messages text-2xl text-emerald-600"></i>
              </div>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="flex items-center gap-1 text-emerald-600 font-semibold">
                <i class="fi fi-rr-arrow-trend-up"></i>
                <span>15%</span>
              </span>
              <span class="text-gray-400">vs last month</span>
            </div>
          </div>

          <!-- Flagged Conversations -->
          <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Flagged Conversations</p>
                <h3 id="flaggedMessages" class="text-4xl font-bold text-gray-800">0</h3>
              </div>
              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-amber-100 to-orange-200 flex items-center justify-center">
                <i class="fi fi-sr-flag text-2xl text-amber-600"></i>
              </div>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="flex items-center gap-1 text-red-600 font-semibold">
                <i class="fi fi-rr-arrow-trend-down"></i>
                <span>8%</span>
              </span>
              <span class="text-gray-400">vs last month</span>
            </div>
          </div>

        </div>

        <!-- Messages Interface -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden" style="height: calc(100vh - 420px); min-height: 600px;">
          <div class="flex h-full">
            <!-- Conversations List -->
            <div class="w-full lg:w-1/3 border-r border-gray-200 flex flex-col">
              <!-- Pusher Connection Status -->
              <div class="px-4 pt-3">
                <div id="pusherStatus" class="mb-3 px-3 py-2 rounded-lg text-xs font-medium hidden">
                  <span class="flex items-center gap-2">
                    <span class="status-dot w-2 h-2 rounded-full"></span>
                    <span class="status-text"></span>
                  </span>
                </div>
              </div>
              <!-- Filter Tabs -->
              <div class="px-4 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                <div class="flex items-center gap-2 bg-white rounded-xl p-1 shadow-sm">
                  <button class="filter-tab active flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all" data-filter="all">
                    All <span class="ml-1 text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">0</span>
                  </button>
                  <button class="filter-tab flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all" data-filter="flagged">
                    Flagged <span class="ml-1 text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">0</span>
                  </button>
                  <button class="filter-tab flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all" data-filter="suspended">
                    Suspended <span class="ml-1 text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">0</span>
                  </button>
                </div>
              </div>

              <!-- Search -->
              <div class="px-4 py-3 border-b border-gray-200">
                <div class="relative">
                  <input type="text" id="searchInput" placeholder="Search conversations..." class="w-full px-4 py-2.5 pl-10 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition text-sm">
                  <i class="fi fi-rr-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
              </div>

              <!-- Conversation Items -->
              <div class="flex-1 overflow-y-auto" id="conversationList">
                <!-- Conversation items will be rendered here -->
              </div>
            </div>

            <!-- Message Details Panel -->
            <div class="hidden lg:flex flex-col flex-1" id="messagePanel">
              <!-- Empty State -->
              <div class="flex-1 flex items-center justify-center text-center p-8" id="emptyState">
                <div>
                  <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                    <i class="fi fi-sr-comment-info text-5xl text-indigo-500"></i>
                  </div>
                  <h3 class="text-xl font-semibold text-gray-800 mb-2">Select a Conversation</h3>
                  <p class="text-gray-500">Choose a conversation from the list to view messages and details</p>
                </div>
              </div>

              <!-- Message Content (hidden initially) -->
              <div class="hidden flex-col h-full" id="messageContent">
                <!-- Conversation Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                      <div class="relative" id="headerAvatar">
                        <img id="selectedAvatar" src="" alt="Avatar" class="w-12 h-12 rounded-full object-cover ring-2 ring-white shadow">
                        <span class="avatar-status offline"></span>
                      </div>
                      <div>
                        <h3 id="selectedName" class="font-semibold text-gray-800 text-lg"></h3>
                        <p id="selectedProject" class="text-sm text-gray-500"></p>
                      </div>
                    </div>
                    <!-- Admin Action Buttons -->
                    <div class="flex items-center gap-2">
                      <button id="suspendConversationBtn" class="hidden px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i class="fi fi-rr-ban"></i>
                        <span>Suspend</span>
                      </button>
                      <button id="restoreConversationBtn" class="hidden px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i class="fi fi-rr-check-circle"></i>
                        <span>Unsuspend</span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-1 overflow-y-auto p-6 bg-gray-50" id="messagesDisplay">
                  <!-- Messages will be rendered here dynamically -->
                </div>

                <!-- Message Input -->
                <div id="messageInputContainer" class="px-6 py-4 border-t border-gray-200 bg-white">
                  <div class="flex items-center gap-3">
                    <button id="attachmentBtn" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition text-gray-600">
                      <i class="fi fi-rr-paperclip-vertical text-xl"></i>
                    </button>
                    <input type="file" id="attachmentInput" multiple accept="image/*,.pdf,.doc,.docx,.txt" class="hidden">

                    <textarea
                      id="messageInput"
                      rows="1"
                      class="flex-1 px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none text-sm"
                      placeholder="Type your message..."
                      style="max-height: 120px;"
                    ></textarea>

                    <button id="sendMessageBtn" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                      <i class="fi fi-rr-paper-plane"></i>
                      Send
                    </button>
                  </div>

                  <!-- File Preview Area -->
                  <div id="filePreviewArea" class="hidden mt-3 flex flex-wrap gap-2"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Flag Confirmation Modal -->
      <div id="flagConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-amber-500 to-orange-500">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center animate-pulse-slow">
                  <i class="fi fi-sr-flag text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Flag This Conversation?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-amber-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-amber-900 font-semibold mb-1">You are about to flag this conversation</p>
                <p class="text-xs text-amber-800">Flagged conversations will be marked for review. The users will not be notified of this action.</p>
              </div>
            </div>

            <div class="space-y-3">
              <div>
                <label class="block text-sm font-semibold text-gray-800 mb-2">Reason for Flagging *</label>
                <select id="flagReason" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition">
                  <option value="">Select a reason...</option>
                  <option value="spam">Spam or Unwanted Content</option>
                  <option value="harassment">Harassment or Abuse</option>
                  <option value="inappropriate">Inappropriate Language</option>
                  <option value="scam">Potential Scam</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div id="otherReasonContainer" class="hidden">
                <label class="block text-sm font-semibold text-gray-800 mb-2">Specify Other Reason *</label>
                <input type="text" id="otherReasonText" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition" placeholder="Please specify the reason...">
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-800 mb-2">Additional Notes (Optional)</label>
                <textarea id="flagNotes" rows="3" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition resize-none" placeholder="Provide additional context about why you're flagging this conversation..."></textarea>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-all transform hover:scale-105">Cancel</button>
              <button id="confirmFlagBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                <span class="flex items-center gap-2">
                  <i class="fi fi-rr-flag"></i>
                  <span>Flag Conversation</span>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Suspend Confirmation Modal -->
      <div id="suspendConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-red-600 to-rose-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center animate-pulse-slow">
                  <i class="fi fi-sr-ban text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Suspend This Conversation?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
              <i class="fi fi-rr-triangle-warning text-red-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-red-900 font-semibold mb-1">Warning: This is a serious action</p>
                <p class="text-xs text-red-800">Suspending this conversation will prevent both parties from sending further messages. They will be notified of the suspension.</p>
              </div>
            </div>

            <div class="flex items-start gap-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
              <i class="fi fi-rr-info text-blue-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-blue-900 font-semibold mb-1">Progressive Ban System</p>
                <div class="text-xs text-blue-800 space-y-1">
                  <p>• 1st offense: 7 days ban</p>
                  <p>• 2nd offense: 15 days ban</p>
                  <p>• 3rd offense: 30 days ban</p>
                  <p>• 4th offense: Permanent ban</p>
                </div>
              </div>
            </div>

            <div class="space-y-3">
              <div>
                <label class="block text-sm font-semibold text-gray-800 mb-2">Reason for Suspension *</label>
                <select id="suspendReason" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-300 focus:border-red-300 transition">
                  <option value="">Select a reason...</option>
                  <option value="violation">Terms of Service Violation</option>
                  <option value="harassment">Harassment or Threatening Behavior</option>
                  <option value="fraud">Fraudulent Activity</option>
                  <option value="spam">Repeated Spam</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div id="otherSuspendReasonContainer" class="hidden">
                <label class="block text-sm font-semibold text-gray-800 mb-2">Specify Other Reason *</label>
                <input type="text" id="otherSuspendReasonText" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-300 focus:border-red-300 transition" placeholder="Please specify the reason...">
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-800 mb-2">Additional Details (Optional)</label>
                <textarea id="suspendNotes" rows="3" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Provide additional context about the suspension..."></textarea>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-all transform hover:scale-105">Cancel</button>
              <button id="confirmSuspendBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                <span class="flex items-center gap-2">
                  <i class="fi fi-rr-ban"></i>
                  <span>Suspend Conversation</span>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Unsuspend Confirmation Modal -->
      <div id="restoreConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 to-teal-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center animate-pulse-slow">
                  <i class="fi fi-sr-check-circle text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Unsuspend This Conversation?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-emerald-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-emerald-900 font-semibold mb-1">You are about to unsuspend this conversation</p>
                <p class="text-xs text-emerald-800">Unsuspending will allow both parties to resume messaging. The suspension will be lifted immediately.</p>
              </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
              <div class="space-y-2 text-sm">
                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Conversation ID:</span>
                  <span class="font-semibold text-gray-800" id="restoreConvId">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Participants:</span>
                  <span class="font-semibold text-gray-800" id="restoreConvName">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Current Status:</span>
                  <span class="font-semibold text-red-600" id="restoreConvStatus">Suspended</span>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-all transform hover:scale-105">Cancel</button>
              <button id="confirmRestoreBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                <span class="flex items-center gap-2">
                  <i class="fi fi-rr-check-circle"></i>
                  <span>Unsuspend Conversation</span>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Unflag Confirmation Modal -->
      <div id="unflagConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center animate-pulse-slow">
                  <i class="fi fi-sr-flag text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Remove Flag?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-indigo-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-indigo-900 font-semibold mb-1">Remove flag from this conversation</p>
                <p class="text-xs text-indigo-800">This will clear the flag status. The conversation will no longer appear in the flagged filter.</p>
              </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
              <div class="space-y-2 text-sm">
                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Current Status:</span>
                  <span class="font-semibold text-amber-600">Flagged</span>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-all transform hover:scale-105">Cancel</button>
              <button id="confirmUnflagBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                <span class="flex items-center gap-2">
                  <i class="fi fi-rr-check"></i>
                  <span>Remove Flag</span>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Unflag Message Confirmation Modal -->
      <div id="unflagMessageConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-amber-500 to-orange-500">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center animate-pulse-slow">
                  <i class="fi fi-sr-check-circle text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Unflag This Message?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-amber-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-amber-900 font-semibold mb-1">You are about to unflag this message</p>
                <p class="text-xs text-amber-800">This will remove the flag and restore the message to its normal appearance. The message will no longer be marked as suspicious.</p>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-all transform hover:scale-105">Cancel</button>
              <button id="confirmUnflagMessageBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                <span class="flex items-center gap-2">
                  <i class="fi fi-rr-check"></i>
                  <span>Unflag Message</span>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Compose New Message Modal -->
      <div id="composeModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-hidden flex flex-col">
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                <i class="fi fi-sr-messages text-white text-lg"></i>
              </div>
              <h3 class="text-lg font-bold text-white">Compose New Message</h3>
            </div>
            <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
          </div>
          <div class="p-6 space-y-4 overflow-y-auto flex-1">
            <!-- Recipients & Context -->
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">To *</label>
                <div id="composeRecipientsWrapper" class="compose-recipients-wrapper w-full flex flex-wrap gap-2 px-3 py-2 border-2 border-gray-200 rounded-xl focus-within:ring-2 focus-within:ring-indigo-300 focus-within:border-indigo-300 bg-white cursor-text">
                  <input id="composeRecipientSearch" type="text" class="flex-1 min-w-[140px] outline-none text-sm bg-transparent" placeholder="Search for a person..." autocomplete="off">
                </div>
                <div id="composeRecipientDropdown" class="compose-recipient-dropdown hidden mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto text-sm"></div>
                <p class="text-xs text-gray-500 mt-1">Start typing to search for recipients</p>
              </div>
            </div>

            <!-- Message & Attachments -->
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Message *</label>
                <textarea id="composeMessage" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none text-sm leading-relaxed" placeholder="Write your message here..."></textarea>
                <p id="composeCharCount" class="text-xs text-gray-400 mt-1">0 / 1000</p>
              </div>
              <div>
                <div class="flex items-center justify-between mb-2">
                  <label class="block text-sm font-semibold text-gray-700">Attachments</label>
                  <span class="text-xs text-gray-400">Images, PDF, DOC (max 5MB each)</span>
                </div>
                <div id="composeAttachmentDrop" class="compose-attachment-drop border-2 border-dashed border-gray-300 rounded-xl p-4 flex flex-col items-center justify-center gap-2 text-center cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                  <i class="fi fi-rr-folder-upload text-xl text-indigo-500"></i>
                  <p class="text-xs text-gray-600">Drag & drop files here or click to browse</p>
                  <input id="composeAttachmentInput" type="file" class="hidden" multiple accept="image/*,.pdf,.doc,.docx">
                </div>
                <div id="composeAttachmentPreview" class="compose-attachment-preview mt-3 flex flex-wrap gap-2"></div>
              </div>
            </div>
          </div>

          <!-- Actions (Fixed Footer) -->
          <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 text-xs text-gray-500">
                <i class="fi fi-rr-shield-check text-indigo-500"></i>
                <span>Messages are monitored for policy compliance.</span>
              </div>
              <div class="flex items-center gap-3">
                <button class="modal-close px-4 py-2 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition text-sm">Cancel</button>
                <button id="sendComposeBtn" class="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 text-sm">
                  <i class="fi fi-rr-paper-plane"></i>
                  <span>Send Message</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </main>


  <script src="{{ asset('js/message/messages.js') }}" defer></script>

</body>

</html>
