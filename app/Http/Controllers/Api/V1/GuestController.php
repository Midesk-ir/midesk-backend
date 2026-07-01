<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GuestAccess;
use App\Services\GuestAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function __construct(private GuestAccessService $guestService) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => GuestAccess::query()->orderByDesc('created_at')->get()]);
    }

    public function invite(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'role' => 'in:viewer,commenter',
            'project_ids' => 'required|array',
            'project_ids.*' => 'uuid',
            'expires_at' => 'required|date',
        ]);

        return response()->json($this->guestService->invite($request->user(), $data), 201);
    }

    public function destroy(GuestAccess $guest): JsonResponse
    {
        $guest->delete();

        return response()->json(['message' => 'Guest access revoked']);
    }

    public function guestProjects(): JsonResponse
    {
        /** @var GuestAccess $guest */
        $guest = app('guest');

        return response()->json(['data' => $this->guestService->projectsForGuest($guest)]);
    }

    public function guestProject(string $id): JsonResponse
    {
        /** @var GuestAccess $guest */
        $guest = app('guest');

        if (! in_array($id, $guest->project_ids ?? [], true)) {
            abort(403);
        }

        $project = \App\Models\Project::withoutGlobalScopes()
            ->where('tenant_id', $guest->tenant_id)
            ->with('tasks')
            ->findOrFail($id);

        return response()->json($project);
    }
}
