<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Services\NoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function __construct(private NoteService $noteService) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->noteService->listForUser($request->user(), $request->all())]);
    }

    public function my(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->noteService->myNotes($request->user())]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'color' => 'nullable|string',
            'visibility' => 'in:private,team,project',
            'team_id' => 'nullable|uuid',
            'project_id' => 'nullable|uuid',
            'task_id' => 'nullable|uuid',
        ]);

        return response()->json($this->noteService->create($request->user(), $data), 201);
    }

    public function show(Note $note): JsonResponse
    {
        return response()->json($note);
    }

    public function update(Request $request, Note $note): JsonResponse
    {
        $note->update($request->validate([
            'title' => 'sometimes|string|max:255',
            'body' => 'sometimes|string',
            'color' => 'nullable|string',
            'visibility' => 'in:private,team,project',
        ]));

        return response()->json($note->fresh());
    }

    public function destroy(Note $note): JsonResponse
    {
        $note->delete();

        return response()->json(['message' => 'Note deleted']);
    }

    public function pin(Note $note): JsonResponse
    {
        return response()->json($this->noteService->pin($note));
    }
}
