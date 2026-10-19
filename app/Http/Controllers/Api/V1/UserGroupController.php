<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\GroupResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserGroupController extends Controller
{
    // GET /api/v1/users/{user}/groups - list groups of a user
    public function indexByUser(User $user): JsonResponse
    {
        $user->load('groups');
        return GroupResource::collection($user->groups)->response();
    }

    // GET /api/v1/groups/{group}/users - list users of a group
    public function indexByGroup(Group $group): JsonResponse
    {
        $group->load('users.role', 'users.unit');
        return UserResource::collection($group->users)->response();
    }

    // POST /api/v1/users/{user}/groups - attach groups
    public function store(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'group_ids' => ['required', 'array', 'min:1'],
            'group_ids.*' => ['uuid', 'exists:groups,id'],
        ]);

        $user->groups()->syncWithoutDetaching($data['group_ids']);
        $user->load('groups');

        return GroupResource::collection($user->groups)->response()->setStatusCode(201);
    }

    // DELETE /api/v1/users/{user}/groups/{group}
    public function destroy(User $user, Group $group): JsonResponse
    {
        $user->groups()->detach($group->id);

        return response()->json(['message' => 'Detached'])->setStatusCode(200);
    }

    // DELETE /api/v1/users/{user}/groups - detach all or selected
    public function destroyMany(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'group_ids' => ['sometimes', 'array'],
            'group_ids.*' => ['uuid', 'exists:groups,id'],
        ]);

        if (isset($data['group_ids'])) {
            $user->groups()->detach($data['group_ids']);
        } else {
            $user->groups()->detach();
        }

        return response()->json(['message' => 'Detached'])->setStatusCode(200);
    }
}
