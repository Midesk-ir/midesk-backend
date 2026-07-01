<?php

namespace App\Http\Controllers\Api\V1\Hr;

use App\Http\Controllers\Controller;
use App\Http\Resources\Hr\ContractResource;
use App\Models\Contract;
use App\Services\ContractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContractController extends Controller
{
    public function __construct(private ContractService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Contract::class);

        return ContractResource::collection(
            $this->service->list($request->only(['status', 'expiring']))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Contract::class);

        $data = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'type' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date'],
            'base_salary' => ['required', 'numeric'],
            'currency' => ['required', 'string', 'max:10'],
            'benefits' => ['nullable', 'array'],
        ]);

        $contract = $this->service->create($data);

        return response()->json(new ContractResource($contract->load('user')), 201);
    }

    public function update(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('update', $contract);

        $data = $request->validate([
            'type' => ['sometimes', 'string'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date'],
            'base_salary' => ['sometimes', 'numeric'],
            'currency' => ['sometimes', 'string', 'max:10'],
            'benefits' => ['nullable', 'array'],
            'status' => ['nullable', 'string'],
        ]);

        return response()->json(new ContractResource($this->service->update($contract, $data)));
    }

    public function renew(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('update', $contract);

        $data = $request->validate([
            'type' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date'],
            'base_salary' => ['required', 'numeric'],
            'currency' => ['required', 'string', 'max:10'],
            'benefits' => ['nullable', 'array'],
        ]);

        $newContract = $this->service->renew($contract, $data);

        return response()->json(new ContractResource($newContract->load('user')), 201);
    }

    public function expiring(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Contract::class);

        return ContractResource::collection($this->service->expiring());
    }
}
