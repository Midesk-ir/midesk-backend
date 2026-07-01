<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\HolidayFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    /** @use HasFactory<HolidayFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'title',
        'date',
        'recurring_yearly',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'recurring_yearly' => 'boolean',
        ];
    }
}
