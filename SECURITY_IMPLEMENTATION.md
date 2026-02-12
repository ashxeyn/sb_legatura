# Security Rules and Reporting Feature - Implementation Complete

## Summary

I've successfully implemented security rules and a reporting feature into your existing Pusher-based chat system without breaking the messaging flow.

## Changes Made

### 1. Security Validation in Backend (messageClass.php)

**Added Methods:**
- `detectContactInfo()` - Detects emails and Philippine phone numbers using regex
- `detectSuspiciousKeywords()` - Scans for prohibited keywords (GCash, Viber, Telegram, harassment terms, etc.)
- `validateMessageContent()` - Validates message content against all security rules

**Updated Fields:**
- Added `is_flagged` and `flag_reason` to fillable array

**Updated storeMessage():**
- **Rule A (Hard Block)**: Messages with email/phone are blocked, returns 422 error
- **Rule B (Auto-Flag)**: Messages with suspicious keywords are saved with `is_flagged=1` and `flag_reason="System: Suspicious Keyword Detected"`

### 2. Controller Updates (messageController.php)

**Updated store():**
- Detects blocked messages from storeMessage() 
- Returns 422 JSON response: `{"success": false, "message": "Sharing contact info is prohibited."}`
- Allows auto-flagged messages to send normally

**Added report():**
- Accepts POST requests to `/messages/report`
- Validates message_id and reason
- Verifies user authorization (must be part of conversation)
- Updates message: `is_flagged=1`, `flag_reason="USER_REPORT: [reason]"`
- Returns success confirmation

### 3. Frontend Updates (messages.js)

**Updated sendMessage():**
- Added 422 error handling
- Shows error toast for blocked messages
- **Keeps input text** so user can edit and resend
- Other errors clear input as before

**Updated appendMessage():**
- Added report button to received messages (visible on hover)
- Button shows flag icon positioned in top-right of message bubble
- Only appears on messages from others, not your own

**Added reportMessage():**
- Prompts user for report reason
- Sends POST to `/messages/report`
- Shows success/error toast
- Removes report button after successful report

### 4. Routes (web.php)

**Added report endpoints for all user types:**
- `POST /owner/messages/api/report`
- `POST /contractor/messages/api/report`
- `POST /admin/messages/report`

## Security Rules Reference

### Rule A: Hard Block (Contact Info)
**Blocked Patterns:**
- Emails: `user@example.com`
- Philippine phones: `+63 9XX XXX XXXX`, `09XXXXXXXXX`, `(02) XXX-XXXX`

**Action:** Message is NOT saved, returns 422 error

### Rule B: Auto-Flag (Suspicious Keywords)
**Keywords:**
- Payment methods: `gcash`, `viber`, `telegram`, `pay outside`, `bank transfer`
- Harassment: `sex`, `nigga`, `vagina`, `penis`, `fuck`, `bitch`, `whore`, etc.

**Action:** Message IS saved but flagged with `is_flagged=1`

## How to Test

### Test Rule A (Hard Block):
1. Open messages and try to send: `Contact me at john@example.com`
2. You should see red toast: "Sharing contact info is prohibited."
3. Input text remains so you can edit it
4. Message is NOT saved to database

### Test Rule B (Auto-Flag):
1. Send a message containing: `I can pay via GCash`
2. Message sends successfully
3. Check database: `is_flagged=1`, `flag_reason="System: Suspicious Keyword Detected"`

### Test Reporting:
1. Receive a message from another user
2. Hover over the message bubble
3. Red flag icon appears in top-right corner
4. Click flag, enter reason (e.g., "Inappropriate content")
5. See toast: "Message reported successfully"
6. Check database: `is_flagged=1`, `flag_reason="USER_REPORT: [your reason]"`

## Database Schema

The `messages` table already has these columns:
- `is_flagged` (tinyint) - 0 or 1
- `flag_reason` (varchar 255) - Stores reason text

## API Endpoints

```
POST /owner/messages/api/report
POST /contractor/messages/api/report  
POST /admin/messages/report

Body:
{
  "message_id": 123,
  "reason": "Inappropriate content"
}

Response (200):
{
  "success": true,
  "message": "Message reported successfully. Our team will review it."
}
```

## Notes

- Real-time messaging still works (Pusher broadcasts)
- Auto-flagged messages appear normal to sender/receiver
- Admin can see flagged messages in admin dashboard
- Report button only shows on hover for received messages
- Multiple security rules can be added easily by updating keyword arrays
