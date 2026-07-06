<?php

namespace App\Support;

/**
 * Permission strings expected by midesk-site (Spatie-style resource:action).
 * Mirrors midesk-site/server/utils/permissions.ts
 */
final class SitePermissions
{
    public const ALL = [
        'project:read', 'project:write', 'task:read', 'task:write', 'member:invite',
        'hr:dashboard:view', 'hr:personnel:view', 'hr:personnel:create', 'hr:personnel:update', 'hr:personnel:delete',
        'hr:attendance:view', 'hr:attendance:manage', 'hr:attendance:clock',
        'hr:contracts:view', 'hr:contracts:manage', 'hr:documents:view', 'hr:documents:manage',
        'hr:missions:view', 'hr:missions:request', 'hr:missions:approve',
        'hr:recruitment:view', 'hr:recruitment:manage', 'hr:onboarding:view', 'hr:onboarding:manage', 'hr:settings:manage',
        'leave:policies:view', 'leave:policies:manage', 'leave:report:view',
        'finance:dashboard:view', 'finance:expenses:view', 'finance:expenses:create', 'finance:expenses:approve', 'finance:expenses:pay',
        'finance:budgets:view', 'finance:budgets:manage', 'finance:invoices:view', 'finance:payroll:view', 'finance:my:view',
        'chat:view', 'chat:manage', 'meetings:view', 'meetings:manage', 'ai:view', 'ai:query',
        'notes:view', 'notes:manage', 'notifications:preferences',
        'performance:dashboard:view', 'performance:indicators:view', 'performance:indicators:manage',
        'performance:scores:view', 'performance:scores:manage', 'performance:objectives:view', 'performance:objectives:manage',
        'performance:reviews:view', 'performance:reviews:manage',
        'correspondence:view', 'correspondence:manage', 'surveys:view', 'surveys:manage',
        'crm:view', 'crm:manage', 'reports:view', 'reports:manage',
        'files:view', 'files:manage', 'subscription:view', 'subscription:manage', 'guests:manage', 'roles:manage',
    ];

    /** @return list<string> */
    public static function forRole(?string $role): array
    {
        return match (strtoupper((string) $role)) {
            'OWNER' => self::ALL,
            'ADMIN' => array_values(array_filter(
                self::ALL,
                static fn (string $p) => ! str_starts_with($p, 'member:') || $p === 'member:invite'
            )),
            'MANAGER' => [
                'project:read', 'project:write', 'task:read', 'task:write',
                'hr:dashboard:view', 'hr:attendance:view', 'hr:missions:view', 'hr:missions:approve', 'leave:report:view',
                'finance:expenses:view', 'finance:expenses:create', 'finance:expenses:approve', 'finance:my:view',
                'chat:view', 'chat:manage', 'meetings:view', 'meetings:manage', 'ai:view', 'ai:query',
                'notes:view', 'notes:manage', 'notifications:preferences',
                'performance:dashboard:view', 'performance:scores:view', 'performance:objectives:view', 'performance:reviews:view',
                'correspondence:view', 'correspondence:manage', 'surveys:view', 'surveys:manage',
                'crm:view', 'crm:manage', 'reports:view', 'files:view',
            ],
            'HR_MANAGER' => array_merge(self::forRole('MANAGER'), [
                'hr:personnel:view', 'hr:personnel:create', 'hr:personnel:update', 'hr:personnel:delete',
                'hr:attendance:manage', 'hr:contracts:view', 'hr:contracts:manage', 'hr:documents:view', 'hr:documents:manage',
                'hr:recruitment:view', 'hr:recruitment:manage', 'hr:onboarding:view', 'hr:onboarding:manage', 'hr:settings:manage',
                'leave:policies:view', 'leave:policies:manage',
            ]),
            'FINANCE' => array_merge(self::forRole('MANAGER'), [
                'finance:dashboard:view', 'finance:budgets:view', 'finance:budgets:manage',
                'finance:invoices:view', 'finance:payroll:view', 'finance:expenses:pay',
            ]),
            default => [
                'project:read', 'task:read', 'task:write',
                'hr:attendance:clock', 'hr:attendance:view', 'hr:missions:view', 'hr:missions:request',
                'finance:expenses:create', 'finance:my:view',
                'chat:view', 'meetings:view', 'ai:view', 'ai:query', 'notes:view', 'notes:manage', 'notifications:preferences',
                'performance:dashboard:view', 'performance:objectives:view', 'performance:reviews:view',
                'correspondence:view', 'surveys:view', 'crm:view', 'reports:view', 'files:view',
            ],
        };
    }
}
