<?php

declare(strict_types=1);

// config/flutterwave.php

return [
    /*
    |--------------------------------------------------------------------------
    | Flutterwave Credentials
    |--------------------------------------------------------------------------
    |
    | Your Flutterwave API credentials. These can be found in your Flutterwave
    | dashboard under Settings > API.
    |
    */

    'client_id' => env('FLUTTERWAVE_CLIENT_ID'),
    'client_secret' => env('FLUTTERWAVE_CLIENT_SECRET'),
    'secret_hash' => env('FLUTTERWAVE_SECRET_HASH'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | The environment to use for API calls. Valid values are:
    | - 'staging': Use the sandbox environment for testing
    | - 'production': Use the live environment for real transactions
    |
    */

    'environment' => env('FLUTTERWAVE_ENVIRONMENT', 'staging'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | The encryption key used to encrypt sensitive card data before sending
    | to Flutterwave. This is required for card transactions.
    |
    */

    'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, logs all API requests and responses for debugging.
    | WARNING: This will log sensitive data. Only enable in development.
    |
    */

    'debug' => env('FLUTTERWAVE_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    |
    | Configure API behavior including timeouts, retries, and rate limiting.
    |
    */

    'timeout' => env('FLUTTERWAVE_TIMEOUT', 30),

    'max_retries' => env('FLUTTERWAVE_MAX_RETRIES', 3),

    'retry_delay' => env('FLUTTERWAVE_RETRY_DELAY', 1000), // milliseconds

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting to prevent hitting Flutterwave API limits.
    | Max requests per time window (in seconds).
    |
    */

    'rate_limit' => [
        'enabled' => env('FLUTTERWAVE_RATE_LIMIT_ENABLED', true),
        'max_requests' => env('FLUTTERWAVE_RATE_LIMIT_MAX', 100),
        'per_seconds' => env('FLUTTERWAVE_RATE_LIMIT_WINDOW', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for Flutterwave API interactions.
    |
    */

    'logging' => [
        'enabled' => env('FLUTTERWAVE_LOGGING_ENABLED', true),
        'channel' => env('FLUTTERWAVE_LOG_CHANNEL', 'stack'),
        'level' => env('FLUTTERWAVE_LOG_LEVEL', 'info'),
        'log_requests' => env('FLUTTERWAVE_LOG_REQUESTS', false),
        'log_responses' => env('FLUTTERWAVE_LOG_RESPONSES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    |
    | Configure webhook handling for Flutterwave events.
    |
    */

    'webhook' => [
        'verify_signature' => env('FLUTTERWAVE_WEBHOOK_VERIFY', true),
        'route_path' => env('FLUTTERWAVE_WEBHOOK_PATH', 'webhooks/flutterwave'),
        'route_name' => 'flutterwave.webhook',
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency to use for transactions if not specified.
    |
    */

    'default_currency' => env('FLUTTERWAVE_DEFAULT_CURRENCY', 'TZS'),

    /*
    |--------------------------------------------------------------------------
    | Charge Sessions
    |--------------------------------------------------------------------------
    |
    | Configure charge session behavior for direct charge authorization flow.
    |
    */

    'charge_sessions' => [
        'enabled' => true,
        'table_name' => 'flutterwave_charge_sessions',
        'cleanup_after_days' => env('FLUTTERWAVE_SESSION_CLEANUP_DAYS', 30),
        'auto_create' => env('FLUTTERWAVE_SESSION_AUTO_CREATE', false),
        'max_polls' => env('FLUTTERWAVE_SESSION_MAX_POLLS', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching for Flutterwave data like access tokens, bank lists.
    |
    */

    'cache' => [
        'enabled' => env('FLUTTERWAVE_CACHE_ENABLED', true),
        'prefix' => 'flutterwave',
        'ttl' => [
            'access_token' => 3600, // 1 hour (managed by auth service)
            'banks' => 86400, // 24 hours
            'mobile_networks' => 86400, // 24 hours
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Classes
    |--------------------------------------------------------------------------
    |
    | Configure the model classes used by the ChargeSession model for
    | relationships. These should be the fully qualified class names of
    | your application's User and Payment models.
    |
    */

    'models' => [
        'user' => env('FLUTTERWAVE_USER_MODEL', 'App\Models\User'),
        'payment' => env('FLUTTERWAVE_PAYMENT_MODEL', 'App\Domain\Payment\Models\Payment'),
    ],
];
