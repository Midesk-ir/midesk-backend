<?php

namespace App\Services;

use App\Enums\SurveyStatus;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;

class SurveyService
{
    public function create(User $user, array $data): Survey
    {
        return Survey::create([
            ...$data,
            'created_by_user_id' => $user->id,
            'status' => SurveyStatus::Draft,
        ]);
    }

    public function publish(Survey $survey): Survey
    {
        $survey->update(['status' => SurveyStatus::Active]);

        return $survey->fresh();
    }

    public function close(Survey $survey): Survey
    {
        $survey->update(['status' => SurveyStatus::Closed]);

        return $survey->fresh();
    }

    public function addQuestion(Survey $survey, array $data): SurveyQuestion
    {
        return SurveyQuestion::create([
            ...$data,
            'survey_id' => $survey->id,
        ]);
    }

    public function respond(Survey $survey, ?User $user, array $answers): SurveyResponse
    {
        return SurveyResponse::create([
            'survey_id' => $survey->id,
            'respondent_user_id' => $survey->is_anonymous ? null : $user?->id,
            'answers' => $answers,
            'submitted_at' => now(),
        ]);
    }

    public function report(Survey $survey): array
    {
        $responses = $survey->responses()->get();
        $questions = $survey->questions;

        return [
            'total_responses' => $responses->count(),
            'questions' => $questions->map(fn ($q) => [
                'id' => $q->id,
                'title' => $q->title,
                'answers' => $responses->pluck('answers')->flatten(1)
                    ->where('question_id', $q->id)->pluck('value'),
            ]),
        ];
    }
}
