# Legatura Messaging System - Complete Documentation

> **Version:** 1.0  
> **Last Updated:** February 7, 2026  
> **Platform:** Laravel 12 + Pusher WebSockets + Real-time Broadcasting

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Architecture & Data Flow](#architecture--data-flow)
3. [Database Schema](#database-schema)
4. [Backend Components](#backend-components)
5. [Pusher & Broadcasting](#pusher--broadcasting)
6. [Frontend Integration](#frontend-integration)
7. [API Endpoints](#api-endpoints)
8. [Message Flow (Step-by-Step)](#message-flow-step-by-step)
9. [Admin Moderation Features](#admin-moderation-features)
10. [Configuration & Deployment](#configuration--deployment)
11. [Troubleshooting](#troubleshooting)

---

## System Overview

### What is the Messaging System?

The Legatura messaging system is a **real-time, private chat platform** that enables communication between:
- Property Owners ↔ Contractors
- Property Owners ↔ Admins
- Contractors ↔ Admins

### Key Features

- ✅ **Real-time message delivery** via Pusher WebSockets
- ✅ **Private, secure conversations** with channel authorization
- ✅ **File attachments** (images, PDFs, documents)
- ✅ **Read receipts** and unread message counts
- ✅ **Admin moderation** (flag, suspend, restore conversations)
- ✅ **Multi-platform support** (Web dashboard + Mobile app via Sanctum API)
- ✅ **Session-based authentication** for web, token-based for mobile

### Technology Stack

| Component | Technology |
|-----------|-----------|
| **Backend Framework** | Laravel 12 (PHP 8.2) |
| **Real-time Engine** | Pusher WebSockets |
| **Broadcasting** | Laravel Echo + Pusher |
| **Database** | MySQL (MariaDB) |
| **Frontend** | Vanilla JavaScript + TailwindCSS |
| **Mobile API** | Laravel Sanctum |
| **File Storage** | Laravel Storage (public disk) |

---

## Architecture & Data Flow

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER CLIENTS                            │
├──────────────────┬──────────────────┬──────────────────────────┤
│   Web Dashboard  │   Mobile App     │   Admin Dashboard        │
│   (Session Auth) │   (Sanctum)      │   (Session Auth)         │
└────────┬─────────┴────────┬─────────┴──────────┬───────────────┘
         │                  │                     │
         │     HTTP API     │                     │
         └──────────────────┴─────────────────────┘
                            ▼
         ┌─────────────────────────────────────────┐
         │        Laravel Application              │
         │  ┌────────────────────────────────────┐ │
         │  │  Routes (web.php, channels.php)    │ │
         │  └────────────┬───────────────────────┘ │
         │               ▼                          │
         │  ┌────────────────────────────────────┐ │
         │  │  Controllers                        │ │
         │  │  - messageController.php            │ │
         │  │  - broadcastAuthController.php      │ │
         │  └────────────┬───────────────────────┘ │
         │               ▼                          │
         │  ┌────────────────────────────────────┐ │
         │  │  Models & Business Logic            │ │
         │  │  - messageClass.php                 │ │
         │  │  - Message.php (Eloquent)           │ │
         │  └────────────┬───────────────────────┘ │
         │               ▼                          │
         │  ┌────────────────────────────────────┐ │
         │  │  Events                             │ │
         │  │  - messageSentEvent.php             │ │
         │  └────────────┬───────────────────────┘ │
         └───────────────┼─────────────────────────┘
                         ▼
         ┌─────────────────────────────────────────┐
         │         Queue System                    │
         │   (Database driver: jobs table)         │
         └────────────┬────────────────────────────┘
                      ▼
         ┌─────────────────────────────────────────┐
         │         Pusher API                      │
         │   (WebSocket Broadcasting)              │
         └────────────┬────────────────────────────┘
                      ▼
         ┌─────────────────────────────────────────┐
         │    Real-time Delivery to Clients        │
         │  (Laravel Echo listens on private       │
         │   channels: chat.{userId})              │
         └─────────────────────────────────────────┘
```

### File Organization

```
sb_legatura/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── message/
│   │           ├── messageController.php        # Main API controller
│   │           └── broadcastAuthController.php  # Pusher auth
│   ├── Models/
│   │   └── message/
│   │       ├── messageClass.php                 # Business logic
│   │       └── Message.php                      # Eloquent model
│   └── Events/
│       └── messageSentEvent.php                 # Pusher broadcast event
├── routes/
│   ├── web.php                                  # HTTP routes
│   ├── channels.php                             # WebSocket auth
│   └── api.php                                  # Mobile endpoints
├── public/
│   ├── js/
│   │   └── message/
│   │       └── messages.js                      # Frontend logic
│   └── css/
│       └── admin/projectManagement/
│           └── messages.css                     # Styles
├── resources/views/
│   └── admin/projectManagement/
│       └── messages.blade.php                   # Admin UI
├── config/
│   └── broadcasting.php                         # Pusher config
└── .env                                         # Pusher credentials
```

---

## Database Schema

### **conversations** Table

Stores conversation metadata between two users.

```sql
CREATE TABLE `conversations` (
  `conversation_id` VARCHAR(50) PRIMARY KEY,     -- e.g., "1_2" or "1000001000002"
  `sender_id` INT(11) NOT NULL,                  -- User who initiated
  `receiver_id` INT(11) NOT NULL,                -- Other participant
  `is_flagged` TINYINT(1) DEFAULT 0,             -- Admin flag
  `status` ENUM('active', 'suspended') DEFAULT 'active',
  `is_suspended` TINYINT(1) DEFAULT 0,           -- Suspension state
  `suspended_until` DATETIME NULL,               -- Auto-restore time
  `reason` TEXT NULL,                            -- Suspension reason
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### **messages** Table

Stores individual messages within conversations.

```sql
CREATE TABLE `messages` (
  `message_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
  `conversation_id` INT(11) NOT NULL,            -- FK to conversations
  `sender_id` INT(11) NOT NULL,                  -- User who sent message
  `receiver_id` INT(11) NOT NULL,                -- User who receives message
  `content` TEXT NOT NULL,                       -- Message content
  `is_read` TINYINT(1) DEFAULT 0,                -- Read receipt
  `is_flagged` TINYINT(1) DEFAULT 0,             -- Message flagged
  `flag_reason` VARCHAR(255) NULL,               -- Why message was flagged
  `status` ENUM('active','suspended') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX `idx_conversation` (`conversation_id`),
  INDEX `idx_receiver_unread` (`receiver_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Note:** The actual implementation uses `from_sender` (boolean) in the model layer, which is calculated from comparing `sender_id` with the conversation's `sender_id`.

### **message_attachments** Table

Stores file attachments linked to messages.

```sql
CREATE TABLE `message_attachments` (
  `attachment_id` INT(11) PRIMARY KEY AUTO_INCREMENT,
  `message_id` INT(11) NOT NULL,                 -- FK to messages
  `file_path` VARCHAR(255) NOT NULL,             -- storage/messages/{file}
  `file_name` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(50) NULL,                  -- MIME type
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`message_id`) REFERENCES `messages`(`message_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### **Key Relationships**

```
users (user_id)
   │
   ├──> conversations (sender_id, receiver_id)
   │       │
   │       └──> messages (conversation_id)
   │               │
   │               └──> message_attachments (message_id)
   │
   └──> Pusher Channel: chat.{user_id}
```

### **Conversation ID Format**

```php
// Formula: (min_user_id * 1000000) + max_user_id
// Example: User 1 and User 2 → "1000002"
// Example: User 42 and User 1337 → "42001337"

$minId = min($senderId, $receiverId);
$maxId = max($senderId, $receiverId);
$conversationId = ($minId * 1000000) + $maxId;
```

**Why this format?**
- ✅ Ensures same conversation ID regardless of who initiates
- ✅ Mathematically unique for any pair of users
- ✅ Sortable and efficient for indexing

---

## Backend Components

### 1. **messageController.php**

**Location:** `app/Http/Controllers/message/messageController.php`

**Purpose:** Main API controller handling all message operations

#### Key Methods:

##### `getAuthUserId(): ?int`
```php
// Resolves user ID from either:
// 1. Sanctum auth (mobile app)
// 2. Session auth (admin web dashboard)
// 3. Laravel default auth (fallback)

private function getAuthUserId(): ?int
{
    $userId = auth()->id(); // Try Sanctum first
    
    if (!$userId) {
        $sessionUser = session('user');
        $userId = $sessionUser->admin_id ?? $sessionUser->user_id ?? $sessionUser->id ?? null;
    }
    
    return $userId;
}
```

##### `index(): JsonResponse`
```php
// Returns user's inbox/conversation list
// Route: GET /admin/messages/

public function index(): JsonResponse
{
    $userId = $this->getAuthUserId();
    $conversations = messageClass::getInbox($userId);
    
    return response()->json([
        'success' => true,
        'data' => $conversations
    ]);
}
```

##### `store(MessageRequest $request): JsonResponse`
```php
// Sends a new message and broadcasts via Pusher
// Route: POST /admin/messages/

public function store(MessageRequest $request): JsonResponse
{
    $userId = $this->getAuthUserId();
    $validated = $request->validated();
    
    $data = [
        'sender_id' => $userId,
        'receiver_id' => $validated['receiver_id'],
        'content' => $validated['content'],
        'attachments' => $request->file('attachments') ?? []
    ];
    
    $message = messageClass::storeMessage($data);
    
    // Broadcast to both participants
    broadcast(new messageSentEvent($message))->toOthers();
    
    return response()->json([
        'success' => true,
        'data' => $message
    ], 201);
}
```

##### `show($conversationId): JsonResponse`
```php
// Returns message history for a conversation
// Route: GET /admin/messages/{conversationId}

public function show($conversationId): JsonResponse
{
    $userId = $this->getAuthUserId();
    
    // Verify user has access to this conversation
    $hasAccess = DB::table('conversations')
        ->where('conversation_id', $conversationId)
        ->where(function($q) use ($userId) {
            $q->where('sender_id', $userId)
              ->orWhere('receiver_id', $userId);
        })
        ->exists();
    
    if (!$hasAccess) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    
    $messages = messageClass::getConversationHistory($conversationId);
    messageClass::markAsRead($conversationId, $userId);
    
    return response()->json([
        'success' => true,
        'data' => ['messages' => $messages]
    ]);
}
```

##### `suspend($conversationId, Request $request): JsonResponse`
```php
// Admin: Suspend a conversation
// Route: POST /admin/messages/conversation/{conversationId}/suspend

public function suspend($conversationId, Request $request): JsonResponse
{
    $validated = $request->validate([
        'reason' => 'required|string|max:500',
        'suspended_until' => 'nullable|date|after:now'
    ]);
    
    $success = messageClass::suspendConversation(
        $conversationId,
        $validated['reason'],
        $validated['suspended_until'] ?? null
    );
    
    return response()->json([
        'success' => $success,
        'message' => 'Conversation suspended'
    ]);
}
```

---

### 2. **messageClass.php**

**Location:** `app/Models/message/messageClass.php`

**Purpose:** Business logic and database operations

#### Key Methods:

##### `getInbox(int $userId): array`
```php
// Returns user's conversation list with latest message preview

public static function getInbox(int $userId): array
{
    $conversations = DB::table('conversations as c')
        ->join('messages as m', 'c.conversation_id', '=', 'm.conversation_id')
        ->select('c.*', 'm.content as last_content', 'm.created_at as last_sent_at')
        ->whereRaw('m.message_id = (
            SELECT message_id FROM messages 
            WHERE conversation_id = c.conversation_id 
            ORDER BY created_at DESC LIMIT 1
        )')
        ->where(function ($query) use ($userId) {
            $query->where('c.sender_id', $userId)
                  ->orWhere('c.receiver_id', $userId);
        })
        ->orderBy('m.created_at', 'desc')
        ->get();
    
    $result = [];
    foreach ($conversations as $conv) {
        $otherUserId = ($conv->sender_id == $userId) 
            ? $conv->receiver_id 
            : $conv->sender_id;
            
        $otherUser = self::getUserDetails($otherUserId);
        
        // Calculate unread count
        $isSender = ($userId == $conv->sender_id);
        $unreadCount = DB::table('messages')
            ->where('conversation_id', $conv->conversation_id)
            ->where('from_sender', !$isSender)
            ->where('is_read', 0)
            ->count();
        
        $result[] = [
            'conversation_id' => $conv->conversation_id,
            'other_user' => $otherUser,
            'last_message' => [
                'content' => $conv->last_content,
                'sent_at' => Carbon::parse($conv->last_sent_at)->diffForHumans()
            ],
            'unread_count' => $unreadCount,
            'is_flagged' => (bool) $conv->is_flagged,
            'status' => $conv->status
        ];
    }
    
    return $result;
}
```

##### `storeMessage(array $data): ?messageClass`
```php
// Creates a new message with transaction safety

public static function storeMessage(array $data): ?messageClass
{
    DB::beginTransaction();
    
    try {
        // Generate conversation ID if not provided
        if (!isset($data['conversation_id'])) {
            $minId = min($data['sender_id'], $data['receiver_id']);
            $maxId = max($data['sender_id'], $data['receiver_id']);
            $data['conversation_id'] = ($minId * 1000000) + $maxId;
        }
        
        // Get or create conversation
        $conversation = self::getOrCreateConversation(
            $data['conversation_id'],
            $data['sender_id'],
            $data['receiver_id']
        );
        
        // Determine if message is from sender or receiver
        $fromSender = ($data['sender_id'] == $conversation->sender_id);
        
        // Create message
        $message = self::create([
            'conversation_id' => $data['conversation_id'],
            'from_sender' => $fromSender,
            'content' => $data['content'],
            'is_read' => 0
        ]);
        
        // Handle attachments
        if (isset($data['attachments'])) {
            foreach ($data['attachments'] as $file) {
                $message->addAttachment($file);
            }
        }
        
        DB::commit();
        return self::find($message->message_id);
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to store message', ['error' => $e->getMessage()]);
        return null;
    }
}
```

##### `getOrCreateConversation(string $conversationId, int $senderId, int $receiverId)`
```php
// Creates conversation if it doesn't exist

protected static function getOrCreateConversation($conversationId, $senderId, $receiverId)
{
    $conversation = DB::table('conversations')
        ->where('conversation_id', $conversationId)
        ->first();
    
    if (!$conversation) {
        DB::table('conversations')->insert([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'is_flagged' => 0,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $conversation = DB::table('conversations')
            ->where('conversation_id', $conversationId)
            ->first();
    }
    
    return $conversation;
}
```

##### `getUserDetails(int $userId): ?array`
```php
// Fetches user details from database

public static function getUserDetails(int $userId): ?array
{
    $user = DB::table('users')->where('user_id', $userId)->first();
    
    if (!$user) return null;
    
    return [
        'id' => $user->user_id,
        'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
        'email' => $user->email,
        'avatar' => $user->profile_picture 
            ? asset('storage/' . $user->profile_picture) 
            : asset('img/default-avatar.png'),
        'type' => $user->user_type
    ];
}
```

---

### 3. **messageSentEvent.php**

**Location:** `app/Events/messageSentEvent.php`

**Purpose:** Broadcasts messages to Pusher WebSocket

```php
<?php

namespace App\Events;

use App\Models\message\messageClass;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class messageSentEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;
    
    public $message;
    public $conversation;
    
    public function __construct(messageClass $message)
    {
        $this->message = $message;
        
        // Load conversation data
        $this->conversation = DB::table('conversations')
            ->where('conversation_id', $message->conversation_id)
            ->first();
    }
    
    /**
     * Broadcast to BOTH participants' private channels
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->conversation->sender_id),
            new PrivateChannel('chat.' . $this->conversation->receiver_id),
        ];
    }
    
    /**
     * Event name that frontend listens for
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }
    
    /**
     * Data payload sent to clients
     */
    public function broadcastWith(): array
    {
        $senderId = $this->message->from_sender 
            ? $this->conversation->sender_id 
            : $this->conversation->receiver_id;
            
        $receiverId = $this->message->from_sender 
            ? $this->conversation->receiver_id 
            : $this->conversation->sender_id;
        
        return [
            'message_id' => $this->message->message_id,
            'conversation_id' => $this->message->conversation_id,
            'content' => $this->message->content,
            'sender' => messageClass::getUserDetails($senderId),
            'receiver' => messageClass::getUserDetails($receiverId),
            'attachments' => $this->message->attachments->toArray(),
            'is_read' => (bool) $this->message->is_read,
            'sent_at' => $this->message->created_at->toIso8601String(),
            'timestamp' => $this->message->created_at->timestamp
        ];
    }
}
```

**How it works:**

1. **Event triggered** when `broadcast(new messageSentEvent($message))` is called
2. **Queued automatically** because it `implements ShouldBroadcast`
3. **Queue worker** processes the job
4. **Pusher API** receives the payload
5. **WebSocket** delivers to subscribed clients on `chat.{userId}` channels

---

## Pusher & Broadcasting

### What is Pusher?

**Pusher** is a hosted WebSocket service that enables real-time, bi-directional communication between server and clients without polling.

### Why Pusher?

| Feature | Benefit |
|---------|---------|
| **Managed Infrastructure** | No need to run your own WebSocket server |
| **Scalability** | Handles millions of concurrent connections |
| **Security** | Built-in authentication and encryption |
| **Laravel Integration** | Native support via Laravel Echo |
| **Global CDN** | Low-latency worldwide delivery |

### Configuration

#### 1. **.env File**

```env
# Broadcasting Driver
BROADCAST_DRIVER=pusher
QUEUE_CONNECTION=database

# Pusher Credentials (from pusher.com dashboard)
PUSHER_APP_ID=2112120
PUSHER_APP_KEY=c8539eba4bad9ec5e663
PUSHER_APP_SECRET=your_secret_key
PUSHER_APP_CLUSTER=ap1

# Optional
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
```

#### 2. **config/broadcasting.php**

```php
'default' => env('BROADCAST_DRIVER', 'pusher'),

'connections' => [
    'pusher' => [
        'driver' => 'pusher',
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'app_id' => env('PUSHER_APP_ID'),
        'options' => [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'encrypted' => true,
            'useTLS' => true,
        ],
    ],
],
```

#### 3. **routes/channels.php**

```php
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    // Handle both Sanctum (API) and session (web) authentication
    $currentUserId = null;
    
    if ($user) {
        $currentUserId = $user->user_id ?? $user->id;
    }
    
    if (!$currentUserId) {
        $sessionUser = session('user');
        $currentUserId = $sessionUser->admin_id ?? $sessionUser->user_id ?? null;
    }
    
    // Only allow users to subscribe to their own channel
    return (int) $currentUserId === (int) $userId;
});
```

#### 4. **routes/web.php**

```php
// Custom broadcast auth for session-based users
Route::post('/broadcasting/auth', [
    \App\Http\Controllers\message\broadcastAuthController::class, 
    'authorize'
])->middleware('web');
```

### Pusher Authentication Flow

```
1. Frontend connects to Pusher
   ↓
2. Attempts to subscribe to private-chat.{userId}
   ↓
3. Laravel Echo sends auth request to /broadcasting/auth
   ↓
4. broadcastAuthController checks session/Sanctum
   ↓
5. Verifies user can only subscribe to their own channel
   ↓
6. Generates Pusher signature using PUSHER_APP_SECRET
   ↓
7. Returns signed auth token to frontend
   ↓
8. Pusher validates signature and allows subscription
   ↓
9. Client now receives real-time messages
```

### Broadcasting Process

```php
// In messageController after saving message:

broadcast(new messageSentEvent($message))->toOthers();

// This triggers:
// 1. Event queued (jobs table)
// 2. Queue worker picks up job (php artisan queue:work)
// 3. Event calls broadcastOn() → Returns [chat.1, chat.2]
// 4. Event calls broadcastWith() → Returns payload
// 5. Laravel sends HTTP request to Pusher API
// 6. Pusher pushes to subscribed clients
// 7. Frontend receives event on Echo.private('chat.1')
```

### Queue System

**Why queues?**
- Broadcasting is I/O intensive (HTTP request to Pusher)
- Queuing prevents blocking the HTTP response
- User gets instant API response, broadcasting happens async

**Start queue worker:**

```bash
php artisan queue:work --queue=default --tries=3
```

**Monitor queue:**

```sql
-- Check pending jobs
SELECT * FROM jobs ORDER BY created_at DESC;

-- Check failed jobs
SELECT * FROM failed_jobs ORDER BY failed_at DESC;
```

---

## Frontend Integration

### Laravel Echo Initialization

**Location:** `resources/views/admin/projectManagement/messages.blade.php`

```html
<!-- Load Pusher and Laravel Echo -->
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

<script>
// Initialize Laravel Echo with Pusher
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '{{ env("PUSHER_APP_KEY") }}',
    cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
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
                        'X-Socket-ID': socketId
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        socket_id: socketId,
                        channel_name: channel.name
                    })
                })
                .then(response => response.json())
                .then(data => callback(null, data))
                .catch(error => callback(error, null));
            }
        };
    }
});
</script>
```

### Listening for Messages

**Location:** `public/js/message/messages.js`

```javascript
function initializePusher() {
    const userId = getUserId(); // From meta tag
    
    if (!userId) return;
    
    // Subscribe to user's private channel
    window.Echo.private(`chat.${userId}`)
        .listen('.message.sent', (event) => {
            // console.log('Pusher: Message received!', event);
            handleIncomingMessage(event);
        })
        .subscribed(() => {
            // console.log('Pusher: Successfully subscribed to chat.' + userId);
        })
        .error((error) => {
            console.error('Pusher subscription failed:', error);
        });
}

function handleIncomingMessage(event) {
    // If message is for active conversation, append it
    if (event.conversation_id === currentConversationId) {
        appendMessage(event);
        markConversationAsRead(event.conversation_id);
    }
    
    // Reload inbox to update preview
    loadInbox();
    
    // Show notification
    toast(`New message from ${event.sender.name}`, 'info');
}
```

### Sending Messages

```javascript
async function sendMessage() {
    const content = document.getElementById('messageInput').value;
    const files = document.getElementById('attachmentInput').files;
    
    const formData = new FormData();
    formData.append('receiver_id', currentReceiverId);
    formData.append('content', content);
    formData.append('conversation_id', currentConversationId);
    
    // Attach files
    for (let i = 0; i < files.length; i++) {
        formData.append('attachments[]', files[i]);
    }
    
    const response = await fetch('/admin/messages/', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
        // Clear input
        document.getElementById('messageInput').value = '';
        
        // Message will appear via Pusher event
        // (backend broadcasts to both sender and receiver)
    }
}
```

---

## API Endpoints

### Authentication

All endpoints require **either**:
- **Session authentication** (web dashboard: `session('user')`)
- **Sanctum token** (mobile app: `Authorization: Bearer {token}`)

### Endpoint Reference

| Method | Endpoint | Purpose | Returns |
|--------|----------|---------|---------|
| `GET` | `/admin/messages/` | Get user's inbox | Conversation list |
| `GET` | `/admin/messages/stats` | Get dashboard stats | Total suspended, active, flagged |
| `GET` | `/admin/messages/users` | Get available users | List of users to message |
| `GET` | `/admin/messages/search?q={query}` | Search conversations | Filtered conversations |
| `GET` | `/admin/messages/{conversationId}` | Get message history | Array of messages |
| `POST` | `/admin/messages/` | Send new message | Created message |
| `POST` | `/admin/messages/conversation/{id}/flag` | Flag conversation | Success status |
| `POST` | `/admin/messages/conversation/{id}/unflag` | Unflag conversation | Success status |
| `POST` | `/admin/messages/conversation/{id}/suspend` | Suspend conversation | Success status |
| `POST` | `/admin/messages/conversation/{id}/restore` | Restore conversation | Success status |
| `POST` | `/broadcasting/auth` | Pusher channel auth | Signed auth token |

### Request/Response Examples

#### Get Inbox

**Request:**
```http
GET /admin/messages/
Accept: application/json
X-CSRF-TOKEN: {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "conversation_id": "1000002",
      "other_user": {
        "id": 2,
        "name": "John Contractor",
        "email": "john@example.com",
        "avatar": "/storage/avatars/2.jpg",
        "type": "contractor"
      },
      "last_message": {
        "content": "When can we schedule the inspection?",
        "sent_at": "5 minutes ago",
        "sent_at_timestamp": "2026-02-07T08:30:00Z"
      },
      "unread_count": 3,
      "is_flagged": false,
      "status": "active",
      "is_suspended": false
    }
  ]
}
```

#### Send Message

**Request:**
```http
POST /admin/messages/
Content-Type: multipart/form-data
X-CSRF-TOKEN: {token}

{
  "receiver_id": 2,
  "content": "Let's schedule for next Tuesday at 10 AM",
  "conversation_id": "1000002",
  "attachments": [File, File]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "message_id": 12345,
    "conversation_id": "1000002",
    "content": "Let's schedule for next Tuesday at 10 AM",
    "sender": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@legatura.com",
      "avatar": "/img/default-avatar.png"
    },
    "receiver": {
      "id": 2,
      "name": "John Contractor",
      "email": "john@example.com",
      "avatar": "/storage/avatars/2.jpg"
    },
    "attachments": [
      {
        "attachment_id": 501,
        "file_name": "blueprint.pdf",
        "file_type": "application/pdf",
        "file_url": "/storage/messages/blueprint.pdf",
        "is_image": false
      }
    ],
    "is_read": false,
    "sent_at": "2026-02-07T08:35:00Z"
  }
}
```

#### Suspend Conversation

**Request:**
```http
POST /admin/messages/conversation/1000002/suspend
Content-Type: application/json
X-CSRF-TOKEN: {token}

{
  "reason": "Inappropriate language",
  "suspended_until": "2026-02-14T00:00:00Z"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Conversation suspended successfully"
}
```

---

## Message Flow (Step-by-Step)

### Scenario: User A sends a message to User B

```
┌──────────────────────────────────────────────────────────────────┐
│ STEP 1: User A clicks "Send" button                             │
└────────────┬─────────────────────────────────────────────────────┘
             ▼
┌──────────────────────────────────────────────────────────────────┐
│ STEP 2: Frontend sends POST /admin/messages/                    │
│   Body: { receiver_id: 2, content: "Hello", attachments: [] }   │
└────────────┬─────────────────────────────────────────────────────┘
             ▼
┌──────────────────────────────────────────────────────────────────┐
│ STEP 3: messageController::store() receives request             │
│   - Validates input via MessageRequest                          │
│   - Resolves sender_id from session/Sanctum                     │
└────────────┬─────────────────────────────────────────────────────┘
             ▼
┌──────────────────────────────────────────────────────────────────┐
│ STEP 4: messageClass::storeMessage() creates message            │
│   - Generates conversation_id: (1 * 1000000) + 2 = "1000002"    │
│   - Creates/updates conversation record                         │
│   - Inserts message into messages table                         │
│   - Processes file attachments (if any)                         │
│   - Commits transaction                                         │
└────────────┬─────────────────────────────────────────────────────┘
             ▼
┌──────────────────────────────────────────────────────────────────┐
│ STEP 5: Controller broadcasts event                             │
│   broadcast(new messageSentEvent($message))->toOthers();        │
│   - Event queued in jobs table                                  │
└────────────┬─────────────────────────────────────────────────────┘
             ▼
┌──────────────────────────────────────────────────────────────────┐
│ STEP 6: HTTP response returned to User A                        │
│   { "success": true, "data": {...} }                            │
│   User A sees message immediately in UI (optimistic update)     │
└────────────┬─────────────────────────────────────────────────────┘
             ▼
┌──────────────────────────────────────────────────────────────────┐
│ STEP 7: Queue worker processes job                              │
│   php artisan queue:work                                        │
│   - Picks up messageSentEvent from jobs table                   │
│   - Calls broadcastOn() → [chat.1, chat.2]                      │
│   - Calls broadcastWith() → Returns payload                     │
└────────────┬─────────────────────────────────────────────────────┘
             ▼
┌──────────────────────────────────────────────────────────────────┐
│ STEP 8: Laravel sends HTTP POST to Pusher API                   │
│   POST https://api-ap1.pusher.com/apps/2112120/events           │
│   {                                                              │
│     "name": "message.sent",                                      │
│     "channels": ["private-chat.1", "private-chat.2"],           │
│     "data": { message_id, content, sender, ... }                │
│   }                                                              │
└────────────┬─────────────────────────────────────────────────────┘
             ▼
┌──────────────────────────────────────────────────────────────────┐
│ STEP 9: Pusher broadcasts via WebSocket                         │
│   - Pushes to all clients subscribed to chat.1 and chat.2       │
└────────────┬─────────────────────────────────────────────────────┘
             ▼
┌──────────────────────────────────────────────────────────────────┐
│ STEP 10: User B's browser receives event                        │
│   Echo.private('chat.2').listen('.message.sent', (event) => {   │
│     handleIncomingMessage(event);                               │
│   })                                                             │
│   - Appends message to conversation view (if active)            │
│   - Updates inbox list                                          │
│   - Shows toast notification                                    │
│   - Marks conversation as unread                                │
└──────────────────────────────────────────────────────────────────┘
```

### Timing

- **Steps 1-6:** ~100-300ms (HTTP request → database → response)
- **Steps 7-10:** ~50-200ms (queue → Pusher → WebSocket)
- **Total perceived latency:** ~150-500ms (near real-time)

---

## Admin Moderation Features

### Flag Conversation

**Purpose:** Mark conversation for review without blocking communication

**UI:**
- Admin clicks "Flag" button on conversation
- Modal prompts for reason: Spam, Harassment, Other
- Conversation displays orange flag icon

**Backend:**
```php
public function flag($conversationId, Request $request)
{
    $validated = $request->validate([
        'reason' => 'required|string|max:500'
    ]);
    
    DB::table('conversations')
        ->where('conversation_id', $conversationId)
        ->update([
            'is_flagged' => 1,
            'flag_reason' => $validated['reason'],
            'updated_at' => now()
        ]);
    
    return response()->json(['success' => true]);
}
```

### Suspend Conversation

**Purpose:** Temporarily or permanently block messaging

**Features:**
- Time-based suspension (24h, 7d, 30d, permanent)
- Auto-restore when `suspended_until` date passes
- Reason tracking
- Input disabled for both participants

**Backend:**
```php
public static function suspendConversation($conversationId, $reason, $suspendedUntil = null)
{
    return DB::table('conversations')
        ->where('conversation_id', $conversationId)
        ->update([
            'is_suspended' => 1,
            'status' => 'suspended',
            'reason' => $reason,
            'suspended_until' => $suspendedUntil,
            'updated_at' => now()
        ]);
}
```

**Auto-restore cron job:**
```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        messageClass::checkSuspensionStatus();
    })->everyMinute();
}

// In messageClass
public static function checkSuspensionStatus()
{
    DB::table('conversations')
        ->where('is_suspended', 1)
        ->where('suspended_until', '<=', now())
        ->whereNotNull('suspended_until')
        ->update([
            'is_suspended' => 0,
            'status' => 'active',
            'reason' => null,
            'suspended_until' => null,
            'updated_at' => now()
        ]);
}
```

### Restore Conversation

**Purpose:** Remove suspension and restore messaging

**UI:**
- "Restore" button appears when conversation is suspended
- Modal shows suspension details
- Admin confirms restoration

**Backend:**
```php
public static function restoreConversation($conversationId)
{
    return DB::table('conversations')
        ->where('conversation_id', $conversationId)
        ->update([
            'is_suspended' => 0,
            'status' => 'active',
            'reason' => null,
            'suspended_until' => null,
            'updated_at' => now()
        ]);
}
```

---

## Configuration & Deployment

### Development Setup

1. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

2. **Configure .env:**
   ```env
   BROADCAST_DRIVER=pusher
   QUEUE_CONNECTION=database
   
   PUSHER_APP_ID=your_app_id
   PUSHER_APP_KEY=your_app_key
   PUSHER_APP_SECRET=your_app_secret
   PUSHER_APP_CLUSTER=ap1
   ```

3. **Run migrations:**
   ```bash
   php artisan migrate
   ```

4. **Start services:**
   ```bash
   # Terminal 1: Application server
   php artisan serve
   
   # Terminal 2: Queue worker
   php artisan queue:work
   
   # Terminal 3: Frontend assets (if using Vite)
   npm run dev
   ```

### Production Deployment (Hostinger)

#### 1. **Pusher Configuration**

```env
# .env on production server
BROADCAST_DRIVER=pusher
QUEUE_CONNECTION=database

PUSHER_APP_ID=2112120
PUSHER_APP_KEY=c8539eba4bad9ec5e663
PUSHER_APP_SECRET={your_secret}
PUSHER_APP_CLUSTER=ap1
PUSHER_SCHEME=https
PUSHER_PORT=443
```

#### 2. **Queue Worker as Systemd Service**

Create `/etc/systemd/system/legatura-queue.service`:

```ini
[Unit]
Description=Legatura Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/home/u942627558/domains/legatura.com/public_html
ExecStart=/usr/bin/php artisan queue:work --queue=default --tries=3 --timeout=90
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable legatura-queue
sudo systemctl start legatura-queue
sudo systemctl status legatura-queue
```

#### 3. **Supervisor Alternative** (if no systemd access)

Install Supervisor via cPanel or SSH:
```bash
sudo apt install supervisor
```

Create `/etc/supervisor/conf.d/legatura-queue.conf`:
```ini
[program:legatura-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /home/u942627558/domains/legatura.com/public_html/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/home/u942627558/domains/legatura.com/storage/logs/queue.log
```

Reload Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start legatura-queue:*
```

#### 4. **Optimize for Production**

```bash
# Cache config and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

#### 5. **File Permissions**

```bash
chmod -R 755 /home/u942627558/domains/legatura.com/public_html
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Troubleshooting

### Issue: Messages not received in real-time

**Symptoms:**
- Messages appear only after page refresh
- No errors in console

**Diagnosis:**

1. Check if Pusher is initialized:
   ```javascript
   // In browser console
   console.log(window.Echo);
   ```

2. Uncomment debug logs in `messages.js`:
   ```javascript
   // console.log('Pusher: Successfully subscribed...');
   // console.log('Pusher: Message received!', event);
   ```

3. Check queue worker status:
   ```bash
   # On server
   ps aux | grep queue:work
   
   # Check queue table
   SELECT * FROM jobs ORDER BY created_at DESC LIMIT 10;
   ```

**Solutions:**

| Cause | Solution |
|-------|----------|
| Queue worker not running | Start: `php artisan queue:work` (or restart systemd service) |
| Wrong Pusher credentials | Verify `.env` matches Pusher dashboard |
| Channel authorization failed | Check `/broadcasting/auth` route returns 200 |
| CSRF token mismatch | Ensure `<meta name="csrf-token">` is present |
| Browser blocking WebSocket | Check browser console for CORS/CSP errors |

---

### Issue: "Unauthorized" error when sending messages

**Symptoms:**
- 401/403 HTTP response
- "User not authenticated" message

**Diagnosis:**

```php
// Add to messageController::store()
\Log::info('Auth check', [
    'auth_id' => auth()->id(),
    'session_user' => session('user'),
    'resolved_user_id' => $this->getAuthUserId()
]);
```

**Solutions:**

| Cause | Solution |
|-------|----------|
| Session expired | Re-login or check session lifetime in `config/session.php` |
| CSRF token missing | Add `X-CSRF-TOKEN` header to all AJAX requests |
| Sanctum token invalid (mobile) | Re-authenticate and get new token |
| Wrong user ID field | Check if `user_id`, `id`, or `admin_id` is used |

---

### Issue: Broadcast auth fails with "Invalid channel"

**Symptoms:**
- Console error: "Pusher channel subscription failed"
- Status 400 from `/broadcasting/auth`

**Diagnosis:**

```php
// Check broadcastAuthController logs
tail -f storage/logs/laravel.log | grep "broadcast auth"
```

**Solutions:**

1. **Channel name mismatch:**
   ```javascript
   // Frontend subscribes to:
   Echo.private('chat.123')
   
   // But event broadcasts to:
   new PrivateChannel('chat.456') // Wrong user ID!
   ```
   **Fix:** Ensure `getUserId()` returns correct value

2. **Missing session:**
   ```php
   // In broadcastAuthController
   if (!session('user')) {
       \Log::warning('No session found');
       return response()->json(['error' => 'Unauthorized'], 403);
   }
   ```
   **Fix:** Ensure middleware `'web'` is applied to auth route

---

### Issue: Messages saved but not broadcast

**Symptoms:**
- Message appears in database
- Sender sees message
- Receiver doesn't see message (even after refresh)

**Diagnosis:**

1. Check if event was queued:
   ```sql
   SELECT * FROM jobs WHERE payload LIKE '%messageSentEvent%';
   ```

2. Check if event failed:
   ```sql
   SELECT * FROM failed_jobs ORDER BY failed_at DESC LIMIT 5;
   ```

3. Test Pusher credentials:
   ```bash
   php artisan tinker
   >>> broadcast(new \App\Events\messageSentEvent(\App\Models\message\messageClass::find(1)));
   ```

**Solutions:**

| Cause | Solution |
|-------|----------|
| Queue not processing | Restart queue worker |
| Pusher credentials wrong | Test with Pusher dashboard debug console |
| Event not implementing `ShouldBroadcast` | Add `implements ShouldBroadcast` to event class |
| `broadcastOn()` returns empty array | Ensure conversation has valid sender_id/receiver_id |
| Firewall blocking Pusher API | Whitelist `api-{cluster}.pusher.com` on port 443 |

---

### Debug Checklist

```bash
# 1. Verify Pusher config
php artisan tinker
>>> config('broadcasting.default')
>>> config('broadcasting.connections.pusher.key')

# 2. Check queue status
php artisan queue:monitor
SELECT COUNT(*) FROM jobs; -- Should be 0 if processed

# 3. Test database connection
php artisan db:show

# 4. Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 5. Check logs
tail -f storage/logs/laravel.log
```

---

## Appendix

### File Attachment Flow

```php
// In messageClass::addAttachment()
public function addAttachment($file)
{
    $fileName = time() . '_' . $file->getClientOriginalName();
    $filePath = $file->storeAs('messages', $fileName, 'public');
    
    DB::table('message_attachments')->insert([
        'message_id' => $this->message_id,
        'file_name' => $file->getClientOriginalName(),
        'file_type' => $file->getMimeType(),
        'file_path' => $filePath,
        'file_size' => $file->getSize(),
        'uploaded_at' => now()
    ]);
}
```

### Security Considerations

1. **Channel Authorization:**
   - Users can ONLY subscribe to `chat.{their_own_id}`
   - Enforced in `routes/channels.php`

2. **Conversation Access Control:**
   - Users can ONLY view conversations where they are sender OR receiver
   - Enforced in `messageController::show()`

3. **File Upload Validation:**
   - Max file size: 10MB
   - Allowed types: Images, PDFs, .doc, .docx, .txt
   - Validated in `MessageRequest`

4. **XSS Prevention:**
   - All message content escaped with `escapeHtml()` in frontend
   - Blade `{{ }}` auto-escapes output

5. **SQL Injection Prevention:**
   - All queries use parameter binding
   - PDO prepared statements

---

## Glossary

| Term | Definition |
|------|------------|
| **Conversation** | A messaging thread between two users |
| **Message** | Individual text/file sent within a conversation |
| **Pusher** | Managed WebSocket service for real-time delivery |
| **Broadcasting** | Laravel's system for sending events to Pusher |
| **Laravel Echo** | JavaScript library for subscribing to Pusher channels |
| **Private Channel** | WebSocket channel requiring authentication |
| **Queue Worker** | Background process that handles queued jobs |
| **Sanctum** | Laravel's API authentication system |
| **CSRF Token** | Security token for web form submissions |
| **WebSocket** | Bi-directional communication protocol |

---

## Support & Maintenance

### Monitoring

**Key metrics to track:**
- Queue length: `SELECT COUNT(*) FROM jobs`
- Failed jobs: `SELECT COUNT(*) FROM failed_jobs`
- Average message latency: Timestamp in DB vs. Pusher delivery
- Pusher API usage: Check Pusher dashboard for quota

### Backup Strategy

```bash
# Daily database backup
mysqldump -u root -p u942627558_legatura > backup_$(date +%Y%m%d).sql

# Weekly storage backup
tar -czf messages_$(date +%Y%m%d).tar.gz storage/app/public/messages/
```

### Scaling Considerations

If you reach **>1000 concurrent users:**
1. Upgrade Pusher plan (current: Free tier, limit 100 connections)
2. Add multiple queue workers: `numprocs=4` in Supervisor
3. Use Redis for queue instead of database
4. Enable Laravel caching (Redis/Memcached)

---

**End of Documentation**

📧 For questions or issues, contact the development team or refer to Laravel/Pusher official documentation.
