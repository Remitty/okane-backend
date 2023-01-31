<?php

return [
    'client_id' => env('PLAID_CLIENT_ID', ''),
    'secret_key' => env('PLAID_SECRET_KEY', ''),
    'mode' => env('PLAID_MODE', 'production')
];
