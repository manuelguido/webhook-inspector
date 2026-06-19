<?php

return [
    'token_length' => env('WEBHOOK_TOKEN_LENGTH', 48),
    'max_body_bytes' => env('WEBHOOK_MAX_BODY_BYTES', 64 * 1024),
    'max_requests_per_bin' => env('WEBHOOK_MAX_REQUESTS_PER_BIN', 50),
    'rate_limit_per_minute' => env('WEBHOOK_RATE_LIMIT_PER_MINUTE', 120),
    'poll_interval_ms' => env('WEBHOOK_POLL_INTERVAL_MS', 4000),
];
