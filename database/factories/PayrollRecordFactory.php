<?php

namespace Database\Factories;

use App\Models\PayrollRecord;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PayrollRecord> */
class PayrollRecordFactory extends Factory
{
    protected $model = PayrollRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
        ];
    }
}
