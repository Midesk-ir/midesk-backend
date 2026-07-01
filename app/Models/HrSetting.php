<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HrSetting extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'attendance_grace_minutes' => 15,
            'auto_checkout_enabled' => true,
            'auto_checkout_time' => '23:59',
            'leave_year_start_month' => 1,
            'contract_expiry_warning_days' => 30,
            'currencies' => ['IRR', 'USD', 'EUR'],
        ];
    }
}
