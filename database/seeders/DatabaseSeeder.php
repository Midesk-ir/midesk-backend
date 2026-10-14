<?php

namespace Database\Seeders;

use App\Enums\ApprovalWorkflowStatus;
use App\Enums\BudgetStatus;
use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Enums\ObjectiveStatus;
use App\Enums\PerformanceDataSource;
use App\Enums\PerformanceIndicatorCategory;
use App\Enums\ProjectMemberRole;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Budget;
use App\Models\CostCenter;
use App\Models\ExpenseCategory;
use App\Models\Label;
use App\Models\Membership;
use App\Models\Objective;
use App\Models\PerformanceIndicator;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\SuperAdmin;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        SuperAdmin::query()->firstOrCreate(
            ['email' => 'superadmin@midesk.io'],
            [
                'password' => 'superadmin',
                'full_name' => 'Super Admin',
                'is_active' => true,
            ]
        );

        $plans = [
            [
                'name' => 'Bronze',
                'code' => 'bronze',
                'features' => [
                    'max_projects' => 10,
                    'max_users' => 25,
                    'modules' => ['projects', 'work', 'collab', 'org'],
                    'trial_days' => 30,
                    'pricing' => [
                        'IR' => ['monthly' => 399000, 'quarterly' => 1100000, 'semiannual' => 2100000, 'yearly' => 3900000],
                    ],
                ],
                'price' => ['monthly' => 399000, 'yearly' => 3900000],
            ],
            [
                'name' => 'Silver',
                'code' => 'silver',
                'features' => [
                    'max_projects' => 30,
                    'max_users' => 100,
                    'modules' => ['projects', 'work', 'collab', 'org', 'hr'],
                    'pricing' => [
                        'IR' => [
                            'monthly' => 999000,
                            'monthly_original' => 1500000,
                            'quarterly' => 3900000,
                            'semiannual' => 7000000,
                            'yearly' => 12000000,
                        ],
                    ],
                ],
                'price' => ['monthly' => 999000, 'yearly' => 12000000],
            ],
            [
                'name' => 'Gold',
                'code' => 'gold',
                'features' => [
                    'max_projects' => 80,
                    'max_users' => 300,
                    'modules' => ['projects', 'work', 'collab', 'org', 'hr', 'business'],
                    'pricing' => [
                        'IR' => [
                            'monthly' => 2500000,
                            'monthly_original' => 3000000,
                            'quarterly' => 7000000,
                            'semiannual' => 12000000,
                            'yearly' => 22000000,
                        ],
                    ],
                ],
                'price' => ['monthly' => 2500000, 'yearly' => 22000000],
            ],
            [
                'name' => 'Diamond',
                'code' => 'diamond',
                'features' => [
                    'max_projects' => 300,
                    'max_users' => 1500,
                    'modules' => ['projects', 'work', 'collab', 'org', 'hr', 'business', 'finance'],
                    'pricing' => [
                        'IR' => [
                            'monthly' => 4500000,
                            'monthly_original' => 5000000,
                            'quarterly' => 12000000,
                            'semiannual' => 21000000,
                            'yearly' => 40000000,
                        ],
                    ],
                ],
                'price' => ['monthly' => 4500000, 'yearly' => 40000000],
            ],
        ];

        $bronzePlan = null;
        foreach ($plans as $item) {
            $plan = Plan::query()->updateOrCreate(
                ['code' => $item['code']],
                ['name' => $item['name'], 'features' => $item['features']]
            );

            PlanPrice::query()->updateOrCreate(
                ['plan_id' => $plan->id, 'region' => 'IR'],
                [
                    'currency' => 'IRR',
                    'monthly_amount' => $item['price']['monthly'],
                    'yearly_amount' => $item['price']['yearly'],
                ]
            );

            if ($item['code'] === 'bronze') {
                $bronzePlan = $plan;
            }
        }

        $owner = User::create([
            'email' => 'owner@midesk.test',
            'full_name' => 'Org Owner',
            'password' => 'password',
            'is_active' => true,
        ]);

        $member = User::create([
            'email' => 'member@midesk.test',
            'full_name' => 'Team Member',
            'password' => 'password',
            'is_active' => true,
        ]);

        $financeUser = User::create([
            'email' => 'finance@midesk.test',
            'full_name' => 'Finance Manager',
            'password' => 'password',
            'is_active' => true,
        ]);

        $tenant = Tenant::create([
            'name' => 'Midesk Demo',
            'slug' => 'midesk-demo',
            'region' => 'IR',
            'locale' => 'fa',
            'timezone' => 'Asia/Tehran',
            'default_currency' => 'IRR',
            'calendar_type' => 'jalali',
            'plan_id' => $bronzePlan?->id,
        ]);

        if ($bronzePlan) {
            Subscription::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'plan_id' => $bronzePlan->id],
                ['status' => 'trial', 'current_period_end' => now()->addMonth()]
            );
        }

        Membership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'role' => MembershipRole::Owner,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
        ]);

        Membership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $member->id,
            'role' => MembershipRole::Member,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
        ]);

        Membership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $financeUser->id,
            'role' => MembershipRole::Finance,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
        ]);

        $project = Project::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Website Redesign',
            'key' => 'WEB',
            'description' => 'Demo project for Phase 0',
            'status' => ProjectStatus::Active,
            'owner_user_id' => $owner->id,
            'start_date' => now()->toDateString(),
            'due_date' => now()->addMonths(2)->toDateString(),
        ]);

        $project->members()->attach($owner->id, [
            'tenant_id' => $tenant->id,
            'role' => ProjectMemberRole::Lead->value,
        ]);

        $project->members()->attach($member->id, [
            'tenant_id' => $tenant->id,
            'role' => ProjectMemberRole::Member->value,
        ]);

        $bugLabel = Label::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Bug',
            'color' => '#ef4444',
        ]);

        $featureLabel = Label::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Feature',
            'color' => '#22c55e',
        ]);

        $taskOne = Task::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'project_id' => $project->id,
            'title' => 'Setup project board',
            'description' => 'Initial kanban setup',
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::High,
            'assignee_user_id' => $member->id,
            'reporter_user_id' => $owner->id,
            'due_date' => now()->addWeek(),
            'order_index' => 1024,
        ]);

        $taskTwo = Task::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'project_id' => $project->id,
            'title' => 'Design homepage mockup',
            'status' => TaskStatus::InProgress,
            'priority' => TaskPriority::Medium,
            'assignee_user_id' => $owner->id,
            'reporter_user_id' => $owner->id,
            'due_date' => now()->subDay(),
            'order_index' => 2048,
        ]);

        $taskOne->labels()->attach($featureLabel->id, ['tenant_id' => $tenant->id]);
        $taskTwo->labels()->attach($bugLabel->id, ['tenant_id' => $tenant->id]);

        $itCenter = CostCenter::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'IT Department',
            'code' => 'IT-001',
            'manager_user_id' => $owner->id,
        ]);

        Budget::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'cost_center_id' => $itCenter->id,
            'year' => (int) now()->year,
            'amount' => 100_000_000,
            'spent' => 0,
            'currency' => 'IRR',
            'status' => BudgetStatus::Active,
        ]);

        ExpenseCategory::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Office Supplies',
        ]);

        Vendor::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo Vendor Co.',
            'email' => 'vendor@example.com',
            'is_active' => true,
        ]);

        PerformanceIndicator::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Task Completion Rate',
            'category' => PerformanceIndicatorCategory::Timeliness,
            'weight' => 40,
            'data_source' => PerformanceDataSource::AutoTasks,
            'is_active' => true,
        ]);

        Objective::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $member->id,
            'title' => 'Improve delivery speed',
            'period' => now()->format('Y').'-Q'.ceil(now()->month / 3),
            'status' => ObjectiveStatus::Active,
            'progress_percent' => 0,
        ]);
    }
}
