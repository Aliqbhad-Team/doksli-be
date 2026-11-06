<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\DestroyGroupRequest;
use App\Http\Requests\Group\StoreGroupRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Http\Resources\Api\V1\GroupResource;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GroupController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Group::query()->withCount('users');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->input('search')}%");
        }

        if ($request->boolean('all')) {
            return GroupResource::collection($query->orderBy('name')->get());
        }

        $perPage = (int) $request->input('per_page', 10);
        return GroupResource::collection($query->orderBy('name')->paginate($perPage));
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
        $group->update($request->validated());

        return GroupResource::make($group);
    }

    public function destroy(DestroyGroupRequest $request, Group $group): Response
    {
        $group->users()->detach();
        $group->delete();

        return response()->noContent();
    }
}