<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\FolderService;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Support\Str;
use App\Http\Requests\Folder\StoreFolderRequest;
use App\Http\Requests\Folder\UpdateFolderRequest;
use App\Http\Resources\Api\V1\FolderResource;

class FolderController extends Controller
{
    // READ: Menampilkan semua folder dengan RECURSIVE QUERY via Service
    public function index(FolderService $folderService)
    {
        $folders = $folderService->getFolderTree();

        return response()->json([
            'data' => $folders
        ]);
    }

    // CREATE: Menambah folder baru 
    public function store(StoreFolderRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user(); 
        
        // Pengecekan role yang lebih aman (mendukung string 'Admin' atau relasi object)
        $isAdmin = (isset($user->role->name) && $user->role->name === 'Admin') || $user->role === 'Admin';
    
        if ($isAdmin) {
            // Admin: wajib pilih unit_id
            if (empty($data['unit_id'])) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors'  => ['unit_id' => ['Folder tidak bisa dibuat tanpa unit_id untuk Admin.']]
                ], 422);
            }
        } else {
            // Pengelola: unit_id otomatis = unit Pengelola (override)
            $data['unit_id'] = $user->unit_id; 
            
            // Validasi darurat jika data unit user ternyata kosong di database
            if (empty($data['unit_id'])) {
                return response()->json([
                    'message' => 'User ini tidak memiliki unit_id yang valid.'
                ], 403);
            }
        }

        // Siapkan data tambahan
        $data['id'] = Str::uuid()->toString();
        $data['created_by'] = $user->id;

        $folder = Folder::create($data);

        return FolderResource::make($folder)->response()->setStatusCode(201);
    }

    // READ: Menampilkan satu folder spesifik beserta seluruh sub-foldernya
    public function show(Folder $folder)
    {
        $folder->load('allChildren');

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