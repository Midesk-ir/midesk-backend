<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CrmActivity;
use App\Models\CrmContact;
use App\Models\CrmOpportunity;
use App\Services\CrmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    public function __construct(private CrmService $service) {}

    public function contacts(): JsonResponse
    {
        return response()->json(['data' => CrmContact::query()->with('assignedUser')->orderByDesc('created_at')->get()]);
    }

    public function storeContact(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'type' => 'in:customer,lead,partner,vendor',
            'source' => 'nullable|string',
            'assigned_user_id' => 'nullable|uuid',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        return response()->json(CrmContact::create($data), 201);
    }

    public function showContact(CrmContact $contact): JsonResponse
    {
        return response()->json($contact->load(['opportunities', 'assignedUser']));
    }

    public function updateContact(Request $request, CrmContact $contact): JsonResponse
    {
        $contact->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'company' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'type' => 'in:customer,lead,partner,vendor',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ]));

        return response()->json($contact->fresh());
    }

    public function destroyContact(CrmContact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json(['message' => 'Contact deleted']);
    }

    public function opportunities(): JsonResponse
    {
        return response()->json(['data' => CrmOpportunity::query()->with(['contact', 'assignedUser'])->get()]);
    }

    public function storeOpportunity(Request $request): JsonResponse
    {
        $data = $request->validate([
            'contact_id' => 'required|uuid',
            'title' => 'required|string|max:255',
            'value' => 'numeric',
            'currency' => 'string|max:10',
            'stage' => 'in:lead,qualified,proposal,negotiation,won,lost',
            'probability' => 'integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'assigned_user_id' => 'nullable|uuid',
            'notes' => 'nullable|string',
        ]);

        return response()->json(CrmOpportunity::create($data), 201);
    }

    public function showOpportunity(CrmOpportunity $opportunity): JsonResponse
    {
        return response()->json($opportunity->load(['contact', 'assignedUser', 'activities']));
    }

    public function updateOpportunity(Request $request, CrmOpportunity $opportunity): JsonResponse
    {
        $opportunity->update($request->validate([
            'title' => 'sometimes|string|max:255',
            'value' => 'numeric',
            'probability' => 'integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]));

        return response()->json($opportunity->fresh());
    }

    public function moveOpportunity(Request $request, CrmOpportunity $opportunity): JsonResponse
    {
        $opportunity->update($request->validate(['stage' => 'required|in:lead,qualified,proposal,negotiation,won,lost']));

        return response()->json($opportunity->fresh());
    }

    public function activities(): JsonResponse
    {
        return response()->json(['data' => CrmActivity::query()->with(['contact', 'opportunity', 'performer'])->latest('performed_at')->get()]);
    }

    public function storeActivity(Request $request): JsonResponse
    {
        $data = $request->validate([
            'contact_id' => 'nullable|uuid',
            'opportunity_id' => 'nullable|uuid',
            'type' => 'required|in:call,email,meeting,note,task',
            'description' => 'required|string',
            'performed_at' => 'nullable|date',
        ]);

        return response()->json($this->service->logActivity($request->user(), $data), 201);
    }

    public function pipeline(): JsonResponse
    {
        return response()->json($this->service->pipeline());
    }

    public function report(): JsonResponse
    {
        return response()->json($this->service->report());
    }
}
