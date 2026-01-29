@extends('layouts.app')

@section('title', 'Messages - Legatura')

@section('content')
    <div class="property-owner-messages bg-gray-50">
        <div class="messages-card-container max-w-7xl mx-auto">
            <div class="messages-card bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="messages-layout flex">
                    <!-- Left Sidebar: Conversation List -->
                    <div class="messages-sidebar bg-white border-r border-gray-200 flex flex-col w-1/3">
                <!-- Filter Section -->
                <div class="messages-filter-section p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <button class="filter-btn active" id="allFilterBtn">
                                <span>All</span>
                                <span class="filter-count">16</span>
                            </button>
                            <button class="filter-btn" id="unreadFilterBtn">
                                <span>Unread</span>
                                <span class="filter-count" id="unreadCount">5</span>
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
                        <input type="text" class="messages-search-input" placeholder="Search conversations..." id="conversationSearchInput">
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

                    <!-- Sample Conversations (will be replaced with dynamic data) -->
                    <div class="conversation-item active" data-conversation-id="1">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-red">
                                <span class="conversation-initials">LA</span>
                                <span class="conversation-status-dot status-dot-red"></span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">Lisa Anderson</span>
                                    <span class="conversation-time">2 hours ago</span>
                                </div>
                                <div class="conversation-project">Kitchen Renovation</div>
                                <div class="conversation-preview">This is unacceptable! I want a refund!</div>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="2">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-green">
                                <span class="conversation-initials">MT</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">Michael Torres</span>
                                    <span class="conversation-time">3 hours ago</span>
                                </div>
                                <div class="conversation-project">Bathroom Remodeling</div>
                                <div class="conversation-preview">The work looks amazing! Thank you.</div>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="3">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-blue">
                                <span class="conversation-initials">EW</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">Emma Wilson</span>
                                    <span class="conversation-time">5 hours ago</span>
                                </div>
                                <div class="conversation-project">Garden Landscaping</div>
                                <div class="conversation-preview">When can we schedule the next phase?</div>
                            </div>
                            <div class="conversation-unread">
                                <span class="unread-count">2</span>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="4">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-orange">
                                <span class="conversation-initials">RK</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">Robert Kim</span>
                                    <span class="conversation-time">1 day ago</span>
                                </div>
                                <div class="conversation-project">Basement Finishing</div>
                                <div class="conversation-preview">I'll send you the updated timeline tomorrow.</div>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="5">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-red">
                                <span class="conversation-initials">DP</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">David Park</span>
                                    <span class="conversation-time">1 hour ago</span>
                                </div>
                                <div class="conversation-project">Office Space Remodeling</div>
                                <div class="conversation-preview">The materials have arrived. Ready to start next week.</div>
                            </div>
                            <div class="conversation-unread">
                                <span class="unread-count">1</span>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="6">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-green">
                                <span class="conversation-initials">SM</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">Sarah Martinez</span>
                                    <span class="conversation-time">4 hours ago</span>
                                </div>
                                <div class="conversation-project">Roof Replacement</div>
                                <div class="conversation-preview">Weather looks good for tomorrow. We'll begin at 7 AM.</div>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="7">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-blue">
                                <span class="conversation-initials">JC</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">James Chen</span>
                                    <span class="conversation-time">6 hours ago</span>
                                </div>
                                <div class="conversation-project">Deck Construction</div>
                                <div class="conversation-preview">Can we discuss the material options? I have some questions.</div>
                            </div>
                            <div class="conversation-unread">
                                <span class="unread-count">3</span>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="8">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-orange">
                                <span class="conversation-initials">AL</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">Amanda Lee</span>
                                    <span class="conversation-time">8 hours ago</span>
                                </div>
                                <div class="conversation-project">Flooring Installation</div>
                                <div class="conversation-preview">The hardwood looks perfect! Thank you so much.</div>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="9">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-red">
                                <span class="conversation-initials">CR</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">Chris Rodriguez</span>
                                    <span class="conversation-time">12 hours ago</span>
                                </div>
                                <div class="conversation-project">Window Replacement</div>
                                <div class="conversation-preview">I need to reschedule the installation. Something came up.</div>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="10">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-green">
                                <span class="conversation-initials">PT</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">Patricia Thompson</span>
                                    <span class="conversation-time">1 day ago</span>
                                </div>
                                <div class="conversation-project">Painting Services</div>
                                <div class="conversation-preview">The color samples are ready. When can you come by?</div>
                            </div>
                            <div class="conversation-unread">
                                <span class="unread-count">1</span>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="11">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-blue">
                                <span class="conversation-initials">BW</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">Brian Williams</span>
                                    <span class="conversation-time">1 day ago</span>
                                </div>
                                <div class="conversation-project">Electrical Wiring</div>
                                <div class="conversation-preview">All inspections passed. Project is complete!</div>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="12">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-orange">
                                <span class="conversation-initials">NG</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">Nicole Garcia</span>
                                    <span class="conversation-time">2 days ago</span>
                                </div>
                                <div class="conversation-project">Plumbing Repair</div>
                                <div class="conversation-preview">The leak has been fixed. Everything is working now.</div>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="13">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-red">
                                <span class="conversation-initials">KL</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">Kevin Liu</span>
                                    <span class="conversation-time">2 days ago</span>
                                </div>
                                <div class="conversation-project">HVAC Installation</div>
                                <div class="conversation-preview">System is running perfectly. Thanks for the quick work!</div>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="14">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-green">
                                <span class="conversation-initials">JH</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">Jennifer Harris</span>
                                    <span class="conversation-time">3 days ago</span>
                                </div>
                                <div class="conversation-project">Fence Installation</div>
                                <div class="conversation-preview">Can we discuss the gate placement? I have concerns.</div>
                            </div>
                            <div class="conversation-unread">
                                <span class="unread-count">2</span>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="15">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-blue">
                                <span class="conversation-initials">MC</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">Mark Cooper</span>
                                    <span class="conversation-time">3 days ago</span>
                                </div>
                                <div class="conversation-project">Driveway Paving</div>
                                <div class="conversation-preview">The asphalt looks great! Very professional work.</div>
                            </div>
                        </div>
                    </div>

                    <div class="conversation-item" data-conversation-id="16">
                        <div class="conversation-item-content">
                            <div class="conversation-avatar avatar-orange">
                                <span class="conversation-initials">LS</span>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-header">
                                    <span class="conversation-name">Linda Sanchez</span>
                                    <span class="conversation-time">4 days ago</span>
                                </div>
                                <div class="conversation-project">Tile Installation</div>
                                <div class="conversation-preview">When will the grout be ready? Need to plan ahead.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Main Area: Active Conversation -->
            <div class="messages-main flex-1 flex flex-col bg-gray-50">
                <!-- Conversation Header -->
                <div class="messages-header bg-white border-b border-gray-200 p-4">
                    <div class="flex items-center gap-4">
                        <div class="messages-header-avatar avatar-red">
                            <span class="messages-header-initials">LA</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="messages-header-name">Lisa Anderson</h3>
                            <p class="messages-header-project">Kitchen Renovation</p>
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

                    <!-- Sample Messages -->
                    <div class="message-bubble message-incoming" data-message-type="incoming">
                        <div class="message-content">Hi, I wanted to discuss the kitchen renovation project.</div>
                        <div class="message-time">3 days ago</div>
                    </div>

                    <div class="message-bubble message-outgoing" data-message-type="outgoing">
                        <div class="message-content">Hello! Yes, I'm here to help. What would you like to discuss?</div>
                        <div class="message-time">3 days ago</div>
                    </div>

                    <div class="message-bubble message-incoming" data-message-type="incoming">
                        <div class="message-content">I noticed that the cabinets you installed are the wrong color. They were supposed to be white, but you installed beige ones.</div>
                        <div class="message-time">2 days ago</div>
                    </div>

                    <div class="message-bubble message-outgoing" data-message-type="outgoing">
                        <div class="message-content">I apologize for the error. Let me check the order details and get back to you.</div>
                        <div class="message-time">2 days ago</div>
                    </div>

                    <div class="message-bubble message-incoming" data-message-type="incoming">
                        <div class="message-content">Please do. This is very disappointing. I specifically requested white cabinets.</div>
                        <div class="message-time">1 day ago</div>
                    </div>

                    <div class="message-bubble message-outgoing" data-message-type="outgoing">
                        <div class="message-content">I understand your frustration. I've reviewed the order and you're absolutely right. We can replace them at no additional cost to you.</div>
                        <div class="message-time">1 day ago</div>
                    </div>

                    <div class="message-bubble message-incoming" data-message-type="incoming">
                        <div class="message-content">How long will that take? I need my kitchen functional as soon as possible.</div>
                        <div class="message-time">1 day ago</div>
                    </div>

                    <div class="message-bubble message-outgoing" data-message-type="outgoing">
                        <div class="message-content">We can have the correct cabinets delivered and installed within 5-7 business days. I'll personally oversee the replacement to ensure everything is done correctly.</div>
                        <div class="message-time">Yesterday</div>
                    </div>

                    <div class="message-bubble message-incoming" data-message-type="incoming">
                        <div class="message-content">That's still quite a delay. This is unacceptable! I want a refund!</div>
                        <div class="message-time">Yesterday</div>
                    </div>

                    <div class="message-bubble message-outgoing" data-message-type="outgoing">
                        <div class="message-content">I completely understand your frustration. Let me see what I can do to expedite this. Can we schedule a call today to discuss options?</div>
                        <div class="message-time">Yesterday</div>
                    </div>

                    <div class="message-bubble message-incoming" data-message-type="incoming">
                        <div class="message-content">Yes, please call me this afternoon. I'm available between 2-4 PM.</div>
                        <div class="message-time">Yesterday</div>
                    </div>

                    <div class="message-bubble message-outgoing" data-message-type="outgoing">
                        <div class="message-content">Perfect. I'll call you at 2:30 PM. I'll also prepare a compensation offer for the inconvenience.</div>
                        <div class="message-time">Yesterday</div>
                    </div>

                    <div class="message-bubble message-incoming" data-message-type="incoming">
                        <div class="message-content">Thank you. I'll be waiting for your call.</div>
                        <div class="message-time">Yesterday</div>
                    </div>

                    <div class="message-bubble message-incoming" data-message-type="incoming">
                        <div class="message-content">I didn't receive your call. What happened?</div>
                        <div class="message-time">2 hours ago</div>
                    </div>

                    <div class="message-bubble message-outgoing" data-message-type="outgoing">
                        <div class="message-content">I apologize for missing the call. I had an emergency on another job site. Can we reschedule for tomorrow at the same time?</div>
                        <div class="message-time">1 hour ago</div>
                    </div>

                    <div class="message-bubble message-incoming" data-message-type="incoming">
                        <div class="message-content">This is getting worse. I really need this resolved immediately. Can you at least send me the compensation offer in writing?</div>
                        <div class="message-time">1 hour ago</div>
                    </div>

                    <div class="message-bubble message-outgoing" data-message-type="outgoing">
                        <div class="message-content">Absolutely. I'll prepare a written offer and send it to you within the next hour. I'm also working on expediting the cabinet delivery to 3-4 days instead of 5-7.</div>
                        <div class="message-time">30 minutes ago</div>
                    </div>
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
                            <textarea 
                                class="messages-input" 
                                id="messageInput" 
                                placeholder="Type your message..." 
                                rows="1"
                            ></textarea>
                        </div>
                        <!-- File Attachment Button -->
                        <button type="button" class="messages-attach-btn" id="attachFileBtn" aria-label="Attach file">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: block;">
                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <input type="file" id="fileInput" multiple accept="image/*,.pdf,.doc,.docx" class="hidden">
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
    <div id="newMessageModal" class="new-message-modal hidden">
        <div class="modal-overlay" id="newMessageModalOverlay"></div>
        <div class="modal-container">
            <!-- Modal Header -->
            <div class="modal-header">
                <div class="modal-header-content">
                    <h2 class="modal-title">
                        <i class="fi fi-rr-envelope"></i>
                        <span>New Message</span>
                    </h2>
                    <button class="modal-close-btn" id="closeNewMessageModalBtn" aria-label="Close modal">
                        <i class="fi fi-rr-cross"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Search Recipient -->
                <div class="recipient-search-section">
                    <div class="recipient-search-container">
                        <i class="fi fi-rr-search recipient-search-icon"></i>
                        <input type="text" class="recipient-search-input" id="recipientSearchInput" placeholder="Search contacts...">
                    </div>
                </div>

                <!-- Recipients List -->
                <div class="recipients-list" id="recipientsList">
                    <!-- Sample Contractors (will be replaced with dynamic data) -->
                    <div class="recipient-item" data-recipient-id="1" data-recipient-name="John Smith">
                        <div class="recipient-avatar avatar-red">
                            <span>JS</span>
                        </div>
                        <div class="recipient-info">
                            <div class="recipient-name">John Smith</div>
                            <div class="recipient-specialty">General Contractor</div>
                        </div>
                        <button class="recipient-select-btn" data-recipient-id="1">
                            <i class="fi fi-rr-arrow-right"></i>
                        </button>
                    </div>

                    <div class="recipient-item" data-recipient-id="2" data-recipient-name="Maria Garcia">
                        <div class="recipient-avatar avatar-green">
                            <span>MG</span>
                        </div>
                        <div class="recipient-info">
                            <div class="recipient-name">Maria Garcia</div>
                            <div class="recipient-specialty">Electrical Contractor</div>
                        </div>
                        <button class="recipient-select-btn" data-recipient-id="2">
                            <i class="fi fi-rr-arrow-right"></i>
                        </button>
                    </div>

                    <div class="recipient-item" data-recipient-id="3" data-recipient-name="David Lee">
                        <div class="recipient-avatar avatar-blue">
                            <span>DL</span>
                        </div>
                        <div class="recipient-info">
                            <div class="recipient-name">David Lee</div>
                            <div class="recipient-specialty">Plumbing Contractor</div>
                        </div>
                        <button class="recipient-select-btn" data-recipient-id="3">
                            <i class="fi fi-rr-arrow-right"></i>
                        </button>
                    </div>

                    <div class="recipient-item" data-recipient-id="4" data-recipient-name="Sarah Johnson">
                        <div class="recipient-avatar avatar-orange">
                            <span>SJ</span>
                        </div>
                        <div class="recipient-info">
                            <div class="recipient-name">Sarah Johnson</div>
                            <div class="recipient-specialty">Roofing Contractor</div>
                        </div>
                        <button class="recipient-select-btn" data-recipient-id="4">
                            <i class="fi fi-rr-arrow-right"></i>
                        </button>
                    </div>

                    <div class="recipient-item" data-recipient-id="5" data-recipient-name="Michael Brown">
                        <div class="recipient-avatar avatar-red">
                            <span>MB</span>
                        </div>
                        <div class="recipient-info">
                            <div class="recipient-name">Michael Brown</div>
                            <div class="recipient-specialty">HVAC Contractor</div>
                        </div>
                        <button class="recipient-select-btn" data-recipient-id="5">
                            <i class="fi fi-rr-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div class="recipients-empty-state hidden" id="recipientsEmptyState">
                    <i class="fi fi-rr-user"></i>
                    <p>No contractors found</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Messages.css') }}">
    <style>
        /* Hide footer on messages page */
        footer.footer-shell {
            display: none !important;
        }
    </style>
@endsection

@section('extra_js')
    <script src="{{ asset('js/owner/propertyOwner_Messages.js') }}"></script>
    <script>
        // Set Messages link as active when on messages page
        document.addEventListener('DOMContentLoaded', () => {
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.textContent.trim() === 'Messages' || link.getAttribute('href') === '{{ route("owner.messages") }}') {
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
