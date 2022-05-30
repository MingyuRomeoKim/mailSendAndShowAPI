<?php

const _SUCCESS_CODE_ = [
    'DEFAULT' => 1,
];

const _ERROR_CODE_ = [
    'DEFAULT' => -1,
    'NO_DATA' => -2,
    'VALIDATION' => -10,
    'EMAIL' => -100,
    'EMAIL_TO' => -101,
    'EMAIL_CC' => -102,
    'EMAIL_BCC' => -103,
    'EMAIL_NAME' => -104,
    'PARTNER_KEY' => -101,
];

const _SUCCESS_MESSAGE_ = [
    'DEFAULT' => 'The Program Execution Success.',
];

const _ERROR_MESSAGE_ = [
    'DEFAULT' => 'Unknown Error.',
    'PARTNER_KEY' => 'The Partner_key is not available. plz check your partner_key.',
    'NO_DATA' => 'Response Data Is Empty. plz check your post data.',
    'EMAIL' => 'The Email is not available, plz check your email.',
    'EMAIL_TO' => 'The To Email is not available, plz check your to email.',
    'EMAIL_CC' => 'The Cc Email is not available, plz check your cc email.',
    'EMAIL_BCC' => 'The Bcc Email is not available, plz check your bcc email.',
    'EMAIL_NAME' => 'The Name is not available, plz check your email name.',
];

const _VALIDATION_ERROR_MESSAGE_ = [
    'required' => 'The Required Field :ateribute field is Required',
    'array' => 'The Validated Field :ateribute field is must Array type',
    'integer' => 'The Validated Field :ateribute field is must Integer type ',
];

const _KAFKA_TOPICS_ = [
    'SINGLE_MAILE' => 'test_mailsingle',
];

