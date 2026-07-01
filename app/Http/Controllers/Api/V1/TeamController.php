<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\TeamMemberRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Http\Resources\UserResource;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class TeamController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Team::class);

        $teams = Team::query()->with(['lead', 'members'])->get();

        return TeamResource::collection($teams);
    }

    public function store(StoreTeamRequest $request): JsonResponse
    {
        $this->authorize('create', Team::class);

        $team = Team::create($request->validated());

        return response()->json(new TeamResource($team->load(['lead', 'members'])), 201);
    }

    public function show(Team $team): JsonResponse
    {
        $this->authorize('view', $team);

        return response()->json(new TeamResource($team->load(['lead', 'members'])));
    }

    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);

        $team->update($request->validated());

        return response()->json(new TeamResource($team->fresh()->load(['lead', 'members'])));
    }

    public function destroy(Team $team): JsonResponse
    {
        $this->authorize('delete', $team);

        $team->delete();

        return response()->json(null, 204);
    }

    public function addMember(TeamMemberRequest $request, Team $team): JsonResponse
    {
        $this->authorize('manageMembers', $team);

        $data = $request->validated();

        if ($team->members()->where('users.id', $data['user_id'])->exists()) {
            throw ValidationException::withMessages([
                'user_id' => [__('errors.member_already_exists')],
            ]);
        }

        $team->members()->attach($data['user_id'], [
            'tenant_id' => app('tenant')->id,
            'role_in_team' => $data['role_in_team'] ?? null,
        ]);

        return response()->json(new TeamResource($team->fresh()->load(['lead', 'members'])));
    }

    public function removeMember(Request $request, Team $team): JsonResponse
    {
        $this->authorize('manageMembers', $team);

        $request->validate(['user_id' => ['required', 'uuid', 'exists:users,id']]);

        $team->members()->detach($request->input('user_id'));

        return response()->json(null, 204);
    }
}
