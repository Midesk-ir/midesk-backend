<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Site\UserResource;
use App\Services\SiteCompatService;
use Illuminate\Http\JsonResponse;

class MemberController extends Controller
{
    public function __construct(private readonly SiteCompatService $siteCompat) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->siteCompat->tenantMembers(),
        ]);
    }
}
