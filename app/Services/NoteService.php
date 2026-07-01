<?php

namespace App\Services;

use App\Enums\NoteVisibility;
use App\Models\Note;
use App\Models\User;

class NoteService
{
    public function listForUser(User $user, array $filters = [])
    {
        $query = Note::query()->with(['creator', 'project', 'team']);

        if ($visibility = ($filters['visibility'] ?? null)) {
            $query->where('visibility', $visibility);
        }
        if ($projectId = ($filters['project_id'] ?? null)) {
            $query->where('project_id', $projectId);
        }
        if ($teamId = ($filters['team_id'] ?? null)) {
            $query->where('team_id', $teamId);
        }

        $query->where(function ($q) use ($user) {
            $q->where('visibility', NoteVisibility::Team)
                ->orWhere('visibility', NoteVisibility::Project)
                ->orWhere(function ($q2) use ($user) {
                    $q2->where('visibility', NoteVisibility::Private)
                        ->where('created_by_user_id', $user->id);
                });
        });

        return $query->orderByDesc('is_pinned')->orderByDesc('updated_at')->get();
    }

    public function myNotes(User $user)
    {
        return Note::query()
            ->where('created_by_user_id', $user->id)
            ->orderByDesc('is_pinned')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function create(User $user, array $data): Note
    {
        return Note::create([
            ...$data,
            'created_by_user_id' => $user->id,
        ]);
    }

    public function pin(Note $note): Note
    {
        $note->update(['is_pinned' => ! $note->is_pinned]);

        return $note->fresh();
    }
}
