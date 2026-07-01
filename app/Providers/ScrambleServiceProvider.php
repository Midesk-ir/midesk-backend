<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Server;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\RouteInfo;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ScrambleServiceProvider extends ServiceProvider
{
    /** @var array<string, string> */
    private const TAG_PREFIXES = [
        'v1/super-admin' => 'Super Admin',
        'v1/guest' => 'Guest',
        'v1/auth' => 'Auth',
        'v1/core' => 'Core',
        'v1/orgs' => 'Organizations',
        'v1/teams' => 'Teams',
        'v1/finance' => 'Finance',
        'v1/my-finance' => 'My Finance',
        'v1/hr' => 'HR',
        'v1/leave' => 'Leave',
        'v1/performance' => 'Performance',
        'v1/objectives' => 'OKR',
        'v1/key-results' => 'OKR',
        'v1/reviews' => 'Performance Reviews',
        'v1/sprints' => 'Agile',
        'v1/kanban' => 'Kanban',
        'v1/worklogs' => 'Worklogs',
        'v1/calendar' => 'Calendar',
        'v1/chat' => 'Chat',
        'v1/meetings' => 'Meetings',
        'v1/ai' => 'AI',
        'v1/notes' => 'Notes',
        'v1/notification-preferences' => 'Notification Preferences',
        'v1/correspondences' => 'Correspondence',
        'v1/correspondence-routing' => 'Correspondence',
        'v1/surveys' => 'Surveys',
        'v1/survey-questions' => 'Surveys',
        'v1/crm' => 'CRM',
        'v1/reports' => 'Reports',
        'v1/files' => 'Files',
        'v1/guests' => 'Guest Management',
        'v1/subscription' => 'Subscription',
        'v1/projects' => 'Projects',
        'v1/tasks' => 'Tasks',
        'v1/labels' => 'Labels',
        'v1/dashboard' => 'Dashboard',
        'v1/notifications' => 'Notifications',
    ];

    public function boot(): void
    {
        Scramble::resolveTagsUsing(
            fn (RouteInfo $routeInfo, Operation $operation): array => [self::resolveTag($routeInfo->route)]
        );

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi): void {
            $openApi->info->title = 'Midesk API';
            $openApi->info->version = (string) config('scramble.info.version', '1.0.0');
            $openApi->info->description = <<<'MD'
REST API for the Midesk platform (phases 0–5).

**Authentication:** `Authorization: Bearer {sanctum_token}`

**Multi-tenant:** most endpoints require `X-Tenant-Id: {tenant_uuid}`.

**Locale:** optional `Accept-Language: fa` or `en`.

**Errors:** `{ "error": { "code": "...", "message": "...", "details": [...] } }`
MD;

            $openApi->servers = [
                Server::make(rtrim((string) config('app.url'), '/').'/api'),
            ];
        });

        Scramble::configure()->withOperationTransformers(
            function (Operation $operation, RouteInfo $routeInfo): void {
                if (self::routeUsesMiddleware($routeInfo->route, 'tenant')) {
                    $operation->addParameters([
                        self::headerParameter('X-Tenant-Id', 'Tenant UUID', true),
                        self::headerParameter('Accept-Language', 'Response locale (`fa` or `en`)', false),
                    ]);
                }

                if (self::routeUsesMiddleware($routeInfo->route, 'guest.token')) {
                    $operation->addParameters([
                        self::headerParameter('X-Guest-Token', 'Guest access token', true),
                    ]);
                }
            }
        );
    }

    private static function headerParameter(string $name, string $description, bool $required): Parameter
    {
        return Parameter::make($name, 'header')
            ->description($description)
            ->required($required)
            ->setSchema(Schema::fromType(new StringType));
    }

    private static function routeUsesMiddleware(Route $route, string $needle): bool
    {
        return collect($route->gatherMiddleware())
            ->contains(fn (string $middleware) => Str::is($needle, $middleware));
    }

    private static function resolveTag(Route $route): string
    {
        $uri = $route->uri();

        foreach (self::TAG_PREFIXES as $prefix => $tag) {
            if (str_starts_with($uri, $prefix)) {
                return $tag;
            }
        }

        $controller = $route->getAction('controller');

        if (is_string($controller)) {
            return Str::before(class_basename(Str::before($controller, '@')), 'Controller');
        }

        return 'General';
    }
}
