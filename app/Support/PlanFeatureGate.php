<?php

namespace App\Support;

use App\Models\Tenant;

final class PlanFeatureGate
{
    /** @return list<string> */
    public static function enabledModules(?Tenant $tenant): array
    {
        $features = $tenant?->plan?->features;
        if (! is_array($features)) {
            return ['projects', 'work', 'collab', 'org', 'hr', 'business', 'finance'];
        }

        $modules = $features['modules'] ?? null;
        if (! is_array($modules) || $modules === []) {
            return ['projects', 'work', 'collab', 'org', 'hr', 'business', 'finance'];
        }

        return array_values(array_map(static fn ($item) => strtolower((string) $item), $modules));
    }

    public static function allowsModule(?Tenant $tenant, string $module): bool
    {
        return in_array(strtolower($module), self::enabledModules($tenant), true);
    }

    public static function routeModule(string $path): ?string
    {
        if (str_starts_with($path, '/api/v1/hr') || str_starts_with($path, '/api/v1/leave')) {
            return 'hr';
        }

        if (
            str_starts_with($path, '/api/v1/crm')
            || str_starts_with($path, '/api/v1/correspondence')
            || str_starts_with($path, '/api/v1/surveys')
            || str_starts_with($path, '/api/v1/reports')
            || str_starts_with($path, '/api/v1/performance')
        ) {
            return 'business';
        }

        if (str_starts_with($path, '/api/v1/finance') || str_starts_with($path, '/api/v1/my-finance')) {
            return 'finance';
        }

        return null;
    }

    /** @param list<string> $permissions * @return list<string> */
    public static function filterPermissions(array $permissions, ?Tenant $tenant): array
    {
        if (self::allowsModule($tenant, 'hr') && self::allowsModule($tenant, 'business') && self::allowsModule($tenant, 'finance')) {
            return $permissions;
        }

        $blockedPrefixes = [];
        if (! self::allowsModule($tenant, 'hr')) {
            $blockedPrefixes = [...$blockedPrefixes, 'hr:', 'leave:'];
        }
        if (! self::allowsModule($tenant, 'business')) {
            $blockedPrefixes = [...$blockedPrefixes, 'correspondence:', 'surveys:', 'crm:', 'reports:', 'performance:'];
        }
        if (! self::allowsModule($tenant, 'finance')) {
            $blockedPrefixes = [...$blockedPrefixes, 'finance:'];
        }

        return array_values(array_filter($permissions, static function (string $permission) use ($blockedPrefixes): bool {
            foreach ($blockedPrefixes as $prefix) {
                if (str_starts_with($permission, $prefix)) {
                    return false;
                }
            }

            return true;
        }));
    }
}

