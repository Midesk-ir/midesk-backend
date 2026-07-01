<?php

namespace Database\Factories;

use App\Models\PurchaseRequest;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PurchaseRequest> */
class PurchaseRequestFactory extends Factory
{
    protected $model = PurchaseRequest::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
        ];
    }
}
