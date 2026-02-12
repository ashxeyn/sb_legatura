/**
 * Messages Page - Real-time Chat with Pusher
 * Legatura Platform
 * Supports: Admin, Contractor, and Property Owner roles
 */

let currentConversationId = null;
let currentConversationData = null; // Store conversation metadata
let currentReceiverId = null;
let selectedRecipients = [];

/**
 * Detect current user role and return appropriate API prefix
 * Based on current URL path
 */
function getApiPrefix() {
    const path = window.location.pathname;
    if (path.includes('/admin/')) {
        return '/admin/messages';
    } else if (path.includes('/contractor/')) {
        return '/contractor/messages/api';
    } else if (path.includes('/owner/')) {
        return '/owner/messages/api';
    }
    // Fallback to admin if no match
    return '/admin/messages';
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    return window.location.pathname.includes('/admin/');
}

/**
 * Format relative time (like "2 minutes ago")
 */
function formatRelativeTime(timestamp) {
    if (!timestamp) return 'just now';

    const now = new Date();
    const past = new Date(timestamp);

    if (isNaN(past.getTime())) return 'just now';

    const diffInSeconds = Math.floor((now - past) / 1000);

    if (diffInSeconds < -600) return 'just now';

    // If slightly in future or very recent, show as "just now"
    if (diffInSeconds < 10) {
        return 'just now';
    }

    if (diffInSeconds < 60) {
        return `${diffInSeconds} seconds ago`;
    }

    const diffInMinutes = Math.floor(diffInSeconds / 60);
    if (diffInMinutes < 60) {
        return diffInMinutes === 1 ? '1 minute ago' : `${diffInMinutes} minutes ago`;
    }

    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) {
        return diffInHours === 1 ? '1 hour ago' : `${diffInHours} hours ago`;
    }

    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) {
        return diffInDays === 1 ? '1 day ago' : `${diffInDays} days ago`;
    }

    const diffInWeeks = Math.floor(diffInDays / 7);
    if (diffInWeeks < 4) {
        return diffInWeeks === 1 ? '1 week ago' : `${diffInWeeks} weeks ago`;
    }

    const diffInMonths = Math.floor(diffInDays / 30);
    if (diffInMonths < 12) {
        return diffInMonths === 1 ? '1 month ago' : `${diffInMonths} months ago`;
    }

    const diffInYears = Math.floor(diffInDays / 365);
    return diffInYears === 1 ? '1 year ago' : `${diffInYears} years ago`;
}

/**
 * Update all relative timestamps on the page
 */
function updateAllTimestamps() {
    document.querySelectorAll('.relative-time').forEach(element => {
        const timestamp = element.getAttribute('data-timestamp');
        if (timestamp) {
            element.textContent = formatRelativeTime(timestamp);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initializePusher();

    // Load dashboard stats only for admin users
    if (isAdmin()) {
        loadDashboardStats();
    }

    loadInbox();
    setupEventListeners();

    // Update timestamps every 30 seconds
    setInterval(updateAllTimestamps, 30000);
});

/**
 * Initialize Laravel Echo with Pusher
 */
function initializePusher() {
    try {
        // Import Laravel Echo and Pusher from CDN (add to blade template)
        if (typeof window.Echo === 'undefined') {
            console.error('Laravel Echo not loaded. Add CDN scripts to template.');
            updatePusherStatus('disconnected', 'Real-time disabled');
            return;
        }

        const userId = getUserId();

        if (!userId) {
            // console.log('Pusher: No user ID found, skipping initialization');
            updatePusherStatus('disconnected', 'Not authenticated');
            return;
        }

        // console.log('Pusher: Initializing for user ID:', userId);
        // console.log('Pusher: Subscribing to channel:', `chat.${userId}`);
        updatePusherStatus('connecting', 'Connecting...');

        // Listen for incoming messages on user's private channel
        window.Echo.private(`chat.${userId}`)
            .listen('.message.sent', (event) => {
                // console.log('Pusher: Message received!', event);
                handleIncomingMessage(event);
            })
            .listen('.messages.read', (event) => {
                // console.log('Pusher: Messages marked as read!', event);
                handleMessagesRead(event);
            })
            .listen('.conversation.suspended', (event) => {
                // console.log('Pusher: Conversation suspended/unsuspended!', event);
                handleConversationSuspension(event);
            })
            .subscribed(() => {
                // console.log('Pusher: Successfully subscribed to channel chat.' + userId);
                updatePusherStatus('connected', 'Real-time enabled');
            })
            .error((error) => {
                console.error('Pusher channel subscription failed:', error);
                // console.info('Tip: Make sure PUSHER credentials are set in .env and queue worker is running');
                updatePusherStatus('error', 'Connection failed');
            });

        // console.log('Pusher: Listener attached for .message.sent and .messages.read events');

    } catch (error) {
        // console.error('Failed to initialize Pusher:', error);
        updatePusherStatus('error', 'Setup failed');
    }
}

/**
 * Update Pusher connection status indicator
 */
function updatePusherStatus(status, message) {
    const statusEl = document.getElementById('pusherStatus');
    if (!statusEl) return;

    const statusDot = statusEl.querySelector('.status-dot');
    const statusText = statusEl.querySelector('.status-text');

    // Show the status indicator
    statusEl.classList.remove('hidden');

    // Reset classes
    statusEl.className = 'mb-3 px-3 py-2 rounded-lg text-xs font-medium';
    statusDot.className = 'status-dot w-2 h-2 rounded-full';

    switch (status) {
        case 'connected':
            statusEl.classList.add('bg-green-50', 'text-green-700', 'border', 'border-green-200');
            statusDot.classList.add('bg-green-500', 'animate-pulse');
            statusText.textContent = message;
            // Auto-hide after 3 seconds
            setTimeout(() => statusEl.classList.add('hidden'), 3000);
            break;
        case 'connecting':
            statusEl.classList.add('bg-yellow-50', 'text-yellow-700', 'border', 'border-yellow-200');
            statusDot.classList.add('bg-yellow-500', 'animate-pulse');
            statusText.textContent = message;
            break;
        case 'disconnected':
        case 'error':
            statusEl.classList.add('bg-gray-50', 'text-gray-600', 'border', 'border-gray-200');
            statusDot.classList.add('bg-gray-400');
            statusText.textContent = message;
            break;
    }
}

/**
 * Load dashboard analytics cards
 */
async function loadDashboardStats() {
    try {
        const cacheBuster = `?_=${Date.now()}`;
        const response = await fetch(`${getApiPrefix()}/stats${cacheBuster}`, {
            headers: getAuthHeaders(),
            credentials: 'include',
            cache: 'no-store' // Prevent browser caching
        });

        if (!response.ok) throw new Error('Failed to load stats');

        const { data } = await response.json();

        // Update stats cards
        document.getElementById('totalSuspended').textContent = data.totalSuspended;
        document.getElementById('activeConversations').textContent = data.activeConversations;
        document.getElementById('flaggedMessages').textContent = data.flaggedMessages;

        // Update filter tab badges
        updateFilterBadge('flagged', data.flaggedMessages);
        updateFilterBadge('suspended', data.totalSuspended);

    } catch (error) {
        // console.error('Error loading stats:', error);
    }
}

/**
 * Load user's inbox/conversations
 */
async function loadInbox() {
    try {
        let endpoint = getApiPrefix();

        // ADMIN: Use specific endpoints for flagged/suspended filters
        if (isAdmin() && currentFilter === 'flagged') {
            endpoint = '/admin/messages/flagged';
        } else if (isAdmin() && currentFilter === 'suspended') {
            endpoint = '/admin/messages/suspended';
        }

        // Add cache-busting parameter to ensure fresh data
        const cacheBuster = `?_=${Date.now()}`;

        const response = await fetch(endpoint + cacheBuster, {
            headers: getAuthHeaders(),
            credentials: 'include',
            cache: 'no-store' // Prevent browser caching
        });

        if (!response.ok) throw new Error('Failed to load inbox');

        const { data } = await response.json();
        renderConversations(data);

        // Update filter badge counts
        if (currentFilter === 'all') {
            updateFilterBadge('all', data.length);
        } else if (currentFilter === 'flagged') {
            updateFilterBadge('flagged', data.length);
        } else if (currentFilter === 'suspended') {
            updateFilterBadge('suspended', data.length);
        }

        // Calculate and update total unread count
        const totalUnread = data.reduce((sum, conv) => sum + (conv.unread_count || 0), 0);
        updateUnreadBadge(totalUnread);

    } catch (error) {
        // console.error('Error loading inbox:', error);
    }
}

/**
 * Render conversations list in sidebar
 */
function renderConversations(conversations) {
    const list = document.getElementById('conversationList');

    if (!conversations || conversations.length === 0) {
        list.innerHTML = '<p class="text-center text-gray-400 py-8">No conversations yet</p>';
        return;
    }

    list.innerHTML = conversations.map(conv => `
        <div class="conversation-item ${conv.unread_count > 0 ? 'unread' : ''} ${conv.is_flagged ? 'flagged' : ''} ${(conv.status === 'suspended' || conv.is_suspended) ? 'suspended' : ''}"
             data-conversation-id="${conv.conversation_id}"
             data-receiver-id="${conv.other_user.id}"
             data-receiver-name="${conv.other_user.name}"
             data-receiver-avatar="${conv.other_user.avatar}"
             data-receiver-type="${conv.other_user.type}">
            <div class="flex items-start gap-3 cursor-pointer p-3 hover:bg-gray-50 rounded-lg transition">
                <div class="relative flex-shrink-0">
                    <img src="${conv.other_user.avatar}"
                         alt="${conv.other_user.name}"
                         class="w-12 h-12 rounded-full object-cover">
                    ${conv.other_user.online ? '<span class="avatar-status online"></span>' : '<span class="avatar-status offline"></span>'}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <h4 class="conversation-name font-semibold text-gray-800 truncate">${conv.other_user.name}</h4>
                            ${conv.is_flagged ? '<i class="fi fi-sr-flag text-amber-500 text-xs"></i>' : ''}
                            ${(conv.status === 'suspended' || conv.is_suspended) ? '<i class="fi fi-sr-ban text-red-500 text-xs"></i>' : ''}
                            ${(conv.no_suspends && conv.no_suspends > 0) ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800" title="Offense count">${conv.no_suspends}x</span>` : ''}
                        </div>
                        <span class="text-xs text-gray-400 flex-shrink-0 relative-time" data-timestamp="${conv.last_message.sent_at_timestamp}">${formatRelativeTime(conv.last_message.sent_at_timestamp)}</span>
                    </div>
                    <p class="text-xs text-gray-500 mb-1">${conv.other_user.type}</p>
                    <div class="flex items-center justify-between">
                        <p class="conversation-preview text-sm text-gray-600 truncate flex-1">${conv.last_message.content}</p>
                        ${conv.unread_count > 0 ? `<span class="unread-badge ml-2">${conv.unread_count}</span>` : ''}
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    // Add click listeners
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.addEventListener('click', () => {
            const conversationId = item.dataset.conversationId; // Keep as string (could be "1_2")
            const receiverId = parseInt(item.dataset.receiverId);
            selectConversation(conversationId, receiverId);
        });
    });
}

/**
 * Select and load a conversation
 */
async function selectConversation(conversationId, receiverId) {
    currentConversationId = conversationId;
    currentReceiverId = receiverId;

    // Update active state
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
    });
    const selectedConversation = document.querySelector(`[data-conversation-id="${conversationId}"]`);
    selectedConversation?.classList.add('active');

    // Update conversation header with receiver info
    if (selectedConversation) {
        const receiverName = selectedConversation.dataset.receiverName;
        const receiverAvatar = selectedConversation.dataset.receiverAvatar;
        const receiverType = selectedConversation.dataset.receiverType;

        // Update header name (contractor/owner uses headerName, admin uses selectedName)
        const headerName = document.getElementById('headerName') || document.getElementById('selectedName');
        if (headerName) headerName.textContent = receiverName;

        // Update header avatar
        const headerAvatar = document.getElementById('headerAvatar');
        const selectedAvatar = document.getElementById('selectedAvatar');

        if (headerAvatar) {
            headerAvatar.innerHTML = `<img src="${receiverAvatar}" alt="${receiverName}" class="w-10 h-10 rounded-full object-cover">`;
        }
        if (selectedAvatar) {
            selectedAvatar.src = receiverAvatar;
            selectedAvatar.alt = receiverName;
        }

        // Update header project/type (contractor/owner uses headerProject, admin uses selectedProject)
        const headerProject = document.getElementById('headerProject') || document.getElementById('selectedProject');
        if (headerProject) headerProject.textContent = receiverType;
    }

    // Show message panel
    document.getElementById('emptyState')?.classList.add('hidden');
    const messageContent = document.getElementById('messageContent');
    messageContent?.classList.remove('hidden');
    messageContent?.classList.add('flex');

    // Initialize action buttons for admin (only show in flagged filter)
    if (isAdmin()) {
        const suspendBtn = document.getElementById('suspendConversationBtn');
        const restoreBtn = document.getElementById('restoreConversationBtn');
        const messageInputContainer = document.getElementById('messageInputContainer');

        // console.log('Admin user - initializing action buttons', {
        //     suspendBtn: !!suspendBtn,
        //     restoreBtn: !!restoreBtn,
        //     currentFilter: currentFilter
        // });

        // Only show suspend/unsuspend buttons in flagged or suspended filters
        if (currentFilter === 'flagged') {
            suspendBtn?.classList.remove('hidden');
            restoreBtn?.classList.add('hidden');
            // Hide message input - admin is moderating, not participating
            messageInputContainer?.classList.add('hidden');
        } else if (currentFilter === 'suspended') {
            suspendBtn?.classList.add('hidden');
            restoreBtn?.classList.remove('hidden');
            // Hide message input - admin is moderating, not participating
            messageInputContainer?.classList.add('hidden');
        } else {
            suspendBtn?.classList.add('hidden');
            restoreBtn?.classList.add('hidden');
            // Show message input - admin can chat with users in "all" filter
            messageInputContainer?.classList.remove('hidden');
        }
    }

    // Load conversation history
    await loadConversationHistory(conversationId);

    // Clear unread badge for this conversation (messages marked as read on backend)
    const conversationItem = document.querySelector(`[data-conversation-id="${conversationId}"]`);
    const unreadBadge = conversationItem?.querySelector('.unread-badge');
    if (unreadBadge) {
        unreadBadge.remove();
        // Also remove 'unread' class from conversation item
        conversationItem?.classList.remove('unread');
    }
}

/**
 * Load conversation message history
 */
async function loadConversationHistory(conversationId) {
    try {
        const response = await fetch(`${getApiPrefix()}/${conversationId}`, {
            headers: getAuthHeaders(),
            credentials: 'include'
        });

        if (!response.ok) throw new Error('Failed to load conversation');

        const { data } = await response.json();

        // Store conversation metadata (for admin viewing user-to-user conversations)
        currentConversationData = data.conversation;

        renderMessages(data.messages, currentConversationData);

        // Check suspension and flag status
        checkAndHandleSuspension(conversationId);
        checkAndHandleFlagStatus(conversationId);

    } catch (error) {
        // console.error('Error loading conversation:', error);
        toast('Failed to load messages', 'error');
    }
}

/**
 * Check if conversation is flagged and handle UI accordingly
 */
async function checkAndHandleFlagStatus(conversationId) {
    try {
        // Get conversation details from inbox to check flag status
        const inbox = await fetch(getApiPrefix(), {
            headers: getAuthHeaders(),
            credentials: 'include'
        }).then(r => r.json());

        const conversation = inbox.data.find(conv => String(conv.conversation_id) === String(conversationId));

        if (!conversation) return;

        const flagBtn = document.getElementById('flagConversationBtn');
        const unflagBtn = document.getElementById('unflagConversationBtn');

        if (conversation.is_flagged) {
            // Hide flag button, show unflag button
            flagBtn?.classList.add('hidden');
            unflagBtn?.classList.remove('hidden');
        } else {
            // Show flag button, hide unflag button
            flagBtn?.classList.remove('hidden');
            unflagBtn?.classList.add('hidden');
        }

    } catch (error) {
        // console.error('Error checking flag status:', error);
    }
}

/**
 * Check if conversation is suspended and handle UI accordingly
 */
async function checkAndHandleSuspension(conversationId) {
    try {
        // Get conversation details - try current filter endpoint first
        let endpoint = getApiPrefix();

        // If we're on flagged or suspended filter, use those endpoints
        if (isAdmin() && currentFilter === 'flagged') {
            endpoint = '/admin/messages/flagged';
        } else if (isAdmin() && currentFilter === 'suspended') {
            endpoint = '/admin/messages/suspended';
        }

        const inbox = await fetch(endpoint, {
            headers: getAuthHeaders(),
            credentials: 'include'
        }).then(r => r.json());

        const conversation = inbox.data?.find(c => c.conversation_id == conversationId);

        if (!conversation) {
            // console.log('Conversation not found in current filter, checking regular inbox');
            // Fallback to regular inbox
            const regularInbox = await fetch(getApiPrefix(), {
                headers: getAuthHeaders(),
                credentials: 'include'
            }).then(r => r.json());

            const fallbackConv = regularInbox.data?.find(c => c.conversation_id == conversationId);
            if (!fallbackConv) return;

            return checkSuspensionState(fallbackConv);
        }

        checkSuspensionState(conversation);

    } catch (error) {
        // console.error('Error checking suspension:', error);
    }
}

/**
 * Helper: Check suspension state and update UI
 */
function checkSuspensionState(conversation) {
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendMessageBtn');
    const attachmentBtn = document.getElementById('attachmentBtn');
    const inputContainer = document.getElementById('messageInputContainer');

    // Remove existing suspension notice if any
    const existingNotice = document.getElementById('suspensionNotice');
    if (existingNotice) existingNotice.remove();

    // For admin viewing flagged/suspended conversations, hide the entire input container
    if (isAdmin() && (currentFilter === 'flagged' || currentFilter === 'suspended')) {
        inputContainer?.classList.add('hidden');

        // Show appropriate moderation buttons
        if (conversation.status === 'suspended' || conversation.is_suspended) {
            document.getElementById('suspendConversationBtn')?.classList.add('hidden');
            document.getElementById('restoreConversationBtn')?.classList.remove('hidden');
        } else {
            if (currentFilter === 'flagged') {
                document.getElementById('suspendConversationBtn')?.classList.remove('hidden');
            }
            document.getElementById('restoreConversationBtn')?.classList.add('hidden');
        }
        return;
    }

    // Show input container for non-admin or admin in "all" filter
    inputContainer?.classList.remove('hidden');

    // Check if suspended
    if (conversation.status === 'suspended' || conversation.is_suspended) {
            // Disable input
            if (messageInput) {
                messageInput.disabled = true;
                messageInput.placeholder = 'This conversation is suspended';
                messageInput.classList.add('bg-gray-100', 'cursor-not-allowed');
            }
            if (sendBtn) {
                sendBtn.disabled = true;
                sendBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            if (attachmentBtn) {
                attachmentBtn.disabled = true;
                attachmentBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }

            // Show suspension notice
            const notice = document.createElement('div');
            notice.id = 'suspensionNotice';
            notice.className = 'px-4 py-3 bg-red-50 border-l-4 border-red-500 text-sm text-red-800';

            let suspensionMessage = 'This conversation has been suspended.';

            // Check if there's a suspended_until date
            if (conversation.suspended_until) {
                const suspendedUntil = new Date(conversation.suspended_until);
                const now = new Date();

                if (suspendedUntil > now) {
                    const options = {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    };
                    suspensionMessage = `This conversation is suspended until ${suspendedUntil.toLocaleDateString('en-US', options)}. No messages can be sent during this period.`;
                }
            } else {
                suspensionMessage = 'This conversation has been permanently suspended. No messages can be sent.';
            }

            if (conversation.reason) {
                suspensionMessage += `<br><strong>Reason:</strong> ${conversation.reason}`;
            }

            notice.innerHTML = suspensionMessage;
            const messagesDisplay = document.getElementById('messagesDisplay');
            if (messagesDisplay && inputContainer) {
                messagesDisplay.parentElement?.insertBefore(notice, inputContainer);
            }

            // Show unsuspend button only in flagged or suspended filter, hide suspend button
            // console.log('Conversation is suspended - showing unsuspend button');
            document.getElementById('suspendConversationBtn')?.classList.add('hidden');
            if (currentFilter === 'flagged' || currentFilter === 'suspended') {
                document.getElementById('restoreConversationBtn')?.classList.remove('hidden');
            } else {
                document.getElementById('restoreConversationBtn')?.classList.add('hidden');
            }

        } else {
            // Enable input (conversation not suspended)
            if (messageInput) {
                messageInput.disabled = false;
                messageInput.placeholder = 'Type your message...';
                messageInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
            }
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
            if (attachmentBtn) {
                attachmentBtn.disabled = false;
                attachmentBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }

            // Show suspend button only in flagged filter, hide restore button
            // console.log('Conversation is NOT suspended - showing suspend button');
            if (currentFilter === 'flagged') {
                document.getElementById('suspendConversationBtn')?.classList.remove('hidden');
            } else {
                document.getElementById('suspendConversationBtn')?.classList.add('hidden');
            }
            document.getElementById('restoreConversationBtn')?.classList.add('hidden');
        }
}

/**
 * Mark a conversation as read (helper function for real-time updates)
 */
async function markConversationAsRead(conversationId) {
    try {
        // Just fetch the conversation - backend marks as read automatically
        await fetch(`${getApiPrefix()}/${conversationId}`, {
            headers: getAuthHeaders(),
            credentials: 'include'
        });
    } catch (error) {
        // console.error('Error marking conversation as read:', error);
    }
}

/**
 * Render messages in conversation view
 */
function renderMessages(messages, conversationData = null) {
    const container = document.getElementById('messagesDisplay');
    const userId = getUserId();

    if (!messages || messages.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-400 py-8">No messages yet</p>';
        return;
    }

    // Check if current user is a participant in the conversation
    // If not (e.g., admin viewing user-to-user chat), use sender_id for alignment
    let alignmentUserId = userId;
    let isAdminView = false;
    if (conversationData) {
        const isParticipant = conversationData.sender_id === userId || conversationData.receiver_id === userId;
        if (!isParticipant && conversationData.sender_id) {
            // Admin viewing: align by sender_id (sender on left, receiver on right)
            alignmentUserId = conversationData.sender_id;
            isAdminView = true;
        }
    }

    container.innerHTML = messages.map(msg => {
        const isSent = msg.sender.id === alignmentUserId;
        const bubbleClass = isSent ? 'message-bubble-sent' : 'message-bubble-received';
        const alignClass = isSent ? 'justify-end' : 'justify-start';

        // Flagged message styling and tooltip
        let flaggedStyle = '';
        let flaggedTooltip = '';
        if (msg.is_flagged) {
            flaggedStyle = 'border-2 border-amber-500 bg-amber-50';
            flaggedTooltip = msg.flag_reason || 'Flagged';
        }

        return `
            <div class="flex ${alignClass} mb-4" data-message-id="${msg.message_id}">
                ${!isSent ? `<img src="${msg.sender.avatar}" class="w-8 h-8 rounded-full mr-2" alt="${msg.sender.name}">` : ''}
                <div class="max-w-[70%]">
                    <div class="${bubbleClass} ${flaggedStyle} relative" ${msg.is_flagged ? `title="🚩 ${flaggedTooltip}"` : ''}>
                        <p class="text-sm">${escapeHtml(msg.content)}</p>
                        ${msg.attachments.length > 0 ? renderAttachments(msg.attachments) : ''}
                        ${msg.is_flagged && isAdmin() ? `
                            <div class="mt-1 pt-1 border-t border-amber-200 text-xs text-amber-700">
                                <i class="fi fi-sr-flag"></i> ${escapeHtml(flaggedTooltip)}
                            </div>
                        ` : ''}
                    </div>
                    <div class="flex items-center gap-1 mt-1 ${isSent ? 'justify-end' : 'justify-start'}">
                        ${isAdminView ? `<span class="text-xs text-gray-500 font-medium">${msg.sender.name}</span>` : ''}
                        <p class="text-xs text-gray-400 relative-time" data-timestamp="${msg.sent_at}">
                            ${formatRelativeTime(msg.sent_at)}
                        </p>
                        ${isSent ? `<span class="read-indicator text-xs ${msg.is_read ? 'text-blue-500' : 'text-gray-400'}" title="${msg.is_read ? 'Seen' : 'Sent'}">✓${msg.is_read ? '✓' : ''}</span>` : ''}
                    </div>
                </div>
                ${isSent ? `<img src="${msg.sender.avatar}" class="w-8 h-8 rounded-full ml-2" alt="${msg.sender.name}">` : ''}
            </div>
        `;
    }).join('');

    // Scroll to bottom
    scrollToBottom();
}

/**
 * Render message attachments
 */
function renderAttachments(attachments) {
    return `
        <div class="mt-2 space-y-2">
            ${attachments.map(att => {
                const isImage = att.is_image || /\.(jpg|jpeg|png|gif|webp)$/i.test(att.file_name);

                if (isImage) {
                    return `
                        <a href="${att.file_url}" target="_blank">
                            <img src="${att.file_url}" class="max-w-full rounded border" alt="${att.file_name}">
                        </a>
                    `;
                } else {
                    return `
                        <a href="${att.file_url}" target="_blank"
                           class="flex items-center gap-2 p-2 bg-gray-100 rounded hover:bg-gray-200">
                            <i class="fi fi-rr-document text-indigo-600"></i>
                            <span class="text-sm truncate">${att.file_name}</span>
                        </a>
                    `;
                }
            }).join('')}
        </div>
    `;
}

/**
 * Send a new message
 */
async function sendMessage() {
    const input = document.getElementById('messageInput');
    const fileInput = document.getElementById('attachmentInput');
    const content = input.value.trim();

    if (!content && !fileInput.files.length) {
        toast('Please enter a message or attach a file', 'warning');
        return;
    }

    if (!currentReceiverId) {
        toast('Please select a conversation', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('receiver_id', currentReceiverId);
    formData.append('content', content || '');

    if (currentConversationId) {
        formData.append('conversation_id', currentConversationId);
    }

    // Append files
    if (fileInput.files.length > 0) {
        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append('attachments[]', fileInput.files[i]);
        }
    }

    try {
        const response = await fetch(getApiPrefix(), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                ...(getAuthToken() && { 'Authorization': `Bearer ${getAuthToken()}` })
            },
            credentials: 'include',
            body: formData
        });

        const result = await response.json();

        // SECURITY: Handle blocked message (422 - Contact info detected)
        if (response.status === 422) {
            toast(result.message || 'Message contains prohibited content', 'error');
            // Don't clear input so user can edit and resend
            return;
        }

        if (!response.ok) {
            console.error('Server error:', result);
            throw new Error(result.message || 'Failed to send message');
        }

        const { data } = result;

        // Clear inputs
        input.value = '';
        fileInput.value = '';

        // Clear file preview
        const previewArea = document.getElementById('filePreviewArea');
        if (previewArea) {
            previewArea.classList.add('hidden');
            previewArea.innerHTML = '';
        }

        // Add message to UI immediately
        appendMessage(data);

        // Update conversation ID if new
        if (!currentConversationId && data.conversation_id) {
            currentConversationId = data.conversation_id;
        }

        // Reload stats
        loadDashboardStats();

        toast('Message sent', 'success');

    } catch (error) {
        // console.error('Error sending message:', error);
        toast(error.message || 'Failed to send message', 'error');
    }
}

/**
 * Render file preview with remove buttons
 */
function renderFilePreview(files) {
    const previewArea = document.getElementById('filePreviewArea');
    const fileInput = document.getElementById('attachmentInput');
    if (!previewArea || !fileInput) return;

    // Show preview area
    previewArea.classList.remove('hidden');
    previewArea.innerHTML = '';

    // Create DataTransfer to manage files
    const dataTransfer = new DataTransfer();

    // Add all files to DataTransfer
    Array.from(files).forEach(file => {
        dataTransfer.items.add(file);
    });

    // Render each file
    Array.from(files).forEach((file, index) => {
        const fileSize = (file.size / 1024).toFixed(1);
        const isImage = file.type.startsWith('image/');

        const fileChip = document.createElement('div');
        fileChip.className = 'flex items-center gap-2 bg-indigo-50 border border-indigo-200 text-indigo-700 px-3 py-2 rounded-lg text-sm';
        fileChip.innerHTML = `
            <i class="fi fi-rr-${isImage ? 'image' : 'file'} text-indigo-500"></i>
            <div class="flex flex-col">
                <span class="font-medium max-w-[150px] truncate">${escapeHtml(file.name)}</span>
                <span class="text-xs text-indigo-500">${fileSize} KB</span>
            </div>
            <button class="remove-file ml-2 hover:bg-indigo-200 rounded-full w-5 h-5 flex items-center justify-center text-sm" data-index="${index}">
                ×
            </button>
        `;

        previewArea.appendChild(fileChip);

        // Add remove listener
        fileChip.querySelector('.remove-file').addEventListener('click', () => {
            // Remove file from DataTransfer
            const newDataTransfer = new DataTransfer();
            Array.from(dataTransfer.files).forEach((f, i) => {
                if (i !== index) {
                    newDataTransfer.items.add(f);
                }
            });

            // Update file input
            fileInput.files = newDataTransfer.files;

            // Re-render preview
            if (newDataTransfer.files.length > 0) {
                renderFilePreview(newDataTransfer.files);
            } else {
                previewArea.classList.add('hidden');
                previewArea.innerHTML = '';
            }
        });
    });
}

/**
 * Handle incoming real-time message from Pusher
 */
function handleIncomingMessage(event) {
    // console.log('Pusher: Handling incoming message', {
    //     conversation_id: event.conversation_id,
    //     sender: event.sender.name,
    //     current_conversation: currentConversationId,
    //     message_preview: event.content?.substring(0, 50),
    //     type_event: typeof event.conversation_id,
    //     type_current: typeof currentConversationId,
    //     comparison_result: event.conversation_id == currentConversationId
    // });

    const userId = getUserId();
    const isMessageFromMe = event.sender.id === userId;

    // Skip messages sent by current user (already added via sendMessage())
    if (isMessageFromMe) {
        // console.log('Pusher: Skipping own message (already in UI)');
        // Just reload inbox to update conversation preview
        loadInbox();
        return;
    }

    // If message is for current conversation, append it and mark as read
    // Use loose equality (==) to handle both string and number conversation IDs
    if (String(event.conversation_id) === String(currentConversationId)) {
        // console.log('Pusher: Message is for active conversation, appending to UI');
        appendMessage(event);
        // Mark as read immediately since user is viewing the conversation
        markConversationAsRead(event.conversation_id);
    } else {
        // console.log('Pusher: Message is for different conversation, updating inbox only');
    }

    // Reload inbox to update preview and unread counts
    // console.log('Reloading inbox to show new message...');
    loadInbox();

    // Reload stats for admin
    if (isAdmin()) {
        loadDashboardStats();
    }

    // Show notification only if message is from someone else
    if (!isMessageFromMe) {
        toast(`New message from ${event.sender.name}`, 'info');
    }
}

/**
 * Handle messages marked as read event
 */
function handleMessagesRead(event) {
    // console.log('Pusher: Messages marked as read', {
    //     conversation_id: event.conversation_id,
    //     read_by_user_id: event.read_by_user_id,
    //     current_conversation: currentConversationId,
    //     type_event: typeof event.conversation_id,
    //     type_current: typeof currentConversationId,
    //     comparison_result: String(event.conversation_id) === String(currentConversationId)
    // });

    // If we're viewing this conversation, update the read receipts
    // Use String() conversion to handle both string and number conversation IDs
    if (String(event.conversation_id) === String(currentConversationId)) {
        // console.log('Updating read receipts in current conversation');
        updateReadReceipts(event.conversation_id);
    }

    // Reload inbox to update unread counts
    loadInbox();
}

/**
 * Handle conversation suspension/restoration event from Pusher
 */
function handleConversationSuspension(event) {
    // console.log('Pusher: Conversation status changed', {
    //     conversation_id: event.conversation_id,
    //     status: event.status,
    //     is_suspended: event.is_suspended,
    //     reason: event.reason
    // });

    // Reload inbox to update conversation list with new status
    loadInbox();

    // Reload dashboard stats
    if (isAdmin()) {
        loadDashboardStats();
    }

    // If we're currently viewing this conversation
    if (String(event.conversation_id) === String(currentConversationId)) {
        // console.log('Current conversation status changed');

        if (event.status === 'suspended') {
            // Conversation was suspended
            if (currentFilter === 'flagged') {
                // In flagged filter, conversation moves to suspended list - clear view
                currentConversationId = null;
                currentReceiverId = null;
                document.getElementById('emptyState')?.classList.remove('hidden');
                document.getElementById('messageContent')?.classList.add('hidden');
            } else {
                // Show suspension notice and update UI
                loadConversationHistory(event.conversation_id);
                toast(`This conversation has been suspended. ${event.reason || ''}`, 'warning');
            }
        } else if (event.status === 'active') {
            // Conversation was unsuspended
            if (currentFilter === 'suspended') {
                // In suspended filter, conversation moves to all/flagged list - clear view
                currentConversationId = null;
                currentReceiverId = null;
                document.getElementById('emptyState')?.classList.remove('hidden');
                document.getElementById('messageContent')?.classList.add('hidden');
                toast('Conversation has been unsuspended', 'success');
            } else {
                // Reload conversation to show updated status
                loadConversationHistory(event.conversation_id);
                toast('Conversation has been unsuspended', 'success');
            }
        }
    } else {
        // Not viewing this conversation, just show a toast if it affects current filter
        if (event.status === 'suspended' && currentFilter === 'all') {
            toast(`A conversation has been suspended`, 'info');
        } else if (event.status === 'active' && currentFilter === 'suspended') {
            toast(`A conversation has been unsuspended`, 'info');
        }
    }
}

/**
 * Update read receipts for messages in current conversation
 */
function updateReadReceipts(conversationId) {
    const userId = getUserId();
    const messagesContainer = document.getElementById('messagesDisplay');
    if (!messagesContainer) return;

    // Find all read indicators for sent messages
    const readIndicators = messagesContainer.querySelectorAll('.read-indicator');

    readIndicators.forEach(readIndicator => {
        // Update to double checkmark blue (read)
        readIndicator.className = 'read-indicator text-xs text-blue-500';
        readIndicator.title = 'Seen';
        readIndicator.textContent = '✓✓';
    });

    // console.log(`Updated ${readIndicators.length} read receipts`);
}

/**
 * Append single message to conversation
 */
function appendMessage(messageData) {
    const container = document.getElementById('messagesDisplay');
    const userId = getUserId();

    // Check if current user is a participant in the conversation
    // If not (e.g., admin viewing user-to-user chat), use sender_id for alignment
    let alignmentUserId = userId;
    let isAdminView = false;
    if (currentConversationData) {
        const isParticipant = currentConversationData.sender_id === userId || currentConversationData.receiver_id === userId;
        if (!isParticipant && currentConversationData.sender_id) {
            // Admin viewing: align by sender_id (sender on left, receiver on right)
            alignmentUserId = currentConversationData.sender_id;
            isAdminView = true;
        }
    }

    const isSent = messageData.sender.id === alignmentUserId;
    const bubbleClass = isSent ? 'message-bubble-sent' : 'message-bubble-received';
    const alignClass = isSent ? 'justify-end' : 'justify-start';

    // Flagged message styling
    let flaggedStyle = '';
    let flaggedTooltip = '';
    if (messageData.is_flagged) {
        flaggedStyle = 'border-2 border-amber-500 bg-amber-50';
        flaggedTooltip = messageData.flag_reason || 'Flagged';
    }

    const messageHtml = `
        <div class="flex ${alignClass} mb-4" data-message-id="${messageData.message_id}">
            ${!isSent ? `<img src="${messageData.sender.avatar}" class="w-8 h-8 rounded-full mr-2" alt="${messageData.sender.name}">` : ''}
            <div class="max-w-[70%]">
                <div class="${bubbleClass} ${flaggedStyle} relative group" ${messageData.is_flagged ? `title="🚩 ${flaggedTooltip}"` : ''}>
                    <p class="text-sm">${escapeHtml(messageData.content)}</p>
                    ${messageData.attachments?.length > 0 ? renderAttachments(messageData.attachments) : ''}
                    ${messageData.is_flagged && isAdmin() ? `
                        <div class="mt-1 pt-1 border-t border-amber-200 text-xs text-amber-700">
                            <i class="fi fi-sr-flag"></i> ${escapeHtml(flaggedTooltip)}
                        </div>
                    ` : ''}
                    ${!isSent && !isAdminView ? `
                        <button class="report-message-btn absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs"
                                data-message-id="${messageData.message_id}"
                                title="Report this message">
                            <i class="fi fi-rr-flag text-xs"></i>
                        </button>
                    ` : ''}
                </div>
                <div class="flex items-center gap-1 mt-1 ${isSent ? 'justify-end' : 'justify-start'}">
                    ${isAdminView ? `<span class="text-xs text-gray-500 font-medium">${messageData.sender.name}</span>` : ''}
                    <p class="text-xs text-gray-400 relative-time" data-timestamp="${messageData.sent_at || new Date().toISOString()}">
                        ${formatRelativeTime(messageData.sent_at || new Date().toISOString())}
                    </p>
                    ${isSent ? `<span class="read-indicator text-xs ${messageData.is_read ? 'text-blue-500' : 'text-gray-400'}" title="${messageData.is_read ? 'Seen' : 'Sent'}">✓${messageData.is_read ? '✓' : ''}</span>` : ''}
                </div>
            </div>
            ${isSent ? `<img src="${messageData.sender.avatar}" class="w-8 h-8 rounded-full ml-2" alt="${messageData.sender.name}">` : ''}
        </div>
    `;

    container.insertAdjacentHTML('beforeend', messageHtml);

    // Attach report button event listener (only for non-admin view)
    if (!isSent && !isAdminView) {
        const reportBtn = container.querySelector(`[data-message-id="${messageData.message_id}"] .report-message-btn`);
        reportBtn?.addEventListener('click', () => reportMessage(messageData.message_id));
    }

    scrollToBottom();
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Send message button
    document.getElementById('sendMessageBtn')?.addEventListener('click', sendMessage);

    // Enter key to send
    document.getElementById('messageInput')?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Attachment button
    document.getElementById('attachmentBtn')?.addEventListener('click', () => {
        document.getElementById('attachmentInput')?.click();
    });

    // File selection handler
    document.getElementById('attachmentInput')?.addEventListener('change', (e) => {
        const files = e.target.files;
        if (files.length > 0) {
            renderFilePreview(files);
        }
    });

    // Compose new message (admin uses composeBtn, contractor/owner uses newMessageBtn)
    document.getElementById('composeBtn')?.addEventListener('click', showComposeModal);
    document.getElementById('newMessageBtn')?.addEventListener('click', showComposeModal);

    // Compose modal recipient search
    document.getElementById('composeRecipientSearch')?.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        const dropdown = document.getElementById('composeRecipientDropdown');

        if (query.length === 0) {
            // Hide dropdown when search is empty (like Messenger)
            dropdown?.classList.add('hidden');
        } else {
            // Show filtered suggestions based on search
            const filtered = availableUsers.filter(user =>
                user.name.toLowerCase().includes(query) ||
                user.type.toLowerCase().includes(query)
            );
            showUserDropdown(filtered);
        }
    });

    // Click on wrapper to focus search input (like Messenger)
    document.getElementById('composeRecipientsWrapper')?.addEventListener('click', () => {
        document.getElementById('composeRecipientSearch')?.focus();
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', (e) => {
        const searchInput = document.getElementById('composeRecipientSearch');
        const dropdown = document.getElementById('composeRecipientDropdown');
        const wrapper = document.getElementById('composeRecipientsWrapper');

        if (searchInput && dropdown && wrapper) {
            if (!wrapper.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        }
    });

    // Compose modal file attachment
    document.getElementById('composeAttachmentDrop')?.addEventListener('click', () => {
        document.getElementById('composeAttachmentInput')?.click();
    });

    document.getElementById('composeAttachmentInput')?.addEventListener('change', (e) => {
        const files = e.target.files;
        if (files.length > 0) {
            renderComposeFilePreview(files);
        }
    });

    // Close compose modal
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('composeModal')?.classList.add('hidden');
            document.getElementById('composeModal')?.classList.remove('flex');
            // Reset
            const searchInput = document.getElementById('composeRecipientSearch');
            const messageInput = document.getElementById('composeMessage');
            const fileInput = document.getElementById('composeAttachmentInput');
            const filePreview = document.getElementById('composeAttachmentPreview');
            if (searchInput) searchInput.value = '';
            if (messageInput) messageInput.value = '';
            if (fileInput) fileInput.value = '';
            if (filePreview) filePreview.innerHTML = '';
            currentReceiverId = null;
            selectedRecipients = [];
            renderRecipientChips();
        });
    });

    // Send compose message
    document.getElementById('sendComposeBtn')?.addEventListener('click', async () => {
        const content = document.getElementById('composeMessage')?.value.trim();
        const fileInput = document.getElementById('composeAttachmentInput');

        if (!content && (!fileInput || !fileInput.files.length)) {
            toast('Please enter a message or attach a file', 'warning');
            return;
        }
        if (selectedRecipients.length === 0) {
            toast('Please select at least one recipient', 'warning');
            return;
        }

        try {
            // Send message to each recipient
            const sendPromises = selectedRecipients.map(recipient => {
                const formData = new FormData();
                formData.append('receiver_id', recipient.id);
                formData.append('content', content || '');

                // Append files if any
                if (fileInput && fileInput.files.length > 0) {
                    for (let i = 0; i < fileInput.files.length; i++) {
                        formData.append('attachments[]', fileInput.files[i]);
                    }
                }

                return fetch(getApiPrefix(), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        ...(getAuthToken() && { 'Authorization': `Bearer ${getAuthToken()}` })
                    },
                    credentials: 'include',
                    body: formData
                });
            });

            await Promise.all(sendPromises);

            const recipientCount = selectedRecipients.length;

            // Close modal
            document.getElementById('composeModal')?.classList.add('hidden');
            document.getElementById('composeModal')?.classList.remove('flex');

            // Reset
            const searchInput = document.getElementById('composeRecipientSearch');
            const messageInput = document.getElementById('composeMessage');
            const filePreview = document.getElementById('composeAttachmentPreview');
            if (searchInput) searchInput.value = '';
            if (messageInput) messageInput.value = '';
            if (fileInput) fileInput.value = '';
            if (filePreview) filePreview.innerHTML = '';
            selectedRecipients = [];
            renderRecipientChips();

            toast(`Message sent to ${recipientCount > 1 ? recipientCount + ' recipients' : 'recipient'}`, 'success');

            // Reload inbox
            loadInbox();

        } catch (error) {
            console.error('Error sending message:', error);
            toast('Failed to send message', 'error');
        }
    });

    // Flag/Suspend/Restore actions (admin moderation)
    document.getElementById('suspendBtn')?.addEventListener('click', suspendCurrentConversation);
    document.getElementById('restoreBtn')?.addEventListener('click', restoreCurrentConversation);

    // Flag/Unflag/Suspend conversation buttons
    document.getElementById('flagConversationBtn')?.addEventListener('click', () => openModal('flagConfirmModal'));
    document.getElementById('unflagConversationBtn')?.addEventListener('click', () => openUnflagModal());
    document.getElementById('suspendConversationBtn')?.addEventListener('click', () => openModal('suspendConfirmModal'));
    document.getElementById('restoreConversationBtn')?.addEventListener('click', () => openModal('restoreConfirmModal'));

    // Modal confirm buttons
    document.getElementById('confirmFlagBtn')?.addEventListener('click', flagCurrentConversation);
    document.getElementById('confirmUnflagBtn')?.addEventListener('click', unflagCurrentConversation);
    document.getElementById('confirmSuspendBtn')?.addEventListener('click', suspendCurrentConversation);
    document.getElementById('confirmRestoreBtn')?.addEventListener('click', restoreCurrentConversation);

    // Show/hide "Other reason" input based on dropdown selection
    document.getElementById('flagReason')?.addEventListener('change', function(e) {
        const otherContainer = document.getElementById('otherReasonContainer');
        const otherInput = document.getElementById('otherReasonText');

        if (e.target.value === 'other') {
            otherContainer?.classList.remove('hidden');
            otherInput?.focus();
        } else {
            otherContainer?.classList.add('hidden');
            if (otherInput) otherInput.value = '';
        }
    });

    // Show/hide "Other reason" input for suspend modal
    document.getElementById('suspendReason')?.addEventListener('change', function(e) {
        const otherContainer = document.getElementById('otherSuspendReasonContainer');
        const otherInput = document.getElementById('otherSuspendReasonText');

        if (e.target.value === 'other') {
            otherContainer?.classList.remove('hidden');
            otherInput?.focus();
        } else {
            otherContainer?.classList.add('hidden');
            if (otherInput) otherInput.value = '';
        }
    });

    // Modal close handlers
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            if (modal) modal.classList.add('hidden');
        });
    });

    // Search
    document.getElementById('searchInput')?.addEventListener('input', debounce(searchMessages, 300));

    // Filter tabs (admin uses .filter-tab, contractor/owner uses .filter-btn)
    document.querySelectorAll('.filter-tab, .filter-btn').forEach(tab => {
        tab.addEventListener('click', handleFilterChange);
    });
}

let currentFilter = 'all';

/**
 * Handle filter tab change
 */
function handleFilterChange(e) {
    const filter = e.currentTarget.dataset.filter;
    currentFilter = filter;

    // Update active state (handle both .filter-tab and .filter-btn)
    document.querySelectorAll('.filter-tab, .filter-btn').forEach(tab => {
        tab.classList.remove('active');
    });
    e.currentTarget.classList.add('active');

    // ADMIN: Reload inbox with specific endpoint for flagged/suspended/all
    // OR if switching to 'all' from any other filter (to reset the conversation list)
    if (isAdmin() && (filter === 'flagged' || filter === 'suspended' || filter === 'all')) {
        loadInbox(); // Will use the correct endpoint based on currentFilter
    } else if (filter === 'all') {
        // Non-admin: reload inbox to get all conversations
        loadInbox();
    } else {
        // Apply filter client-side for other cases (unread, etc.)
        filterConversations(filter);
    }
}

/**
 * Filter conversations by status
 */
function filterConversations(filter) {
    const conversationItems = document.querySelectorAll('.conversation-item');
    let visibleCount = 0;

    // Clear search results message if exists
    const container = document.getElementById('conversationList');
    const searchNoResults = container.querySelector('.no-search-results');
    if (searchNoResults) searchNoResults.remove();

    conversationItems.forEach(item => {
        let show = true;

        if (filter === 'unread') {
            show = item.classList.contains('unread');
        } else if (filter === 'flagged') {
            show = item.classList.contains('flagged');
        } else if (filter === 'suspended') {
            show = item.classList.contains('suspended');
        }
        // 'all' shows everything

        if (show) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    // Show "no results" message if nothing matches
    let noResults = container.querySelector('.no-filter-results');

    if (visibleCount === 0) {
        // Remove old message if exists
        if (noResults) noResults.remove();

        // Create new message
        noResults = document.createElement('div');
        noResults.className = 'no-filter-results text-center py-8 text-gray-500';
        let icon = 'fi-rr-inbox';
        let message = 'No conversations found';

        if (filter === 'unread') {
            icon = 'fi-rr-envelope';
            message = 'No unread conversations';
        } else if (filter === 'flagged') {
            icon = 'fi-rr-flag';
            message = 'No flagged conversations';
        } else if (filter === 'suspended') {
            icon = 'fi-rr-ban';
            message = 'No suspended conversations';
        }

        noResults.innerHTML = `
            <i class="fi ${icon} text-4xl mb-2 text-gray-300"></i>
            <p class="text-sm">${message}</p>
        `;
        container.appendChild(noResults);
    } else {
        if (noResults) noResults.remove();
    }
}

/**
 * Search messages
 */
async function searchMessages(e) {
    const query = e.target.value.trim().toLowerCase();

    if (query.length < 2) {
        // Reapply current filter instead of loading all
        filterConversations(currentFilter);
        return;
    }

    // Filter conversations already in the list (respecting current filter)
    const conversationItems = document.querySelectorAll('.conversation-item');
    let visibleCount = 0;

    // Clear filter results message if exists
    const container = document.getElementById('conversationList');
    const filterNoResults = container.querySelector('.no-filter-results');
    if (filterNoResults) filterNoResults.remove();

    conversationItems.forEach(item => {
        const name = item.querySelector('.conversation-name')?.textContent.toLowerCase() || '';
        const preview = item.querySelector('.conversation-preview')?.textContent.toLowerCase() || '';

        const matchesSearch = name.includes(query) || preview.includes(query);

        // Also check if it matches the current filter
        let matchesFilter = true;
        if (currentFilter === 'flagged') {
            matchesFilter = item.classList.contains('flagged');
        } else if (currentFilter === 'suspended') {
            matchesFilter = item.classList.contains('suspended');
        }

        if (matchesSearch && matchesFilter) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    // Show "no results" message if nothing matches
    let noResults = container.querySelector('.no-search-results');

    if (visibleCount === 0) {
        if (!noResults) {
            noResults = document.createElement('div');
            noResults.className = 'no-search-results text-center py-8 text-gray-500';
            noResults.innerHTML = `
                <i class="fi fi-rr-search text-4xl mb-2 text-gray-300"></i>
                <p class="text-sm">No conversations found matching "${e.target.value}"</p>
            `;
            container.appendChild(noResults);
        }
    } else {
        if (noResults) noResults.remove();
    }
}

/**
 * Show compose new message modal
 */
let availableUsers = [];

async function showComposeModal() {
    try {
        // Load available users
        const response = await fetch(`${getApiPrefix()}/users`, {
            headers: getAuthHeaders(),
            credentials: 'include'
        });

        if (!response.ok) throw new Error('Failed to load users');

        const { data } = await response.json();
        availableUsers = data;

        // Show modal
        const modal = document.getElementById('composeModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Focus search input
            document.getElementById('composeRecipientSearch')?.focus();

            // Don't show dropdown initially - wait for user to type
            document.getElementById('composeRecipientDropdown')?.classList.add('hidden');
        } else {
            console.error('Compose modal element not found');
            toast('Compose modal not available', 'error');
        }

    } catch (error) {
        console.error('Error loading users:', error);
        toast('Failed to load users', 'error');
    }
}

/**
 * Show user dropdown with filtered users
 */
function showUserDropdown(users) {
    const dropdown = document.getElementById('composeRecipientDropdown');
    if (!dropdown) return;

    if (users.length === 0) {
        dropdown.innerHTML = '<div class="p-3 text-gray-500 text-center">No users found</div>';
        dropdown.classList.remove('hidden');
        return;
    }

    dropdown.innerHTML = users.map(user => `
        <div class="user-option p-3 hover:bg-indigo-50 cursor-pointer flex items-center gap-3 transition" data-user-id="${user.id}">
            <img src="${user.avatar}" class="w-8 h-8 rounded-full" alt="${user.name}">
            <div class="flex-1">
                <div class="font-semibold text-gray-800">${user.name}</div>
                <div class="text-xs text-gray-500">${user.type}</div>
            </div>
        </div>
    `).join('');

    dropdown.classList.remove('hidden');

    // Add click listeners
    dropdown.querySelectorAll('.user-option').forEach(option => {
        option.addEventListener('click', () => {
            const userId = parseInt(option.dataset.userId);
            selectRecipient(userId);
        });
    });
}

/**
 * Select a recipient from compose modal
 */
function selectRecipient(userId) {
    const user = availableUsers.find(u => u.id === userId);
    if (!user) return;

    // Check if already selected
    if (selectedRecipients.find(r => r.id === userId)) {
        toast('Recipient already selected', 'warning');
        return;
    }

    // Add to selected recipients
    selectedRecipients.push(user);

    // Clear search input
    const searchInput = document.getElementById('composeRecipientSearch');
    if (searchInput) {
        searchInput.value = '';
    }

    // Hide dropdown
    const dropdown = document.getElementById('composeRecipientDropdown');
    if (dropdown) {
        dropdown.classList.add('hidden');
    }

    // Render recipient chips
    renderRecipientChips();

    // Add recipient to selection
}

/**
 * Render recipient chips (like Messenger)
 */
function renderRecipientChips() {
    const wrapper = document.getElementById('composeRecipientsWrapper');
    const searchInput = document.getElementById('composeRecipientSearch');
    if (!wrapper || !searchInput) return;

    // Remove existing chips
    wrapper.querySelectorAll('.recipient-chip').forEach(chip => chip.remove());

    // Add chips for each selected recipient
    selectedRecipients.forEach(user => {
        const chip = document.createElement('div');
        chip.className = 'recipient-chip flex items-center gap-1.5 bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-full text-sm font-medium';
        chip.innerHTML = `
            <span>${escapeHtml(user.name)}</span>
            <button class="remove-recipient hover:bg-indigo-200 rounded-full w-4 h-4 flex items-center justify-center text-sm" data-user-id="${user.id}">
                ×
            </button>
        `;
        wrapper.insertBefore(chip, searchInput);

        // Add remove listener
        chip.querySelector('.remove-recipient').addEventListener('click', (e) => {
            e.stopPropagation();
            removeRecipient(user.id);
        });
    });
}

/**
 * Remove a recipient chip
 */
function removeRecipient(userId) {
    selectedRecipients = selectedRecipients.filter(r => r.id !== userId);
    renderRecipientChips();
}

/**
 * Render compose modal file preview
 */
function renderComposeFilePreview(files) {
    const previewArea = document.getElementById('composeAttachmentPreview');
    const fileInput = document.getElementById('composeAttachmentInput');
    if (!previewArea || !fileInput) return;

    previewArea.innerHTML = '';

    // Create DataTransfer to manage files
    const dataTransfer = new DataTransfer();

    // Add all files to DataTransfer
    Array.from(files).forEach(file => {
        dataTransfer.items.add(file);
    });

    // Render each file
    Array.from(files).forEach((file, index) => {
        const fileSize = (file.size / 1024).toFixed(1);
        const isImage = file.type.startsWith('image/');

        const fileChip = document.createElement('div');
        fileChip.className = 'flex items-center gap-2 bg-indigo-50 border border-indigo-200 text-indigo-700 px-3 py-2 rounded-lg text-sm';
        fileChip.innerHTML = `
            <i class="fi fi-rr-${isImage ? 'image' : 'file'} text-indigo-500"></i>
            <div class="flex flex-col">
                <span class="font-medium max-w-[150px] truncate">${escapeHtml(file.name)}</span>
                <span class="text-xs text-indigo-500">${fileSize} KB</span>
            </div>
            <button class="remove-compose-file ml-2 hover:bg-indigo-200 rounded-full w-5 h-5 flex items-center justify-center text-sm" data-index="${index}">
                ×
            </button>
        `;

        previewArea.appendChild(fileChip);

        // Add remove listener
        fileChip.querySelector('.remove-compose-file').addEventListener('click', () => {
            // Remove file from DataTransfer
            const newDataTransfer = new DataTransfer();
            Array.from(dataTransfer.files).forEach((f, i) => {
                if (i !== index) {
                    newDataTransfer.items.add(f);
                }
            });

            // Update file input
            fileInput.files = newDataTransfer.files;

            // Re-render preview
            if (newDataTransfer.files.length > 0) {
                renderComposeFilePreview(newDataTransfer.files);
            } else {
                previewArea.innerHTML = '';
            }
        });
    });
}

/**
 * Open modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

/**
 * Open restore modal with conversation details
 */
async function openRestoreModal() {
    if (!currentConversationId) return;

    try {
        // Get conversation details from inbox
        const response = await fetch(getApiPrefix(), {
            headers: getAuthHeaders(),
            credentials: 'include'
        });

        if (!response.ok) throw new Error('Failed to load inbox');

        const { data } = await response.json();
        const conversation = data.find(c => c.conversation_id == currentConversationId);

        if (!conversation) {
            toast('Conversation not found', 'error');
            return;
        }

        // Populate modal fields
        document.getElementById('restoreConvId').textContent = currentConversationId;
        document.getElementById('restoreConvName').textContent = conversation.other_user.name;
        document.getElementById('restoreConvStatus').textContent = conversation.status || 'Suspended';

        // Open the modal
        openModal('restoreConfirmModal');

    } catch (error) {
        console.error('Error opening restore modal:', error);
        toast('Failed to load conversation details', 'error');
    }
}

/**
 * Admin: Flag conversation
 */
async function flagCurrentConversation() {
    if (!isAdmin()) return; // Admin-only feature
    if (!currentConversationId) return;

    let reason = document.getElementById('flagReason')?.value;
    const notes = document.getElementById('flagNotes')?.value;

    if (!reason) {
        toast('Please select a reason for flagging', 'warning');
        return;
    }

    // If "other" is selected, validate and use the custom reason
    if (reason === 'other') {
        const otherReasonText = document.getElementById('otherReasonText')?.value?.trim();
        if (!otherReasonText) {
            toast('Please specify the other reason', 'warning');
            return;
        }
        reason = `Other: ${otherReasonText}`;
    }

    try {
        const response = await fetch(`/admin/messages/conversation/${currentConversationId}/flag`, {
            method: 'POST',
            headers: {
                ...getAuthHeaders(),
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ reason, notes })
        });

        if (!response.ok) throw new Error('Failed to flag');

        toast('Conversation flagged successfully', 'success');

        // Close modal
        document.getElementById('flagConfirmModal')?.classList.add('hidden');
        document.getElementById('flagConfirmModal')?.classList.remove('flex');

        // Clear form
        document.getElementById('flagReason').value = '';
        document.getElementById('flagNotes').value = '';
        document.getElementById('otherReasonText').value = '';
        document.getElementById('otherReasonContainer')?.classList.add('hidden');

        // Reload data
        loadInbox();
        loadDashboardStats();

    } catch (error) {
        console.error('Error flagging:', error);
        toast('Failed to flag conversation', 'error');
    }
}

/**
 * Open unflag modal and populate with conversation data
 */
async function openUnflagModal() {
    if (!currentConversationId) return;

    try {
        // Get conversation details from inbox
        const inbox = await fetch(getApiPrefix(), {
            headers: getAuthHeaders(),
            credentials: 'include'
        }).then(r => r.json());

        const conversation = inbox.data.find(conv => String(conv.conversation_id) === String(currentConversationId));

        if (conversation) {
            // Populate modal with conversation name/participants
            const convNameElement = document.getElementById('unflagConvName');
            if (convNameElement) {
                convNameElement.textContent = conversation.name || 'Unknown';
            }
        }

        // Open modal
        const modal = document.getElementById('unflagConfirmModal');
        modal?.classList.remove('hidden');
        modal?.classList.add('flex');

    } catch (error) {
        console.error('Error opening unflag modal:', error);
        toast('Failed to load conversation details', 'error');
    }
}

/**
 * Admin: Unflag conversation
 */
async function unflagCurrentConversation() {
    if (!isAdmin()) return; // Admin-only feature
    if (!currentConversationId) return;

    try {
        const response = await fetch(`/admin/messages/conversation/${currentConversationId}/unflag`, {
            method: 'POST',
            headers: getAuthHeaders(),
            credentials: 'include'
        });

        if (!response.ok) throw new Error('Failed to unflag');

        toast('Conversation unflagged successfully', 'success');

        // Close modal
        document.getElementById('unflagConfirmModal')?.classList.add('hidden');
        document.getElementById('unflagConfirmModal')?.classList.remove('flex');

        // Reload data and update button states
        loadInbox();
        loadDashboardStats();
        checkAndHandleFlagStatus(currentConversationId);

    } catch (error) {
        console.error('Error unflagging:', error);
        toast('Failed to unflag conversation', 'error');
    }
}

/**
 * Admin: Suspend conversation
 */
async function suspendCurrentConversation() {
    if (!isAdmin()) return; // Admin-only feature
    if (!currentConversationId) return;

    // Get form elements with null checks
    const suspendReasonEl = document.getElementById('suspendReason');
    const otherReasonTextEl = document.getElementById('otherSuspendReasonText');
    const notesEl = document.getElementById('suspendNotes');

    if (!suspendReasonEl) {
        console.error('Suspend reason dropdown not found');
        return;
    }

    let reason = suspendReasonEl.value;
    const otherReasonText = otherReasonTextEl?.value || '';
    const notes = notesEl?.value || '';

    if (!reason) {
        toast('Please select a reason for suspension', 'warning');
        return;
    }

    // If "other" is selected, use the custom reason
    if (reason === 'other') {
        if (!otherReasonText || otherReasonText.trim() === '') {
            toast('Please specify the reason for suspension', 'warning');
            return;
        }
        reason = otherReasonText.trim();
    }

    const fullReason = notes ? `${reason}: ${notes}` : reason;

    try {
        const response = await fetch(`/admin/messages/conversation/${currentConversationId}/suspend`, {
            method: 'POST',
            headers: {
                ...getAuthHeaders(),
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                reason: fullReason
            })
        });

        if (!response.ok) throw new Error('Failed to suspend');

        const result = await response.json();

        toast(`Conversation suspended - ${result.offense_level}`, 'success');

        // Close modal
        document.getElementById('suspendConfirmModal')?.classList.add('hidden');
        document.getElementById('suspendConfirmModal')?.classList.remove('flex');

        // Reset form with null checks
        const suspendReasonEl = document.getElementById('suspendReason');
        const suspendDurationEl = document.getElementById('suspendDuration');
        const suspendNotesEl = document.getElementById('suspendNotes');
        const otherReasonTextEl = document.getElementById('otherSuspendReasonText');
        const otherReasonContainerEl = document.getElementById('otherSuspendReasonContainer');

        if (suspendReasonEl) suspendReasonEl.value = '';
        if (suspendDurationEl) suspendDurationEl.value = '24h';
        if (suspendNotesEl) suspendNotesEl.value = '';
        if (otherReasonTextEl) otherReasonTextEl.value = '';
        if (otherReasonContainerEl) otherReasonContainerEl.classList.add('hidden');

        // Reload inbox and stats
        await loadInbox();
        loadDashboardStats();

        // Clear current conversation since it's now suspended and removed from flagged list
        if (currentFilter === 'flagged') {
            // Conversation moved to suspended list, clear the view
            currentConversationId = null;
            currentReceiverId = null;
            document.getElementById('emptyState')?.classList.remove('hidden');
            document.getElementById('messageContent')?.classList.add('hidden');
        }

    } catch (error) {
        console.error('Error suspending:', error);
        toast('Failed to suspend conversation', 'error');
    }
}

/**
 * Admin: Restore conversation
 */
async function restoreCurrentConversation() {
    if (!isAdmin()) return; // Admin-only feature
    if (!currentConversationId) return;

    try {
        const response = await fetch(`/admin/messages/conversation/${currentConversationId}/restore`, {
            method: 'POST',
            headers: getAuthHeaders(),
            credentials: 'include'
        });

        if (!response.ok) throw new Error('Failed to unsuspend');

        // Success - toast will be shown by Pusher event handler
        // Close modal
        document.getElementById('restoreConfirmModal')?.classList.add('hidden');
        document.getElementById('restoreConfirmModal')?.classList.remove('flex');

        // Reload inbox and stats
        await loadInbox();
        loadDashboardStats();

        // Clear current conversation since it's no longer in suspended list
        if (currentFilter === 'suspended') {
            currentConversationId = null;
            currentReceiverId = null;
            document.getElementById('emptyState')?.classList.remove('hidden');
            document.getElementById('messageContent')?.classList.add('hidden');
        }

    } catch (error) {
        console.error('Error unsuspending:', error);
        toast('Failed to unsuspend conversation', 'error');
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function getAuthHeaders() {
    const token = getAuthToken();
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
    };

    // Add Bearer token only if available (for API auth)
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    return headers;
}

function getAuthToken() {
    // For web dashboard, get from session/meta tag
    // May be empty if using session-based auth instead of Sanctum
    return document.querySelector('meta[name="api-token"]')?.content || '';
}

/**
 * Update unread count badge and document title
 */
function updateUnreadBadge(count) {
    // Update unread filter badge
    const unreadCountEl = document.getElementById('unreadCount');
    if (unreadCountEl) {
        unreadCountEl.textContent = count;
    }

    // Update document title with unread count
    const baseTitle = 'Messages - Legatura';
    if (count > 0) {
        document.title = `(${count}) ${baseTitle}`;
    } else {
        document.title = baseTitle;
    }
}

function getUserId() {
    // Get from session/meta tag
    return parseInt(document.querySelector('meta[name="user-id"]')?.content) || null;
}

function scrollToBottom() {
    const container = document.getElementById('messagesDisplay');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Update filter tab badge count
 */
function updateFilterBadge(filter, count) {
    const button = document.querySelector(`[data-filter="${filter}"]`);
    if (button) {
        // Try to find span with filter-count class (contractor/owner), or last span (admin)
        const badge = button.querySelector('span.filter-count') || button.querySelector('span:last-of-type');
        if (badge) {
            badge.textContent = count;
        }
    }
}

/**
 * SECURITY: Report a message for inappropriate content
 */
async function reportMessage(messageId) {
    // Prompt user for reason
    const reason = prompt('Please describe why you are reporting this message:');

    if (!reason || reason.trim() === '') {
        toast('Report cancelled', 'info');
        return;
    }

    try {
        const response = await fetch(`${getApiPrefix()}/report`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                ...(getAuthToken() && { 'Authorization': `Bearer ${getAuthToken()}` })
            },
            credentials: 'include',
            body: JSON.stringify({
                message_id: messageId,
                reason: reason.trim()
            })
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Failed to report message');
        }

        toast(result.message || 'Message reported successfully', 'success');

        // Optionally hide the report button after reporting
        const reportBtn = document.querySelector(`[data-message-id="${messageId}"] .report-message-btn`);
        if (reportBtn) {
            reportBtn.remove();
        }

    } catch (error) {
        console.error('Error reporting message:', error);
        toast(error.message || 'Failed to report message', 'error');
    }
}

function toast(message, type = 'info') {
    const colors = {
        info: 'bg-blue-500',
        success: 'bg-green-500',
        warning: 'bg-amber-500',
        error: 'bg-red-500'
    };

    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
