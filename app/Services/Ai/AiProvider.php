<?php

namespace App\Services\Ai;

interface AiProvider
{
    public function complete(string $prompt, array $context = []): string;

    public function modelName(): string;
}
