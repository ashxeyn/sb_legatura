// Messages Page JavaScript

document.addEventListener('DOMContentLoaded', () => {
    // Initialize messaging interface
    initMessagesInterface();
});

// Store selected files
let selectedFiles = [];

function initMessagesInterface() {
    // Conversation selection
    setupConversationSelection();
    
    // Message sending
    setupMessageSending();
    
    // Search functionality
    setupSearch();
    
    // File attachment functionality
    setupFileAttachment();
    
    // Filter functionality
    setupFilterButtons();
    
    // New message button
    setupNewMessageButton();
    
    // Setup modal close handlers
    setupModalCloseHandlers();
    
    // Auto-scroll to bottom of messages
    scrollToBottom();
    
    // Initialize message count
    updateMessageCount();
    
    // Update unread count
    updateUnreadCount();
    
    // Add view history link interaction
    setupViewHistoryLink();
}

// Setup filter buttons
function setupFilterButtons() {
    const allFilterBtn = document.getElementById('allFilterBtn');
    const unreadFilterBtn = document.getElementById('unreadFilterBtn');
    
    if (!allFilterBtn || !unreadFilterBtn) return;
    
    // All filter button
    allFilterBtn.addEventListener('click', () => {
        allFilterBtn.classList.add('active');
        unreadFilterBtn.classList.remove('active');
        filterConversations('all');
    });
    
    // Unread filter button
    unreadFilterBtn.addEventListener('click', () => {
        unreadFilterBtn.classList.add('active');
        allFilterBtn.classList.remove('active');
        filterConversations('unread');
    });
}

// Filter conversations
function filterConversations(filterType) {
    const conversationItems = document.querySelectorAll('.conversation-item');
    
    conversationItems.forEach(item => {
        if (filterType === 'all') {
            item.style.display = '';
        } else if (filterType === 'unread') {
            const hasUnread = item.querySelector('.conversation-unread:not(.hidden)');
            if (hasUnread) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        }
    });
    
    // Update active conversation if it's hidden
    const activeItem = document.querySelector('.conversation-item.active');
    if (activeItem && activeItem.style.display === 'none') {
        // Find first visible conversation and make it active
        const visibleItems = Array.from(conversationItems).filter(item => item.style.display !== 'none');
        if (visibleItems.length > 0) {
            activeItem.classList.remove('active');
            visibleItems[0].classList.add('active');
            const conversationId = visibleItems[0].getAttribute('data-conversation-id');
            loadConversation(conversationId);
        } else {
            // No unread conversations, show empty state
            const messagesDisplay = document.getElementById('messagesDisplay');
            if (messagesDisplay) {
                messagesDisplay.innerHTML = '<div class="messages-empty-state"><i class="fi fi-rr-envelope"></i><h3>No unread messages</h3><p>All caught up!</p></div>';
            }
        }
    }
}

// Setup new message button
function setupNewMessageButton() {
    const newMessageBtn = document.getElementById('newMessageBtn');
    
    if (!newMessageBtn) return;
    
    newMessageBtn.addEventListener('click', () => {
        // Add click animation
        newMessageBtn.style.transform = 'scale(0.95)';
        setTimeout(() => {
            newMessageBtn.style.transform = '';
        }, 150);
        
        // Open new message modal or interface
        openNewMessageModal();
    });
}

// Open new message modal
function openNewMessageModal() {
    const modal = document.getElementById('newMessageModal');
    if (!modal) {
        console.error('New message modal not found');
        return;
    }
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Focus on search input
    const searchInput = document.getElementById('recipientSearchInput');
    if (searchInput) {
        setTimeout(() => {
            searchInput.focus();
            searchInput.value = '';
        }, 100);
    }
    
    // Setup recipient search
    setupRecipientSearch();
    
    // Setup recipient selection
    setupRecipientSelection();
    
    // Reset filter to show all recipients
    filterRecipients('');
}

// Setup modal close handlers (called once on init)
function setupModalCloseHandlers() {
    const modal = document.getElementById('newMessageModal');
    const closeBtn = document.getElementById('closeNewMessageModalBtn');
    const overlay = document.getElementById('newMessageModalOverlay');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeNewMessageModal);
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeNewMessageModal);
    }
    
    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeNewMessageModal();
        }
    });
}

// Close new message modal
function closeNewMessageModal() {
    const modal = document.getElementById('newMessageModal');
    if (!modal) return;
    
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    
    // Clear search
    const searchInput = document.getElementById('recipientSearchInput');
    if (searchInput) {
        searchInput.value = '';
    }
    
    // Reset filter
    filterRecipients('');
}

// Setup recipient search
function setupRecipientSearch() {
    const searchInput = document.getElementById('recipientSearchInput');
    if (!searchInput) return;
    
    // Remove existing listener if it exists
    if (window.recipientSearchHandler) {
        searchInput.removeEventListener('input', window.recipientSearchHandler);
    }
    
    // Create new handler
    window.recipientSearchHandler = (e) => {
        filterRecipients(e.target.value);
    };
    
    // Add event listener
    searchInput.addEventListener('input', window.recipientSearchHandler);
}

// Filter recipients
function filterRecipients(searchTerm) {
    const recipientItems = document.querySelectorAll('.recipient-item');
    const emptyState = document.getElementById('recipientsEmptyState');
    let visibleCount = 0;
    
    recipientItems.forEach(item => {
        const name = item.getAttribute('data-recipient-name') || '';
        const specialty = item.querySelector('.recipient-specialty')?.textContent || '';
        const searchLower = searchTerm.toLowerCase();
        
        const matches = name.toLowerCase().includes(searchLower) || 
                       specialty.toLowerCase().includes(searchLower);
        
        if (matches || !searchTerm) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show/hide empty state
    if (emptyState) {
        if (visibleCount === 0 && searchTerm) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
        }
    }
}

// Setup recipient selection
function setupRecipientSelection() {
    // Use event delegation on the container for better performance
    const recipientsList = document.getElementById('recipientsList');
    if (!recipientsList) return;
    
    // Remove any existing listener by using a named function
    if (window.recipientClickHandler) {
        recipientsList.removeEventListener('click', window.recipientClickHandler);
    }
    
    // Create new handler
    window.recipientClickHandler = (e) => {
        const selectBtn = e.target.closest('.recipient-select-btn');
        const recipientItem = e.target.closest('.recipient-item');
        
        if (selectBtn || recipientItem) {
            e.stopPropagation();
            
            const item = recipientItem || selectBtn.closest('.recipient-item');
            if (!item) return;
            
            const recipientId = item.getAttribute('data-recipient-id');
            const recipientName = item.getAttribute('data-recipient-name');
            const recipientInitials = item.querySelector('.recipient-avatar span')?.textContent || '';
            const recipientSpecialty = item.querySelector('.recipient-specialty')?.textContent || '';
            const avatarClass = item.querySelector('.recipient-avatar').classList;
            
            if (recipientId && recipientName) {
                selectRecipient(recipientId, recipientName, recipientInitials, recipientSpecialty, avatarClass);
            }
        }
    };
    
    // Add event listener
    recipientsList.addEventListener('click', window.recipientClickHandler);
}

// Select recipient and start conversation
function selectRecipient(recipientId, recipientName, recipientInitials, recipientSpecialty, avatarClass) {
    // Close modal
    closeNewMessageModal();
    
    // Check if conversation already exists
    const existingConversation = document.querySelector(`.conversation-item[data-conversation-id="${recipientId}"]`);
    
    if (existingConversation) {
        // Switch to existing conversation
        existingConversation.click();
    } else {
        // Create new conversation
        createNewConversation(recipientId, recipientName, recipientInitials, recipientSpecialty, avatarClass);
    }
}

// Create new conversation
function createNewConversation(recipientId, recipientName, recipientInitials, recipientSpecialty, avatarClass) {
    const conversationList = document.getElementById('conversationList');
    const conversationTemplate = document.getElementById('conversationItemTemplate');
    
    if (!conversationList || !conversationTemplate) return;
    
    // Create conversation item
    const conversationClone = conversationTemplate.content.cloneNode(true);
    const conversationItem = conversationClone.querySelector('.conversation-item');
    const avatar = conversationClone.querySelector('.conversation-avatar');
    const initials = conversationClone.querySelector('.conversation-initials');
    const name = conversationClone.querySelector('.conversation-name');
    const project = conversationClone.querySelector('.conversation-project');
    const preview = conversationClone.querySelector('.conversation-preview');
    const time = conversationClone.querySelector('.conversation-time');
    
    conversationItem.setAttribute('data-conversation-id', recipientId);
    conversationItem.classList.add('active');
    
    // Remove active from other items
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Set avatar
    avatar.className = 'conversation-avatar';
    if (avatarClass.contains('avatar-red')) avatar.classList.add('avatar-red');
    else if (avatarClass.contains('avatar-green')) avatar.classList.add('avatar-green');
    else if (avatarClass.contains('avatar-blue')) avatar.classList.add('avatar-blue');
    else if (avatarClass.contains('avatar-orange')) avatar.classList.add('avatar-orange');
    else avatar.classList.add('avatar-red'); // default
    
    initials.textContent = recipientInitials;
    name.textContent = recipientName;
    project.textContent = recipientSpecialty;
    preview.textContent = 'Start a conversation...';
    time.textContent = 'Just now';
    
    // Add to top of list
    conversationList.insertBefore(conversationClone, conversationList.firstChild);
    
    // Setup click handler for new conversation
    const newConversationItem = conversationList.querySelector(`.conversation-item[data-conversation-id="${recipientId}"]`);
    if (newConversationItem) {
        newConversationItem.addEventListener('click', () => {
            // Remove active class from all items
            document.querySelectorAll('.conversation-item').forEach(i => i.classList.remove('active'));
            newConversationItem.classList.add('active');
            loadConversation(recipientId);
        });
    }
    
    // Load conversation
    loadConversation(recipientId, recipientName, recipientSpecialty, recipientInitials, avatarClass);
    
    // Update counts
    const allCount = document.querySelector('#allFilterBtn .filter-count');
    if (allCount) {
        const currentCount = parseInt(allCount.textContent) || 0;
        allCount.textContent = currentCount + 1;
    }
}

// Setup view history link interaction
function setupViewHistoryLink() {
    const viewHistoryLink = document.getElementById('viewHistoryLink');
    if (viewHistoryLink) {
        viewHistoryLink.addEventListener('click', (e) => {
            e.preventDefault();
            // Add click animation
            viewHistoryLink.style.transform = 'scale(0.95)';
            setTimeout(() => {
                viewHistoryLink.style.transform = '';
            }, 150);
            
            // TODO: Implement view full history functionality
            console.log('View full history clicked');
        });
    }
}

// Setup conversation selection
function setupConversationSelection() {
    const conversationItems = document.querySelectorAll('.conversation-item');
    
    conversationItems.forEach(item => {
        item.addEventListener('click', (e) => {
            // Add click animation
            item.style.transform = 'scale(0.98)';
            setTimeout(() => {
                item.style.transform = '';
            }, 150);
            
            // Remove active class from all items
            conversationItems.forEach(i => {
                i.classList.remove('active');
                i.style.transform = '';
            });
            
            // Add active class to clicked item
            item.classList.add('active');
            
            // Load conversation messages with fade effect
            const conversationId = item.getAttribute('data-conversation-id');
            loadConversation(conversationId);
        });
        
        // Add hover sound effect (optional - can be removed)
        item.addEventListener('mouseenter', () => {
            item.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
    });
}

// Load conversation messages
function loadConversation(conversationId, name, project, initials, avatarClass) {
    const messagesDisplay = document.getElementById('messagesDisplay');
    
    // Fade out current messages
    if (messagesDisplay) {
        messagesDisplay.style.opacity = '0';
        messagesDisplay.style.transform = 'translateY(10px)';
    }
    
    // Get conversation details from item or parameters
    let conversationName, conversationProject, conversationInitials, conversationAvatarClass;
    
    const conversationItem = document.querySelector(`.conversation-item[data-conversation-id="${conversationId}"]`);
    
    if (conversationItem) {
        conversationName = conversationItem.querySelector('.conversation-name').textContent;
        conversationProject = conversationItem.querySelector('.conversation-project').textContent;
        conversationInitials = conversationItem.querySelector('.conversation-initials').textContent;
        conversationAvatarClass = conversationItem.querySelector('.conversation-avatar').classList;
    } else if (name && project && initials) {
        // Use provided parameters (for new conversations)
        conversationName = name;
        conversationProject = project;
        conversationInitials = initials;
        conversationAvatarClass = avatarClass || { contains: () => false };
    } else {
        return; // No data available
    }
    
    // Update header with animation
    const headerName = document.querySelector('.messages-header-name');
    const headerProject = document.querySelector('.messages-header-project');
    const headerInitials = document.querySelector('.messages-header-initials');
    const headerAvatar = document.querySelector('.messages-header-avatar');
    
    if (headerName) {
        headerName.style.opacity = '0';
        setTimeout(() => {
            headerName.textContent = conversationName;
            headerName.style.opacity = '1';
        }, 150);
    }
    
    if (headerProject) {
        headerProject.style.opacity = '0';
        setTimeout(() => {
            headerProject.textContent = conversationProject;
            headerProject.style.opacity = '1';
        }, 150);
    }
    
    if (headerInitials) {
        headerInitials.textContent = conversationInitials;
    }
    
    // Update avatar color with animation
    if (headerAvatar) {
        headerAvatar.style.transform = 'scale(0.8)';
        headerAvatar.className = 'messages-header-avatar';
        if (conversationAvatarClass.contains('avatar-red')) {
            headerAvatar.classList.add('avatar-red');
        } else if (conversationAvatarClass.contains('avatar-green')) {
            headerAvatar.classList.add('avatar-green');
        } else if (conversationAvatarClass.contains('avatar-blue')) {
            headerAvatar.classList.add('avatar-blue');
        } else if (conversationAvatarClass.contains('avatar-orange')) {
            headerAvatar.classList.add('avatar-orange');
        } else {
            headerAvatar.classList.add('avatar-red'); // default
        }
        setTimeout(() => {
            headerAvatar.style.transform = 'scale(1)';
        }, 150);
    }
    
    // Clear unread badge with animation
    if (conversationItem) {
        const unreadBadge = conversationItem.querySelector('.conversation-unread');
        if (unreadBadge) {
            unreadBadge.style.transform = 'scale(0)';
            setTimeout(() => {
                unreadBadge.classList.add('hidden');
                updateUnreadCount();
            }, 200);
        }
    }
    
    // Clear messages for new conversation or load existing ones
    if (messagesDisplay) {
        const previewText = conversationItem?.querySelector('.conversation-preview')?.textContent;
        if (!conversationItem || previewText === 'Start a conversation...') {
            // New conversation - clear messages and show empty state
            messagesDisplay.innerHTML = '';
        }
    }
    
    // Fade in messages after a short delay
    setTimeout(() => {
        if (messagesDisplay) {
            messagesDisplay.style.transition = 'all 0.3s ease';
            messagesDisplay.style.opacity = '1';
            messagesDisplay.style.transform = 'translateY(0)';
        }
        scrollToBottom();
    }, 200);
}

// Setup file attachment
function setupFileAttachment() {
    const attachBtn = document.getElementById('attachFileBtn');
    const fileInput = document.getElementById('fileInput');
    
    if (!attachBtn || !fileInput) return;
    
    // Open file dialog when attach button is clicked
    attachBtn.addEventListener('click', () => {
        fileInput.click();
    });
    
    // Handle file selection
    fileInput.addEventListener('change', (e) => {
        handleFileSelection(e.target.files);
    });
}

// Handle file selection
function handleFileSelection(files) {
    const fileArray = Array.from(files);
    
    fileArray.forEach(file => {
        // Check file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert(`File "${file.name}" is too large. Maximum size is 10MB.`);
            return;
        }
        
        // Add file to selected files
        selectedFiles.push(file);
        addFilePreview(file);
    });
    
    // Show preview area
    const previewArea = document.getElementById('filePreviewArea');
    if (previewArea && selectedFiles.length > 0) {
        previewArea.classList.remove('hidden');
    }
}

// Add file preview
function addFilePreview(file) {
    const previewContainer = document.getElementById('filePreviewContainer');
    const previewTemplate = document.getElementById('filePreviewTemplate');
    
    if (!previewContainer || !previewTemplate) return;
    
    const previewClone = previewTemplate.content.cloneNode(true);
    const previewItem = previewClone.querySelector('.file-preview-item');
    const previewName = previewClone.querySelector('.file-preview-name');
    const previewSize = previewClone.querySelector('.file-preview-size');
    const previewIcon = previewClone.querySelector('.file-preview-icon');
    const removeBtn = previewClone.querySelector('.file-preview-remove');
    
    previewItem.setAttribute('data-file-name', file.name);
    previewName.textContent = file.name;
    previewSize.textContent = formatFileSize(file.size);
    
    // Set icon based on file type
    if (file.type.startsWith('image/')) {
        previewIcon.className = 'file-preview-icon fi fi-rr-picture';
    } else {
        previewIcon.className = 'file-preview-icon fi fi-rr-file';
    }
    
    // Remove file on button click
    removeBtn.addEventListener('click', () => {
        removeFilePreview(file.name);
    });
    
    previewContainer.appendChild(previewClone);
}

// Remove file preview
function removeFilePreview(fileName) {
    // Remove from selected files
    selectedFiles = selectedFiles.filter(file => file.name !== fileName);
    
    // Remove preview element
    const previewItem = document.querySelector(`.file-preview-item[data-file-name="${fileName}"]`);
    if (previewItem) {
        previewItem.remove();
    }
    
    // Hide preview area if no files
    const previewArea = document.getElementById('filePreviewArea');
    if (previewArea && selectedFiles.length === 0) {
        previewArea.classList.add('hidden');
    }
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Setup message sending
function setupMessageSending() {
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendMessageBtn');
    
    if (!messageInput || !sendBtn) return;
    
    // Auto-resize textarea
    messageInput.addEventListener('input', () => {
        messageInput.style.height = 'auto';
        messageInput.style.height = messageInput.scrollHeight + 'px';
        messageInput.style.maxHeight = '120px';
    });
    
    // Send message on button click
    sendBtn.addEventListener('click', () => {
        sendMessage();
    });
    
    // Send message on Enter (but allow Shift+Enter for new line)
    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
}

// Send message
function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const messagesDisplay = document.getElementById('messagesDisplay');
    const sendBtn = document.getElementById('sendMessageBtn');
    
    if (!messageInput || !messagesDisplay) return;
    
    const messageText = messageInput.value.trim();
    
    // Allow sending if there's text or files
    if (!messageText && selectedFiles.length === 0) return;
    
    // Disable send button and show loading state
    if (sendBtn) {
        sendBtn.disabled = true;
        sendBtn.style.opacity = '0.6';
        sendBtn.style.cursor = 'not-allowed';
    }
    
    // Create message bubble with animation
    const messageTemplate = document.getElementById('messageTemplate');
    let messageBubble;
    
    if (messageTemplate) {
        const messageClone = messageTemplate.content.cloneNode(true);
        messageBubble = messageClone.querySelector('.message-bubble');
        const messageContent = messageClone.querySelector('.message-content');
        const messageTime = messageClone.querySelector('.message-time');
        const messageAttachments = messageClone.querySelector('.message-attachments');
        
        messageBubble.setAttribute('data-message-type', 'outgoing');
        messageBubble.classList.add('message-outgoing');
        
        // Set message text (or placeholder if only files)
        if (messageText) {
            messageContent.textContent = messageText;
        } else {
            messageContent.textContent = 'Sent files';
        }
        
        messageTime.textContent = 'Just now';
        
        // Add file attachments if any
        if (selectedFiles.length > 0) {
            messageAttachments.classList.remove('hidden');
            selectedFiles.forEach(file => {
                addAttachmentToMessage(messageAttachments, file);
            });
        }
        
        // Add initial animation state
        messageBubble.style.opacity = '0';
        messageBubble.style.transform = 'translateY(20px) scale(0.9)';
        
        messagesDisplay.appendChild(messageClone);
    } else {
        // Fallback if template doesn't exist
        messageBubble = document.createElement('div');
        messageBubble.className = 'message-bubble message-outgoing';
        messageBubble.setAttribute('data-message-type', 'outgoing');
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.textContent = messageText || 'Sent files';
        
        const timeDiv = document.createElement('div');
        timeDiv.className = 'message-time';
        timeDiv.textContent = 'Just now';
        
        messageBubble.appendChild(contentDiv);
        messageBubble.appendChild(timeDiv);
        
        messageBubble.style.opacity = '0';
        messageBubble.style.transform = 'translateY(20px) scale(0.9)';
        
        messagesDisplay.appendChild(messageBubble);
    }
    
    // Update conversation preview
    updateConversationPreview(messageText || 'Sent files');
    
    // Clear selected files
    clearFileSelection();
    
    // Animate message appearance
    setTimeout(() => {
        if (messageBubble) {
            messageBubble.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            messageBubble.style.opacity = '1';
            messageBubble.style.transform = 'translateY(0) scale(1)';
        }
    }, 10);
    
    // Clear input with animation
    messageInput.value = '';
    messageInput.style.height = 'auto';
    
    // Scroll to bottom smoothly
    setTimeout(() => {
        scrollToBottom();
    }, 100);
    
    // Re-enable send button
    setTimeout(() => {
        if (sendBtn) {
            sendBtn.disabled = false;
            sendBtn.style.opacity = '1';
            sendBtn.style.cursor = 'pointer';
        }
    }, 500);
    
    // Update message count in footer
    updateMessageCount();
    
    // TODO: Send message to server via API
    // sendMessageToServer(messageText);
}

// Update message count in footer
function updateMessageCount() {
    const messagesDisplay = document.getElementById('messagesDisplay');
    const totalMessagesCount = document.getElementById('totalMessagesCount');
    
    if (messagesDisplay && totalMessagesCount) {
        const messageCount = messagesDisplay.querySelectorAll('.message-bubble').length;
        totalMessagesCount.textContent = messageCount;
        
        // Add animation to count
        totalMessagesCount.style.transform = 'scale(1.2)';
        setTimeout(() => {
            totalMessagesCount.style.transform = 'scale(1)';
        }, 200);
    }
}

// Update unread count
function updateUnreadCount() {
    const unreadCountElement = document.getElementById('unreadCount');
    if (!unreadCountElement) return;
    
    const unreadConversations = document.querySelectorAll('.conversation-unread:not(.hidden)');
    const count = unreadConversations.length;
    unreadCountElement.textContent = count;
    
    // Hide unread filter if no unread messages
    const unreadFilterBtn = document.getElementById('unreadFilterBtn');
    if (unreadFilterBtn) {
        if (count === 0) {
            unreadFilterBtn.style.opacity = '0.5';
            unreadFilterBtn.style.cursor = 'not-allowed';
        } else {
            unreadFilterBtn.style.opacity = '1';
            unreadFilterBtn.style.cursor = 'pointer';
        }
    }
}

// Setup search functionality
function setupSearch() {
    const searchInput = document.getElementById('conversationSearchInput');
    const conversationList = document.getElementById('conversationList');
    
    if (!searchInput || !conversationList) return;
    
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase().trim();
        const conversationItems = conversationList.querySelectorAll('.conversation-item');
        
        conversationItems.forEach(item => {
            const name = item.querySelector('.conversation-name')?.textContent.toLowerCase() || '';
            const project = item.querySelector('.conversation-project')?.textContent.toLowerCase() || '';
            const preview = item.querySelector('.conversation-preview')?.textContent.toLowerCase() || '';
            
            const matches = name.includes(searchTerm) || 
                          project.includes(searchTerm) || 
                          preview.includes(searchTerm);
            
            if (matches || !searchTerm) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
}

// Scroll to bottom of messages with smooth animation
function scrollToBottom() {
    const messagesDisplay = document.getElementById('messagesDisplay');
    if (messagesDisplay) {
        messagesDisplay.scrollTo({
            top: messagesDisplay.scrollHeight,
            behavior: 'smooth'
        });
    }
}

// Format timestamp
function formatTimestamp(timestamp) {
    const now = new Date();
    const messageDate = new Date(timestamp);
    const diffInSeconds = Math.floor((now - messageDate) / 1000);
    
    if (diffInSeconds < 60) {
        return 'Just now';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} ${minutes === 1 ? 'minute' : 'minutes'} ago`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} ${hours === 1 ? 'hour' : 'hours'} ago`;
    } else if (diffInSeconds < 604800) {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days} ${days === 1 ? 'day' : 'days'} ago`;
    } else {
        return messageDate.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        });
    }
}

// Add attachment to message display
function addAttachmentToMessage(attachmentsContainer, file) {
    const attachmentDiv = document.createElement('div');
    attachmentDiv.className = 'message-attachment';
    
    if (file.type.startsWith('image/')) {
        // For images, create image preview
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.className = 'message-attachment-image';
        img.alt = file.name;
        img.onclick = () => {
            // Open image in new window/tab
            window.open(img.src, '_blank');
        };
        attachmentDiv.appendChild(img);
    } else {
        // For other files, create file link
        const icon = document.createElement('i');
        icon.className = 'message-attachment-icon fi fi-rr-file';
        
        const info = document.createElement('div');
        info.className = 'message-attachment-info';
        
        const name = document.createElement('div');
        name.className = 'message-attachment-name';
        name.textContent = file.name;
        
        const size = document.createElement('div');
        size.className = 'message-attachment-size';
        size.textContent = formatFileSize(file.size);
        
        info.appendChild(name);
        info.appendChild(size);
        
        attachmentDiv.appendChild(icon);
        attachmentDiv.appendChild(info);
        
        // Make it clickable to download
        attachmentDiv.style.cursor = 'pointer';
        attachmentDiv.onclick = () => {
            const url = URL.createObjectURL(file);
            const a = document.createElement('a');
            a.href = url;
            a.download = file.name;
            a.click();
            URL.revokeObjectURL(url);
        };
    }
    
    attachmentsContainer.appendChild(attachmentDiv);
}

// Update conversation preview
function updateConversationPreview(messageText) {
    const activeConversation = document.querySelector('.conversation-item.active');
    if (activeConversation) {
        const preview = activeConversation.querySelector('.conversation-preview');
        const time = activeConversation.querySelector('.conversation-time');
        
        if (preview) {
            preview.textContent = messageText.length > 50 ? messageText.substring(0, 50) + '...' : messageText;
        }
        
        if (time) {
            time.textContent = 'Just now';
        }
    }
}

// Clear file selection
function clearFileSelection() {
    selectedFiles = [];
    const previewContainer = document.getElementById('filePreviewContainer');
    const previewArea = document.getElementById('filePreviewArea');
    const fileInput = document.getElementById('fileInput');
    
    if (previewContainer) {
        previewContainer.innerHTML = '';
    }
    
    if (previewArea) {
        previewArea.classList.add('hidden');
    }
    
    if (fileInput) {
        fileInput.value = '';
    }
}

// TODO: API Functions (to be implemented)
// function loadConversations() {
//     // Fetch conversations from API
// }

// function loadMessages(conversationId) {
//     // Fetch messages for a conversation from API
// }

// function sendMessageToServer(messageText, files) {
//     // Send message with files to server via API
//     const formData = new FormData();
//     formData.append('message', messageText);
//     files.forEach((file, index) => {
//         formData.append(`files[${index}]`, file);
//     });
//     // Send formData to server
// }
