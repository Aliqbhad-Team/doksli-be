<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Document\DestroyDocumentRequest;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Http\Resources\Api\V1\DocumentResource;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DocumentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Document::query()
            ->with(['folder', 'category', 'owner', 'latestVersion', 'tags']);

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
        $data['owner_id'] = $data['owner_id'] ?? $request->user()?->id;

        if (isset($data['status'])) {
            if ($data['status'] === DocumentStatus::Trashed->value || $data['status'] === DocumentStatus::Trashed) {
                $data['deleted_at'] = $data['deleted_at'] ?? now();
            } else {
                $data['deleted_at'] = null;
            }
        }

        // Ambil tags & file sebelum create document
        $tagsInput = $data['tags'] ?? null;
        $file = $request->file('file');
        $note = $data['note'] ?? null;
        unset($data['tags'], $data['file'], $data['note']);

        $document = Document::create($data);

        // Sync tags (koma-separated → auto-create jika belum ada)
        if ($tagsInput) {
            $tagIds = $this->syncTags($tagsInput, $request->user()?->name);
            $document->tags()->sync($tagIds);
        }

        // Jika ada file, buat document_version pertama
        if ($file) {
            $checksum = hash_file('sha256', $file->getRealPath());
            $sizeBytes = $file->getSize();
            $path = $file->store('documents/' . $document->id);

            DocumentVersion::create([
                'document_id' => $document->id,
                'version_number' => 1,
                'file_url' => $path,
                'checksum' => $checksum,
                'size_bytes' => $sizeBytes,
                'note' => $note ?? 'Unggahan awal',
                'uploaded_by' => $request->user()?->id,
            ]);
        }

        return DocumentResource::make(
            $document->load(['folder', 'category', 'owner', 'tags', 'latestVersion'])
        )->response()->setStatusCode(201);
    }

    public function show(Document $document): DocumentResource
    {
        return DocumentResource::make(
            $document->load(['folder', 'category', 'owner', 'latestVersion', 'versions.uploader', 'tags'])
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

        // Handle tags jika dikirim
        $tagsInput = $data['tags'] ?? null;
        unset($data['tags']);

        $document->update($data);

        if ($tagsInput !== null) {
            $tagIds = $this->syncTags($tagsInput, $request->user()?->name);
            $document->tags()->sync($tagIds);
        }

        return DocumentResource::make($document->load(['folder', 'category', 'owner', 'tags']));
    }

    public function destroy(DestroyDocumentRequest $request, Document $document): Response
    {
        $document->delete();

        return response()->noContent();
    }

    /**
     * Parse koma-separated tags, auto-create jika belum ada di DB.
     *
     * @return array<string> tag IDs
     */
    private function syncTags(string $tagsInput, ?string $userName = null): array
    {
        $tagNames = array_filter(
            array_map('trim', explode(',', $tagsInput))
        );

        $tagIds = [];
        foreach ($tagNames as $name) {
            $tag = Tag::firstOrCreate(
                ['name' => $name],
                [
                    'created_by' => $userName,
                    'updated_by' => $userName,
                ]
            );
            $tagIds[] = $tag->id;
        }

        return $tagIds;
    }
}
