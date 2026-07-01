<?php

namespace App\Services;

use App\Enums\CorrespondenceStatus;
use App\Enums\RoutingStatus;
use App\Models\Correspondence;
use App\Models\CorrespondenceRouting;
use App\Models\User;

class CorrespondenceService
{
    public function nextNumber(): string
    {
        $year = now()->year;
        $lastSeq = Correspondence::query()
            ->whereYear('created_at', $year)
            ->max('number_seq') ?? 0;

        return $this->formatNumber($year, $lastSeq + 1);
    }

    public function create(User $user, array $data): Correspondence
    {
        $year = now()->year;
        $lastSeq = Correspondence::query()->whereYear('created_at', $year)->max('number_seq') ?? 0;
        $nextSeq = $lastSeq + 1;

        return Correspondence::create([
            ...$data,
            'number' => $this->formatNumber($year, $nextSeq),
            'number_seq' => $nextSeq,
            'status' => CorrespondenceStatus::Draft,
            'created_by_user_id' => $user->id,
        ]);
    }

    public function send(Correspondence $correspondence): Correspondence
    {
        $correspondence->update(['status' => CorrespondenceStatus::Sent]);

        return $correspondence->fresh();
    }

    public function sign(Correspondence $correspondence, User $user): Correspondence
    {
        $correspondence->update([
            'signed_by_user_id' => $user->id,
            'signed_at' => now(),
        ]);

        return $correspondence->fresh();
    }

    public function route(Correspondence $correspondence, User $from, array $data): CorrespondenceRouting
    {
        return CorrespondenceRouting::create([
            'correspondence_id' => $correspondence->id,
            'from_user_id' => $from->id,
            'to_user_id' => $data['to_user_id'],
            'action' => $data['action'],
            'note' => $data['note'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'status' => RoutingStatus::Pending,
            'created_at' => now(),
        ]);
    }

    public function inbox(User $user)
    {
        return CorrespondenceRouting::query()
            ->where('to_user_id', $user->id)
            ->with(['correspondence', 'fromUser'])
            ->orderByDesc('created_at')
            ->get();
    }

    private function formatNumber(int $year, int $seq): string
    {
        return sprintf('MID-%d-%05d', $year, $seq);
    }
}
