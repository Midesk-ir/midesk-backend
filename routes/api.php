<?php

use App\Http\Controllers\Api\V1\AiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\CoreController;
use App\Http\Controllers\Api\V1\CorrespondenceController;
use App\Http\Controllers\Api\V1\CrmController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\GuestController;
use App\Http\Controllers\Api\V1\LabelController;
use App\Http\Controllers\Api\V1\Hr\DocumentController as HrDocumentController;
use App\Http\Controllers\Api\V1\MeetingController;
use App\Http\Controllers\Api\V1\NoteController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ProjectFileController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\SurveyController;
use App\Http\Controllers\Api\V1\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\Api\V1\SuperAdmin\ConsultationController as SuperAdminConsultationController;
use App\Http\Controllers\Api\V1\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\Api\V1\SuperAdmin\FinancialController as SuperAdminFinancialController;
use App\Http\Controllers\Api\V1\SuperAdmin\PlanController as SuperAdminPlanController;
use App\Http\Controllers\Api\V1\SuperAdmin\TenantController as SuperAdminTenantController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Models\Membership;
use Illuminate\Support\Facades\Route;

Route::get('v1/hr/documents/{document}/serve', [HrDocumentController::class, 'serve'])
    ->name('hr.documents.serve');

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::post('login/otp', [AuthController::class, 'loginOtp'])->middleware('throttle:5,1');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
        Route::get('google/redirect', [AuthController::class, 'googleRedirect']);
        Route::get('google/callback', [AuthController::class, 'googleCallback']);

        Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
    });

    Route::post('super-admin/login', [SuperAdminAuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('super-admin/auth/login', [SuperAdminAuthController::class, 'login'])->middleware('throttle:5,1');

    Route::prefix('guest')->middleware('guest.token')->group(function (): void {
        Route::get('projects', [GuestController::class, 'guestProjects']);
        Route::get('projects/{id}', [GuestController::class, 'guestProject']);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('orgs', [OrganizationController::class, 'store']);
    });

    Route::middleware(['auth:sanctum', 'tenant', 'plan.access', 'locale'])->group(function (): void {
        Route::prefix('core')->group(function (): void {
            Route::get('bootstrap', [CoreController::class, 'bootstrap']);
            Route::get('me', [CoreController::class, 'me']);
            Route::patch('me', [CoreController::class, 'updateMe']);
        });

        Route::prefix('orgs')->group(function (): void {
            Route::get('current', [OrganizationController::class, 'current']);
            Route::patch('current', [OrganizationController::class, 'updateCurrent']);
            Route::get('current/members', [OrganizationController::class, 'members']);
            Route::post('current/members/invite', [OrganizationController::class, 'invite']);
            Route::patch('current/members/{member}', [OrganizationController::class, 'updateMember']);
            Route::delete('current/members/{member}', [OrganizationController::class, 'destroyMember']);
        });

        Route::apiResource('teams', TeamController::class);
        Route::post('teams/{team}/members', [TeamController::class, 'addMember']);
        Route::delete('teams/{team}/members', [TeamController::class, 'removeMember']);

        Route::apiResource('projects', ProjectController::class);
        Route::get('projects/{project}/members', [ProjectController::class, 'members']);
        Route::post('projects/{project}/members', [ProjectController::class, 'addMember']);
        Route::delete('projects/{project}/members', [ProjectController::class, 'removeMember']);
        Route::get('projects/{project}/tasks', [TaskController::class, 'index']);
        Route::get('projects/{project}/board', [TaskController::class, 'board']);
        Route::get('projects/{project}/files', [ProjectFileController::class, 'index']);
        Route::post('projects/{project}/files', [ProjectFileController::class, 'store']);

        Route::post('tasks', [TaskController::class, 'store']);
        Route::get('tasks/{task}', [TaskController::class, 'show']);
        Route::patch('tasks/{task}', [TaskController::class, 'update']);
        Route::patch('tasks/{task}/move', [TaskController::class, 'move']);
        Route::patch('tasks/{task}/sprint', [TaskController::class, 'updateSprint']);
        Route::delete('tasks/{task}', [TaskController::class, 'destroy']);
        Route::post('tasks/{task}/checklist', [TaskController::class, 'storeChecklist']);
        Route::patch('tasks/{task}/checklist', [TaskController::class, 'updateChecklist']);
        Route::delete('tasks/{task}/checklist', [TaskController::class, 'destroyChecklist']);
        Route::get('tasks/{task}/comments', [TaskController::class, 'comments']);
        Route::post('tasks/{task}/comments', [TaskController::class, 'storeComment']);
        Route::post('tasks/{task}/labels', [TaskController::class, 'attachLabel']);
        Route::delete('tasks/{task}/labels', [TaskController::class, 'detachLabel']);

        Route::get('labels', [LabelController::class, 'index']);
        Route::post('labels', [LabelController::class, 'store']);

        Route::get('dashboard/summary', [DashboardController::class, 'summary']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/count', [NotificationController::class, 'count']);
        Route::post('notifications/read-all', [NotificationController::class, 'readAll']);
        Route::patch('notifications/{id}/read', [NotificationController::class, 'markRead']);

        require __DIR__.'/api_phase123.php';
        require __DIR__.'/api_site_compat.php';

        // Phase 4: Chat
        Route::prefix('chat')->group(function (): void {
            Route::get('channels', [ChatController::class, 'channels']);
            Route::post('channels', [ChatController::class, 'storeChannel']);
            Route::post('dm', [ChatController::class, 'dm']);
            Route::get('unread-count', [ChatController::class, 'unreadCount']);
            Route::get('channels/{channel}', [ChatController::class, 'showChannel']);
            Route::patch('channels/{channel}', [ChatController::class, 'updateChannel']);
            Route::post('channels/{channel}/archive', [ChatController::class, 'archiveChannel']);
            Route::get('channels/{channel}/members', [ChatController::class, 'members']);
            Route::post('channels/{channel}/members', [ChatController::class, 'addMember']);
            Route::delete('channels/{channel}/members/{userId}', [ChatController::class, 'removeMember']);
            Route::post('channels/{channel}/mute', [ChatController::class, 'mute']);
            Route::get('channels/{channel}/messages', [ChatController::class, 'messages']);
            Route::post('channels/{channel}/messages', [ChatController::class, 'sendMessage']);
            Route::post('channels/{channel}/read', [ChatController::class, 'markRead']);
            Route::post('messages/{message}/file', [ChatController::class, 'uploadFile']);
            Route::patch('messages/{message}', [ChatController::class, 'updateMessage']);
            Route::delete('messages/{message}', [ChatController::class, 'deleteMessage']);
            Route::post('messages/{message}/reactions', [ChatController::class, 'react']);
        });

        // Phase 4: Meetings
        Route::get('meetings/today', [MeetingController::class, 'today']);
        Route::get('meetings/my-attendance', [MeetingController::class, 'myAttendance']);
        Route::apiResource('meetings', MeetingController::class)->except(['create', 'edit']);
        Route::post('meetings/{meeting}/start', [MeetingController::class, 'start']);
        Route::post('meetings/{meeting}/join', [MeetingController::class, 'join']);
        Route::post('meetings/{meeting}/end', [MeetingController::class, 'end']);
        Route::post('meetings/{meeting}/accept', [MeetingController::class, 'accept']);
        Route::post('meetings/{meeting}/decline', [MeetingController::class, 'decline']);

        // Phase 4: AI
        Route::prefix('ai')->middleware('throttle:10,60')->group(function (): void {
            Route::post('analyze-tasks', [AiController::class, 'analyzeTasks']);
            Route::post('generate-document', [AiController::class, 'generateDocument']);
            Route::post('screen-resume', [AiController::class, 'screenResume']);
            Route::post('analyze-performance', [AiController::class, 'analyzePerformance']);
            Route::post('query', [AiController::class, 'query']);
            Route::get('requests', [AiController::class, 'index']);
            Route::get('requests/{aiRequest}', [AiController::class, 'show']);
        });

        // Phase 4: Notes
        Route::get('notes/my', [NoteController::class, 'my']);
        Route::post('notes/{note}/pin', [NoteController::class, 'pin']);
        Route::apiResource('notes', NoteController::class)->except(['create', 'edit']);

        // Phase 4: Notification preferences
        Route::get('notification-preferences/events', [NotificationPreferenceController::class, 'events']);
        Route::get('notification-preferences', [NotificationPreferenceController::class, 'index']);
        Route::patch('notification-preferences', [NotificationPreferenceController::class, 'update']);

        // Phase 5: Correspondence
        Route::get('correspondences/inbox', [CorrespondenceController::class, 'inbox']);
        Route::get('correspondences/next-number', [CorrespondenceController::class, 'nextNumber']);
        Route::post('correspondences/{correspondence}/send', [CorrespondenceController::class, 'send']);
        Route::post('correspondences/{correspondence}/sign', [CorrespondenceController::class, 'sign']);
        Route::post('correspondences/{correspondence}/route', [CorrespondenceController::class, 'route']);
        Route::post('correspondence-routing/{correspondenceRouting}/complete', [CorrespondenceController::class, 'completeRouting']);
        Route::apiResource('correspondences', CorrespondenceController::class)->except(['create', 'edit']);

        // Phase 5: Surveys
        Route::post('surveys/{survey}/publish', [SurveyController::class, 'publish']);
        Route::post('surveys/{survey}/close', [SurveyController::class, 'close']);
        Route::post('surveys/{survey}/questions', [SurveyController::class, 'addQuestion']);
        Route::post('surveys/{survey}/respond', [SurveyController::class, 'respond']);
        Route::get('surveys/{survey}/responses', [SurveyController::class, 'responses']);
        Route::get('surveys/{survey}/report', [SurveyController::class, 'report']);
        Route::apiResource('surveys', SurveyController::class)->except(['create', 'edit']);
        Route::patch('survey-questions/{surveyQuestion}', [SurveyController::class, 'updateQuestion']);
        Route::delete('survey-questions/{surveyQuestion}', [SurveyController::class, 'deleteQuestion']);

        // Phase 5: CRM
        Route::prefix('crm')->group(function (): void {
            Route::get('contacts', [CrmController::class, 'contacts']);
            Route::post('contacts', [CrmController::class, 'storeContact']);
            Route::get('contacts/{contact}', [CrmController::class, 'showContact']);
            Route::patch('contacts/{contact}', [CrmController::class, 'updateContact']);
            Route::delete('contacts/{contact}', [CrmController::class, 'destroyContact']);
            Route::get('opportunities', [CrmController::class, 'opportunities']);
            Route::post('opportunities', [CrmController::class, 'storeOpportunity']);
            Route::get('opportunities/{opportunity}', [CrmController::class, 'showOpportunity']);
            Route::patch('opportunities/{opportunity}', [CrmController::class, 'updateOpportunity']);
            Route::post('opportunities/{opportunity}/move', [CrmController::class, 'moveOpportunity']);
            Route::get('activities', [CrmController::class, 'activities']);
            Route::post('activities', [CrmController::class, 'storeActivity']);
            Route::get('pipeline', [CrmController::class, 'pipeline']);
            Route::get('report', [CrmController::class, 'report']);
        });

        // Phase 5: Reports
        Route::get('reports/data-sources', [ReportController::class, 'dataSources']);
        Route::post('reports/preview', [ReportController::class, 'preview']);
        Route::get('reports/{report}/export', [ReportController::class, 'export']);
        Route::post('reports/{report}/schedule', [ReportController::class, 'schedule']);
        Route::apiResource('reports', ReportController::class)->except(['create', 'edit']);

        // Phase 5: Files
        Route::get('files/{file}/download', [ProjectFileController::class, 'download']);
        Route::delete('files/{file}', [ProjectFileController::class, 'destroy']);
        Route::post('files/{file}/new-version', [ProjectFileController::class, 'newVersion']);

        // Phase 5: Guests
        Route::get('guests', [GuestController::class, 'index']);
        Route::post('guests/invite', [GuestController::class, 'invite']);
        Route::delete('guests/{guest}', [GuestController::class, 'destroy']);

        // Phase 5: Subscription
        Route::prefix('subscription')->group(function (): void {
            Route::get('/', [SubscriptionController::class, 'current']);
            Route::get('plans', [SubscriptionController::class, 'plans']);
            Route::post('change-request', [SubscriptionController::class, 'changeRequest']);
            Route::get('invoices', [SubscriptionController::class, 'invoices']);
            Route::post('pay', [SubscriptionController::class, 'pay']);
            Route::get('verify', [SubscriptionController::class, 'verify']);
        });
    });

    Route::middleware(['auth:sanctum', 'super-admin'])->prefix('super-admin')->group(function (): void {
        Route::get('dashboard', [SuperAdminDashboardController::class, 'index']);
        Route::get('tenants', [SuperAdminTenantController::class, 'index']);
        Route::post('tenants', [SuperAdminTenantController::class, 'store']);
        Route::get('tenants/{tenant}', [SuperAdminTenantController::class, 'show']);
        Route::patch('tenants/{tenant}', [SuperAdminTenantController::class, 'update']);
        Route::post('tenants/{tenant}/suspend', [SuperAdminTenantController::class, 'suspend']);
        Route::get('plans', [SuperAdminPlanController::class, 'index']);
        Route::post('plans', [SuperAdminPlanController::class, 'store']);
        Route::patch('plans/{plan}', [SuperAdminPlanController::class, 'update']);
        Route::delete('plans/{plan}', [SuperAdminPlanController::class, 'destroy']);
        Route::get('financial/overview', [SuperAdminFinancialController::class, 'overview']);
        Route::get('financial/revenue-by-plan', [SuperAdminFinancialController::class, 'revenueByPlan']);
        Route::get('financial/revenue-by-cycle', [SuperAdminFinancialController::class, 'revenueByCycle']);
        Route::get('financial/cancellations', [SuperAdminFinancialController::class, 'cancellations']);
        Route::post('financial/send-expiry-reminders', [SuperAdminFinancialController::class, 'sendExpiryReminders']);
        Route::get('consultations', [SuperAdminConsultationController::class, 'index']);
        Route::get('consultations/stats', [SuperAdminConsultationController::class, 'stats']);
        Route::patch('consultations/{consultation}', [SuperAdminConsultationController::class, 'update']);
    });
});

Route::bind('member', function (string $value): Membership {
    $tenantId = app()->bound('tenant') ? app('tenant')->id : request()->header('X-Tenant-Id');

    return Membership::query()
        ->where('tenant_id', $tenantId)
        ->findOrFail($value);
});

Route::bind('personnel', fn (string $value) => tenantScopedFind(\App\Models\Personnel::class, $value));
Route::bind('attendance', fn (string $value) => tenantScopedFind(\App\Models\AttendanceRecord::class, $value));
Route::bind('contract', fn (string $value) => tenantScopedFind(\App\Models\Contract::class, $value));
Route::bind('document', fn (string $value) => tenantScopedFind(\App\Models\HrDocument::class, $value));
Route::bind('mission', fn (string $value) => tenantScopedFind(\App\Models\Mission::class, $value));
Route::bind('requisition', fn (string $value) => tenantScopedFind(\App\Models\Requisition::class, $value));
Route::bind('candidate', fn (string $value) => tenantScopedFind(\App\Models\Candidate::class, $value));
Route::bind('interview', fn (string $value) => tenantScopedFind(\App\Models\Interview::class, $value));
Route::bind('template', fn (string $value) => tenantScopedFind(\App\Models\OnboardingTemplate::class, $value));
Route::bind('policy', fn (string $value) => tenantScopedFind(\App\Models\LeavePolicy::class, $value));
Route::bind('sprint', fn (string $value) => tenantScopedFind(\App\Models\Sprint::class, $value));
Route::bind('column', fn (string $value) => tenantScopedFind(\App\Models\KanbanColumn::class, $value));
Route::bind('worklog', fn (string $value) => tenantScopedFind(\App\Models\Worklog::class, $value));
Route::bind('event', fn (string $value) => tenantScopedFind(\App\Models\CalendarEvent::class, $value));
Route::bind('holiday', fn (string $value) => tenantScopedFind(\App\Models\Holiday::class, $value));
Route::bind('shift', fn (string $value) => tenantScopedFind(\App\Models\Shift::class, $value));
Route::bind('leaveType', fn (string $value) => tenantScopedFind(\App\Models\LeaveType::class, $value));
Route::bind('leaveRequest', fn (string $value) => tenantScopedFind(\App\Models\LeaveRequest::class, $value));
Route::bind('user', fn (string $value) => \App\Models\User::findOrFail($value));
Route::bind('costCenter', fn (string $value) => \App\Models\CostCenter::findOrFail($value));
Route::bind('budget', fn (string $value) => \App\Models\Budget::findOrFail($value));
Route::bind('expense', fn (string $value) => tenantScopedFind(\App\Models\Expense::class, $value));
Route::bind('expenseCategory', fn (string $value) => \App\Models\ExpenseCategory::findOrFail($value));
Route::bind('invoice', fn (string $value) => \App\Models\Invoice::findOrFail($value));
Route::bind('purchaseRequest', fn (string $value) => \App\Models\PurchaseRequest::findOrFail($value));
Route::bind('purchaseOrder', fn (string $value) => \App\Models\PurchaseOrder::findOrFail($value));
Route::bind('vendor', fn (string $value) => \App\Models\Vendor::findOrFail($value));
Route::bind('payrollRecord', fn (string $value) => \App\Models\PayrollRecord::findOrFail($value));
Route::bind('indicator', fn (string $value) => \App\Models\PerformanceIndicator::findOrFail($value));
Route::bind('objective', fn (string $value) => \App\Models\Objective::findOrFail($value));
Route::bind('keyResult', fn (string $value) => \App\Models\KeyResult::findOrFail($value));
Route::bind('review', fn (string $value) => \App\Models\PerformanceReview::findOrFail($value));
Route::bind('channel', fn (string $value) => \App\Models\ChatChannel::findOrFail($value));
Route::bind('message', fn (string $value) => \App\Models\ChatMessage::findOrFail($value));
Route::bind('aiRequest', fn (string $value) => \App\Models\AiRequest::findOrFail($value));
Route::bind('correspondenceRouting', fn (string $value) => \App\Models\CorrespondenceRouting::findOrFail($value));
Route::bind('surveyQuestion', fn (string $value) => \App\Models\SurveyQuestion::findOrFail($value));
Route::bind('file', fn (string $value) => \App\Models\ProjectFile::findOrFail($value));
Route::bind('guest', fn (string $value) => \App\Models\GuestAccess::findOrFail($value));
Route::bind('customRole', fn (string $value) => tenantScopedFind(\App\Models\CustomRole::class, $value));
Route::bind('progress', fn (string $value) => tenantScopedFind(\App\Models\OnboardingProgress::class, $value));
Route::bind('report', fn (string $value) => \App\Models\CustomReport::findOrFail($value));
Route::bind('consultation', fn (string $value) => \App\Models\Consultation::findOrFail($value));

/** @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
if (! function_exists('tenantScopedFind')) {
    function tenantScopedFind(string $modelClass, string $value): mixed
    {
        $query = $modelClass::query();

        if (app()->bound('tenant')) {
            $query->where($query->getModel()->getTable().'.tenant_id', app('tenant')->id);
        }

        return $query->findOrFail($value);
    }
}
