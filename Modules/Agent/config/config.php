<?php

return [
    'name' => 'Agent',
    'api_key' => env('gemini_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    'internal_api_base_url' => env('APP_URL', 'http://127.0.0.1:8000') . '/api/v1/sectors',
];
