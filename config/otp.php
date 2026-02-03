<?php

return [
    // OTP lifetime in seconds
    'ttl_seconds' => env('OTP_TTL_SECONDS', 900), // 15 minutes

    // Small grace window in seconds to account for clock skew / network delays
    'grace_seconds' => env('OTP_GRACE_SECONDS', 30),

    // Rate limits
    'send_limit_per_hour' => env('OTP_SEND_LIMIT_PER_HOUR', 5),
    'verify_attempts_limit' => env('OTP_VERIFY_ATTEMPTS_LIMIT', 5),

    // Lockout durations
    'send_block_seconds' => env('OTP_SEND_BLOCK_SECONDS', 3600), // block send after limit for 1 hour
    'verify_block_seconds' => env('OTP_VERIFY_BLOCK_SECONDS', 900) // block verify attempts for 15 minutes
];
