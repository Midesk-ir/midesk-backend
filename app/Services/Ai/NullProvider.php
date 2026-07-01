<?php

namespace App\Services\Ai;

class NullProvider implements AiProvider
{
    public function complete(string $prompt, array $context = []): string
    {
        return json_encode(['message' => 'AI is disabled', 'prompt' => $prompt]);
    }

    public function modelName(): string
    {
        return 'null';
    }
}
