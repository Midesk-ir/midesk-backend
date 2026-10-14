<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sprint\CloseSprintRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\AiRequest;
use App\Models\CrmOpportunity;
use App\Models\Correspondence;
use App\Models\CorrespondenceRouting;
use App\Models\KanbanColumn;
use App\Models\LeaveRequest;
use App\Models\Mission;
use App\Models\OnboardingProgress;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\Sprint;
use App\Models\SubscriptionInvoice;
use App\Services\ChatService;
use App\Services\SiteCompatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Route aliases and response shims required by midesk-site (Nuxt mock API contract).
 */
class SiteCompatController extends Controller
{
    public function __construct(private readonly SiteCompatService $siteCompat) {}

    public function chatUnreadCount(Request $request): JsonResponse
    {
        $counts = app(ChatService::class)->unreadCounts($request->user());

        return response()->json(['count' => array_sum($counts)]);
    }

    public function labelsAll(): JsonResponse
    {
        return app(LabelController::class)->index();
    }

    public function teamWorklogs(Request $request): JsonResponse
    {
        return app(WorklogController::class)->index($request);
    }

    public function pendingLeaveRequests(): AnonymousResourceCollection
    {
        return LeaveRequestResource::collection($this->siteCompat->pendingLeaveRequests());
    }

    public function approveLeavePatch(LeaveRequest $leaveRequest): JsonResponse
    {
        return app(LeaveController::class)->approve($leaveRequest);
    }

    public function rejectLeavePatch(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        return app(LeaveController::class)->reject($request, $leaveRequest);
    }

    public function approveMissionPatch(Mission $mission): JsonResponse
    {
        return app(Hr\MissionController::class)->approve($mission);
    }

    public function rejectMissionPatch(Mission $mission): JsonResponse
    {
        return app(Hr\MissionController::class)->reject($mission);
    }

    public function breakStart(Request $request): JsonResponse
    {
        return app(Hr\AttendanceController::class)->startBreak($request);
    }

    public function breakEnd(Request $request): JsonResponse
    {
        return app(Hr\AttendanceController::class)->endBreak($request);
    }

    public function completeOnboardingStep(Request $request, OnboardingProgress $progress): JsonResponse
    {
        abort_unless($progress->user, 404);

        return app(Hr\OnboardingController::class)->completeStep($request, $progress->user);
    }

    public function activateSprint(Project $project, Sprint $sprint): JsonResponse
    {
        abort_unless($sprint->project_id === $project->id, 404);

        return app(SprintController::class)->activate($sprint);
    }

    public function closeSprint(CloseSprintRequest $request, Project $project, Sprint $sprint): JsonResponse
    {
        abort_unless($sprint->project_id === $project->id, 404);

        return app(SprintController::class)->close($request, $sprint);
    }

    public function reorderProjectColumns(Request $request, Project $project): JsonResponse
    {
        $request->merge(['project_id' => $project->id]);

        return app(KanbanColumnController::class)->reorder($request);
    }

    public function deleteProjectFile(Project $project, ProjectFile $file): JsonResponse
    {
        abort_unless($file->project_id === $project->id, 404);

        return app(ProjectFileController::class)->destroy($file);
    }

    public function newProjectFileVersion(Request $request, Project $project, ProjectFile $file): JsonResponse
    {
        abort_unless($file->project_id === $project->id, 404);

        return app(ProjectFileController::class)->newVersion($request, $file);
    }

    public function storeGuest(Request $request): JsonResponse
    {
        $response = app(GuestController::class)->invite($request);

        return response()->json(['data' => $response->getData(true)], $response->status());
    }

    public function updateOpportunityStage(Request $request, CrmOpportunity $opportunity): JsonResponse
    {
        $request->merge([
            'stage' => $request->input('stage'),
        ]);

        return app(CrmController::class)->moveOpportunity($request, $opportunity);
    }

    public function completeCorrespondenceRouting(Correspondence $correspondence, CorrespondenceRouting $routing): JsonResponse
    {
        abort_unless($routing->correspondence_id === $correspondence->id, 404);

        return app(CorrespondenceController::class)->completeRouting($routing);
    }

    public function updateNotificationPreference(Request $request, string $eventType): JsonResponse
    {
        $request->merge(['event_type' => $eventType]);

        return app(NotificationPreferenceController::class)->update($request);
    }

    public function verifySubscriptionPost(Request $request): JsonResponse
    {
        $status = $request->input('status', 'success');
        if ($status !== 'success') {
            return response()->json(['status' => 'failed'], 422);
        }

        $invoice = SubscriptionInvoice::query()
            ->where('tenant_id', app('tenant')->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($invoice) {
            $invoice->update(['status' => 'paid', 'paid_at' => now()]);
        }

        return response()->json(['data' => $invoice]);
    }

    public function superAdminFinancialSummary(): JsonResponse
    {
        return response()->json(['data' => $this->siteCompat->superAdminFinancial()]);
    }

    public function completeAiRequest(AiRequest $aiRequest): JsonResponse
    {
        if ($aiRequest->requested_by_user_id !== request()->user()->id) {
            abort(403);
        }

        $aiRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
            'result' => $aiRequest->result ?? ['message' => 'Completed'],
        ]);

        return response()->json(['data' => $aiRequest->fresh()]);
    }

    public function broadcastingAuth(): JsonResponse
    {
        return response()->json(['auth' => 'ok']);
    }

    public function convertPurchaseRequestToPo(\App\Models\PurchaseRequest $purchaseRequest): JsonResponse
    {
        return app(Finance\PurchaseOrderController::class)->createFromRequest($purchaseRequest);
    }

    public function deleteKanbanColumn(Project $project, KanbanColumn $column): JsonResponse
    {
        abort_unless($column->project_id === $project->id, 404);

        return app(KanbanColumnController::class)->destroy($column);
    }
}
