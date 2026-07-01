<?php

namespace App\Jobs;

use App\Models\GuestAccess;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CleanupExpiredGuests implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        GuestAccess::withoutGlobalScopes()
            ->where('expires_at', '<', now())
            ->delete();
    }
}
