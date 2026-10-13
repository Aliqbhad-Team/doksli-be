<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\DestroyRoleRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\Api\V1\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return RoleResource::collection(Role::query()->orderBy('name')->paginate(25));
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::create($request->validated());

        return RoleResource::make($role)->response()->setStatusCode(201);
    }

    public function show(Role $role): RoleResource
    {
        return RoleResource::make($role);
    }

    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        $this->authorize('update', $role);
        $role->update($request->validated());

        return RoleResource::make($role);
    }

    public function destroy(DestroyRoleRequest $request, Role $role): Response
    {
        $this->authorize('delete', $role);
        abort_if($role->users()->exists(), 409, 'Role masih dipakai oleh user.');

        $role->delete();

        return response()->noContent();
    }
}
