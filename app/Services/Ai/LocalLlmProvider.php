<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class LocalLlmProvider implements AiProvider
{
    public function complete(string $prompt, array $context = []): string
    {
        $url = rtrim(config('ai.local_url'), '/');
        $model = config('ai.local_model', 'llama3');

        $response = Http::timeout(120)->post("{$url}/api/generate", [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false,
        ]);

        if ($response->failed()) {
            return json_encode(['summary' => 'Local LLM unavailable', 'prompt' => $prompt]);
        }

        return $response->json('response', $response->body());
    }

    public function modelName(): string
    {
        return config('ai.local_model', 'local');
    }
}
