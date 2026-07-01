<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Services\SurveyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function __construct(private SurveyService $service) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => Survey::query()->with('questions')->orderByDesc('created_at')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'in:survey,quiz,form',
            'is_anonymous' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        return response()->json($this->service->create($request->user(), $data), 201);
    }

    public function show(Survey $survey): JsonResponse
    {
        return response()->json($survey->load('questions'));
    }

    public function update(Request $request, Survey $survey): JsonResponse
    {
        $survey->update($request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]));

        return response()->json($survey->fresh()->load('questions'));
    }

    public function publish(Survey $survey): JsonResponse
    {
        return response()->json($this->service->publish($survey));
    }

    public function close(Survey $survey): JsonResponse
    {
        return response()->json($this->service->close($survey));
    }

    public function addQuestion(Request $request, Survey $survey): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string',
            'type' => 'required|in:text,textarea,single_choice,multiple_choice,rating,number,date',
            'options' => 'nullable|array',
            'is_required' => 'boolean',
            'order_index' => 'integer',
        ]);

        return response()->json($this->service->addQuestion($survey, $data), 201);
    }

    public function updateQuestion(Request $request, SurveyQuestion $surveyQuestion): JsonResponse
    {
        $surveyQuestion->update($request->validate([
            'title' => 'sometimes|string',
            'options' => 'nullable|array',
            'is_required' => 'boolean',
            'order_index' => 'integer',
        ]));

        return response()->json($surveyQuestion->fresh());
    }

    public function deleteQuestion(SurveyQuestion $surveyQuestion): JsonResponse
    {
        $surveyQuestion->delete();

        return response()->json(['message' => 'Question deleted']);
    }

    public function respond(Request $request, Survey $survey): JsonResponse
    {
        $data = $request->validate(['answers' => 'required|array']);

        return response()->json($this->service->respond($survey, $request->user(), $data['answers']), 201);
    }

    public function responses(Survey $survey): JsonResponse
    {
        return response()->json(['data' => $survey->responses()->get()]);
    }

    public function report(Survey $survey): JsonResponse
    {
        return response()->json($this->service->report($survey));
    }
}
