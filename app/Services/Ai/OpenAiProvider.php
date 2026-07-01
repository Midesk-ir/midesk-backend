<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class OpenAiProvider implements AiProvider
{
    public function complete(string $prompt, array $context = []): string
    {
        $key = config('ai.openai_key');
        if (! $key) {
            return json_encode(['summary' => 'OpenAI API key not configured', 'context' => $context]);
        }

        $response = Http::withToken($key)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('ai.openai_model', 'gpt-4o'),
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant for Midesk workplace management.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('OpenAI request failed: '.$response->body());
        }

        return $response->json('choices.0.message.content', '');
    }

    public function modelName(): string
    {
        return config('ai.openai_model', 'gpt-4o');
    }
}
