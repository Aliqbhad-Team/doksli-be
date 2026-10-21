<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentVersion\DestroyDocumentVersionRequest;
use App\Http\Requests\DocumentVersion\StoreDocumentVersionRequest;
use App\Http\Resources\Api\V1\DocumentVersionResource;
use App\Models\DocumentVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DocumentVersionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = DocumentVersion::query()
            ->with(['uploader', 'document']);

        if ($request->filled('document_id')) {
            $query->where('document_id', $request->query('document_id'));
        }

        if ($request->filled('uploaded_by')) {
            $query->where('uploaded_by', $request->query('uploaded_by'));
        }

        $versions = $query->orderByDesc('version_number')->paginate(25);

        return DocumentVersionResource::collection($versions);
    }

    public function store(StoreDocumentVersionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $documentId = $data['document_id'];

        // Auto-increment version_number if not provided
        if (empty($data['version_number'])) {
            $lastVersion = DocumentVersion::where('document_id', $documentId)->max('version_number') ?? 0;
            $data['version_number'] = $lastVersion + 1;
        }

        // Handle uploaded file if present
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['checksum'] = hash_file('sha256', $file->getRealPath());
            $data['size_bytes'] = $file->getSize();
            $path = $file->store('documents/'.$documentId);
            $data['file_url'] = $path;
            unset($data['file']);
        }

        $version = DocumentVersion::create($data);

        return DocumentVersionResource::make($version->load(['uploader', 'document']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(DocumentVersion $documentVersion): DocumentVersionResource
    {
        return DocumentVersionResource::make($documentVersion->load(['uploader', 'document']));
    }

    public function destroy(DestroyDocumentVersionRequest $request, DocumentVersion $documentVersion): Response
    {
        $documentVersion->delete();

        return response()->noContent();
    }
}
