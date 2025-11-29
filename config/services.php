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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS
    |--------------------------------------------------------------------------
    |
    | playmobilening username va parollarini envdan olib kelish
    |
    */
    'sms' => [
        'url' => env('SMS_API_URL'),
        'face_name' => env('SMS_API_FACE_NAME'),
        'username' => env('SMS_API_USERNAME'),
        'password' => env('SMS_API_PASSWORD'),
    ],

    'hamkorbank' => [
        'url' => env('BANK_URL_HAMKORBANK'),
        'key' => env('ACQUIRING_KEY'),
        'secret' => env('ACQUIRING_SECRET'),
    ],
    [
        'service_fee_for_compliting_order' => env('SERVICE_FEE_FOR_COMPLITING_ORDER'),
    ],

];
