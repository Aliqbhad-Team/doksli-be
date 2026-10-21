<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\DestroyGroupRequest;
use App\Http\Requests\Group\StoreGroupRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Http\Resources\Api\V1\GroupResource;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GroupController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return GroupResource::collection(Group::query()->orderBy('name')->paginate(25));
    }

    public function store(StoreGroupRequest $request): JsonResponse
    {
        $group = Group::create($request->validated());

        return GroupResource::make($group)->response()->setStatusCode(201);
    }

    public function show(Group $group): GroupResource
    {
        return GroupResource::make($group->load('users'));
    }

    public function update(UpdateGroupRequest $request, Group $group): GroupResource
    {
        $this->authorize('update', $group);
        $group->update($request->validated());

        return GroupResource::make($group);
    }

    public function destroy(DestroyGroupRequest $request, Group $group): Response
    {
        $group->delete();

        return response()->noContent();
    }
}
