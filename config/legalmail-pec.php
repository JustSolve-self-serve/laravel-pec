<?php

return [
    'base_url' => env('LEGALMAIL_PEC_BASE_URL', ''),
    'token' => env('LEGALMAIL_PEC_TOKEN'),
    'timeout' => (int) env('LEGALMAIL_PEC_TIMEOUT', 20),
    'mailbox_id' => env('LEGALMAIL_PEC_MAILBOX_ID'),
    'folder_id' => env('LEGALMAIL_PEC_FOLDER_ID'),
    'message_uid_validity' => env('LEGALMAIL_PEC_MESSAGE_UID_VALIDITY'),

    // Optional extra headers required by the provider.
    'headers' => [
        // 'X-Customer-Id' => env('LEGALMAIL_PEC_CUSTOMER_ID', ''),
    ],
];
