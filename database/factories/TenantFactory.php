<?php

namespace Database\Factories;

use App\Enums\CalendarType;
use App\Enums\TenantRegion;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Tenant> */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => str()->slug($name).'-'.fake()->unique()->numerify('###'),
            'region' => TenantRegion::IR,
            'locale' => 'fa',
            'timezone' => 'Asia/Tehran',
            'default_currency' => 'IRR',
            'calendar_type' => CalendarType::Jalali,
            'plan_id' => null,
        ];
    }
}
