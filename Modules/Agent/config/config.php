<?php

return [
    'name' => 'Agent',
    'api_key' => env('GEMINI_KEY') ?: env('gemini_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-3.1-flash-lite'),
    'internal_api_base_url' => env('APP_URL', 'http://127.0.0.1:8000') . '/api/v1/sectors',
];
