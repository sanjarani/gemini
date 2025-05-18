<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gemini API Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for configuring the Gemini API client.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your Google Gemini API key. You can get one from the Google AI Studio.
    |
    */
    'api_key' => env('GEMINI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Gemini API. This should not be changed unless
    | Google changes their API endpoint.
    |
    */
    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1'),

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | The default model to use for requests. This can be overridden at runtime.
    |
    */
    'default_model' => env('GEMINI_DEFAULT_MODEL', 'gemini-pro'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout for API requests in seconds.
    |
    */
    'request_timeout' => env('GEMINI_REQUEST_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching API responses to save on tokens and improve
    | response times.
    |
    */
    'cache' => [
        'enabled' => env('GEMINI_ENABLE_CACHE', false),
        'ttl' => env('GEMINI_CACHE_TTL', 3600), // Time to live in seconds
        'prefix' => 'gemini_cache_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for logging API requests and responses.
    |
    */
    'logging' => [
        'enabled' => env('GEMINI_ENABLE_LOGGING', false),
        'channel' => env('GEMINI_LOG_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configuration for rate limiting API requests.
    |
    */
    'rate_limiting' => [
        'max_retries' => env('GEMINI_MAX_RETRIES', 3),
        'retry_delay' => env('GEMINI_RETRY_DELAY', 1000), // In milliseconds
        'backoff_multiplier' => env('GEMINI_BACKOFF_MULTIPLIER', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Models Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for different Gemini models including token limits and pricing.
    |
    */
    'models' => [
        'gemini-pro' => [
            'max_tokens' => 8192,
            'input_price_per_1k' => 0.00025,
            'output_price_per_1k' => 0.0005,
        ],
        'gemini-pro-vision' => [
            'max_tokens' => 8192,
            'input_price_per_1k' => 0.0025,
            'output_price_per_1k' => 0.0005,
        ],
        'gemini-ultra' => [
            'max_tokens' => 8192,
            'input_price_per_1k' => 0.0025,
            'output_price_per_1k' => 0.0075,
        ],
        'gemini-ultra-vision' => [
            'max_tokens' => 8192,
            'input_price_per_1k' => 0.0025,
            'output_price_per_1k' => 0.0075,
        ],
    ],
];
