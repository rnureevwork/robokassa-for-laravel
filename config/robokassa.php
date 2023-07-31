<?php
return [
    'is_test_mode' => env('ROBOKASSA_TEST_MODE', true),
    'base_url' => "https://auth.robokassa.ru/Merchant/Index.aspx?",

    'redirect_success_url' => env('ROBOKASSA_REDIRECT_SUCCESS_URL', null),
    'redirect_fail_url' => env('ROBOKASSA_REDIRECT_FAIL_URL', null),
];
