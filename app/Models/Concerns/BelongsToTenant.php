<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            if (app()->bound('tenant') && ($tenant = app('tenant'))) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $tenant->id);
            }
        });

        static::creating(function (Model $model): void {
            if (! $model->tenant_id && app()->bound('tenant') && ($tenant = app('tenant'))) {
                $model->tenant_id = $tenant->id;
            }
        });
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
