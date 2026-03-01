//not yet use
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    // Simple SMS sender stub — replace with real provider integration
    public function sendSms($phoneNumber, $message)
    {
        try {
            Log::info("Sending SMS to {$phoneNumber}", ['message' => $message]);
            // TODO: integrate with real SMS provider (Twilio, Nexmo, etc.)
            return true;
        } catch (\Throwable $e) {
            Log::warning('SMS send failed: ' . $e->getMessage());
            return false;
        }
    }
}
