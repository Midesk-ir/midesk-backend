<?php

return [
    'approval_threshold' => (float) env('FINANCE_APPROVAL_THRESHOLD', 10_000_000),
    'budget_alert_percent' => (int) env('FINANCE_BUDGET_ALERT_PERCENT', 80),
];
