<?php

use App\Http\Controllers\Api\V1\MemberController;
use App\Http\Controllers\Api\V1\ModuleDashboardController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SiteCompatController;
use App\Http\Controllers\Api\V1\SuperAdmin\SettingsController as SuperAdminSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| midesk-site API compatibility routes
|--------------------------------------------------------------------------
| Aliases and endpoints required by the Nuxt frontend mock API contract.
| Base prefix: /api/v1 (applied by parent route group).
*/

Route::middleware(['auth:sanctum', 'tenant', 'locale'])->group(function (): void {
    Route::get('members', [MemberController::class, 'index']);

    Route::prefix('roles')->group(function (): void {
        Route::get('permissions', [RoleController::class, 'permissions']);
        Route::get('custom', [RoleController::class, 'indexCustom']);
        Route::post('custom', [RoleController::class, 'storeCustom']);
        Route::patch('custom/{customRole}', [RoleController::class, 'updateCustom']);
        Route::delete('custom/{customRole}', [RoleController::class, 'destroyCustom']);
    });

    Route::get('hr/dashboard', [ModuleDashboardController::class, 'hr']);
    Route::get('finance/dashboard', [ModuleDashboardController::class, 'finance']);
    Route::get('crm/dashboard', [ModuleDashboardController::class, 'crm']);
    Route::get('performance/dashboard', [ModuleDashboardController::class, 'performance']);

    Route::get('tasks/labels/all', [SiteCompatController::class, 'labelsAll']);
    Route::get('worklogs/team', [SiteCompatController::class, 'teamWorklogs']);
    Route::get('leave/requests/pending', [SiteCompatController::class, 'pendingLeaveRequests']);
    Route::patch('leave/requests/{leaveRequest}/approve', [SiteCompatController::class, 'approveLeavePatch']);
    Route::patch('leave/requests/{leaveRequest}/reject', [SiteCompatController::class, 'rejectLeavePatch']);

    Route::post('hr/attendance/break-start', [SiteCompatController::class, 'breakStart']);
    Route::post('hr/attendance/break-end', [SiteCompatController::class, 'breakEnd']);
    Route::patch('hr/missions/{mission}/approve', [SiteCompatController::class, 'approveMissionPatch']);
    Route::patch('hr/missions/{mission}/reject', [SiteCompatController::class, 'rejectMissionPatch']);
    Route::patch('hr/onboarding/progress/{progress}/complete-step', [SiteCompatController::class, 'completeOnboardingStep']);

    Route::patch('projects/{project}/sprints/{sprint}/activate', [SiteCompatController::class, 'activateSprint']);
    Route::patch('projects/{project}/sprints/{sprint}/close', [SiteCompatController::class, 'closeSprint']);
    Route::patch('projects/{project}/columns/reorder', [SiteCompatController::class, 'reorderProjectColumns']);
    Route::delete('projects/{project}/columns/{column}', [SiteCompatController::class, 'deleteKanbanColumn']);
    Route::delete('projects/{project}/files/{file}', [SiteCompatController::class, 'deleteProjectFile']);
    Route::post('projects/{project}/files/{file}/new-version', [SiteCompatController::class, 'newProjectFileVersion']);

    Route::post('guests', [SiteCompatController::class, 'storeGuest']);
    Route::patch('crm/opportunities/{opportunity}/stage', [SiteCompatController::class, 'updateOpportunityStage']);
    Route::post('correspondences/{correspondence}/routing/{correspondenceRouting}/complete', [SiteCompatController::class, 'completeCorrespondenceRouting']);
    Route::patch('notification-preferences/{eventType}', [SiteCompatController::class, 'updateNotificationPreference']);

    Route::post('subscription/verify', [SiteCompatController::class, 'verifySubscriptionPost']);
    Route::post('finance/purchase-requests/{purchaseRequest}/convert-to-po', [SiteCompatController::class, 'convertPurchaseRequestToPo']);

    Route::get('chat/channels/unread', [SiteCompatController::class, 'chatUnreadCount']);
    Route::post('ai/requests/{aiRequest}/complete', [SiteCompatController::class, 'completeAiRequest']);
    Route::post('broadcasting/auth', [SiteCompatController::class, 'broadcastingAuth']);
});

Route::middleware(['auth:sanctum', 'super-admin'])->prefix('super-admin')->group(function (): void {
    Route::get('financial', [SiteCompatController::class, 'superAdminFinancialSummary']);
    Route::get('settings', [SuperAdminSettingsController::class, 'show']);
    Route::patch('settings', [SuperAdminSettingsController::class, 'update']);
});
