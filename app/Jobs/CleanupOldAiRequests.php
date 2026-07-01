<?php

namespace App\Jobs;

use App\Models\AiRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CleanupOldAiRequests implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        AiRequest::query()
            ->where('completed_at', '<', now()->subDay())
            ->update(['result' => null]);
    }
}
