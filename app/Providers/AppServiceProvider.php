<?php

namespace App\Providers;

use App\Models\AttendanceRecord;
use App\Models\Budget;
use App\Models\CostCenter;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\KeyResult;
use App\Models\Objective;
use App\Models\PayrollRecord;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceReview;
use App\Models\PerformanceScore;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Vendor;
use App\Models\CalendarEvent;
use App\Models\Candidate;
use App\Models\Contract;
use App\Models\Holiday;
use App\Models\HrDocument;
use App\Models\HrSetting;
use App\Models\Interview;
use App\Models\KanbanColumn;
use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Mission;
use App\Models\OnboardingTemplate;
use App\Models\Personnel;
use App\Models\Requisition;
use App\Models\Shift;
use App\Models\Sprint;
use App\Models\Worklog;
use App\Policies\BudgetPolicy;
use App\Policies\CostCenterPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\KeyResultPolicy;
use App\Policies\ObjectivePolicy;
use App\Policies\PayrollPolicy;
use App\Policies\PerformanceIndicatorPolicy;
use App\Policies\PerformanceScorePolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\PurchaseRequestPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\VendorPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\CalendarEventPolicy;
use App\Policies\ContractPolicy;
use App\Policies\HolidayPolicy;
use App\Policies\HrDocumentPolicy;
use App\Policies\HrSettingsPolicy;
use App\Policies\KanbanColumnPolicy;
use App\Policies\LeavePolicyPolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\LeaveTypePolicy;
use App\Policies\MissionPolicy;
use App\Policies\OnboardingPolicy;
use App\Policies\PersonnelPolicy;
use App\Policies\RecruitmentPolicy;
use App\Policies\ShiftPolicy;
use App\Policies\SprintPolicy;
use App\Policies\WorklogPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Sprint::class, SprintPolicy::class);
        Gate::policy(KanbanColumn::class, KanbanColumnPolicy::class);
        Gate::policy(Worklog::class, WorklogPolicy::class);
        Gate::policy(CalendarEvent::class, CalendarEventPolicy::class);
        Gate::policy(Holiday::class, HolidayPolicy::class);
        Gate::policy(Shift::class, ShiftPolicy::class);
        Gate::policy(LeaveType::class, LeaveTypePolicy::class);
        Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
        Gate::policy(LeavePolicy::class, LeavePolicyPolicy::class);
        Gate::policy(Personnel::class, PersonnelPolicy::class);
        Gate::policy(AttendanceRecord::class, AttendancePolicy::class);
        Gate::policy(Contract::class, ContractPolicy::class);
        Gate::policy(HrDocument::class, HrDocumentPolicy::class);
        Gate::policy(Mission::class, MissionPolicy::class);
        Gate::policy(Requisition::class, RecruitmentPolicy::class);
        Gate::policy(Candidate::class, RecruitmentPolicy::class);
        Gate::policy(Interview::class, RecruitmentPolicy::class);
        Gate::policy(OnboardingTemplate::class, OnboardingPolicy::class);
        Gate::policy(HrSetting::class, HrSettingsPolicy::class);

        Gate::policy(CostCenter::class, CostCenterPolicy::class);
        Gate::policy(Budget::class, BudgetPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(PurchaseRequest::class, PurchaseRequestPolicy::class);
        Gate::policy(PurchaseOrder::class, PurchaseOrderPolicy::class);
        Gate::policy(Vendor::class, VendorPolicy::class);
        Gate::policy(PayrollRecord::class, PayrollPolicy::class);
        Gate::policy(PerformanceIndicator::class, PerformanceIndicatorPolicy::class);
        Gate::policy(PerformanceScore::class, PerformanceScorePolicy::class);
        Gate::policy(Objective::class, ObjectivePolicy::class);
        Gate::policy(KeyResult::class, KeyResultPolicy::class);
        Gate::policy(PerformanceReview::class, ReviewPolicy::class);
    }
}
