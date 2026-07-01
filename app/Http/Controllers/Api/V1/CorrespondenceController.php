<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Correspondence;
use App\Models\CorrespondenceRouting;
use App\Services\CorrespondenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CorrespondenceController extends Controller
{
    public function __construct(private CorrespondenceService $service) {}

    public function index(Request $request): JsonResponse
    {
        $query = Correspondence::query()->with(['creator', 'routings']);

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->query('search')) {
            $query->where('subject', 'like', "%{$search}%");
        }

        return response()->json(['data' => $query->orderByDesc('created_at')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|in:incoming,outgoing,internal',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'sender_info' => 'nullable|string',
            'receiver_info' => 'nullable|string',
            'priority' => 'in:normal,urgent,confidential',
            'parent_id' => 'nullable|uuid',
            'attachments' => 'nullable|array',
        ]);

        return response()->json($this->service->create($request->user(), $data), 201);
    }

    public function show(Correspondence $correspondence): JsonResponse
    {
        return response()->json($correspondence->load(['creator', 'routings.fromUser', 'routings.toUser', 'parent']));
    }

    public function update(Request $request, Correspondence $correspondence): JsonResponse
    {
        if ($correspondence->status->value !== 'draft') {
            abort(422, 'Only draft correspondences can be edited');
        }

        $correspondence->update($request->validate([
            'subject' => 'sometimes|string|max:255',
            'body' => 'sometimes|string',
            'priority' => 'in:normal,urgent,confidential',
        ]));

        return response()->json($correspondence->fresh());
    }

    public function send(Correspondence $correspondence): JsonResponse
    {
        return response()->json($this->service->send($correspondence));
    }

    public function sign(Request $request, Correspondence $correspondence): JsonResponse
    {
        return response()->json($this->service->sign($correspondence, $request->user()));
    }

    public function route(Request $request, Correspondence $correspondence): JsonResponse
    {
        $data = $request->validate([
            'to_user_id' => 'required|uuid',
            'action' => 'required|in:review,approve,sign,for_info,execute',
            'note' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        return response()->json($this->service->route($correspondence, $request->user(), $data), 201);
    }

    public function completeRouting(CorrespondenceRouting $correspondenceRouting): JsonResponse
    {
        $correspondenceRouting->update(['status' => 'completed', 'completed_at' => now()]);

        return response()->json($correspondenceRouting->fresh());
    }

    public function inbox(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->service->inbox($request->user())]);
    }

    public function nextNumber(): JsonResponse
    {
        return response()->json(['number' => $this->service->nextNumber()]);
    }
}
