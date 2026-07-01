<?php

namespace App\Http\Controllers\Api\V1\Hr;

use App\Http\Controllers\Controller;
use App\Http\Resources\Hr\HrDocumentResource;
use App\Models\HrDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DocumentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', HrDocument::class);

        $query = HrDocument::query()->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        return HrDocumentResource::collection($query->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', HrDocument::class);

        $data = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'category' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->store("hr-documents/{$data['user_id']}", 'local');

        $document = HrDocument::create([
            'user_id' => $data['user_id'],
            'category' => $data['category'],
            'title' => $data['title'],
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'uploaded_by_user_id' => $request->user()->id,
        ]);

        return response()->json(new HrDocumentResource($document), 201);
    }

    public function download(HrDocument $document): JsonResponse
    {
        $this->authorize('view', $document);

        $url = URL::temporarySignedRoute(
            'hr.documents.serve',
            now()->addMinutes(30),
            ['document' => $document->id, 'tenant' => app('tenant')->id]
        );

        return response()->json(['url' => $url]);
    }

    public function serve(Request $request, string $document): mixed
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        $doc = HrDocument::withoutGlobalScopes()
            ->where('tenant_id', $request->query('tenant'))
            ->findOrFail($document);

        return Storage::disk('local')->download($doc->file_path, $doc->title);
    }

    public function destroy(HrDocument $document): JsonResponse
    {
        $this->authorize('delete', $document);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return response()->json(null, 204);
    }
}
