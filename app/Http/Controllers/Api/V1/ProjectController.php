<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProjectMemberRole;
use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\ProjectMemberRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\UserResource;
use App\Models\Project;
use App\Services\KanbanColumnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProjectController extends Controller
{
    public function __construct(
        private readonly KanbanColumnService $kanbanService,
    ) {}
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Project::class);

        $user = $request->user();

        $projects = QueryBuilder::for(Project::class)
            ->allowedFilters(
                AllowedFilter::exact('status'),
                AllowedFilter::partial('search', 'name'),
            )
            ->allowedSorts('created_at', 'name')
            ->defaultSort('-created_at')
            ->where(function ($q) use ($user) {
                $q->where('owner_user_id', $user->id)
                    ->orWhereHas('members', fn ($mq) => $mq->where('users.id', $user->id));
            })
            ->with(['owner'])
            ->withCount('tasks')
            ->paginate();

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $user = $request->user();

        $project = Project::create([
            ...$request->validated(),
            'key' => $request->filled('key')
                ? (string) $request->input('key')
                : $this->generateProjectKey((string) $request->input('name'), (string) app('tenant')->id),
            'owner_user_id' => $user->id,
            'status' => $request->input('status', ProjectStatus::Active),
        ]);

        $project->members()->attach($user->id, [
            'tenant_id' => app('tenant')->id,
            'role' => ProjectMemberRole::Lead->value,
        ]);

        $this->kanbanService->createDefaultColumns($project);

        return response()->json(new ProjectResource($project->load(['owner', 'members'])), 201);
    }

    private function generateProjectKey(string $name, string $tenantId): string
    {
        $base = Str::upper(Str::slug($name, '_'));
        $base = trim($base, '_');
        $base = $base !== '' ? Str::limit($base, 20, '') : 'PROJECT';

        $candidate = $base;
        $suffix = 1;

        while (
            Project::query()
                ->where('tenant_id', $tenantId)
                ->where('key', $candidate)
                ->exists()
        ) {
            $counter = (string) $suffix++;
            $prefixMax = max(1, 20 - (strlen($counter) + 1));
            $prefix = Str::limit($base, $prefixMax, '');
            $candidate = "{$prefix}_{$counter}";
        }

        return $candidate;
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return response()->json(new ProjectResource($project->load(['owner', 'members'])));
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return response()->json(new ProjectResource($project->fresh()->load(['owner', 'members'])));
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json(null, 204);
    }

    public function members(Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        return UserResource::collection($project->members);
    }

    public function addMember(ProjectMemberRequest $request, Project $project): JsonResponse
    {
        $this->authorize('manageMembers', $project);

        $data = $request->validated();

        if ($project->members()->where('users.id', $data['user_id'])->exists()) {
            throw ValidationException::withMessages([
                'user_id' => [__('errors.member_already_exists')],
            ]);
        }

        $project->members()->attach($data['user_id'], [
            'tenant_id' => app('tenant')->id,
            'role' => $data['role'],
        ]);

        return response()->json(new ProjectResource($project->fresh()->load(['owner', 'members'])));
    }

    public function removeMember(Request $request, Project $project): JsonResponse
    {
        $this->authorize('manageMembers', $project);

        $request->validate(['user_id' => ['required', 'uuid', 'exists:users,id']]);

        $project->members()->detach($request->input('user_id'));

        return response()->json(null, 204);
    }
}
