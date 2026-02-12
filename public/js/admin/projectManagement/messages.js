/**
 * Admin Messages Page - Real-time Chat with Pusher
 * Legatura Platform
 */

let currentConversationId = null;
let currentReceiverId = null;
let selectedRecipients = [];

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
    loadDashboardStats();
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
            return;
        }

        const userId = getUserId();

        if (!userId) return;

        // Listen for incoming messages on user's private channel
        window.Echo.private(`chat.${userId}`)
            .listen('.message.sent', (event) => {
                handleIncomingMessage(event);
            })
            .subscribed(() => {})
            .error((error) => {
                console.error('Pusher channel subscription failed:', error);
                // console.info('Tip: Make sure PUSHER credentials are set in .env and queue worker is running');
            });

    } catch (error) {
        console.error('Failed to initialize Pusher:', error);
    }
}

/**
 * Load dashboard analytics cards
 */
async function loadDashboardStats() {
    try {
        const response = await fetch('/admin/messages/stats', {
            headers: getAuthHeaders()
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
        console.error('Error loading stats:', error);
    }
}

/**
 * Load user's inbox/conversations
 */
async function loadInbox() {
    try {
        const response = await fetch('/admin/messages/', {
            headers: getAuthHeaders()
        });

        if (!response.ok) throw new Error('Failed to load inbox');

        const { data } = await response.json();
        renderConversations(data);

        // Update "All" filter badge with total count
        updateFilterBadge('all', data.length);

    } catch (error) {
        console.error('Error loading inbox:', error);
    }
}

/**
 * Render conversations list in sidebar
 */
function renderConversations(conversations) {
    const list = document.getElementById('conversationsList');

    if (!conversations || conversations.length === 0) {
        list.innerHTML = '<p class="text-center text-gray-400 py-8">No conversations yet</p>';
        return;
    }

    list.innerHTML = conversations.map(conv => `
        <div class="conversation-item ${conv.is_flagged ? 'flagged' : ''} ${(conv.status === 'suspended' || conv.is_suspended) ? 'suspended' : ''}"
             data-conversation-id="${conv.conversation_id}"
             data-receiver-id="${conv.other_user.id}">
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
    document.querySelector(`[data-conversation-id="${conversationId}"]`)?.classList.add('active');

    // Show message panel
    document.getElementById('emptyState')?.classList.add('hidden');
    const messageContent = document.getElementById('messageContent');
    messageContent?.classList.remove('hidden');
    messageContent?.classList.add('flex');

    // Load conversation history
    await loadConversationHistory(conversationId);

    // Clear unread badge for this conversation (messages marked as read on backend)
    const conversationItem = document.querySelector(`[data-conversation-id="${conversationId}"]`);
    const unreadBadge = conversationItem?.querySelector('.unread-badge');
    if (unreadBadge) {
        unreadBadge.remove();
    }
}

/**
 * Load conversation message history
 */
async function loadConversationHistory(conversationId) {
    try {
        const response = await fetch(`/admin/messages/${conversationId}`, {
            headers: getAuthHeaders()
        });

        if (!response.ok) throw new Error('Failed to load conversation');

        const { data } = await response.json();

        renderMessages(data.messages);

        // Check suspension status
        checkAndHandleSuspension(conversationId);

    } catch (error) {
        console.error('Error loading conversation:', error);
        toast('Failed to load messages', 'error');
    }
}

/**
 * Check if conversation is suspended and handle UI accordingly
 */
async function checkAndHandleSuspension(conversationId) {
    try {
        // Get conversation details from inbox to check suspension
        const inbox = await fetch('/admin/messages/', {
            headers: getAuthHeaders()
        }).then(r => r.json());

        const conversation = inbox.data?.find(c => c.conversation_id == conversationId);

        if (!conversation) return;

        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendMessageBtn');
        const inputContainer = messageInput?.parentElement?.parentElement;

        // Remove existing suspension notice if any
        const existingNotice = document.getElementById('suspensionNotice');
        if (existingNotice) existingNotice.remove();

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

            // Show suspension notice
            const notice = document.createElement('div');
            notice.id = 'suspensionNotice';
            notice.className = 'px-4 py-3 bg-red-50 border-l-4 border-red-500 text-sm text-red-800';

            let suspensionMessage = '⚠️ This conversation has been suspended.';

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
                    suspensionMessage = `⚠️ This conversation is suspended until ${suspendedUntil.toLocaleDateString('en-US', options)}. No messages can be sent during this period.`;
                }
            } else {
                suspensionMessage = '⚠️ This conversation has been permanently suspended. No messages can be sent.';
            }

            if (conversation.reason) {
                suspensionMessage += `<br><strong>Reason:</strong> ${conversation.reason}`;
            }

            notice.innerHTML = suspensionMessage;
            inputContainer?.parentElement?.insertBefore(notice, inputContainer);

            // Show restore button, hide suspend button
            document.getElementById('suspendConversationBtn')?.classList.add('hidden');

            // Create restore button if it doesn't exist
            let restoreBtn = document.getElementById('restoreConversationBtn');
            if (!restoreBtn) {
                restoreBtn = document.createElement('button');
                restoreBtn.id = 'restoreConversationBtn';
                restoreBtn.className = 'px-4 py-2 rounded-lg border-2 border-emerald-300 text-emerald-700 hover:bg-emerald-50 transition text-sm font-semibold flex items-center gap-2';
                restoreBtn.innerHTML = '<i class="fi fi-rr-check-circle"></i><span>Restore</span>';
                restoreBtn.addEventListener('click', () => openRestoreModal());

                // Insert after flag button
                const flagBtn = document.getElementById('flagConversationBtn');
                if (flagBtn && flagBtn.parentElement) {
                    flagBtn.parentElement.insertBefore(restoreBtn, flagBtn.nextSibling);
                }
            } else {
                restoreBtn.classList.remove('hidden');
            }

        } else {
            // Enable input
            if (messageInput) {
                messageInput.disabled = false;
                messageInput.placeholder = 'Type your message...';
                messageInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
            }
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }

            // Show suspend button, hide restore button
            document.getElementById('suspendConversationBtn')?.classList.remove('hidden');
            document.getElementById('restoreConversationBtn')?.classList.add('hidden');
        }

    } catch (error) {
        console.error('Error checking suspension:', error);
    }
}

/**
 * Mark a conversation as read (helper function for real-time updates)
 */
async function markConversationAsRead(conversationId) {
    try {
        // Just fetch the conversation - backend marks as read automatically
        await fetch(`/admin/messages/${conversationId}`, {
            headers: getAuthHeaders()
        });
    } catch (error) {
        console.error('Error marking conversation as read:', error);
    }
}

/**
 * Render messages in conversation view
 */
function renderMessages(messages) {
    const container = document.getElementById('messagesContainer');
    const userId = getUserId();

    if (!messages || messages.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-400 py-8">No messages yet</p>';
        return;
    }

    container.innerHTML = messages.map(msg => {
        const isSent = msg.sender.id === userId;
        const bubbleClass = isSent ? 'message-bubble-sent' : 'message-bubble-received';
        const alignClass = isSent ? 'justify-end' : 'justify-start';

        return `
            <div class="flex ${alignClass} mb-4" data-message-id="${msg.message_id}">
                ${!isSent ? `<img src="${msg.sender.avatar}" class="w-8 h-8 rounded-full mr-2" alt="${msg.sender.name}">` : ''}
                <div class="max-w-[70%]">
                    <div class="${bubbleClass} ${msg.is_flagged ? 'border-2 border-amber-500' : ''}">
                        <p class="text-sm">${escapeHtml(msg.content)}</p>
                        ${msg.attachments.length > 0 ? renderAttachments(msg.attachments) : ''}
                    </div>
                    <div class="flex items-center gap-1 mt-1 ${isSent ? 'justify-end' : 'justify-start'}">
                        <p class="text-xs text-gray-400 relative-time" data-timestamp="${msg.sent_at}">
                            ${formatRelativeTime(msg.sent_at)}
                        </p>
                        ${isSent ? `<span class="text-xs ${msg.is_read ? 'text-blue-500' : 'text-gray-400'}" title="${msg.is_read ? 'Seen' : 'Sent'}">✓${msg.is_read ? '✓' : ''}</span>` : ''}
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
        const response = await fetch('/admin/messages/', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                ...(getAuthToken() && { 'Authorization': `Bearer ${getAuthToken()}` })
            },
            body: formData
        });

        const result = await response.json();

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
        console.error('Error sending message:', error);
        toast('Failed to send message', 'error');
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
    // Handle incoming message

    // If message is for current conversation, append it and mark as read
    if (event.conversation_id === currentConversationId) {
        appendMessage(event);
        // Mark as read immediately since user is viewing the conversation
        markConversationAsRead(event.conversation_id);
    }

    // Reload inbox to update preview
    loadInbox();

    // Reload stats
    loadDashboardStats();

    // Show notification
    toast(`New message from ${event.sender.name}`, 'info');
}

/**
 * Append single message to conversation
 */
function appendMessage(messageData) {
    const container = document.getElementById('messagesContainer');
    const userId = getUserId();
    const isSent = messageData.sender.id === userId;
    const bubbleClass = isSent ? 'message-bubble-sent' : 'message-bubble-received';
    const alignClass = isSent ? 'justify-end' : 'justify-start';

    const messageHtml = `
        <div class="flex ${alignClass} mb-4" data-message-id="${messageData.message_id}">
            ${!isSent ? `<img src="${messageData.sender.avatar}" class="w-8 h-8 rounded-full mr-2" alt="${messageData.sender.name}">` : ''}
            <div class="max-w-[70%]">
                <div class="${bubbleClass}">
                    <p class="text-sm">${escapeHtml(messageData.content)}</p>
                    ${messageData.attachments?.length > 0 ? renderAttachments(messageData.attachments) : ''}
                </div>
                <div class="flex items-center gap-1 mt-1 ${isSent ? 'justify-end' : 'justify-start'}">
                    <p class="text-xs text-gray-400 relative-time" data-timestamp="${messageData.sent_at || new Date().toISOString()}">
                        ${formatRelativeTime(messageData.sent_at || new Date().toISOString())}
                    </p>
                    ${isSent ? `<span class="text-xs ${messageData.is_read ? 'text-blue-500' : 'text-gray-400'}" title="${messageData.is_read ? 'Seen' : 'Sent'}">✓${messageData.is_read ? '✓' : ''}</span>` : ''}
                </div>
            </div>
            ${isSent ? `<img src="${messageData.sender.avatar}" class="w-8 h-8 rounded-full ml-2" alt="${messageData.sender.name}">` : ''}
        </div>
    `;

    container.insertAdjacentHTML('beforeend', messageHtml);
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

    // Compose new message
    document.getElementById('composeBtn')?.addEventListener('click', showComposeModal);

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
        if (!content) {
            toast('Please enter a message', 'warning');
            return;
        }
        if (selectedRecipients.length === 0) {
            toast('Please select at least one recipient', 'warning');
            return;
        }

        try {
            // Send message to each recipient
            const sendPromises = selectedRecipients.map(recipient =>
                fetch('/admin/messages/', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        receiver_id: recipient.id,
                        content: content
                    })
                })
            );

            await Promise.all(sendPromises);

            const recipientCount = selectedRecipients.length;

            // Close modal
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

    // Flag/Suspend conversation buttons
    document.getElementById('flagConversationBtn')?.addEventListener('click', () => openModal('flagConfirmModal'));
    document.getElementById('suspendConversationBtn')?.addEventListener('click', () => openModal('suspendConfirmModal'));

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

    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
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

    // Update active state
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    e.currentTarget.classList.add('active');

    // Apply filter
    filterConversations(filter);
}

/**
 * Filter conversations by status
 */
function filterConversations(filter) {
    const conversationItems = document.querySelectorAll('.conversation-item');
    let visibleCount = 0;

    // Clear search results message if exists
    const container = document.getElementById('conversationsList');
    const searchNoResults = container.querySelector('.no-search-results');
    if (searchNoResults) searchNoResults.remove();

    conversationItems.forEach(item => {
        let show = true;

        if (filter === 'flagged') {
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

        if (filter === 'flagged') {
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
    const container = document.getElementById('conversationsList');
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
        const response = await fetch('/admin/messages/users', {
            headers: getAuthHeaders()
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
            <button class="remove-recipient hover:bg-indigo-200 rounded-full w-4 h-4 flex items-center justify-center text-xs" data-user-id="${user.id}">
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
        const response = await fetch('/admin/messages/', {
            headers: getAuthHeaders()
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
 * Admin: Unflag conversation
 */
async function unflagCurrentConversation() {
    if (!currentConversationId) return;

    try {
        const response = await fetch(`/admin/messages/conversation/${currentConversationId}/unflag`, {
            method: 'POST',
            headers: getAuthHeaders()
        });

        if (!response.ok) throw new Error('Failed to unflag');

        toast('Conversation unflagged successfully', 'success');

        // Close modal
        document.getElementById('unflagConfirmModal')?.classList.add('hidden');
        document.getElementById('unflagConfirmModal')?.classList.remove('flex');

        // Reload data
        loadInbox();
        loadDashboardStats();

    } catch (error) {
        console.error('Error unflagging:', error);
        toast('Failed to unflag conversation', 'error');
    }
}

/**
 * Admin: Suspend conversation
 */
async function suspendCurrentConversation() {
    if (!currentConversationId) return;

    let reason = document.getElementById('suspendReason')?.value;
    const otherReasonText = document.getElementById('otherSuspendReasonText')?.value;
    const duration = document.getElementById('suspendDuration')?.value;
    const notes = document.getElementById('suspendNotes')?.value || '';

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

    if (!duration) {
        toast('Please select suspension duration', 'warning');
        return;
    }

    // Calculate suspended_until based on duration
    let suspendedUntil = null;
    const now = new Date();

    if (duration === '24h') {
        now.setHours(now.getHours() + 24);
        suspendedUntil = now.toISOString().slice(0, 19).replace('T', ' ');
    } else if (duration === '7d') {
        now.setDate(now.getDate() + 7);
        suspendedUntil = now.toISOString().slice(0, 19).replace('T', ' ');
    } else if (duration === '30d') {
        now.setDate(now.getDate() + 30);
        suspendedUntil = now.toISOString().slice(0, 19).replace('T', ' ');
    } else if (duration === 'permanent') {
        // Set to 100 years from now
        now.setFullYear(now.getFullYear() + 100);
        suspendedUntil = now.toISOString().slice(0, 19).replace('T', ' ');
    }

    const fullReason = notes ? `${reason}: ${notes}` : reason;

    try {
        const response = await fetch(`/admin/messages/conversation/${currentConversationId}/suspend`, {
            method: 'POST',
            headers: {
                ...getAuthHeaders(),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                reason: fullReason,
                suspended_until: suspendedUntil
            })
        });

        if (!response.ok) throw new Error('Failed to suspend');

        toast('Conversation suspended', 'success');

        // Close modal
        document.getElementById('suspendConfirmModal')?.classList.add('hidden');
        document.getElementById('suspendConfirmModal')?.classList.remove('flex');

        // Reset form
        document.getElementById('suspendReason').value = '';
        document.getElementById('suspendDuration').value = '24h';
        document.getElementById('suspendNotes').value = '';
        document.getElementById('otherSuspendReasonText').value = '';
        document.getElementById('otherSuspendReasonContainer')?.classList.add('hidden');

        loadInbox();
        loadDashboardStats();

        // Reload current conversation to show suspended state
        if (currentConversationId) {
            selectConversation(currentConversationId, currentReceiverId);
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
    if (!currentConversationId) return;

    try {
        const response = await fetch(`/admin/messages/conversation/${currentConversationId}/restore`, {
            method: 'POST',
            headers: getAuthHeaders()
        });

        if (!response.ok) throw new Error('Failed to restore');

        toast('Conversation restored', 'success');

        // Close modal
        document.getElementById('restoreConfirmModal')?.classList.add('hidden');
        document.getElementById('restoreConfirmModal')?.classList.remove('flex');

        loadInbox();
        loadDashboardStats();

        // Reload current conversation to remove suspended state
        if (currentConversationId) {
            selectConversation(currentConversationId, currentReceiverId);
        }

    } catch (error) {
        console.error('Error restoring:', error);
        toast('Failed to restore conversation', 'error');
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

function getUserId() {
    // Get from session/meta tag
    return parseInt(document.querySelector('meta[name="user-id"]')?.content) || null;
}

function scrollToBottom() {
    const container = document.getElementById('messagesContainer');
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
        const badge = button.querySelector('span');
        if (badge) {
            badge.textContent = count;
        }
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
