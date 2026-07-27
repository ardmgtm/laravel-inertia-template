<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sensitive Data Keys
    |--------------------------------------------------------------------------
    |
    | This array contains all keys that should be masked in logs and activity
    | tracking. Any data with these keys will be replaced with a masked value
    | to protect sensitive information.
    |
    */

    'keys' => [
        // Authentication & Security
        'password',
        'password_confirmation',
        'old_password',
        'new_password',
        'confirm_password',
        'current_password',
        'token',
        'api_key',
        'api_secret',
        'access_token',
        'refresh_token',
        'bearer_token',
        'auth_token',
        'session_token',
        'csrf_token',
        'secret',
        'secret_key',
        'private_key',
        'public_key',
        'encryption_key',
        'secret_answer',
        'security_answer',
        'security_question',
        'pin',
        'otp',
        'verification_code',
        'two_factor_code',
        '2fa_code',
        'mfa_code',

        // Financial Information
        'credit_card',
        'credit_card_number',
        'card_number',
        'cvv',
        'cvc',
        'card_cvv',
        'card_cvc',
        'expiry',
        'expiry_date',
        'card_expiry',
        'bank_account',
        'bank_account_number',
        'account_number',
        'routing_number',
        'iban',
        'swift',
        'swift_code',
        'bic',

        // Personal Identification
        'ssn',
        'social_security',
        'social_security_number',
        'national_id',
        'passport',
        'passport_number',
        'driving_license',
        'license_number',
        'tax_id',
        'tax_identification_number',

        // Cookies & Session
        'cookie',
        'session',
        'session_id',

        // Database
        'db_password',
        'database_password',
        'mysql_password',
        'postgres_password',

        // External Services
        'aws_secret',
        'aws_access_key',
        'aws_secret_key',
        'stripe_secret',
        'stripe_key',
        'paypal_secret',
        'google_secret',
        'facebook_secret',
        'oauth_secret',
        'client_secret',

        // Other Sensitive Data
        'signature',
        'authorization',
        'x-api-key',
    ],

    /*
    |--------------------------------------------------------------------------
    | Mask Value
    |--------------------------------------------------------------------------
    |
    | The value that will replace sensitive data when masking.
    |
    */

    'mask_value' => '<information hidden>',

    /*
    |--------------------------------------------------------------------------
    | Case Sensitive
    |--------------------------------------------------------------------------
    |
    | Whether the key matching should be case sensitive.
    |
    */

    'case_sensitive' => false,
];
