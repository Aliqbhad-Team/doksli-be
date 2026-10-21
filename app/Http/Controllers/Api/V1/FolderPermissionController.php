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
    // Ambil data yang sudah divalidasi
    $data = $request->validated();

    // 1. Ambil ID user yang sedang login via JWT
    $user = $request->user();

    // 2. Jika tidak ada user yang login (unauthorized), tolak requestnya
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // 3. Otomatis isi granted_by dengan ID user yang sedang login
    $data['granted_by'] = $user->id;
    
    // (granted_at tidak perlu diset manual karena di migration sudah pakai useCurrent())

    // 4. Simpan ke database
    $permission = FolderPermission::create($data);

    // 5. Kembalikan response sukses
    return FolderPermissionResource::make($permission)->response()->setStatusCode(201);
}

    public function show(FolderPermission $folderPermission)
    {
        return FolderPermissionResource::make($folderPermission);
    }

    public function update(UpdateFolderPermissionRequest $request, FolderPermission $folderPermission)
    {
        $folderPermission->update($request->validated());
        return FolderPermissionResource::make($folderPermission);
    }

    public function destroy(FolderPermission $folderPermission)
    {
        $folderPermission->delete();
        return response()->noContent();
    }
}