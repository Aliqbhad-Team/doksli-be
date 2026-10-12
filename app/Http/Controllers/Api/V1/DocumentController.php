<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Document\DestroyDocumentRequest;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Http\Resources\Api\V1\DocumentResource;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DocumentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Document::query()
            ->with(['folder', 'category', 'owner', 'latestVersion']);

        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->query('folder_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->query('owner_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $documents = $query->latest('created_at')->paginate(25);

        return DocumentResource::collection($documents);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['status'])) {
            if ($data['status'] === DocumentStatus::Trashed->value || $data['status'] === DocumentStatus::Trashed) {
                $data['deleted_at'] = $data['deleted_at'] ?? now();
            } else {
                $data['deleted_at'] = null;
            }
        }

        $document = Document::create($data);

        return DocumentResource::make($document->load(['folder', 'category', 'owner']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Document $document): DocumentResource
    {
        return DocumentResource::make(
            $document->load(['folder', 'category', 'owner', 'latestVersion', 'versions.uploader'])
        );
    }

    public function update(UpdateDocumentRequest $request, Document $document): DocumentResource
    {
        $data = $request->validated();

        if (isset($data['status'])) {
            if ($data['status'] === DocumentStatus::Trashed->value || $data['status'] === DocumentStatus::Trashed) {
                $data['deleted_at'] = $data['deleted_at'] ?? now();
            } else {
                $data['deleted_at'] = null;
            }
        }

        $document->update($data);

        return DocumentResource::make($document->load(['folder', 'category', 'owner']));
    }

    public function destroy(DestroyDocumentRequest $request, Document $document): Response
    {
        $document->delete();

        return response()->noContent();
    }
}
