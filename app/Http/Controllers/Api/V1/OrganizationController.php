<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MembershipStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Org\CreateOrganizationRequest;
use App\Http\Requests\Org\InviteMemberRequest;
use App\Http\Requests\Org\UpdateMemberRequest;
use App\Http\Requests\Org\UpdateOrganizationRequest;
use App\Http\Resources\MembershipResource;
use App\Http\Resources\TenantResource;
use App\Models\Membership;
use App\Models\User;
use App\Notifications\MemberInvitedNotification;
use App\Services\AuthService;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class OrganizationController extends Controller
{
    public function __construct(
        private readonly TenantService $tenantService,
        private readonly AuthService $authService,
    ) {}

    public function store(CreateOrganizationRequest $request): JsonResponse
    {
        $tenant = $this->tenantService->createForUser($request->user(), $request->validated());
        $token = $this->authService->createTokenResponse($request->user(), $tenant->id);

        return response()->json([
            'tenant' => new TenantResource($tenant),
            'token' => $token['token'],
        ], 201);
    }

    public function current(Request $request): JsonResponse
    {
        return response()->json(new TenantResource(app('tenant')));
    }

    public function updateCurrent(UpdateOrganizationRequest $request): JsonResponse
    {
        if (! app('membership')->isOwnerOrAdmin()) {
            abort(403, __('errors.forbidden'));
        }

        $tenant = app('tenant');
        $tenant->update($request->validated());

        return response()->json(new TenantResource($tenant->fresh()));
    }

    public function members(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Membership::class);

        $members = Membership::query()
            ->where('tenant_id', app('tenant')->id)
            ->with('user')
            ->get();

        return MembershipResource::collection($members);
    }

    public function invite(InviteMemberRequest $request): JsonResponse
    {
        $this->authorize('invite', Membership::class);

        $tenant = app('tenant');
        $data = $request->validated();

        $identifierField = ! empty($data['email']) ? 'email' : 'phone';
        $identifier = $data[$identifierField];
        $user = User::query()->where($identifierField, $identifier)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                $identifierField => [__('errors.user_not_found')],
            ]);
        }

        $membership = Membership::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $user->id],
            [
                'role' => $data['role'],
                'status' => MembershipStatus::Invited,
                'invited_email' => $user->email,
            ]
        );

        if ($membership->wasRecentlyCreated) {
            $user->notify(new MemberInvitedNotification($membership));
        }

        return response()->json(new MembershipResource($membership->load('user')), 201);
    }

    public function updateMember(UpdateMemberRequest $request, Membership $member): JsonResponse
    {
        $this->authorize('update', $member);

        $member->update(['role' => $request->validated('role')]);

        return response()->json(new MembershipResource($member->fresh()->load('user')));
    }

    public function destroyMember(Membership $member): JsonResponse
    {
        $this->authorize('delete', $member);

        $member->delete();

        return response()->json(null, 204);
    }
}
