<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @php
    $currentUser = session('user') ?? auth()->user();
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
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ env("PUSHER_APP_KEY") }}',
        cluster: '{{ env("PUSHER_APP_CLUSTER", "mt1") }}',
        forceTLS: true,
        encrypted: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        },
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
                        credentials: 'same-origin',
                        body: JSON.stringify({ socket_id: socketId, channel_name: channel.name })
                    })
                    .then(r => { if (!r.ok) throw new Error(`Auth failed: ${r.status}`); return r.json(); })
                    .then(data => callback(null, data))
                    .catch(err => callback(err, null));
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
      ])

      <!-- Content -->
      <section class="px-4 py-4 sm:px-6 sm:py-5 lg:px-8 lg:py-6 space-y-4">

        <!-- Page Header Row: compose button -->
        <div class="flex justify-end">
          <button id="composeBtn" class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-4 py-2 rounded-lg font-semibold text-sm shadow-sm hover:shadow-md transition transform hover:scale-[1.01]">
            <i class="fi fi-rr-edit"></i>
            <span>Compose</span>
          </button>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <!-- Total Suspended -->
          <div class="stat-card bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3 hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-sm flex-shrink-0">
              <i class="fi fi-sr-comment-slash text-white text-sm"></i>
            </div>
            <div>
              <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider">Suspended Chats</p>
              <p id="totalSuspended" class="text-xl font-bold text-gray-800 leading-tight">0</p>
            </div>
          </div>

          <!-- Active Conversations -->
          <div class="stat-card bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3 hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-sm flex-shrink-0">
              <i class="fi fi-sr-messages text-white text-sm"></i>
            </div>
            <div>
              <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider">Active Conversations</p>
              <p id="activeConversations" class="text-xl font-bold text-gray-800 leading-tight">0</p>
            </div>
          </div>

          <!-- Flagged -->
          <div class="stat-card bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3 hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-yellow-400 to-amber-500 flex items-center justify-center shadow-sm flex-shrink-0">
              <i class="fi fi-sr-flag text-white text-sm"></i>
            </div>
            <div>
              <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider">Flagged Conversations</p>
              <p id="flaggedMessages" class="text-xl font-bold text-gray-800 leading-tight">0</p>
            </div>
          </div>
        </div>

        <!-- Messages Interface -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" style="height: calc(100vh - 340px); min-height: 560px;">
          <div class="flex h-full">

            <!-- Conversations List -->
            <div class="w-full lg:w-1/3 border-r border-gray-200 flex flex-col">
              <!-- Pusher Status -->
              <div class="px-4 pt-3">
                <div id="pusherStatus" class="mb-2 px-3 py-1.5 rounded-lg text-xs font-medium hidden">
                  <span class="flex items-center gap-2">
                    <span class="status-dot w-2 h-2 rounded-full"></span>
                    <span class="status-text"></span>
                  </span>
                </div>
              </div>

              <!-- Filter Tabs -->
              <div class="px-4 py-3 border-b border-gray-200">
                <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                  <button class="filter-tab active flex-1 px-3 py-2 rounded-md text-xs font-semibold transition-all" data-filter="all">
                    All <span class="ml-1 text-[10px] bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded-full">0</span>
                  </button>
                  <button class="filter-tab flex-1 px-3 py-2 rounded-md text-xs font-semibold transition-all" data-filter="flagged">
                    Flagged <span class="ml-1 text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full">0</span>
                  </button>
                  <button class="filter-tab flex-1 px-3 py-2 rounded-md text-xs font-semibold transition-all" data-filter="suspended">
                    Suspended <span class="ml-1 text-[10px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full">0</span>
                  </button>
                </div>
              </div>

              <!-- Search -->
              <div class="px-4 py-2.5 border-b border-gray-200">
                <div class="relative">
                  <input type="text" id="searchInput" placeholder="Search conversations..."
                    class="w-full px-3 py-2 pl-9 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition text-xs bg-gray-50">
                  <i class="fi fi-rr-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                </div>
              </div>

              <!-- Conversation Items -->
              <div class="flex-1 overflow-y-auto" id="conversationList"></div>
            </div>

            <!-- Message Details Panel -->
            <div class="hidden lg:flex flex-col flex-1" id="messagePanel">
              <!-- Empty State -->
              <div class="flex-1 flex items-center justify-center text-center p-8" id="emptyState">
                <div>
                  <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-orange-100 to-orange-50 flex items-center justify-center">
                    <i class="fi fi-sr-comment-info text-4xl text-orange-400"></i>
                  </div>
                  <h3 class="text-base font-semibold text-gray-700 mb-1">Select a Conversation</h3>
                  <p class="text-xs text-gray-400">Choose a conversation from the list to view messages</p>
                </div>
              </div>

              <!-- Message Content (hidden initially) -->
              <div class="hidden flex-col h-full" id="messageContent">
                <!-- Conversation Header -->
                <div class="px-5 py-3.5 border-b border-gray-200 bg-white">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <div class="relative" id="headerAvatar">
                        <img id="selectedAvatar" src="" alt="Avatar" class="w-10 h-10 rounded-full object-cover ring-2 ring-white shadow-sm">
                        <span class="avatar-status offline"></span>
                      </div>
                      <div>
                        <h3 id="selectedName" class="font-semibold text-gray-800 text-sm leading-tight"></h3>
                        <p id="selectedProject" class="text-xs text-gray-500"></p>
                      </div>
                    </div>
                    <!-- Admin Action Buttons -->
                    <div class="flex items-center gap-2">
                      <button id="suspendConversationBtn" class="hidden px-3 py-1.5 rounded-lg bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold text-xs shadow-sm hover:shadow-md transition-all flex items-center gap-1.5">
                        <i class="fi fi-rr-ban text-xs"></i>
                        <span>Suspend</span>
                      </button>
                      <button id="restoreConversationBtn" class="hidden px-3 py-1.5 rounded-lg bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold text-xs shadow-sm hover:shadow-md transition-all flex items-center gap-1.5">
                        <i class="fi fi-rr-check-circle text-xs"></i>
                        <span>Unsuspend</span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-1 overflow-y-auto p-5 bg-gray-50" id="messagesDisplay"></div>

                <!-- Message Input -->
                <div id="messageInputContainer" class="px-5 py-3.5 border-t border-gray-200 bg-white">
                  <div class="flex items-center gap-2.5">
                    <button id="attachmentBtn" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 transition text-gray-500">
                      <i class="fi fi-rr-paperclip-vertical text-base"></i>
                    </button>
                    <input type="file" id="attachmentInput" multiple accept="image/*,.pdf,.doc,.docx,.txt" class="hidden">
                    <textarea id="messageInput" rows="1"
                      class="flex-1 px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition resize-none text-sm bg-gray-50"
                      placeholder="Type your message..."
                      style="max-height: 120px;"></textarea>
                    <button id="sendMessageBtn" class="px-4 py-2 rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold text-xs shadow-sm hover:shadow-md transition-all flex items-center gap-1.5">
                      <i class="fi fi-rr-paper-plane text-xs"></i>
                      Send
                    </button>
                  </div>
                  <div id="filePreviewArea" class="hidden mt-2.5 flex flex-wrap gap-2"></div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </section>

      <!-- Flag Confirmation Modal -->
      <div id="flagConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg z-10">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                <i class="fi fi-sr-flag text-white text-sm"></i>
              </div>
              <h3 class="text-sm font-bold text-white">Flag This Conversation?</h3>
            </div>
            <button class="modal-close text-white hover:text-orange-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
              <i class="fi fi-rr-cross text-lg"></i>
            </button>
          </div>
          <div class="p-5 space-y-4">
            <div class="flex items-start gap-3 p-3.5 bg-orange-50 border-l-4 border-orange-400 rounded-lg">
              <i class="fi fi-rr-info-circle text-orange-500 text-base mt-0.5"></i>
              <div>
                <p class="text-xs text-orange-900 font-semibold mb-0.5">You are about to flag this conversation</p>
                <p class="text-[11px] text-orange-800">Flagged conversations will be marked for review. Users will not be notified.</p>
              </div>
            </div>
            <div class="space-y-3">
              <div>
                <label class="block text-xs font-semibold text-gray-800 mb-1.5">Reason for Flagging <span class="text-red-500">*</span></label>
                <select id="flagReason" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition text-sm">
                  <option value="">Select a reason...</option>
                  <option value="spam">Spam or Unwanted Content</option>
                  <option value="harassment">Harassment or Abuse</option>
                  <option value="inappropriate">Inappropriate Language</option>
                  <option value="scam">Potential Scam</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div id="otherReasonContainer" class="hidden">
                <label class="block text-xs font-semibold text-gray-800 mb-1.5">Specify Other Reason <span class="text-red-500">*</span></label>
                <input type="text" id="otherReasonText" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition text-sm" placeholder="Please specify the reason...">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-800 mb-1.5">Additional Notes <span class="text-gray-400 font-normal">(Optional)</span></label>
                <textarea id="flagNotes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition resize-none text-sm" placeholder="Provide additional context..."></textarea>
              </div>
            </div>
          </div>
          <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
            <button class="modal-close px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition font-semibold active:scale-95 text-xs">Cancel</button>
            <button id="confirmFlagBtn" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition active:scale-95 flex items-center gap-1.5 text-xs">
              <i class="fi fi-rr-flag text-xs"></i>Flag Conversation
            </button>
          </div>
        </div>
      </div>

      <!-- Suspend Confirmation Modal -->
      <div id="suspendConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="sticky top-0 bg-gradient-to-r from-red-500 to-red-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg z-10">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                <i class="fi fi-sr-ban text-white text-sm"></i>
              </div>
              <h3 class="text-sm font-bold text-white">Suspend This Conversation?</h3>
            </div>
            <button class="modal-close text-white hover:text-red-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
              <i class="fi fi-rr-cross text-lg"></i>
            </button>
          </div>
          <div class="p-5 space-y-4">
            <div class="flex items-start gap-3 p-3.5 bg-red-50 border-l-4 border-red-400 rounded-lg">
              <i class="fi fi-rr-triangle-warning text-red-500 text-base mt-0.5"></i>
              <div>
                <p class="text-xs text-red-900 font-semibold mb-0.5">Warning: This is a serious action</p>
                <p class="text-[11px] text-red-800">Suspending will prevent both parties from sending further messages. They will be notified.</p>
              </div>
            </div>
            <div class="flex items-start gap-3 p-3.5 bg-blue-50 border-l-4 border-blue-400 rounded-lg">
              <i class="fi fi-rr-info text-blue-500 text-base mt-0.5"></i>
              <div>
                <p class="text-xs text-blue-900 font-semibold mb-0.5">Progressive Ban System</p>
                <div class="text-[11px] text-blue-800 space-y-0.5">
                  <p>• 1st offense: 7 days ban</p>
                  <p>• 2nd offense: 15 days ban</p>
                  <p>• 3rd offense: 30 days ban</p>
                  <p>• 4th offense: Permanent ban</p>
                </div>
              </div>
            </div>
            <div class="space-y-3">
              <div>
                <label class="block text-xs font-semibold text-gray-800 mb-1.5">Reason for Suspension <span class="text-red-500">*</span></label>
                <select id="suspendReason" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-300 focus:border-red-300 transition text-sm">
                  <option value="">Select a reason...</option>
                  <option value="violation">Terms of Service Violation</option>
                  <option value="harassment">Harassment or Threatening Behavior</option>
                  <option value="fraud">Fraudulent Activity</option>
                  <option value="spam">Repeated Spam</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div id="otherSuspendReasonContainer" class="hidden">
                <label class="block text-xs font-semibold text-gray-800 mb-1.5">Specify Other Reason <span class="text-red-500">*</span></label>
                <input type="text" id="otherSuspendReasonText" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-300 focus:border-red-300 transition text-sm" placeholder="Please specify the reason...">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-800 mb-1.5">Additional Details <span class="text-gray-400 font-normal">(Optional)</span></label>
                <textarea id="suspendNotes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none text-sm" placeholder="Provide additional context..."></textarea>
              </div>
            </div>
          </div>
          <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
            <button class="modal-close px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition font-semibold active:scale-95 text-xs">Cancel</button>
            <button id="confirmSuspendBtn" class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition active:scale-95 flex items-center gap-1.5 text-xs">
              <i class="fi fi-rr-ban text-xs"></i>Suspend Conversation
            </button>
          </div>
        </div>
      </div>

      <!-- Unsuspend Confirmation Modal -->
      <div id="restoreConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="sticky top-0 bg-gradient-to-r from-green-500 to-green-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg z-10">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                <i class="fi fi-sr-check-circle text-white text-sm"></i>
              </div>
              <h3 class="text-sm font-bold text-white">Unsuspend This Conversation?</h3>
            </div>
            <button class="modal-close text-white hover:text-green-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
              <i class="fi fi-rr-cross text-lg"></i>
            </button>
          </div>
          <div class="p-5 space-y-4">
            <div class="flex items-start gap-3 p-3.5 bg-green-50 border-l-4 border-green-400 rounded-lg">
              <i class="fi fi-rr-info-circle text-green-500 text-base mt-0.5"></i>
              <div>
                <p class="text-xs text-green-900 font-semibold mb-0.5">You are about to unsuspend this conversation</p>
                <p class="text-[11px] text-green-800">Both parties will be able to resume messaging immediately.</p>
              </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
              <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between">
                  <span class="text-gray-500">Conversation ID:</span>
                  <span class="font-semibold text-gray-800" id="restoreConvId">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-500">Participants:</span>
                  <span class="font-semibold text-gray-800" id="restoreConvName">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-500">Current Status:</span>
                  <span class="font-semibold text-red-600" id="restoreConvStatus">Suspended</span>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
            <button class="modal-close px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition font-semibold active:scale-95 text-xs">Cancel</button>
            <button id="confirmRestoreBtn" class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition active:scale-95 flex items-center gap-1.5 text-xs">
              <i class="fi fi-rr-check-circle text-xs"></i>Unsuspend Conversation
            </button>
          </div>
        </div>
      </div>

      <!-- Unflag Conversation Modal -->
      <div id="unflagConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg z-10">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                <i class="fi fi-sr-flag text-white text-sm"></i>
              </div>
              <h3 class="text-sm font-bold text-white">Remove Flag?</h3>
            </div>
            <button class="modal-close text-white hover:text-orange-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
              <i class="fi fi-rr-cross text-lg"></i>
            </button>
          </div>
          <div class="p-5 space-y-4">
            <div class="flex items-start gap-3 p-3.5 bg-orange-50 border-l-4 border-orange-400 rounded-lg">
              <i class="fi fi-rr-info-circle text-orange-500 text-base mt-0.5"></i>
              <div>
                <p class="text-xs text-orange-900 font-semibold mb-0.5">Remove flag from this conversation</p>
                <p class="text-[11px] text-orange-800">This will clear the flag status. The conversation will no longer appear in the flagged filter.</p>
              </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
              <div class="flex items-center justify-between text-xs">
                <span class="text-gray-500">Current Status:</span>
                <span class="font-semibold text-amber-600">Flagged</span>
              </div>
            </div>
          </div>
          <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
            <button class="modal-close px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition font-semibold active:scale-95 text-xs">Cancel</button>
            <button id="confirmUnflagBtn" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition active:scale-95 flex items-center gap-1.5 text-xs">
              <i class="fi fi-rr-check text-xs"></i>Remove Flag
            </button>
          </div>
        </div>
      </div>

      <!-- Unflag Message Modal -->
      <div id="unflagMessageConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg z-10">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                <i class="fi fi-sr-check-circle text-white text-sm"></i>
              </div>
              <h3 class="text-sm font-bold text-white">Unflag This Message?</h3>
            </div>
            <button class="modal-close text-white hover:text-orange-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
              <i class="fi fi-rr-cross text-lg"></i>
            </button>
          </div>
          <div class="p-5 space-y-4">
            <div class="flex items-start gap-3 p-3.5 bg-orange-50 border-l-4 border-orange-400 rounded-lg">
              <i class="fi fi-rr-info-circle text-orange-500 text-base mt-0.5"></i>
              <div>
                <p class="text-xs text-orange-900 font-semibold mb-0.5">You are about to unflag this message</p>
                <p class="text-[11px] text-orange-800">This will remove the flag and restore the message to its normal appearance.</p>
              </div>
            </div>
          </div>
          <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
            <button class="modal-close px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition font-semibold active:scale-95 text-xs">Cancel</button>
            <button id="confirmUnflagMessageBtn" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition active:scale-95 flex items-center gap-1.5 text-xs">
              <i class="fi fi-rr-check text-xs"></i>Unflag Message
            </button>
          </div>
        </div>
      </div>

      <!-- Compose New Message Modal -->
      <div id="composeModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-hidden flex flex-col">
          <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg z-10 flex-shrink-0">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                <i class="fi fi-sr-messages text-white text-sm"></i>
              </div>
              <h3 class="text-sm font-bold text-white">Compose New Message</h3>
            </div>
            <button class="modal-close text-white hover:text-orange-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
              <i class="fi fi-rr-cross text-lg"></i>
            </button>
          </div>
          <div class="p-5 space-y-4 overflow-y-auto flex-1">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5">To <span class="text-red-500">*</span></label>
              <div id="composeRecipientsWrapper" class="compose-recipients-wrapper w-full flex flex-wrap gap-2 px-3 py-2 border border-gray-300 rounded-lg focus-within:ring-2 focus-within:ring-orange-300 focus-within:border-orange-300 bg-white cursor-text">
                <input id="composeRecipientSearch" type="text" class="flex-1 min-w-[140px] outline-none text-sm bg-transparent" placeholder="Search for a person..." autocomplete="off">
              </div>
              <div id="composeRecipientDropdown" class="compose-recipient-dropdown hidden mt-1.5 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto text-sm"></div>
              <p class="text-[11px] text-gray-400 mt-1">Start typing to search for recipients</p>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5">Message <span class="text-red-500">*</span></label>
              <textarea id="composeMessage" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition resize-none text-sm leading-relaxed" placeholder="Write your message here..."></textarea>
              <p id="composeCharCount" class="text-[11px] text-gray-400 mt-1">0 / 1000</p>
            </div>
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-semibold text-gray-700">Attachments</label>
                <span class="text-[11px] text-gray-400">Images, PDF, DOC (max 5MB each)</span>
              </div>
              <div id="composeAttachmentDrop" class="compose-attachment-drop border-2 border-dashed border-gray-300 rounded-xl p-4 flex flex-col items-center justify-center gap-2 text-center cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                <i class="fi fi-rr-folder-upload text-xl text-orange-400"></i>
                <p class="text-xs text-gray-500">Drag & drop files here or click to browse</p>
                <input id="composeAttachmentInput" type="file" class="hidden" multiple accept="image/*,.pdf,.doc,.docx">
              </div>
              <div id="composeAttachmentPreview" class="compose-attachment-preview mt-3 flex flex-wrap gap-2"></div>
            </div>
          </div>
          <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex-shrink-0">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-1.5 text-[11px] text-gray-400">
                <i class="fi fi-rr-shield-check text-orange-400"></i>
                <span>Messages are monitored for policy compliance.</span>
              </div>
              <div class="flex items-center gap-2">
                <button class="modal-close px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition font-semibold active:scale-95 text-xs">Cancel</button>
                <button id="sendComposeBtn" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition active:scale-95 flex items-center gap-1.5 text-xs disabled:opacity-50 disabled:cursor-not-allowed">
                  <i class="fi fi-rr-paper-plane text-xs"></i>Send Message
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
