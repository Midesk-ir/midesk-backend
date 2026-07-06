<?php

namespace App\Services;

use App\Enums\CrmOpportunityStage;
use App\Enums\LeaveRequestStatus;
use App\Http\Resources\Site\UserResource;
use App\Http\Resources\TenantResource;
use App\Models\Candidate;
use App\Models\Contract;
use App\Models\CrmActivity;
use App\Models\CrmOpportunity;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\LeaveRequest;
use App\Models\Membership;
use App\Models\Objective;
use App\Models\PayrollRecord;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceReview;
use App\Models\Personnel;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\Tenant;
use App\Models\User;
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
        return [
            'user' => new UserResource($user, $membership),
            'tenant' => new TenantResource($tenant),
            'permissions' => SitePermissions::forRole($membership->role?->value),
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

        return [
            'indicators' => PerformanceIndicator::query()->with('owner')->get(),
            'objectives_summary' => [
                'on_track' => $objectives->where('status', 'on_track')->count(),
                'at_risk' => $objectives->where('status', 'at_risk')->count(),
                'off_track' => $objectives->where('status', 'off_track')->count(),
                'completed' => $objectives->where('status', 'completed')->count(),
            ],
            'upcoming_reviews' => PerformanceReview::query()
                ->with(['employee', 'reviewer'])
                ->where('status', '!=', 'completed')
                ->orderBy('scheduled_at')
                ->limit(10)
                ->get(),
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
    public function pendingLeaveRequests(): array
    {
        return LeaveRequest::query()
            ->with(['leaveType', 'user'])
            ->where('status', LeaveRequestStatus::Pending)
            ->orderByDesc('created_at')
            ->get()
            ->all();
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
