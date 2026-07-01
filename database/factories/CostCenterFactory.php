<?php

namespace Database\Factories;

use App\Models\CostCenter;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CostCenter> */
class CostCenterFactory extends Factory
{
    protected $model = CostCenter::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
        ];
    }
}
