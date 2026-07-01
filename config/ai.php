<?php

return [
    'provider' => env('AI_PROVIDER', 'openai'),
    'openai_key' => env('OPENAI_API_KEY'),
    'openai_model' => env('OPENAI_MODEL', 'gpt-4o'),
    'local_url' => env('LOCAL_LLM_URL', 'http://localhost:11434'),
    'local_model' => env('LOCAL_LLM_MODEL', 'llama3'),
];
