<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\DestroyUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $users = User::query()
            ->with(['role', 'unit'])
            ->latest()
            ->orderByDesc('id')
            ->paginate(25);

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            ...Arr::except($data, 'password'),
            'password_hash' => $data['password'],
        ]);

        return UserResource::make($user->load(['role', 'unit']))->response()->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        return UserResource::make($user->load(['role', 'unit']));
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $data = $request->validated();

        if (array_key_exists('password', $data)) {
            $data['password_hash'] = $data['password'];
        }

        $user->update(Arr::except($data, 'password'));

        return UserResource::make($user->load(['role', 'unit']));
    }

    public function destroy(DestroyUserRequest $request, User $user): Response
    {
        $user->delete();

        return response()->noContent();
    }
}
