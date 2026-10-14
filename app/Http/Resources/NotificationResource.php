<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

/** @mixin DatabaseNotification */
class NotificationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $payload = is_array($this->data) ? $this->data : [];
        $taskId = $payload['task_id'] ?? null;
        $projectId = $payload['project_id'] ?? null;

        return [
            'id' => $this->id,
            'type' => class_basename($this->type),
            'data' => $this->data,
            'title' => $payload['task_title'] ?? $payload['step_title'] ?? $payload['title'] ?? class_basename($this->type),
            'body' => $payload['message'] ?? $payload['body'] ?? '',
            'action_url' => $payload['action_url'] ?? (
                $taskId && $projectId
                    ? "/projects/{$projectId}?task={$taskId}"
                    : null
            ),
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
