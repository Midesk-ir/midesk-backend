<?php

namespace App\Enums;

enum ApprovalWorkflowStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case ManagerReview = 'manager_review';
    case FinanceReview = 'finance_review';
    case Approved = 'approved';
    case ReadyToPay = 'ready_to_pay';
    case Paid = 'paid';
    case Rejected = 'rejected';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
