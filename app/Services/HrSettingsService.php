<?php

namespace App\Services;

use App\Models\HrSetting;
use App\Models\Tenant;

class HrSettingsService
{
    /** @return array<string, mixed> */
    public function getSettings(): array
    {
        $tenant = app('tenant');
        $record = HrSetting::query()->where('tenant_id', $tenant->id)->first();

        return array_merge(HrSetting::defaults(), $record?->settings ?? []);
    }

    /** @param array<string, mixed> $data */
    public function updateSettings(array $data): array
    {
        $tenant = app('tenant');
        $current = $this->getSettings();
        $merged = array_merge($current, $data);

        HrSetting::updateOrCreate(
            ['tenant_id' => $tenant->id],
            ['settings' => $merged]
        );

        return $merged;
    }

    /** @return list<string> */
    public function currencies(): array
    {
        $settings = $this->getSettings();

        return $settings['currencies'] ?? HrSetting::defaults()['currencies'];
    }
}
