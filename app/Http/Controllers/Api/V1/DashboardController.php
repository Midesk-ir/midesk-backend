<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CandidateStatus;
use App\Enums\OnboardingProgressStatus;
use App\Enums\ProjectStatus;
use App\Enums\SprintStatus;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardSummaryResource;
use App\Models\AttendanceRecord;
use App\Models\Candidate;
use App\Models\KanbanColumn;
use App\Models\OnboardingProgress;
use App\Models\Project;
use App\Models\Task;
use App\Services\CalendarService;
use App\Services\ContractService;
use App\Services\LeaveService;
use App\Services\WorklogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly WorklogService $worklogService,
        private readonly LeaveService $leaveService,
        private readonly CalendarService $calendarService,
        private readonly ContractService $contractService,
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $membership = app('membership');

        $doneColumnIds = KanbanColumn::query()
            ->where('is_done_column', true)
            ->pluck('id');

        $myTasks = Task::query()
            ->where('assignee_user_id', $user->id)
            ->when($doneColumnIds->isNotEmpty(), fn ($q) => $q->whereNotIn('kanban_column_id', $doneColumnIds))
            ->when($doneColumnIds->isEmpty(), fn ($q) => $q->whereNot('status', TaskStatus::Done))
            ->with(['assignee', 'labels', 'kanbanColumn'])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $overdueTasks = Task::query()
            ->where('assignee_user_id', $user->id)
            ->when($doneColumnIds->isNotEmpty(), fn ($q) => $q->whereNotIn('kanban_column_id', $doneColumnIds))
            ->when($doneColumnIds->isEmpty(), fn ($q) => $q->whereNot('status', TaskStatus::Done))
            ->where('due_date', '<', now())
            ->with(['assignee', 'labels', 'kanbanColumn'])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $activeSprintTasks = Task::query()
            ->where('assignee_user_id', $user->id)
            ->whereHas('sprint', fn ($q) => $q->where('status', SprintStatus::Active))
            ->with(['assignee', 'labels', 'sprint', 'kanbanColumn'])
            ->orderBy('order_index')
            ->limit(10)
            ->get();

        $activeProjects = Project::query()
            ->where('status', ProjectStatus::Active)
            ->where(function ($q) use ($user) {
                $q->where('owner_user_id', $user->id)
                    ->orWhereHas('members', fn ($mq) => $mq->where('users.id', $user->id));
            })
            ->with(['owner'])
            ->withCount('tasks')
            ->limit(10)
            ->get();

        $attendanceToday = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();

        $data = [
            'my_tasks' => $myTasks,
            'overdue_tasks' => $overdueTasks,
            'active_sprint_tasks' => $activeSprintTasks,
            'active_projects' => $activeProjects,
            'week_worklog_minutes' => $this->worklogService->weekMinutesForUser($user),
            'pending_leave_requests' => $this->leaveService->pendingCountForManager(),
            'upcoming_holidays' => $this->calendarService->upcomingHolidays(5),
            'attendance_today' => [
                'checked_in' => $attendanceToday?->check_in_at !== null,
                'checked_out' => $attendanceToday?->check_out_at !== null,
                'status' => $attendanceToday?->status?->value,
            ],
            'leave_balances' => $this->leaveService->balancesForUser($user),
        ];

        if ($membership?->canManageHr()) {
            $data['expiring_contracts_count'] = $this->contractService->expiryReminderCount();
            $data['new_candidates_count'] = Candidate::query()
                ->where('status', CandidateStatus::New)
                ->count();
            $data['active_onboarding_count'] = OnboardingProgress::query()
                ->where('status', OnboardingProgressStatus::InProgress)
                ->count();
        }

        return response()->json(new DashboardSummaryResource($data));
    }
}
