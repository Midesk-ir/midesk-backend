<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\UpdateProfileRequest;
use App\Http\Resources\Site\UserResource;
use App\Models\Membership;
use App\Models\Tenant;
use App\Services\SiteCompatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoreController extends Controller
{
    public function __construct(private readonly SiteCompatService $siteCompat) {}

    public function bootstrap(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var Tenant $tenant */
        $tenant = app('tenant');
        /** @var Membership $membership */
        $membership = app('membership');

        return response()->json($this->siteCompat->bootstrapPayload($user, $tenant, $membership));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()));
    }

    public function updateMe(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        $user->update($data);

        return response()->json(new UserResource($user->fresh()));
    }
}
