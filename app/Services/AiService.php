<?php

namespace App\Services;

use App\Enums\AiRequestStatus;
use App\Enums\AiRequestType;
use App\Jobs\ProcessAiRequest;
use App\Models\AiRequest;
use App\Models\User;
use App\Services\Ai\AiProvider;
use App\Services\Ai\LocalLlmProvider;
use App\Services\Ai\NullProvider;
use App\Services\Ai\OpenAiProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AiService
{
    public function isEnabled(): bool
    {
        return config('ai.provider') !== 'null';
    }

    public function provider(): AiProvider
    {
        return match (config('ai.provider', 'openai')) {
            'local' => new LocalLlmProvider,
            'null' => new NullProvider,
            default => new OpenAiProvider,
        };
    }

    public function queueRequest(AiRequestType $type, array $inputData, User $user): AiRequest
    {
        if (! $this->isEnabled()) {
            throw new HttpException(403, 'AI is disabled');
        }

        $request = AiRequest::create([
            'type' => $type,
            'input_data' => $inputData,
            'status' => AiRequestStatus::Queued,
            'requested_by_user_id' => $user->id,
            'created_at' => now(),
        ]);

        ProcessAiRequest::dispatch($request->id);

        return $request;
    }

    public function process(AiRequest $request): AiRequest
    {
        $request->update(['status' => AiRequestStatus::Processing]);

        try {
            $prompt = $this->buildPrompt($request);
            $provider = $this->provider();
            $result = $provider->complete($prompt, $request->input_data ?? []);

            $request->update([
                'status' => AiRequestStatus::Completed,
                'result' => ['content' => $result],
                'model_used' => $provider->modelName(),
                'tokens_used' => strlen($result),
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $request->update([
                'status' => AiRequestStatus::Failed,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }

        return $request->fresh();
    }

    private function buildPrompt(AiRequest $request): string
    {
        $data = json_encode($request->input_data, JSON_UNESCAPED_UNICODE);

        return match ($request->type) {
            AiRequestType::TaskAnalysis => "Analyze project tasks: {$data}",
            AiRequestType::DocumentGeneration => "Generate document: {$data}",
            AiRequestType::ResumeScreening => "Screen resume: {$data}",
            AiRequestType::PerformanceAnalysis => "Analyze performance: {$data}",
            AiRequestType::GeneralQuery => $request->input_data['prompt'] ?? $data,
        };
    }
}
