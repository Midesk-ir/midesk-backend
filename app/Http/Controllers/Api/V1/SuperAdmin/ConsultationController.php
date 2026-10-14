<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConsultationController extends Controller
{
    public function index(): JsonResponse
    {
        $consultations = Consultation::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Consultation $consultation) => $this->toPayload($consultation));

        return response()->json(['data' => $consultations]);
    }

    public function stats(): JsonResponse
    {
        $total = Consultation::query()->count();
        $pending = Consultation::query()->where('status', 'pending')->count();
        $completed = Consultation::query()->where('status', 'closed')->count();

        return response()->json([
            'total' => $total,
            'pending' => $pending,
            'completed' => $completed,
        ]);
    }

    public function update(Request $request, Consultation $consultation): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'contacted', 'closed'])],
        ]);

        $consultation->update($data);

        return response()->json(['data' => $this->toPayload($consultation->fresh())]);
    }

    /** @return array<string, mixed> */
    private function toPayload(Consultation $consultation): array
    {
        return [
            'id' => $consultation->id,
            'name' => $consultation->name,
            'email' => $consultation->email,
            'company' => $consultation->company,
            'message' => $consultation->message,
            'status' => $consultation->status,
            'created_at' => $consultation->created_at?->toIso8601String(),
        ];
    }
}
