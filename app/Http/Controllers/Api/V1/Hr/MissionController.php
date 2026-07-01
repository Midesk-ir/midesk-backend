<?php

namespace App\Http\Controllers\Api\V1\Hr;

use App\Enums\MissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hr\MissionResource;
use App\Models\Mission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MissionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Mission::class);

        $query = Mission::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return MissionResource::collection($query->paginate());
    }

    public function my(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Mission::class);

        return MissionResource::collection(
            Mission::query()
                ->where('user_id', $request->user()->id)
                ->orderByDesc('created_at')
                ->paginate()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Mission::class);

        $data = $request->validate([
            'destination' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'transport_type' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $mission = Mission::create([
            ...$data,
            'user_id' => $request->user()->id,
            'status' => MissionStatus::Pending,
        ]);

        return response()->json(new MissionResource($mission), 201);
    }

    public function approve(Request $request, Mission $mission): JsonResponse
    {
        $this->authorize('approve', $mission);

        $mission->update([
            'status' => MissionStatus::Approved,
            'approved_by_user_id' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json(new MissionResource($mission->fresh()));
    }

    public function reject(Request $request, Mission $mission): JsonResponse
    {
        $this->authorize('approve', $mission);

        $mission->update(['status' => MissionStatus::Rejected]);

        return response()->json(new MissionResource($mission->fresh()));
    }

    public function complete(Request $request, Mission $mission): JsonResponse
    {
        $this->authorize('view', $mission);

        $mission->update(['status' => MissionStatus::Completed]);

        return response()->json(new MissionResource($mission->fresh()));
    }
}
