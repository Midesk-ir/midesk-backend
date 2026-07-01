<?php

namespace App\Support;

use App\Enums\ApprovalWorkflowStatus;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ApprovalWorkflow
{
    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'submit' => [
            ApprovalWorkflowStatus::Draft->value => [ApprovalWorkflowStatus::ManagerReview],
            ApprovalWorkflowStatus::Rejected->value => [ApprovalWorkflowStatus::ManagerReview],
        ],
        'manager_approve' => [
            ApprovalWorkflowStatus::Submitted->value => [ApprovalWorkflowStatus::FinanceReview, ApprovalWorkflowStatus::Approved],
            ApprovalWorkflowStatus::ManagerReview->value => [ApprovalWorkflowStatus::FinanceReview, ApprovalWorkflowStatus::Approved],
        ],
        'finance_approve' => [
            ApprovalWorkflowStatus::FinanceReview->value => [ApprovalWorkflowStatus::Approved],
        ],
        'reject' => [
            ApprovalWorkflowStatus::Submitted->value => [ApprovalWorkflowStatus::Rejected],
            ApprovalWorkflowStatus::ManagerReview->value => [ApprovalWorkflowStatus::Rejected],
            ApprovalWorkflowStatus::FinanceReview->value => [ApprovalWorkflowStatus::Rejected],
        ],
        'pay' => [
            ApprovalWorkflowStatus::Approved->value => [ApprovalWorkflowStatus::Paid],
            ApprovalWorkflowStatus::ReadyToPay->value => [ApprovalWorkflowStatus::Paid],
        ],
    ];

    public static function canTransition(string $action, ApprovalWorkflowStatus $from, ApprovalWorkflowStatus $to): bool
    {
        $allowed = self::TRANSITIONS[$action][$from->value] ?? [];

        return in_array($to, $allowed, true);
    }

    public static function assertTransition(string $action, ApprovalWorkflowStatus $from, ApprovalWorkflowStatus $to): void
    {
        if (! self::canTransition($action, $from, $to)) {
            throw ValidationException::withMessages([
                'status' => [__('errors.invalid_workflow_transition')],
            ]);
        }
    }

    public static function requiresFinanceReview(float $amount): bool
    {
        return $amount > (float) config('finance.approval_threshold', 10_000_000);
    }

    public static function managerTargetStatus(float $amount): ApprovalWorkflowStatus
    {
        return self::requiresFinanceReview($amount)
            ? ApprovalWorkflowStatus::FinanceReview
            : ApprovalWorkflowStatus::Approved;
    }

    public static function userManagesTeamMember(User $manager, User $member): bool
    {
        if ($manager->id === $member->id) {
            return true;
        }

        return Team::query()
            ->where('lead_user_id', $manager->id)
            ->whereHas('members', fn ($q) => $q->where('users.id', $member->id))
            ->exists();
    }

    public static function canManagerReview(User $reviewer, User $submitter, ?Membership $membership): bool
    {
        if (! $membership) {
            return false;
        }

        if ($membership->isOwnerOrAdmin()) {
            return true;
        }

        if ($membership->role === \App\Enums\MembershipRole::Manager && self::userManagesTeamMember($reviewer, $submitter)) {
            return true;
        }

        return false;
    }

    public static function canFinanceReview(?Membership $membership): bool
    {
        return $membership?->canManageFinance() ?? false;
    }
}
