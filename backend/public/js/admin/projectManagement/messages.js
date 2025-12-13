// Messages page interactivity

let currentFilter = 'all';
let selectedConversation = null;
let messagesAPI = null;
let currentPage = 1;
let currentSearch = '';

document.addEventListener('DOMContentLoaded', () => {
  messagesAPI = new MessagesAPI();
  loadMessages();
  setupEventListeners();
});

// Load messages from API
async function loadMessages(page = 1, search = '') {
  try {
    const response = await messagesAPI.getMessages(page, search);
    renderMessagesList(response.data);
    updatePagination(response);
  } catch (error) {
    console.error('Error loading messages:', error);
    toast('Failed to load messages', 'error');
  }
}

// Render messages list from API data
function renderMessagesList(messages) {
  const list = document.getElementById('conversationsList');

  if (!messages || messages.length === 0) {
    list.innerHTML = '<div class="text-center text-gray-500 py-8">No messages found</div>';
    return;
  }

  list.innerHTML = messages.map(message => {
    const senderAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(message.sender)}&background=${message.sender_type === 'contractor' ? '6366f1' : '10b981'}&color=fff`;
    const timeAgo = formatTimeAgo(message.date);

    return `
      <div class="conversation-item" data-id="${message.message_id}">
        <div class="flex items-start gap-3">
          <div class="relative flex-shrink-0">
            <img src="${senderAvatar}" alt="${message.sender}" class="w-12 h-12 rounded-full object-cover">
            <span class="avatar-status ${message.is_read ? 'offline' : 'online'}"></span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between mb-1">
              <div class="flex items-center gap-2">
                <h4 class="font-semibold text-gray-800 truncate">${message.sender}</h4>
                <span class="text-xs px-2 py-1 rounded-full ${message.sender_type === 'contractor' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'}">
                  ${message.sender_type}
                </span>
              </div>
              <span class="text-xs text-gray-400 flex-shrink-0">${timeAgo}</span>
            </div>
            <p class="text-xs text-gray-500 mb-1 truncate">To: ${message.receiver}</p>
            <div class="flex items-center justify-between">
              <p class="text-sm text-gray-600 truncate flex-1">${message.content}</p>
              ${!message.is_read ? '<span class="unread-badge ml-2">New</span>' : ''}
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');

  // Add click listeners
  document.querySelectorAll('.conversation-item').forEach(item => {
    item.addEventListener('click', () => {
      const id = parseInt(item.dataset.id);
      selectMessage(id);
    });
  });
}

// Update pagination
function updatePagination(response) {
  const paginationContainer = document.getElementById('messagesPagination');
  if (!paginationContainer) return;

  const { current_page, last_page, per_page, total } = response;

  if (last_page <= 1) {
    paginationContainer.innerHTML = '';
    return;
  }

  let paginationHTML = '<div class="flex items-center justify-between">';

  // Previous button
  if (current_page > 1) {
    paginationHTML += `<button class="pagination-btn" data-page="${current_page - 1}">Previous</button>`;
  }

  // Page numbers
  const startPage = Math.max(1, current_page - 2);
  const endPage = Math.min(last_page, current_page + 2);

  if (startPage > 1) {
    paginationHTML += `<button class="pagination-btn" data-page="1">1</button>`;
    if (startPage > 2) {
      paginationHTML += '<span class="px-2">...</span>';
    }
  }

  for (let i = startPage; i <= endPage; i++) {
    paginationHTML += `<button class="pagination-btn ${i === current_page ? 'active' : ''}" data-page="${i}">${i}</button>`;
  }

  if (endPage < last_page) {
    if (endPage < last_page - 1) {
      paginationHTML += '<span class="px-2">...</span>';
    }
    paginationHTML += `<button class="pagination-btn" data-page="${last_page}">${last_page}</button>`;
  }

  // Next button
  if (current_page < last_page) {
    paginationHTML += `<button class="pagination-btn" data-page="${current_page + 1}">Next</button>`;
  }

  paginationHTML += '</div>';

  paginationContainer.innerHTML = paginationHTML;

  // Add pagination event listeners
  document.querySelectorAll('.pagination-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      if (!e.target.classList.contains('active')) {
        const page = parseInt(e.target.dataset.page);
        currentPage = page;
        loadMessages(page, currentSearch);
      }
    });
  });
}

// Format time ago
function formatTimeAgo(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffInSeconds = Math.floor((now - date) / 1000);

  if (diffInSeconds < 60) return 'just now';
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;

  return date.toLocaleDateString();
}

// Select and display message
function selectMessage(id) {
  // For now, just show a placeholder - in a real implementation you'd fetch the full message thread
  document.getElementById('emptyState').classList.add('hidden');
  document.getElementById('messageContent').classList.remove('hidden');
  document.getElementById('messageContent').classList.add('flex');

  // Update header with placeholder
  document.getElementById('selectedAvatar').src = 'https://ui-avatars.com/api/?name=Message&background=6366f1&color=fff';
  document.getElementById('selectedName').textContent = 'Message Details';
  document.getElementById('selectedProject').textContent = 'Message ID: ' + id;
  document.getElementById('selectedStatus').className = 'absolute bottom-0 right-0 w-3.5 h-3.5 bg-gray-400 border-2 border-white rounded-full';

  // Clear messages area
  document.getElementById('messagesArea').innerHTML = '<div class="text-center text-gray-500 py-8">Message details would be displayed here</div>';

  // Update footer
  document.getElementById('conversationDate').textContent = 'Message selected';
  document.getElementById('messageCount').textContent = '1 message';
}

// Setup event listeners
function setupEventListeners() {
  // Search
  const searchInput = document.getElementById('conversationSearch');
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      currentSearch = e.target.value.trim();
      currentPage = 1;
      loadMessages(1, currentSearch);
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
