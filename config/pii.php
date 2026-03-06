<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PII Detection Patterns
    |--------------------------------------------------------------------------
    |
    | Column names matching these patterns (case-insensitive, partial match)
    | will be flagged as potentially containing personally identifiable
    | information. Flagged columns are excluded from API specs by default.
    |
    */

    'patterns' => [
        'password',
        'secret',
        'token',
        'ic_number',
        'nric',
        'mykad',
        'passport',
        'ssn',
        'social_security',
        'credit_card',
        'card_number',
        'cvv',
        'bank_account',
        'pin',
        'private_key',
        'api_key',
        'access_key',
        'secret_key',
        'auth_token',
        'refresh_token',
        'remember_token',
        'two_factor',
    ],

];
