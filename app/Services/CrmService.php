<?php

namespace App\Services;

use App\Enums\CrmOpportunityStage;
use App\Models\CrmActivity;
use App\Models\CrmContact;
use App\Models\CrmOpportunity;
use App\Models\User;

class CrmService
{
    public function pipeline(): array
    {
        $stages = collect(CrmOpportunityStage::cases())->mapWithKeys(function ($stage) {
            $query = CrmOpportunity::query()->where('stage', $stage);

            return [$stage->value => [
                'count' => $query->count(),
                'total_value' => (float) $query->sum('value'),
            ]];
        });

        return ['stages' => $stages];
    }

    public function report(): array
    {
        $won = CrmOpportunity::query()->where('stage', CrmOpportunityStage::Won)->count();
        $lost = CrmOpportunity::query()->where('stage', CrmOpportunityStage::Lost)->count();
        $total = max($won + $lost, 1);

        return [
            'won_lost_ratio' => round($won / $total, 2),
            'won' => $won,
            'lost' => $lost,
            'by_month' => CrmOpportunity::query()
                ->selectRaw("strftime('%Y-%m', created_at) as month, count(*) as count")
                ->groupBy('month')
                ->pluck('count', 'month'),
        ];
    }

    public function logActivity(User $user, array $data): CrmActivity
    {
        return CrmActivity::create([
            ...$data,
            'performed_by_user_id' => $user->id,
            'performed_at' => $data['performed_at'] ?? now(),
        ]);
    }
}
