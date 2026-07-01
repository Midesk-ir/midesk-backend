<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\NotificationPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function __construct(private NotificationPreferenceService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->service->listForUser($request->user())]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event_type' => 'required|string',
            'channels' => 'required|array',
        ]);

        $pref = $this->service->upsert($request->user(), $data['event_type'], $data['channels']);

        return response()->json($pref);
    }

    public function events(): JsonResponse
    {
        return response()->json(['data' => NotificationPreferenceService::EVENT_TYPES]);
    }
}
