<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\UpdateProfileRequest;
use App\Http\Resources\BootstrapResource;
use App\Http\Resources\UserResource;
use App\Models\Membership;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CoreController extends Controller
{
    public function bootstrap(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var Tenant $tenant */
        $tenant = app('tenant');
        /** @var Membership $membership */
        $membership = app('membership');

        $tenants = Tenant::query()
            ->whereHas('memberships', fn ($q) => $q->where('user_id', $user->id)->where('status', 'active'))
            ->get();

        $unreadCount = $user->unreadNotifications()
            ->when($tenant, fn ($q) => $q->where('tenant_id', $tenant->id))
            ->count();

        return response()->json(new BootstrapResource([
            'user' => $user,
            'tenant' => $tenant,
            'membership' => $membership,
            'tenants' => $tenants,
            'unread_notifications_count' => $unreadCount,
            'locale' => App::getLocale(),
        ]));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()));
    }

    public function updateMe(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return response()->json(new UserResource($user->fresh()));
    }
}
