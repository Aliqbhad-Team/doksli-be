<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\DestroyTagRequest;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Http\Resources\Api\V1\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TagController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TagResource::collection(Tag::query()->orderBy('name')->paginate(25));
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = $data['created_by'] ?? ($request->user()?->name ?? $request->user()?->id);

        $tag = Tag::create($data);

        return TagResource::make($tag)->response()->setStatusCode(201);
    }

    public function show(Tag $tag): TagResource
    {
        return TagResource::make($tag);
    }

    public function update(UpdateTagRequest $request, Tag $tag): TagResource
    {
        $data = $request->validated();
        $data['updated_by'] = $data['updated_by'] ?? ($request->user()?->name ?? $request->user()?->id);

        $tag->update($data);

        return TagResource::make($tag);
    }

    public function destroy(DestroyTagRequest $request, Tag $tag): Response
    {
        $tag->delete();

        return response()->noContent();
    }
}
