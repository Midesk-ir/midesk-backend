<?php

namespace App\Models;

use App\Enums\ReportChartType;
use App\Enums\ReportDataSource;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomReport extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'name',
        'data_source',
        'filters',
        'columns',
        'group_by',
        'chart_type',
        'schedule',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'data_source' => ReportDataSource::class,
            'filters' => 'array',
            'columns' => 'array',
            'chart_type' => ReportChartType::class,
            'schedule' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
