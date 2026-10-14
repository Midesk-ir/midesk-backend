<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

class TenantService
{
    public function createForUser(User $user, array $data): Tenant
    {
        $slug = Str::slug($data['name']);
        $baseSlug = $slug;
        $counter = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        $plan = Plan::query()->where('code', 'bronze')->first();

        $tenant = Tenant::create([
            'name' => $data['name'],
            'slug' => $slug,
            'region' => $data['region'] ?? 'IR',
            'locale' => $data['locale'] ?? 'fa',
            'timezone' => $data['timezone'] ?? 'Asia/Tehran',
            'default_currency' => $data['default_currency'] ?? 'IRR',
            'calendar_type' => $data['calendar_type'] ?? 'jalali',
            'plan_id' => $plan?->id,
        ]);

        Membership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role' => 'OWNER',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        if ($plan) {
            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'trial',
                'current_period_end' => now()->addMonth(),
            ]);
        }

        return $tenant;
    }
}
