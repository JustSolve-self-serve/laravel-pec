<?php

return [
    'default' => env('LEGALMAIL_PEC_DRIVER', 'legalmail'),
    'mailbox_id' => env('LEGALMAIL_PEC_MAILBOX_ID'),
    'folder_id' => env('LEGALMAIL_PEC_FOLDER_ID'),
    'message_uid_validity' => env('LEGALMAIL_PEC_MESSAGE_UID_VALIDITY'),

    'drivers' => [
        'legalmail' => [
            'base_url' => env('LEGALMAIL_PEC_BASE_URL', ''),
            'token' => env('LEGALMAIL_PEC_TOKEN'),

            // Optional extra headers required by the provider.
            'headers' => [
                // 'X-Customer-Id' => env('LEGALMAIL_PEC_CUSTOMER_ID', ''),
            ],
        ],
        'openapi_pec_massiva' => [
            'base_url' => env('OPENAPI_PEC_MASSIVA_BASE_URL', ''),
            'token' => env('OPENAPI_PEC_MASSIVA_TOKEN'),
            'headers' => [],
        ],
    ],
];
