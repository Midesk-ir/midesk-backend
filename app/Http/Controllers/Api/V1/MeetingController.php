<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Services\MeetingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function __construct(private MeetingService $meetingService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Meeting::query()->with(['attendees.user', 'organizer']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($from = $request->query('from')) {
            $query->where('start_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->where('start_at', '<=', $to);
        }

        return response()->json(['data' => $query->orderBy('start_at')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'provider' => 'in:jitsi,bbb,google_meet',
            'attendee_ids' => 'array',
            'attendee_ids.*' => 'uuid',
        ]);

        $meeting = $this->meetingService->create($request->user(), $data);

        return response()->json($meeting, 201);
    }

    public function show(Meeting $meeting): JsonResponse
    {
        return response()->json($meeting->load(['attendees.user', 'organizer']));
    }

    public function update(Request $request, Meeting $meeting): JsonResponse
    {
        $meeting->update($request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'sometimes|date',
            'end_at' => 'sometimes|date|after:start_at',
        ]));

        return response()->json($meeting->fresh()->load(['attendees.user', 'organizer']));
    }

    public function destroy(Meeting $meeting): JsonResponse
    {
        $meeting->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Meeting cancelled']);
    }

    public function today(): JsonResponse
    {
        $meetings = Meeting::query()
            ->whereDate('start_at', today())
            ->with(['attendees.user', 'organizer'])
            ->orderBy('start_at')
            ->get();

        return response()->json(['data' => $meetings]);
    }

    public function myAttendance(Request $request): JsonResponse
    {
        $meetings = Meeting::query()
            ->whereHas('attendees', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with(['attendees.user', 'organizer'])
            ->orderByDesc('start_at')
            ->get();

        return response()->json(['data' => $meetings]);
    }

    public function start(Request $request, Meeting $meeting): JsonResponse
    {
        return response()->json($this->meetingService->start($meeting, $request->user()));
    }

    public function join(Request $request, Meeting $meeting): JsonResponse
    {
        return response()->json($this->meetingService->join($meeting, $request->user()));
    }

    public function end(Meeting $meeting): JsonResponse
    {
        return response()->json($this->meetingService->end($meeting));
    }

    public function accept(Request $request, Meeting $meeting): JsonResponse
    {
        return response()->json($this->meetingService->respond($meeting, $request->user(), \App\Enums\MeetingAttendeeStatus::Accepted));
    }

    public function decline(Request $request, Meeting $meeting): JsonResponse
    {
        return response()->json($this->meetingService->respond($meeting, $request->user(), \App\Enums\MeetingAttendeeStatus::Declined));
    }
}
