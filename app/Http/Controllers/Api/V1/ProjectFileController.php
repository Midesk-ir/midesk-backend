<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectFileController extends Controller
{
    public function index(Request $request, Project $project): JsonResponse
    {
        $query = ProjectFile::query()->where('project_id', $project->id);
        if ($folder = $request->query('folder')) {
            $query->where('folder_path', $folder);
        }

        return response()->json(['data' => $query->orderByDesc('created_at')->get()]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $request->validate(['file' => 'required|file|max:51200', 'folder_path' => 'nullable|string']);
        $file = $request->file('file');
        $path = $file->store('projects/'.app('tenant')->id, 'local');

        $record = ProjectFile::create([
            'project_id' => $project->id,
            'folder_path' => $request->input('folder_path'),
            'name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'uploaded_by_user_id' => $request->user()->id,
            'created_at' => now(),
        ]);

        return response()->json($record, 201);
    }

    public function download(ProjectFile $file): BinaryFileResponse
    {
        return response()->download(Storage::disk('local')->path($file->file_path), $file->name);
    }

    public function destroy(ProjectFile $file): JsonResponse
    {
        Storage::disk('local')->delete($file->file_path);
        $file->delete();

        return response()->json(['message' => 'File deleted']);
    }

    public function newVersion(Request $request, ProjectFile $file): JsonResponse
    {
        $request->validate(['file' => 'required|file|max:51200']);
        $upload = $request->file('file');
        $path = $upload->store('projects/'.app('tenant')->id, 'local');
        Storage::disk('local')->delete($file->file_path);

        $file->update([
            'name' => $upload->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $upload->getSize(),
            'mime_type' => $upload->getClientMimeType(),
            'version' => $file->version + 1,
        ]);

        return response()->json($file->fresh());
    }
}
