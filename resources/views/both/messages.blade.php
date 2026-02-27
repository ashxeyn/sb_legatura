@php
    // Detect current role for dynamic layout and assets - prioritize active_role if set
    $activeRole = session('active_role');
    $currentRole = $activeRole ?? session('current_role', session('userType'));

    // Explicitly check for contractor versus owner
    $isContractor = ($currentRole === 'contractor');
    $layout = $isContractor ? 'layouts.appContractor' : 'layouts.app';
    $cssPath = $isContractor ? 'css/contractor/contractor_Messages.css' : 'css/owner/propertyOwner_Messages.css';
    $routeName = $isContractor ? 'contractor.messages' : 'owner.messages';

    // Get current user for messaging backend
    $currentUser = session('user') ?? auth()->user();
    $userId = $currentUser->user_id ?? ($currentUser->id ?? null);
@endphp

@extends($layout)

@section('title', 'Messages - Legatura')

@section('content')
    <div class="{{ $isContractor ? 'contractor-messages' : 'property-owner-messages' }} bg-gray-50">
        <div class="messages-card-container max-w-7xl mx-auto">
            <div class="messages-card bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="messages-layout flex">
                    <!-- Left Sidebar: Conversation List -->
                    <div class="messages-sidebar bg-white border-r border-gray-200 flex flex-col w-1/3">
                        <!-- Filter Section -->
                        <div class="messages-filter-section p-4 border-b border-gray-200">
                            <!-- Pusher Connection Status -->
                            <div id="pusherStatus" class="mb-3 px-3 py-2 rounded-lg text-xs font-medium hidden">
                                <span class="flex items-center gap-2">
                                    <span class="status-dot w-2 h-2 rounded-full"></span>
                                    <span class="status-text"></span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <button class="filter-btn active" id="allFilterBtn" data-filter="all">
                                        <span>All</span>
                                        <span class="filter-count" id="allCount">0</span>
                                    </button>
                                    <button class="filter-btn" id="unreadFilterBtn" data-filter="unread">
                                        <span>Unread</span>
                                        <span class="filter-count" id="unreadCount">0</span>
                                    </button>
                                </div>
                                <button class="new-message-btn" id="newMessageBtn" aria-label="New message">
                                    <i class="fi fi-rr-plus"></i>
                                    <span>New Message</span>
                                </button>
                            </div>
                        </div>

                        <!-- Search Bar -->
                        <div class="messages-search-section p-4 border-b border-gray-200">
                            <div class="messages-search-container relative">
                                <i class="fi fi-rr-search messages-search-icon"></i>
                                <input type="text" class="messages-search-input" placeholder="Search conversations..."
                                    id="conversationSearchInput">
                            </div>
                        </div>

                        <!-- Conversation List -->
                        <div class="messages-conversation-list flex-1 overflow-y-auto" id="conversationList">
                            <!-- Conversation Item Template -->
                            <template id="conversationItemTemplate">
                                <div class="conversation-item" data-conversation-id="">
                                    <div class="conversation-item-content">
                                        <div class="conversation-avatar">
                                            <span class="conversation-initials"></span>
                                            <span class="conversation-status-dot hidden"></span>
                                        </div>
                                        <div class="conversation-info">
                                            <div class="conversation-header">
                                                <span class="conversation-name"></span>
                                                <span class="conversation-time"></span>
                                            </div>
                                            <div class="conversation-project"></div>
                                            <div class="conversation-preview"></div>
                                        </div>
                                        <div class="conversation-unread hidden">
                                            <span class="unread-count"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Conversations will be loaded dynamically from backend via JavaScript -->
                            <p class="text-center text-gray-400 py-8">Loading conversations...</p>
                        </div>
                    </div>

                    <!-- Right Main Area: Active Conversation -->
                    <div class="messages-main flex-1 flex flex-col bg-gray-50">
                        <!-- Conversation Header -->
                        <div class="messages-header bg-white border-b border-gray-200 p-4" id="conversationHeader">
                            <div class="flex items-center gap-4">
                                <div class="messages-header-avatar" id="headerAvatar">
                                    <span class="messages-header-initials" id="headerInitials"></span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="messages-header-name" id="headerName">Select a conversation</h3>
                                    <p class="messages-header-project" id="headerProject"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Messages Display Area -->
                        <div class="messages-display flex-1 overflow-y-auto p-6" id="messagesDisplay">
                            <!-- Message Template -->
                            <template id="messageTemplate">
                                <div class="message-bubble" data-message-type="">
                                    <div class="message-content"></div>
                                    <div class="message-attachments hidden" id="messageAttachments">
                                        <!-- File attachments will be inserted here -->
                                    </div>
                                    <div class="message-time"></div>
                                </div>
                            </template>

                            <!-- File Preview Template -->
                            <template id="filePreviewTemplate">
                                <div class="file-preview-item" data-file-name="">
                                    <div class="file-preview-content">
                                        <i class="file-preview-icon"></i>
                                        <span class="file-preview-name"></span>
                                        <span class="file-preview-size"></span>
                                    </div>
                                    <button type="button" class="file-preview-remove" aria-label="Remove file">
                                        <i class="fi fi-rr-cross-small"></i>
                                    </button>
                                </div>
                            </template>

                            <!-- Messages will be loaded dynamically from backend via JavaScript -->
                        </div>

                        <!-- Message Input Area -->
                        <div class="messages-input-area bg-white border-t border-gray-200 p-4">
                            <!-- File Preview Area -->
                            <div class="messages-file-preview hidden" id="filePreviewArea">
                                <div class="file-preview-container" id="filePreviewContainer">
                                    <!-- File previews will be inserted here -->
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex-1 relative">
                                    <textarea class="messages-input" id="messageInput" placeholder="Type your message..."
                                        rows="1"></textarea>
                                </div>
                                <!-- File Attachment Button -->
                                <button type="button" class="messages-attach-btn" id="attachmentBtn"
                                    aria-label="Attach file">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" style="display: block;">
                                        <path
                                            d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"
                                            stroke="white" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    <input type="file" id="attachmentInput" multiple accept="image/*,.pdf,.doc,.docx"
                                        class="hidden">
                                </button>
                                <button class="messages-send-btn" id="sendMessageBtn" aria-label="Send message">
                                    <i class="fi fi-rr-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Message Modal -->
        <div id="composeModal" class="new-message-modal hidden">
            <div class="modal-overlay" id="newMessageModalOverlay"></div>
            <div class="modal-container">
                <!-- Modal Header -->
                <div class="modal-header">
                    <div class="modal-header-content">
                        <h2 class="modal-title">
                            <i class="fi fi-rr-envelope"></i>
                            <span>New Message</span>
                        </h2>
                        <button class="modal-close-btn modal-close" id="closeNewMessageModalBtn" aria-label="Close modal">
                            <i class="fi fi-rr-cross"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <!-- Search Recipient -->
                    <div class="recipient-search-section">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">To</label>
                        <div id="composeRecipientsWrapper"
                            class="w-full flex flex-wrap gap-2 px-3 py-2 border-2 border-gray-200 rounded-xl focus-within:ring-2 focus-within:ring-indigo-300 focus-within:border-indigo-300 bg-white cursor-text"
                            style="min-height: 42px;">
                            <input type="text" class="flex-1 min-w-[140px] outline-none text-sm bg-transparent"
                                id="composeRecipientSearch"
                                placeholder="{{ $isContractor ? 'Search property owners...' : 'Search contractors...' }}">
                        </div>
                        <!-- Recipient Dropdown -->
                        <div id="composeRecipientDropdown"
                            class="hidden mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto text-sm">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Start typing to search for recipients</p>
                    </div>

                    <!-- Message Input -->
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                        <textarea id="composeMessage" rows="4"
                            class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none text-sm leading-relaxed"
                            placeholder="Write your message here..."></textarea>
                    </div>

                    <!-- Attachments -->
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Attachments (optional)</label>
                        <div id="composeAttachmentDrop"
                            class="border-2 border-dashed border-gray-300 rounded-xl p-4 flex flex-col items-center justify-center gap-2 text-center cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                            <i class="fi fi-rr-folder-upload text-xl text-indigo-500"></i>
                            <p class="text-xs text-gray-600">Click to attach files</p>
                            <input id="composeAttachmentInput" type="file" class="hidden" multiple
                                accept="image/*,.pdf,.doc,.docx">
                        </div>
                        <div id="composeAttachmentPreview" class="mt-3 flex flex-wrap gap-2"></div>
                    </div>

                    <!-- Send Button -->
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            class="modal-close px-4 py-2 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition text-sm">Cancel</button>
                        <button id="sendComposeBtn"
                            class="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 text-sm">
                            <i class="fi fi-rr-paper-plane"></i>
                            <span>Send Message</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
@endsection

    @section('extra_css')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="user-id" content="{{ $userId ?? '' }}">

        <link rel="stylesheet" href="{{ asset($cssPath) }}">
        <style>
            /* Hide footer on messages page */
            footer.footer-shell {
                display: none !important;
            }

            /* Recipient chip styles for compose modal */
            .recipient-chip {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                background-color: #e0e7ff;
                color: #4338ca;
                padding: 0.25rem 0.625rem;
                border-radius: 9999px;
                font-size: 0.875rem;
                font-weight: 500;
            }

            .recipient-chip .remove-recipient {
                width: 1rem;
                height: 1rem;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 9999px;
                font-size: 0.75rem;
                cursor: pointer;
                transition: background-color 0.2s;
            }

            .recipient-chip .remove-recipient:hover {
                background-color: #c7d2fe;
            }
        </style>
    @endsection

    @section('extra_js')
        @if(env('PUSHER_APP_KEY'))
            <!-- Laravel Echo & Pusher for Real-time Chat -->
            <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

            <script>
                // Initialize Laravel Echo with Pusher
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

        <!-- Unified messages JavaScript for all roles -->
        <script src="{{ asset('js/message/messages.js') }}"></script>
        <script>
            // Set Messages link as active when on messages page
            document.addEventListener('DOMContentLoaded', () => {
                const navbarLinks = document.querySelectorAll('.navbar-link');
                navbarLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.textContent.trim() === 'Messages' ||
                        link.getAttribute('href') === '{{ route($routeName) }}') {
                        link.classList.add('active');
                    }
                });

                // Update navbar search placeholder
                const navbarSearchInput = document.querySelector('.navbar-search-input');
                if (navbarSearchInput) {
                    navbarSearchInput.placeholder = 'Search messages...';
                }
            });
        </script>
    @endsection