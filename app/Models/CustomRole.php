<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CustomRole extends Model
{
    use BelongsToTenant;
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'name',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }
}
