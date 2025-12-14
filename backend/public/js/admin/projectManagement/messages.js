// Messages page interactivity

// Mock data for conversations
const conversationsData = [
  {
    id: 1,
    name: 'John Martinez',
    avatar: 'https://ui-avatars.com/api/?name=John+Martinez&background=6366f1&color=fff',
    project: 'Commercial Building Renovation',
    lastMessage: 'Thanks for the update on the materials delivery.',
    time: '2 min ago',
    unread: 3,
    status: 'active',
    flagged: false,
    suspended: false,
    online: true,
    messages: [
      { text: 'Hi, I wanted to check on the project timeline', sent: false, time: '10:30 AM' },
      { text: 'Hello! The timeline is on track. We should be done by next week.', sent: true, time: '10:32 AM' },
      { text: 'Great! What about the materials?', sent: false, time: '10:35 AM' },
      { text: 'Materials are scheduled for delivery tomorrow morning.', sent: true, time: '10:37 AM' },
      { text: 'Thanks for the update on the materials delivery.', sent: false, time: '2 min ago' }
    ],
    startDate: 'Nov 20, 2025',
    messageCount: 45
  },
  {
    id: 2,
    name: 'Sarah Chen',
    avatar: 'https://ui-avatars.com/api/?name=Sarah+Chen&background=8b5cf6&color=fff',
    project: 'Residential Home Extension',
    lastMessage: 'Can we schedule a site visit?',
    time: '15 min ago',
    unread: 1,
    status: 'active',
    flagged: false,
    suspended: false,
    online: true,
    messages: [
      { text: 'Good morning! How is the extension coming along?', sent: false, time: '9:00 AM' },
      { text: 'Morning! Everything is progressing well. We finished the foundation work.', sent: true, time: '9:15 AM' },
      { text: 'Excellent news! When can I come see it?', sent: false, time: '9:20 AM' },
      { text: 'Can we schedule a site visit?', sent: false, time: '15 min ago' }
    ],
    startDate: 'Nov 18, 2025',
    messageCount: 32
  },
  {
    id: 3,
    name: 'David Park',
    avatar: 'https://ui-avatars.com/api/?name=David+Park&background=f59e0b&color=fff',
    project: 'Office Space Remodeling',
    lastMessage: 'I need to discuss the payment terms.',
    time: '1 hour ago',
    unread: 0,
    status: 'flagged',
    flagged: true,
    suspended: false,
    online: false,
    messages: [
      { text: 'The initial quote seems higher than expected', sent: false, time: '8:00 AM' },
      { text: 'I understand your concern. Let me break down the costs for you.', sent: true, time: '8:30 AM' },
      { text: 'I need to discuss the payment terms.', sent: false, time: '1 hour ago' }
    ],
    startDate: 'Nov 15, 2025',
    messageCount: 28
  },
  {
    id: 4,
    name: 'Lisa Anderson',
    avatar: 'https://ui-avatars.com/api/?name=Lisa+Anderson&background=ef4444&color=fff',
    project: 'Kitchen Renovation',
    lastMessage: 'This is unacceptable! I want a refund!',
    time: '2 hours ago',
    unread: 0,
    status: 'suspended',
    flagged: false,
    suspended: true,
    online: false,
    messages: [
      { text: 'The cabinets you installed are the wrong color!', sent: false, time: 'Yesterday' },
      { text: 'I apologize for the error. We can replace them at no additional cost.', sent: true, time: 'Yesterday' },
      { text: 'This is unacceptable! I want a refund!', sent: false, time: '2 hours ago' }
    ],
    startDate: 'Nov 10, 2025',
    messageCount: 56
  },
  {
    id: 5,
    name: 'Michael Torres',
    avatar: 'https://ui-avatars.com/api/?name=Michael+Torres&background=10b981&color=fff',
    project: 'Bathroom Remodeling',
    lastMessage: 'The work looks amazing! Thank you.',
    time: '3 hours ago',
    unread: 0,
    status: 'active',
    flagged: false,
    suspended: false,
    online: true,
    messages: [
      { text: 'Just finished inspecting the bathroom. Looks great!', sent: false, time: '3 hours ago' },
      { text: 'Thank you! We are glad you are satisfied with the work.', sent: true, time: '3 hours ago' },
      { text: 'The work looks amazing! Thank you.', sent: false, time: '3 hours ago' }
    ],
    startDate: 'Nov 12, 2025',
    messageCount: 41
  },
  {
    id: 6,
    name: 'Emma Wilson',
    avatar: 'https://ui-avatars.com/api/?name=Emma+Wilson&background=3b82f6&color=fff',
    project: 'Garden Landscaping',
    lastMessage: 'When will the plants be delivered?',
    time: '5 hours ago',
    unread: 2,
    status: 'active',
    flagged: false,
    suspended: false,
    online: false,
    messages: [
      { text: 'I love the design you proposed!', sent: false, time: 'Yesterday' },
      { text: 'Thank you! We will start installation next week.', sent: true, time: 'Yesterday' },
      { text: 'When will the plants be delivered?', sent: false, time: '5 hours ago' }
    ],
    startDate: 'Nov 19, 2025',
    messageCount: 23
  },
  {
    id: 7,
    name: 'Robert Kim',
    avatar: 'https://ui-avatars.com/api/?name=Robert+Kim&background=f59e0b&color=fff',
    project: 'Basement Finishing',
    lastMessage: 'I have concerns about the electrical work.',
    time: '1 day ago',
    unread: 0,
    status: 'flagged',
    flagged: true,
    suspended: false,
    online: false,
    messages: [
      { text: 'The electrical outlets are not where I asked them to be', sent: false, time: '1 day ago' },
      { text: 'I apologize. Let me review the plans and get back to you.', sent: true, time: '1 day ago' },
      { text: 'I have concerns about the electrical work.', sent: false, time: '1 day ago' }
    ],
    startDate: 'Nov 8, 2025',
    messageCount: 67
  }
];

let currentFilter = 'all';
let selectedConversation = null;

document.addEventListener('DOMContentLoaded', () => {
  renderConversations();
  setupEventListeners();
});

// Render conversations list
function renderConversations(filter = 'all') {
  const list = document.getElementById('conversationsList');
  let filtered = conversationsData;

  if (filter === 'flagged') {
    filtered = conversationsData.filter(c => c.flagged);
  } else if (filter === 'suspended') {
    filtered = conversationsData.filter(c => c.suspended);
  }

  list.innerHTML = filtered.map(conv => `
    <div class="conversation-item ${conv.flagged ? 'flagged' : ''} ${conv.suspended ? 'suspended' : ''}" data-id="${conv.id}">
      <div class="flex items-start gap-3">
        <div class="relative flex-shrink-0">
          <img src="${conv.avatar}" alt="${conv.name}" class="w-12 h-12 rounded-full object-cover">
          <span class="avatar-status ${conv.online ? 'online' : 'offline'}"></span>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between mb-1">
            <div class="flex items-center gap-2">
              <h4 class="font-semibold text-gray-800 truncate">${conv.name}</h4>
              ${conv.flagged ? '<i class="fi fi-sr-flag text-amber-500 text-xs"></i>' : ''}
              ${conv.suspended ? '<i class="fi fi-sr-ban text-red-500 text-xs"></i>' : ''}
            </div>
            <span class="text-xs text-gray-400 flex-shrink-0">${conv.time}</span>
          </div>
          <p class="text-xs text-gray-500 mb-1 truncate">${conv.project}</p>
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600 truncate flex-1">${conv.lastMessage}</p>
            ${conv.unread > 0 ? `<span class="unread-badge ml-2">${conv.unread}</span>` : ''}
          </div>
        </div>
      </div>
    </div>
  `).join('');

  // Add click listeners
  document.querySelectorAll('.conversation-item').forEach(item => {
    item.addEventListener('click', () => {
      const id = parseInt(item.dataset.id);
      selectConversation(id);
    });
  });
}

// Select and display conversation
function selectConversation(id) {
  selectedConversation = conversationsData.find(c => c.id === id);
  if (!selectedConversation) return;

  // Update active state
  document.querySelectorAll('.conversation-item').forEach(item => {
    item.classList.remove('active');
  });
  document.querySelector(`[data-id="${id}"]`).classList.add('active');

  // Clear unread count
  selectedConversation.unread = 0;

  // Show message panel
  document.getElementById('emptyState').classList.add('hidden');
  document.getElementById('messageContent').classList.remove('hidden');
  document.getElementById('messageContent').classList.add('flex');

  // Update header
  document.getElementById('selectedAvatar').src = selectedConversation.avatar;
  document.getElementById('selectedName').textContent = selectedConversation.name;
  document.getElementById('selectedProject').textContent = selectedConversation.project;
  document.getElementById('selectedStatus').className = `absolute bottom-0 right-0 w-3.5 h-3.5 ${selectedConversation.online ? 'bg-emerald-500' : 'bg-gray-400'} border-2 border-white rounded-full`;

  // Update footer
  document.getElementById('conversationDate').textContent = `Started: ${selectedConversation.startDate}`;
  document.getElementById('messageCount').textContent = `${selectedConversation.messageCount} messages`;

  // Render messages
  renderMessages();

  // Update button states
  updateActionButtons();
}

// Render messages in conversation
function renderMessages() {
  const area = document.getElementById('messagesArea');
  area.innerHTML = selectedConversation.messages.map(msg => `
    <div class="flex ${msg.sent ? 'justify-end' : 'justify-start'} mb-4">
      <div class="message-bubble ${msg.sent ? 'sent' : 'received'}">
        <div>${msg.text}</div>
        <span class="message-time">${msg.time}</span>
      </div>
    </div>
  `).join('');
  area.scrollTop = area.scrollHeight;
}

// Update action button states
function updateActionButtons() {
  const flagBtn = document.getElementById('flagConversationBtn');
  const suspendBtn = document.getElementById('suspendConversationBtn');

  if (selectedConversation.flagged) {
    flagBtn.innerHTML = '<i class="fi fi-rr-flag"></i><span>Unflag</span>';
    flagBtn.classList.add('bg-amber-50', 'border-amber-400', 'text-amber-700');
  } else {
    flagBtn.innerHTML = '<i class="fi fi-rr-flag"></i><span>Flag</span>';
    flagBtn.classList.remove('bg-amber-50', 'border-amber-400', 'text-amber-700');
  }

  if (selectedConversation.suspended) {
    suspendBtn.innerHTML = '<i class="fi fi-rr-check-circle"></i><span>Restore</span>';
    suspendBtn.classList.add('bg-emerald-50', 'border-emerald-400', 'text-emerald-700');
    suspendBtn.classList.remove('border-red-300', 'text-red-700');
  } else {
    suspendBtn.innerHTML = '<i class="fi fi-rr-ban"></i><span>Suspend</span>';
    suspendBtn.classList.remove('bg-emerald-50', 'border-emerald-400', 'text-emerald-700');
    suspendBtn.classList.add('border-red-300', 'text-red-700');
  }
}

// Setup event listeners
function setupEventListeners() {
  // Filter tabs
  document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      currentFilter = tab.dataset.filter;
      renderConversations(currentFilter);
    });
  });

  // Search
  document.getElementById('conversationSearch').addEventListener('input', (e) => {
    const query = e.target.value.toLowerCase();
    document.querySelectorAll('.conversation-item').forEach(item => {
      const name = item.querySelector('h4').textContent.toLowerCase();
      const project = item.querySelector('.text-xs.text-gray-500').textContent.toLowerCase();
      if (name.includes(query) || project.includes(query)) {
        item.style.display = '';
      } else {
        item.style.display = 'none';
      }
    });
  });

  // Flag button
  document.getElementById('flagConversationBtn').addEventListener('click', () => {
    if (selectedConversation.flagged) {
      // Show unflag confirmation modal
      document.getElementById('unflagConvName').textContent = selectedConversation.name;
      showModal('unflagConfirmModal');
    } else {
      // Show flag modal
      showModal('flagConfirmModal');
    }
  });

  // Suspend button
  document.getElementById('suspendConversationBtn').addEventListener('click', () => {
    if (selectedConversation.suspended) {
      // Show restore confirmation modal
      document.getElementById('restoreConvId').textContent = `#${selectedConversation.id}`;
      document.getElementById('restoreConvName').textContent = selectedConversation.name;
      document.getElementById('restoreConvStatus').textContent = selectedConversation.flagged ? 'Flagged & Suspended' : 'Suspended';
      showModal('restoreConfirmModal');
    } else {
      // Show suspend modal
      showModal('suspendConfirmModal');
    }
  });

  // Confirm flag
  document.getElementById('confirmFlagBtn').addEventListener('click', () => {
    const reason = document.getElementById('flagReason').value;
    if (!reason) {
      toast('Please select a reason', 'error');
      return;
    }
    selectedConversation.flagged = true;
    hideModal('flagConfirmModal');
    updateActionButtons();
    renderConversations(currentFilter);
    toast('Conversation flagged successfully', 'success');
    // Reset form
    document.getElementById('flagReason').value = '';
    document.getElementById('flagNotes').value = '';
  });

  // Confirm unflag
  document.getElementById('confirmUnflagBtn').addEventListener('click', () => {
    selectedConversation.flagged = false;
    hideModal('unflagConfirmModal');
    updateActionButtons();
    renderConversations(currentFilter);
    toast('Flag removed successfully', 'info');
  });

  // Confirm suspend
  document.getElementById('confirmSuspendBtn').addEventListener('click', () => {
    const reason = document.getElementById('suspendReason').value;
    if (!reason) {
      toast('Please select a reason', 'error');
      return;
    }
    selectedConversation.suspended = true;
    hideModal('suspendConfirmModal');
    updateActionButtons();
    renderConversations(currentFilter);
    toast('Conversation suspended successfully', 'success');
    // Reset form
    document.getElementById('suspendReason').value = '';
    document.getElementById('suspendDuration').value = '24h';
    document.getElementById('suspendNotes').value = '';
  });

  // Confirm restore
  document.getElementById('confirmRestoreBtn').addEventListener('click', () => {
    selectedConversation.suspended = false;
    selectedConversation.flagged = false; // Also remove flag when restoring
    hideModal('restoreConfirmModal');
    updateActionButtons();
    renderConversations(currentFilter);
    toast('Conversation restored successfully', 'success');
    // Reset form
    document.getElementById('restoreNotes').value = '';
  });

  // Modal close buttons
  document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const modal = e.target.closest('.modal-overlay');
      if (modal) hideModal(modal.id);
    });
  });

  // Close modal on overlay click
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        hideModal(overlay.id);
      }
    });
  });

  // Compose button & enhanced multi-recipient + attachments logic
  const composeBtn = document.getElementById('composeBtn');
  const sendComposeBtn = document.getElementById('sendComposeBtn');
  const composeMessage = document.getElementById('composeMessage');
  const composeProject = document.getElementById('composeProject');
  const composeCharCount = document.getElementById('composeCharCount');
  const recipientsWrapper = document.getElementById('composeRecipientsWrapper');
  const recipientSearch = document.getElementById('composeRecipientSearch');
  const recipientDropdown = document.getElementById('composeRecipientDropdown');
  const attachmentInput = document.getElementById('composeAttachmentInput');
  const attachmentDrop = document.getElementById('composeAttachmentDrop');
  const attachmentPreview = document.getElementById('composeAttachmentPreview');

  let selectedRecipients = []; // { name, email }
  let selectedAttachments = []; // { file, url, isImage }
  const MAX_RECIPIENTS = 8;
  const MAX_ATTACHMENTS = 8;
  const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

  // Build suggestion list from existing conversations (demo purpose)
  const availableRecipients = Array.from(
    new Map(
      conversationsData.map(c => [c.name, {
        name: c.name,
        email: (c.name.split(' ').join('.').toLowerCase() + '@example.com')
      }])
    ).values()
  );

  if (composeBtn) {
    composeBtn.addEventListener('click', () => {
      resetComposeForm();
      showModal('composeModal');
      recipientSearch.focus();
    });
  }

  // Compose tabs
  document.querySelectorAll('.compose-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.compose-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
    });
  });

  // Recipient search & dropdown
  if (recipientSearch) {
    recipientSearch.addEventListener('input', () => {
      renderRecipientDropdown(recipientSearch.value.trim().toLowerCase());
    });

    recipientSearch.addEventListener('keydown', (e) => {
      const value = recipientSearch.value.trim();
      if ((e.key === 'Enter' || e.key === ',') && value.length > 1) {
        e.preventDefault();
        addRecipient({
          name: value.replace(/,$/, ''),
          email: value.replace(/\s+/g, '.').toLowerCase() + '@example.com'
        });
        recipientSearch.value = '';
        hideRecipientDropdown();
      } else if (e.key === 'Backspace' && value === '' && selectedRecipients.length) {
        // remove last recipient quickly
        selectedRecipients.pop();
        renderRecipientChips();
        validateCompose();
      }
    });

    recipientSearch.addEventListener('focus', () => {
      if (recipientSearch.value.trim() === '') renderRecipientDropdown('');
    });
  }

  function renderRecipientDropdown(filter) {
    if (!recipientDropdown) return;
    const filtered = availableRecipients.filter(r => r.name.toLowerCase().includes(filter) && !selectedRecipients.some(s => s.name === r.name)).slice(0, 10);
    if (filtered.length === 0) {
      recipientDropdown.innerHTML = '<div class="dropdown-item text-gray-500">No matches</div>';
    } else {
      recipientDropdown.innerHTML = filtered.map(r => `<div class="dropdown-item" data-name="${r.name}"><i class="fi fi-rr-user text-indigo-500"></i><span>${r.name}</span></div>`).join('');
    }
    recipientDropdown.classList.remove('hidden');
    recipientDropdown.querySelectorAll('.dropdown-item[data-name]').forEach(item => {
      item.addEventListener('click', () => {
        const name = item.dataset.name;
        const rec = availableRecipients.find(r => r.name === name);
        if (rec) addRecipient(rec);
        recipientSearch.value = '';
        hideRecipientDropdown();
        recipientSearch.focus();
      });
    });
  }

  function hideRecipientDropdown() {
    if (recipientDropdown) recipientDropdown.classList.add('hidden');
  }

  document.addEventListener('click', (e) => {
    if (recipientDropdown && !recipientDropdown.contains(e.target) && !recipientsWrapper.contains(e.target)) hideRecipientDropdown();
  });

  function addRecipient(r) {
    if (selectedRecipients.length >= MAX_RECIPIENTS) {
      toast('Max recipients reached', 'error');
      return;
    }
    if (!selectedRecipients.some(x => x.name === r.name)) {
      selectedRecipients.push(r);
      renderRecipientChips();
      validateCompose();
    }
  }

  function renderRecipientChips() {
    if (!recipientsWrapper) return;
    // Preserve input element, clear chips first
    const inputEl = recipientSearch;
    recipientsWrapper.innerHTML = '';
    selectedRecipients.forEach(rec => {
      const chip = document.createElement('div');
      chip.className = 'compose-recipient-chip';
      chip.innerHTML = `<span>${rec.name}</span><button type="button" aria-label="Remove">&times;</button>`;
      chip.querySelector('button').addEventListener('click', () => {
        selectedRecipients = selectedRecipients.filter(r => r.name !== rec.name);
        renderRecipientChips();
        validateCompose();
      });
      recipientsWrapper.appendChild(chip);
    });
    recipientsWrapper.appendChild(inputEl);
  }

  // Attachments handling
  if (attachmentDrop) {
    attachmentDrop.addEventListener('click', () => attachmentInput && attachmentInput.click());
    attachmentDrop.addEventListener('dragover', (e) => {
      e.preventDefault();
      attachmentDrop.classList.add('dragover');
    });
    attachmentDrop.addEventListener('dragleave', () => attachmentDrop.classList.remove('dragover'));
    attachmentDrop.addEventListener('drop', (e) => {
      e.preventDefault();
      attachmentDrop.classList.remove('dragover');
      if (e.dataTransfer.files) handleFiles(e.dataTransfer.files);
    });
  }

  if (attachmentInput) {
    attachmentInput.addEventListener('change', (e) => {
      if (e.target.files) handleFiles(e.target.files);
      attachmentInput.value = '';
    });
  }

  function handleFiles(fileList) {
    const files = Array.from(fileList);
    for (const file of files) {
      if (selectedAttachments.length >= MAX_ATTACHMENTS) {
        toast('Max attachments reached', 'error');
        break;
      }
      if (file.size > MAX_FILE_SIZE) {
        toast(`${file.name} exceeds 5MB`, 'error');
        continue;
      }
      const isImage = file.type.startsWith('image/');
      const url = isImage ? URL.createObjectURL(file) : null;
      selectedAttachments.push({ file, url, isImage });
    }
    renderAttachmentPreviews();
    validateCompose();
  }

  function renderAttachmentPreviews() {
    if (!attachmentPreview) return;
    attachmentPreview.innerHTML = selectedAttachments.map((att, idx) => {
      if (att.isImage) {
        return `<div class="attachment-item" data-idx="${idx}"><img src="${att.url}" alt="attachment"><div class="attachment-meta">${Math.round(att.file.size/1024)}KB</div><div class="remove-attachment" title="Remove">&times;</div></div>`;
      }
      const short = att.file.name.length > 14 ? att.file.name.slice(0,11)+'…' : att.file.name;
      return `<div class="attachment-item" data-idx="${idx}"><div class="file-icon"><i class="fi fi-rr-file"></i><div>${short}</div></div><div class="attachment-meta">${Math.round(att.file.size/1024)}KB</div><div class="remove-attachment" title="Remove">&times;</div></div>`;
    }).join('');
    attachmentPreview.querySelectorAll('.remove-attachment').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const parent = e.target.closest('.attachment-item');
        const idx = parseInt(parent.dataset.idx);
        const att = selectedAttachments[idx];
        if (att && att.url) URL.revokeObjectURL(att.url);
        selectedAttachments.splice(idx,1);
        renderAttachmentPreviews();
        validateCompose();
      });
    });
  }

  // Textarea char count & auto-height
  if (composeMessage) {
    composeMessage.addEventListener('input', () => {
      const max = 1000;
      const len = composeMessage.value.length;
      composeCharCount.textContent = `${len} / ${max}`;
      composeCharCount.classList.remove('warn','error');
      if (len > 900 && len <= max) composeCharCount.classList.add('warn');
      if (len > max) composeCharCount.classList.add('error');
      composeMessage.style.height = 'auto';
      composeMessage.style.height = composeMessage.scrollHeight + 'px';
      validateCompose();
    });
  }

  function validateCompose() {
    if (!sendComposeBtn) return;
    const msgValid = composeMessage.value.trim().length > 0 && composeMessage.value.length <= 1000;
    const recipientsValid = selectedRecipients.length > 0;
    sendComposeBtn.disabled = !(msgValid && recipientsValid);
  }

  function resetComposeForm() {
    selectedRecipients = [];
    selectedAttachments.forEach(a => a.url && URL.revokeObjectURL(a.url));
    selectedAttachments = [];
    composeProject.value = '';
    composeMessage.value = '';
    composeMessage.style.height = '140px';
    composeCharCount.textContent = '0 / 1000';
    composeCharCount.className = composeCharCount.className.replace(/warn|error/g,'').trim();
    renderRecipientChips();
    renderAttachmentPreviews();
    validateCompose();
  }

  if (sendComposeBtn) {
    sendComposeBtn.addEventListener('click', () => {
      if (sendComposeBtn.disabled) return;
      const typeTab = document.querySelector('.compose-tab.active');
      const type = typeTab ? typeTab.dataset.type : 'contractor';
      const project = composeProject.value.trim() || 'General Inquiry';
      const messageText = composeMessage.value.trim();
      const id = Math.max(0, ...conversationsData.map(c => c.id)) + 1;
      const backgroundMap = { contractor:'6366f1', property_owner:'10b981' };
      const bg = backgroundMap[type] || '6366f1';
      const now = new Date();
      const timeString = 'just now';
      const startDate = now.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

      // Conversation display name logic for multi-recipient
      const displayName = selectedRecipients.length === 1 
        ? selectedRecipients[0].name 
        : `${selectedRecipients[0].name} + ${selectedRecipients.length - 1} others`;

      const newConv = {
        id,
        name: displayName,
        avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(selectedRecipients[0].name)}&background=${bg}&color=fff`,
        project,
        lastMessage: messageText,
        time: timeString,
        unread: 0,
        status: 'active',
        flagged: false,
        suspended: false,
        online: true,
        messages: [
          { text: messageText, sent: true, time: now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }
        ],
        attachments: selectedAttachments.map(a => ({ name:a.file.name, type:a.file.type, size:a.file.size, isImage:a.isImage })),
        recipients: selectedRecipients,
        startDate,
        messageCount: 1
      };
      conversationsData.unshift(newConv);
      hideModal('composeModal');
      renderConversations(currentFilter);
      selectConversation(id);
      toast('Message sent successfully', 'success');
    });
  }
}

// Modal helpers
function showModal(id) {
  const modal = document.getElementById(id);
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function hideModal(id) {
  const modal = document.getElementById(id);
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

// Toast notification
function toast(message, type = 'info') {
  const existing = document.querySelector('.toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.className = 'toast';
  
  if (type === 'success') {
    toast.style.background = 'linear-gradient(135deg, #10b981, #059669)';
  } else if (type === 'error') {
    toast.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
  } else {
    toast.style.background = 'linear-gradient(135deg, #3b82f6, #2563eb)';
  }
  
  toast.textContent = message;
  document.body.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}
