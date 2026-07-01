<?php

namespace Tests\Feature;

use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Enums\ObjectiveStatus;
use App\Enums\PerformanceIndicatorCategory;
use App\Enums\PerformanceDataSource;
use App\Enums\ReviewStatus;
use App\Models\Membership;
use App\Models\Objective;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceReview;
use App\Models\User;
use Tests\TestCase;

class PerformancePhase3Test extends TestCase
{
    public function test_hr_can_create_performance_indicator(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        Membership::where('tenant_id', $tenant->id)->where('user_id', $user->id)->update([
            'role' => MembershipRole::HrManager,
        ]);

        $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/performance/indicators', [
                'name' => 'Quality Score',
                'category' => PerformanceIndicatorCategory::Quality->value,
                'weight' => 30,
                'data_source' => PerformanceDataSource::Manual->value,
            ])
            ->assertCreated()
            ->assertJsonPath('name', 'Quality Score');
    }

    public function test_member_can_create_and_update_objective(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        Membership::where('tenant_id', $tenant->id)->where('user_id', $user->id)->update([
            'role' => MembershipRole::Member,
        ]);

        $response = $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/objectives', [
                'title' => 'Launch feature X',
                'period' => '2026-Q3',
                'description' => 'Ship by end of quarter',
            ])
            ->assertCreated();

        $objectiveId = $response->json('id');

        $this->withTenantAuth($user, $tenant, $token)
            ->patchJson("/api/v1/objectives/{$objectiveId}", [
                'status' => ObjectiveStatus::Active->value,
            ])
            ->assertOk()
            ->assertJsonPath('status', ObjectiveStatus::Active->value);
    }

    public function test_key_result_progress_updates_objective_percent(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        $objective = Objective::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'title' => 'Grow revenue',
            'period' => '2026-Q2',
            'status' => ObjectiveStatus::Active,
            'progress_percent' => 0,
        ]);

        $krResponse = $this->withTenantAuth($user, $tenant, $token)
            ->postJson("/api/v1/objectives/{$objective->id}/key-results", [
                'title' => 'Increase sales',
                'target_value' => 100,
                'unit' => '%',
                'weight' => 1,
            ])
            ->assertCreated();

        $krId = $krResponse->json('id');

        $this->withTenantAuth($user, $tenant, $token)
            ->patchJson("/api/v1/key-results/{$krId}/progress", [
                'current_value' => 50,
            ])
            ->assertOk();

        $this->assertEquals(50, $objective->fresh()->progress_percent);
    }

    public function test_review_self_assessment_workflow(): void
    {
        ['user' => $employee, 'tenant' => $tenant] = $this->createUserWithTenant();
        $hr = $this->createHrManager($tenant);

        app()->instance('tenant', $tenant);

        $review = app(\App\Services\ReviewService::class)->create([
            'user_id' => $employee->id,
            'reviewer_user_id' => $hr['user']->id,
            'period' => '2026-Q2',
        ]);

        $updated = app(\App\Services\ReviewService::class)->submitSelfAssessment(
            $review,
            $employee,
            'Met all goals this quarter.'
        );

        $this->assertSame(ReviewStatus::SelfAssessment, $updated->status);
    }

    public function test_performance_score_can_be_recorded(): void
    {
        ['user' => $evaluator, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        $subject = User::factory()->create();

        Membership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $subject->id,
            'role' => MembershipRole::Member,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
        ]);

        $indicator = PerformanceIndicator::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Delivery',
            'category' => PerformanceIndicatorCategory::Timeliness,
            'weight' => 50,
            'data_source' => PerformanceDataSource::Manual,
            'is_active' => true,
        ]);

        Membership::where('tenant_id', $tenant->id)->where('user_id', $evaluator->id)->update([
            'role' => MembershipRole::HrManager,
        ]);

        $this->withTenantAuth($evaluator, $tenant, $token)
            ->postJson('/api/v1/performance/scores', [
                'user_id' => $subject->id,
                'indicator_id' => $indicator->id,
                'period' => '2026-Q2',
                'score' => 88,
                'evidence' => 'Strong delivery',
            ])
            ->assertCreated()
            ->assertJsonPath('score', '88.00');
    }
}
