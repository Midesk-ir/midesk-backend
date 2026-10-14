<?php

namespace App\Services;

use App\Enums\CrmOpportunityStage;
use App\Enums\LeaveRequestStatus;
use App\Enums\ReviewStatus;
use App\Http\Resources\Site\UserResource;
use App\Http\Resources\TenantResource;
use App\Models\Candidate;
use App\Models\Contract;
use App\Models\CrmActivity;
use App\Models\CrmOpportunity;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Membership;
use App\Models\Objective;
use App\Models\PayrollRecord;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceReview;
use App\Models\PerformanceScore;
use App\Models\Personnel;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\Tenant;
use App\Models\User;
use App\Support\PlanFeatureGate;
use App\Support\SitePermissions;
use Illuminate\Support\Facades\Cache;

class SiteCompatService
{
    public function authPayload(User $user): array
    {
        $tenant = $this->defaultTenantForUser($user);
        $membership = $tenant
            ? Membership::query()->where('tenant_id', $tenant->id)->where('user_id', $user->id)->where('status', 'active')->first()
            : null;

        return [
            'token' => null,
            'user' => new UserResource($user, $membership),
            'tenant' => $tenant ? new TenantResource($tenant) : null,
        ];
    }

    public function bootstrapPayload(User $user, Tenant $tenant, Membership $membership): array
    {
        $permissions = SitePermissions::forRole($membership->role?->value);

        return [
            'user' => new UserResource($user, $membership),
            'tenant' => new TenantResource($tenant),
            'permissions' => PlanFeatureGate::filterPermissions($permissions, $tenant),
        ];
    }

    public function defaultTenantForUser(User $user): ?Tenant
    {
        return Tenant::query()
            ->whereHas('memberships', fn ($q) => $q->where('user_id', $user->id)->where('status', 'active'))
            ->orderBy('created_at')
            ->first();
    }

    /** @return array<string, mixed> */
    public function hrDashboard(): array
    {
        $tenantId = app('tenant')->id;

        return [
            'active_personnel_count' => Personnel::query()->where('status', 'active')->count(),
            'attendance_today' => app(AttendanceService::class)->todayOrganization(),
            'expiring_contracts' => Contract::query()
                ->where('end_date', '<=', now()->addDays(30))
                ->where('end_date', '>=', now())
                ->limit(10)
                ->get(),
            'active_onboarding' => app(OnboardingService::class)->listActiveProgress()->items(),
            'new_candidates_count' => Candidate::query()->where('status', 'new')->count(),
        ];
    }

    /** @return array<string, mixed> */
    public function financeDashboard(): array
    {
        $tenant = app('tenant');
        $currency = $tenant->default_currency ?? 'IRR';

        $overdueInvoices = Invoice::query()
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->get();

        $pendingExpenses = Expense::query()->where('status', 'pending')->get();
        $payrollPending = PayrollRecord::query()->where('status', 'pending')->count();

        return [
            'budget_overview' => app(BudgetService::class)->overview(),
            'expense_summary' => [
                'pending_count' => $pendingExpenses->count(),
                'pending_amount' => (float) $pendingExpenses->sum('amount'),
                'currency' => $currency,
            ],
            'overdue_invoices_count' => $overdueInvoices->count(),
            'overdue_invoices_amount' => (float) $overdueInvoices->sum('amount'),
            'payroll_status' => $payrollPending > 0 ? 'pending' : 'clear',
            'currency' => $currency,
        ];
    }

    /** @return array<string, mixed> */
    public function crmDashboard(): array
    {
        $funnel = collect(CrmOpportunityStage::cases())->mapWithKeys(
            fn ($stage) => [$stage->value => CrmOpportunity::query()->where('stage', $stage)->count()]
        )->all();

        $hot = CrmOpportunity::query()
            ->with('contact')
            ->where('probability', '>=', 70)
            ->whereNotIn('stage', [CrmOpportunityStage::Won, CrmOpportunityStage::Lost])
            ->limit(10)
            ->get();

        return [
            'funnel' => $funnel,
            'hot_opportunities' => $hot,
            'recent_activities' => CrmActivity::query()->with(['contact', 'opportunity'])->latest('performed_at')->limit(10)->get(),
        ];
    }

    /** @return array<string, mixed> */
    public function performanceDashboard(): array
    {
        $objectives = Objective::query()->get();

        $objectivesSummary = [
            'on_track' => 0,
            'at_risk' => 0,
            'off_track' => 0,
            'completed' => 0,
        ];

        foreach ($objectives as $objective) {
            $status = $objective->status?->value ?? $objective->status;
            if ($status === 'completed') {
                $objectivesSummary['completed']++;
                continue;
            }
            if ($status !== 'active') {
                continue;
            }

            $progress = (int) $objective->progress_percent;
            if ($progress >= 70) {
                $objectivesSummary['on_track']++;
            } elseif ($progress >= 40) {
                $objectivesSummary['at_risk']++;
            } else {
                $objectivesSummary['off_track']++;
            }
        }

        $indicators = PerformanceIndicator::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (PerformanceIndicator $indicator) {
                $latestScore = PerformanceScore::query()
                    ->with('user')
                    ->where('indicator_id', $indicator->id)
                    ->orderByDesc('created_at')
                    ->first();

                return [
                    'id' => $indicator->id,
                    'name' => $indicator->name,
                    'description' => $indicator->description,
                    'unit' => $indicator->measurement_unit ?? '',
                    'target' => (float) ($indicator->target_value ?? 0),
                    'current' => (float) ($latestScore?->score ?? 0),
                    'owner' => $this->userBrief($latestScore?->user),
                ];
            })
            ->values()
            ->all();

        $upcomingReviews = PerformanceReview::query()
            ->with(['user', 'reviewer'])
            ->where('status', '!=', ReviewStatus::Finalized->value)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn (PerformanceReview $review) => [
                'id' => $review->id,
                'employee' => $this->userBrief($review->user),
                'reviewer' => $this->userBrief($review->reviewer),
                'period' => $review->period,
                'status' => $review->status?->value ?? $review->status,
                'score' => $review->overall_score !== null ? (float) $review->overall_score : null,
                'scheduled_at' => ($review->created_at ?? now())->toIso8601String(),
            ])
            ->values()
            ->all();

        return [
            'indicators' => $indicators,
            'objectives_summary' => $objectivesSummary,
            'upcoming_reviews' => $upcomingReviews,
        ];
    }

    /** @return array{id: string, name: string, avatar_url: string|null} */
    private function userBrief(?User $user): array
    {
        if (! $user) {
            return ['id' => '', 'name' => '—', 'avatar_url' => null];
        }

        return [
            'id' => $user->id,
            'name' => $user->full_name,
            'avatar_url' => $user->avatar_url,
        ];
    }

    /** @return array<string, mixed> */
    public function superAdminFinancial(): array
    {
        $revenueByPlan = SubscriptionInvoice::query()
            ->where('status', 'paid')
            ->with('subscription.plan')
            ->get()
            ->groupBy(fn ($i) => $i->subscription?->plan?->name ?? 'Unknown')
            ->map(fn ($group) => (float) $group->sum('amount'))
            ->all();

        return [
            'mrr' => (float) Cache::get('super_admin:mrr', 0),
            'arr' => (float) Cache::get('super_admin:arr', 0),
            'revenue_by_plan' => $revenueByPlan,
            'revenue_by_cycle' => ['monthly' => 0, 'yearly' => 0],
            'cancellations_this_month' => Subscription::query()
                ->where('status', 'cancelled')
                ->whereMonth('updated_at', now()->month)
                ->count(),
        ];
    }

    /** @return list<\App\Models\LeaveRequest> */
    public function pendingLeaveRequests(): Collection
    {
        return LeaveRequest::query()
            ->with(['leaveType', 'user'])
            ->where('status', LeaveRequestStatus::Pending)
            ->orderByDesc('created_at')
            ->get();
    }

    /** @return list<UserResource> */
    public function tenantMembers(): array
    {
        $memberships = Membership::query()
            ->with('user')
            ->where('tenant_id', app('tenant')->id)
            ->where('status', 'active')
            ->get();

        return $memberships
            ->map(fn (Membership $m) => new UserResource($m->user, $m))
            ->all();
    }
}
