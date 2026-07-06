<?php

use App\Http\Controllers\Api\V1\AgileReportController;
use App\Http\Controllers\Api\V1\BacklogController;
use App\Http\Controllers\Api\V1\CalendarController;
use App\Http\Controllers\Api\V1\Finance\BudgetController;
use App\Http\Controllers\Api\V1\Finance\CostCenterController;
use App\Http\Controllers\Api\V1\Finance\ExpenseController;
use App\Http\Controllers\Api\V1\Finance\InvoiceController;
use App\Http\Controllers\Api\V1\Finance\PayrollController;
use App\Http\Controllers\Api\V1\Finance\PurchaseOrderController;
use App\Http\Controllers\Api\V1\Finance\PurchaseRequestController;
use App\Http\Controllers\Api\V1\Finance\VendorController;
use App\Http\Controllers\Api\V1\Hr\AttendanceController;
use App\Http\Controllers\Api\V1\Hr\CandidateController;
use App\Http\Controllers\Api\V1\Hr\ContactController;
use App\Http\Controllers\Api\V1\Hr\ContractController;
use App\Http\Controllers\Api\V1\Hr\DocumentController;
use App\Http\Controllers\Api\V1\Hr\HrSettingsController;
use App\Http\Controllers\Api\V1\Hr\InterviewController;
use App\Http\Controllers\Api\V1\Hr\MissionController;
use App\Http\Controllers\Api\V1\Hr\OnboardingController;
use App\Http\Controllers\Api\V1\Hr\PersonnelController;
use App\Http\Controllers\Api\V1\Hr\RequisitionController;
use App\Http\Controllers\Api\V1\KanbanColumnController;
use App\Http\Controllers\Api\V1\KeyResultController;
use App\Http\Controllers\Api\V1\LeaveController;
use App\Http\Controllers\Api\V1\LeavePolicyController;
use App\Http\Controllers\Api\V1\LeaveReportController;
use App\Http\Controllers\Api\V1\MyFinanceController;
use App\Http\Controllers\Api\V1\ObjectiveController;
use App\Http\Controllers\Api\V1\Performance\IndicatorController;
use App\Http\Controllers\Api\V1\Performance\ScoreController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\SprintController;
use App\Http\Controllers\Api\V1\WorklogController;
use Illuminate\Support\Facades\Route;

// Phase 1: Sprints & Backlog
Route::get('projects/{project}/sprints', [SprintController::class, 'index']);
Route::post('projects/{project}/sprints', [SprintController::class, 'store']);
Route::get('projects/{project}/backlog', [BacklogController::class, 'index']);
Route::get('sprints/{sprint}', [SprintController::class, 'show']);
Route::patch('sprints/{sprint}', [SprintController::class, 'update']);
Route::post('sprints/{sprint}/activate', [SprintController::class, 'activate']);
Route::post('sprints/{sprint}/close', [SprintController::class, 'close']);
Route::delete('sprints/{sprint}', [SprintController::class, 'destroy']);
Route::post('sprints/{sprint}/tasks', [SprintController::class, 'addTask']);
Route::delete('sprints/{sprint}/tasks/{task}', [SprintController::class, 'removeTask']);

// Phase 1: Kanban columns
Route::get('projects/{project}/columns', [KanbanColumnController::class, 'index']);
Route::post('projects/{project}/columns', [KanbanColumnController::class, 'store']);
Route::patch('columns/reorder', [KanbanColumnController::class, 'reorder']);
Route::patch('columns/{column}', [KanbanColumnController::class, 'update']);
Route::delete('columns/{column}', [KanbanColumnController::class, 'destroy']);

// Phase 1: Agile reports
Route::get('sprints/{sprint}/report/burndown', [AgileReportController::class, 'burndown']);
Route::get('sprints/{sprint}/report/burnup', [AgileReportController::class, 'burnup']);
Route::get('projects/{project}/report/velocity', [AgileReportController::class, 'velocity']);
Route::get('projects/{project}/report/cfd', [AgileReportController::class, 'cfd']);

// Phase 1: Worklogs
Route::get('worklogs/my', [WorklogController::class, 'my']);
Route::get('worklogs/reports', [WorklogController::class, 'reports']);
Route::get('worklogs', [WorklogController::class, 'index']);
Route::post('worklogs', [WorklogController::class, 'store']);
Route::patch('worklogs/{worklog}', [WorklogController::class, 'update']);
Route::delete('worklogs/{worklog}', [WorklogController::class, 'destroy']);

// Phase 1: Calendar
Route::prefix('calendar')->group(function (): void {
    Route::get('events', [CalendarController::class, 'events']);
    Route::post('events', [CalendarController::class, 'storeEvent']);
    Route::patch('events/{event}', [CalendarController::class, 'updateEvent']);
    Route::delete('events/{event}', [CalendarController::class, 'destroyEvent']);
    Route::get('holidays', [CalendarController::class, 'holidays']);
    Route::post('holidays', [CalendarController::class, 'storeHoliday']);
    Route::patch('holidays/{holiday}', [CalendarController::class, 'updateHoliday']);
    Route::delete('holidays/{holiday}', [CalendarController::class, 'destroyHoliday']);
    Route::get('shifts', [CalendarController::class, 'shifts']);
    Route::post('shifts', [CalendarController::class, 'storeShift']);
    Route::patch('shifts/{shift}', [CalendarController::class, 'updateShift']);
    Route::delete('shifts/{shift}', [CalendarController::class, 'destroyShift']);
});

// Phase 1: Leave
Route::prefix('leave')->group(function (): void {
    Route::get('types', [LeaveController::class, 'types']);
    Route::post('types', [LeaveController::class, 'storeType']);
    Route::patch('types/{leaveType}', [LeaveController::class, 'updateType']);
    Route::delete('types/{leaveType}', [LeaveController::class, 'destroyType']);
    Route::get('balance', [LeaveController::class, 'myBalance']);
    Route::get('balance/{user}', [LeaveController::class, 'userBalance']);
    Route::get('requests/my', [LeaveController::class, 'myRequests']);
    Route::get('requests', [LeaveController::class, 'requests']);
    Route::post('requests', [LeaveController::class, 'storeRequest']);
    Route::post('requests/{leaveRequest}/approve', [LeaveController::class, 'approve']);
    Route::post('requests/{leaveRequest}/reject', [LeaveController::class, 'reject']);
    Route::post('requests/{leaveRequest}/cancel', [LeaveController::class, 'cancel']);
    Route::get('policies', [LeavePolicyController::class, 'index']);
    Route::post('policies', [LeavePolicyController::class, 'store']);
    Route::patch('policies/{policy}', [LeavePolicyController::class, 'update']);
    Route::delete('policies/{policy}', [LeavePolicyController::class, 'destroy']);
    Route::get('report', [LeaveReportController::class, 'index']);
});

// Phase 2: HR
Route::prefix('hr')->group(function (): void {
    Route::get('personnel', [PersonnelController::class, 'index']);
    Route::post('personnel', [PersonnelController::class, 'store']);
    Route::get('personnel/{personnel}', [PersonnelController::class, 'show']);
    Route::patch('personnel/{personnel}', [PersonnelController::class, 'update']);
    Route::delete('personnel/{personnel}', [PersonnelController::class, 'destroy']);
    Route::get('contacts', [ContactController::class, 'index']);

    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);
    Route::post('attendance/break/start', [AttendanceController::class, 'startBreak']);
    Route::post('attendance/break/end', [AttendanceController::class, 'endBreak']);
    Route::get('attendance/my', [AttendanceController::class, 'my']);
    Route::get('attendance/today', [AttendanceController::class, 'today']);
    Route::get('attendance/report', [AttendanceController::class, 'report']);
    Route::patch('attendance/{attendance}', [AttendanceController::class, 'update']);

    Route::get('contracts', [ContractController::class, 'index']);
    Route::post('contracts', [ContractController::class, 'store']);
    Route::patch('contracts/{contract}', [ContractController::class, 'update']);
    Route::post('contracts/{contract}/renew', [ContractController::class, 'renew']);
    Route::get('contracts/expiring', [ContractController::class, 'expiring']);

    Route::get('documents', [DocumentController::class, 'index']);
    Route::post('documents', [DocumentController::class, 'store']);
    Route::get('documents/{document}/download', [DocumentController::class, 'download']);
    Route::delete('documents/{document}', [DocumentController::class, 'destroy']);

    Route::get('missions', [MissionController::class, 'index']);
    Route::get('missions/my', [MissionController::class, 'my']);
    Route::post('missions', [MissionController::class, 'store']);
    Route::post('missions/{mission}/approve', [MissionController::class, 'approve']);
    Route::post('missions/{mission}/reject', [MissionController::class, 'reject']);
    Route::post('missions/{mission}/complete', [MissionController::class, 'complete']);

    Route::get('requisitions', [RequisitionController::class, 'index']);
    Route::post('requisitions', [RequisitionController::class, 'store']);
    Route::get('requisitions/{requisition}', [RequisitionController::class, 'show']);
    Route::patch('requisitions/{requisition}', [RequisitionController::class, 'update']);

    Route::get('candidates', [CandidateController::class, 'index']);
    Route::post('candidates', [CandidateController::class, 'store']);
    Route::get('candidates/{candidate}', [CandidateController::class, 'show']);
    Route::patch('candidates/{candidate}', [CandidateController::class, 'update']);
    Route::post('candidates/{candidate}/hire', [CandidateController::class, 'hire']);
    Route::get('candidates/{candidate}/resume', [CandidateController::class, 'resume']);

    Route::get('interviews', [InterviewController::class, 'index']);
    Route::post('interviews', [InterviewController::class, 'store']);
    Route::patch('interviews/{interview}', [InterviewController::class, 'update']);

    Route::prefix('onboarding')->group(function (): void {
        Route::get('templates/default', [OnboardingController::class, 'defaultTemplate']);
        Route::get('templates', [OnboardingController::class, 'listTemplates']);
        Route::post('templates', [OnboardingController::class, 'storeTemplate']);
        Route::patch('templates/{template}', [OnboardingController::class, 'updateTemplate']);
        Route::get('progress', [OnboardingController::class, 'listProgress']);
        Route::get('progress/{user}', [OnboardingController::class, 'showProgress']);
        Route::post('progress/{user}/start', [OnboardingController::class, 'startProgress']);
        Route::post('progress/{user}/complete-step', [OnboardingController::class, 'completeStep']);
    });

    Route::get('settings/currencies', [HrSettingsController::class, 'currencies']);
    Route::get('settings', [HrSettingsController::class, 'show']);
    Route::patch('settings', [HrSettingsController::class, 'update']);
});

// Phase 3: Finance
Route::prefix('finance')->group(function (): void {
    Route::get('cost-centers', [CostCenterController::class, 'index']);
    Route::post('cost-centers', [CostCenterController::class, 'store']);
    Route::get('cost-centers/{costCenter}', [CostCenterController::class, 'show']);
    Route::patch('cost-centers/{costCenter}', [CostCenterController::class, 'update']);
    Route::delete('cost-centers/{costCenter}', [CostCenterController::class, 'destroy']);

    Route::get('budgets/overview', [BudgetController::class, 'overview']);
    Route::get('budgets', [BudgetController::class, 'index']);
    Route::post('budgets', [BudgetController::class, 'store']);
    Route::get('budgets/{budget}', [BudgetController::class, 'show']);
    Route::patch('budgets/{budget}', [BudgetController::class, 'update']);

    Route::get('expenses/categories', [ExpenseController::class, 'categories']);
    Route::post('expenses/categories', [ExpenseController::class, 'storeCategory']);
    Route::patch('expenses/categories/{expenseCategory}', [ExpenseController::class, 'updateCategory']);
    Route::delete('expenses/categories/{expenseCategory}', [ExpenseController::class, 'destroyCategory']);
    Route::get('expenses/summary', [ExpenseController::class, 'summary']);
    Route::get('expenses/my', [ExpenseController::class, 'my']);
    Route::get('expenses', [ExpenseController::class, 'index']);
    Route::post('expenses', [ExpenseController::class, 'store']);
    Route::get('expenses/{expense}', [ExpenseController::class, 'show']);
    Route::patch('expenses/{expense}', [ExpenseController::class, 'update']);
    Route::post('expenses/{expense}/submit', [ExpenseController::class, 'submit']);
    Route::post('expenses/{expense}/approve', [ExpenseController::class, 'approve']);
    Route::post('expenses/{expense}/reject', [ExpenseController::class, 'reject']);
    Route::post('expenses/{expense}/pay', [ExpenseController::class, 'pay']);

    Route::get('invoices', [InvoiceController::class, 'index']);
    Route::post('invoices', [InvoiceController::class, 'store']);
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::patch('invoices/{invoice}', [InvoiceController::class, 'update']);
    Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'pay']);

    Route::get('purchase-requests', [PurchaseRequestController::class, 'index']);
    Route::post('purchase-requests', [PurchaseRequestController::class, 'store']);
    Route::get('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'show']);
    Route::patch('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'update']);
    Route::post('purchase-requests/{purchaseRequest}/submit', [PurchaseRequestController::class, 'submit']);
    Route::post('purchase-requests/{purchaseRequest}/owner-review', [PurchaseRequestController::class, 'ownerReview']);
    Route::post('purchase-requests/{purchaseRequest}/finance-review', [PurchaseRequestController::class, 'financeReview']);
    Route::post('purchase-requests/{purchaseRequest}/cancel', [PurchaseRequestController::class, 'cancel']);

    Route::get('purchase-orders', [PurchaseOrderController::class, 'index']);
    Route::post('purchase-orders', [PurchaseOrderController::class, 'store']);
    Route::post('purchase-orders/from-request/{purchaseRequest}', [PurchaseOrderController::class, 'createFromRequest']);
    Route::patch('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update']);

    Route::get('vendors', [VendorController::class, 'index']);
    Route::post('vendors', [VendorController::class, 'store']);
    Route::get('vendors/{vendor}', [VendorController::class, 'show']);
    Route::patch('vendors/{vendor}', [VendorController::class, 'update']);
    Route::delete('vendors/{vendor}', [VendorController::class, 'destroy']);

    Route::get('payroll', [PayrollController::class, 'index']);
    Route::post('payroll/calculate', [PayrollController::class, 'calculate']);
    Route::post('payroll/bulk-pay', [PayrollController::class, 'bulkPay']);
    Route::get('payroll/{payrollRecord}', [PayrollController::class, 'show']);
    Route::post('payroll/{payrollRecord}/approve', [PayrollController::class, 'approve']);
});

Route::prefix('my-finance')->group(function (): void {
    Route::get('payslips', [MyFinanceController::class, 'payslips']);
    Route::get('payslips/{id}', [MyFinanceController::class, 'showPayslip']);
    Route::get('expenses', [MyFinanceController::class, 'expenses']);
    Route::get('receipts', [MyFinanceController::class, 'receipts']);
});

// Phase 3: Performance
Route::prefix('performance')->group(function (): void {
    Route::get('indicators', [IndicatorController::class, 'index']);
    Route::post('indicators', [IndicatorController::class, 'store']);
    Route::patch('indicators/{indicator}', [IndicatorController::class, 'update']);
    Route::delete('indicators/{indicator}', [IndicatorController::class, 'destroy']);
    Route::get('scores-summary', [ScoreController::class, 'scoresSummary']);
    Route::get('work-summary', [ScoreController::class, 'workSummary']);
    Route::get('scores', [ScoreController::class, 'index']);
    Route::post('scores', [ScoreController::class, 'store']);
});

Route::get('objectives', [ObjectiveController::class, 'index']);
Route::post('objectives', [ObjectiveController::class, 'store']);
Route::get('objectives/{objective}', [ObjectiveController::class, 'show']);
Route::patch('objectives/{objective}', [ObjectiveController::class, 'update']);
Route::delete('objectives/{objective}', [ObjectiveController::class, 'destroy']);
Route::post('objectives/{objective}/key-results', [ObjectiveController::class, 'storeKeyResult']);

Route::patch('key-results/{keyResult}', [KeyResultController::class, 'update']);
Route::patch('key-results/{keyResult}/progress', [KeyResultController::class, 'progress']);

Route::get('reviews', [ReviewController::class, 'index']);
Route::post('reviews', [ReviewController::class, 'store']);
Route::get('reviews/{review}', [ReviewController::class, 'show']);
Route::patch('reviews/{review}', [ReviewController::class, 'update']);
Route::post('reviews/{review}/submit-self', [ReviewController::class, 'submitSelf']);
Route::post('reviews/{review}/submit-manager', [ReviewController::class, 'submitManager']);
Route::post('reviews/{review}/finalize', [ReviewController::class, 'finalize']);
