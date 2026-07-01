<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\PersonnelStatus;
use App\Models\Personnel;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Personnel> */
class PersonnelFactory extends Factory
{
    protected $model = Personnel::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'employee_code' => 'EMP-'.fake()->unique()->numerify('###'),
            'hire_date' => now()->subMonths(6)->toDateString(),
            'employment_type' => EmploymentType::FullTime,
            'status' => PersonnelStatus::Active,
            'department' => fake()->randomElement(['Engineering', 'HR', 'Sales']),
            'job_title' => fake()->jobTitle(),
        ];
    }
}
