<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FolderPermission;
use App\Models\User;
use Illuminate\Support\Str;
use App\Http\Requests\FolderPermission\StoreFolderPermissionRequest;
use App\Http\Requests\FolderPermission\UpdateFolderPermissionRequest;
use App\Http\Resources\Api\V1\FolderPermissionResource;

class FolderPermissionController extends Controller
{
    public function index()
    {
        return FolderPermissionResource::collection(FolderPermission::all());
    }

    public function store(StoreFolderPermissionRequest $request)
    {
        $data = $request->validated();
        
        // Buat UUID otomatis dan set waktu
        $data['id'] = Str::uuid()->toString();
        $data['granted_at'] = now();
        
        // Gunakan akun user pertama untuk mengisi foreign key 'granted_by'
        $user = User::first();
        $data['granted_by'] = $user ? $user->id : Str::uuid()->toString();

        $permission = FolderPermission::create($data);

        return FolderPermissionResource::make($permission)->response()->setStatusCode(201);
    }

    public function show(FolderPermission $folderPermission)
    {
        return FolderPermissionResource::make($folderPermission);
    }

    public function update(UpdateFolderPermissionRequest $request, FolderPermission $folderPermission)
    {
        // Pastikan authorize() di UpdateFolderPermissionRequest sudah diubah jadi true
        $folderPermission->update($request->validated());
        return FolderPermissionResource::make($folderPermission);
    }

    public function destroy(FolderPermission $folderPermission)
    {
        $folderPermission->delete();
        return response()->noContent();
    }
}