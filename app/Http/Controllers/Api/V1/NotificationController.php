<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tenant = app('tenant');

        $notifications = $request->user()
            ->notifications()
            ->when($tenant, fn ($q) => $q->where('tenant_id', $tenant->id))
            ->latest()
            ->paginate();

        return NotificationResource::collection($notifications);
    }

    public function count(Request $request): JsonResponse
    {
        $tenant = app('tenant');

        $count = $request->user()
            ->unreadNotifications()
            ->when($tenant, fn ($q) => $q->where('tenant_id', $tenant->id))
            ->count();

        return response()->json(['count' => $count]);
    }

    public function readAll(Request $request): JsonResponse
    {
        $tenant = app('tenant');

        $request->user()
            ->unreadNotifications()
            ->when($tenant, fn ($q) => $q->where('tenant_id', $tenant->id))
            ->update(['read_at' => now()]);

        return response()->json(['message' => __('notifications.all_read')]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        /** @var DatabaseNotification|null $notification */
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return response()->json(new NotificationResource($notification->fresh()));
    }
}
