<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Smartping cloud telephony (Dialer module). Several values are still TBD
    // with Smartping — see the integration reference. Until configured, the
    // dialer UI shows a "not connected" state and webhooks accept everything.
    'smartping' => [
        'base_url'        => env('SMARTPING_BASE_URL', 'http://ccs.smartping.io/crm-integration/api/v1/integration'),
        'api_key'         => env('SMARTPING_API_KEY'),
        'sme_id'          => env('SMARTPING_SME_ID'),
        'iframe_url'      => env('SMARTPING_IFRAME_URL'),       // dialer iframe Smartping hosts (TBD)
        'webhook_secret'  => env('SMARTPING_WEBHOOK_SECRET'),   // shared secret on inbound webhooks (optional)
        'webhook_ips'     => env('SMARTPING_WEBHOOK_IPS'),      // comma-separated IP allowlist (optional)
        'recording_disk'  => env('SMARTPING_RECORDING_DISK', 'local'),  // where the backup cron stores recordings
    ],

];
