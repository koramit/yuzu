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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'toothpaste' => [
        'url' => env('TOOTHPASTE_URL'),
        'token' => env('TOOTHPASTE_TOKEN'),
        'user_by_id' => [
            'endpoint' => env('HANNAH_URL').'user-by-id',
            'auth' => 'token_secret',
            'app' => env('HANNAH_APP'),
            'token' => env('HANNAH_TOKEN'),
        ],
        'authenticate' => [
            'endpoint' => env('HANNAH_URL').'auth',
            'auth' => 'token_secret',
            'app' => env('HANNAH_APP'),
            'token' => env('HANNAH_TOKEN'),
        ],
        'patient' => [
            'endpoint' => env('HANNAH_URL').'patient',
            'auth' => 'token_secret',
            'app' => env('HANNAH_APP'),
            'token' => env('HANNAH_TOKEN'),
        ],
        'admission' => [
            'endpoint' => env('HANNAH_URL').'admission',
            'auth' => 'token_secret',
            'app' => env('HANNAH_APP'),
            'token' => env('HANNAH_TOKEN'),
        ],
        'recently_admit' => [
            'endpoint' => env('HANNAH_URL').'patient-recently-admit',
            'auth' => 'token_secret',
            'app' => env('HANNAH_APP'),
            'token' => env('HANNAH_TOKEN'),
        ],
    ],

    'SUBHANNAH_API_NAME' => env('SUBHANNAH_API_NAME'),
    'SUBHANNAH_API_TOKEN' => env('SUBHANNAH_API_TOKEN'),
    'SUBHANNAH_API_URL' => env('SUBHANNAH_API_URL'),

    'mocktail' => [
        'link_user_endpoint' => env('MOCKTAIL_LINK_USER_ENDPOINT'),
        'refer_case_endpoint' => env('MOCKTAIL_REFER_CASE_ENDPOINT'),
    ],

    'siit' => [
        'export_case_endpoint' => env('SIIT_EXPORT_CASE_ENDPOINT'),
        'export_certificate_endpoint' => env('SIIT_EXPORT_CERTIFICATE_ENDPOINT'),
        'export_certificate_token' => env('SIIT_EXPORT_CERTIFICATE_TOKEN'),
    ],

    'line' => [
        'bot_secret' => env('LINE_BOT_SECRET'),
        'bot_token' => env('LINE_BOT_TOKEN'),
        'bot_link_url' => env('LINE_BOT_LINK_URL'),
        'base_endpoint' => env('LINE_BASE_ENDPOINT'),
        'bot_qrcode' =>  env('LINE_BOT_QRCODE'),
    ],

    'toto' => [
        'url' => env('TOTO_URL'),
        'token' => env('TOTO_TOKEN'),
        'method' => env('TOTO_METHOD'),
    ],

    'lisapi' => [
        'auth_url' => env('LISAPI_AUTH_URL'),
        'service_url' => env('LISAPI_SERVICE_URL'),
        'id' => env('LISAPI_ID'),
        'secret' => env('LISAPI_SECRET'),
    ],

    'sivaccination_api_url' => env('SIVACCINATION_API_URL'),

    'vaccine_brands' => [
        1 => 'AstraZeneca',
        5 => 'Moderna',
        6 => 'Pfizer',
        7 => 'Sinovac',
        8 => 'Sinopharm'
    ]
];
