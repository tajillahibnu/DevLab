<?php

return [
    'provider' => env('AI_REPORT_PROVIDER', 'ollama'),
    'model' => env('AI_REPORT_MODEL'),
    'timeout' => env('AI_REPORT_TIMEOUT', 120),
    'url' => env('AI_REPORT_URL', 'http://localhost:11434'),
    'api_key' => env('AI_REPORT_API_KEY', ''),
];
