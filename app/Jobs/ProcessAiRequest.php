<?php

namespace App\Jobs;

use App\Models\AiRequest;
use App\Services\AiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessAiRequest implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $aiRequestId) {}

    public function handle(AiService $aiService): void
    {
        $request = AiRequest::find($this->aiRequestId);
        if ($request) {
            $aiService->process($request);
        }
    }
}
