<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Str;
use App\Http\Requests\Folder\StoreFolderRequest;
use App\Http\Requests\Folder\UpdateFolderRequest;
use App\Http\Resources\Api\V1\FolderResource;

class FolderController extends Controller
{
    // READ: Menampilkan semua folder
    public function index()
    {
        return FolderResource::collection(Folder::all());
    }

    // CREATE: Menambah folder baru 
    public function store(StoreFolderRequest $request)
    {
    $data = $request->validated();
    $data['id'] = Str::uuid()->toString();
    $data['created_at'] = now();
    
    // JWT mendeteksi siapa yang sedang login
    $data['created_by'] = Auth::id(); 

    // Simpan folder ke database
    $folder = Folder::create($data);

    // Panggil CCTV AuditService untuk merekam aktivitas
    AuditService::log('CREATE', 'Folder', $folder->id);

    return FolderResource::make($folder)->response()->setStatusCode(201);
    }

    // READ: Menampilkan satu folder spesifik
    public function show(Folder $folder)
    {
        return FolderResource::make($folder);
    }

    // UPDATE: Mengubah data folder
    public function update(UpdateFolderRequest $request, Folder $folder)
    {
        $folder->update($request->validated());
        
        return FolderResource::make($folder);
    }

    // DELETE: Menghapus folder
    public function destroy(Folder $folder)
    {
        $folder->delete();
        
        return response()->noContent();
    }
}