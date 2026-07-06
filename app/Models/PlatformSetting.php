<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = [
        'platform_name',
        'support_email',
        'default_trial_days',
        'maintenance_mode',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_mode' => 'boolean',
            'default_trial_days' => 'integer',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'platform_name' => 'Midesk',
            'support_email' => 'support@midesk.io',
            'default_trial_days' => 14,
            'maintenance_mode' => false,
        ]);
    }
}
