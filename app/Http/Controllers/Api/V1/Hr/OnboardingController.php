<?php

namespace App\Http\Controllers\Api\V1\Hr;

use App\Http\Controllers\Controller;
use App\Http\Resources\Hr\OnboardingProgressResource;
use App\Http\Resources\Hr\OnboardingTemplateResource;
use App\Models\OnboardingTemplate;
use App\Models\User;
use App\Policies\OnboardingPolicy;
use App\Services\OnboardingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OnboardingController extends Controller
{
    public function __construct(private OnboardingService $service) {}

    public function listTemplates(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', OnboardingTemplate::class);

        return OnboardingTemplateResource::collection($this->service->listTemplates());
    }

    public function storeTemplate(Request $request): JsonResponse
    {
        $this->authorize('manage', OnboardingTemplate::class);

        $data = $request->validate([
            'name' => ['required', 'string'],
            'is_default' => ['nullable', 'boolean'],
            'steps' => ['required', 'array'],
        ]);

        $template = $this->service->createTemplate($data);

        return response()->json(new OnboardingTemplateResource($template), 201);
    }

    public function updateTemplate(Request $request, OnboardingTemplate $template): JsonResponse
    {
        $this->authorize('manage', OnboardingTemplate::class);

        $data = $request->validate([
            'name' => ['sometimes', 'string'],
            'is_default' => ['nullable', 'boolean'],
            'steps' => ['sometimes', 'array'],
        ]);

        return response()->json(new OnboardingTemplateResource($this->service->updateTemplate($template, $data)));
    }

    public function defaultTemplate(): JsonResponse
    {
        $this->authorize('viewAny', OnboardingTemplate::class);

        $template = $this->service->defaultTemplate();

        return response()->json($template ? new OnboardingTemplateResource($template) : null);
    }

    public function listProgress(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', OnboardingTemplate::class);

        return OnboardingProgressResource::collection($this->service->listActiveProgress());
    }

    public function showProgress(Request $request, User $user): JsonResponse
    {
        $policy = app(OnboardingPolicy::class);
        if (! $policy->viewOwn($request->user(), $user)) {
            abort(403);
        }

        $progress = $this->service->progressForUser($user);

        return response()->json($progress ? new OnboardingProgressResource($progress) : null);
    }

    public function startProgress(Request $request, User $user): JsonResponse
    {
        $this->authorize('manage', OnboardingTemplate::class);

        $data = $request->validate([
            'template_id' => ['nullable', 'uuid', 'exists:onboarding_templates,id'],
        ]);

        $progress = $this->service->startForUser($user, $data['template_id'] ?? null);

        return response()->json(new OnboardingProgressResource($progress->load('template')), 201);
    }

    public function completeStep(Request $request, User $user): JsonResponse
    {
        $this->authorize('manage', OnboardingTemplate::class);

        $data = $request->validate([
            'step_index' => ['required', 'integer', 'min:0'],
        ]);

        $progress = $this->service->completeStep($user, $data['step_index'], $request->user());

        return response()->json(new OnboardingProgressResource($progress));
    }
}
