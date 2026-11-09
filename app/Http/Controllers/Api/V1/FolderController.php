<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\FolderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Support\Str;
use App\Http\Requests\Folder\StoreFolderRequest;
use App\Http\Requests\Folder\UpdateFolderRequest;
use App\Http\Resources\Api\V1\FolderResource;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    // READ: Menampilkan folder dengan opsi Server Pagination atau RECURSIVE TREE via Service
    public function index(FolderService $folderService, Request $request)
    {
        if ($request->boolean('paginate') || $request->boolean('all')) {
            $query = Folder::query()->with('unit')->withCount([
                'documents as documents_count' => function ($q) {
                    $q->whereNull('deleted_at')->where('status', 'active');
                },
                'documents as trashed_documents_count' => function ($q) {
                    $q->where(function ($sq) {
                        $sq->whereNotNull('deleted_at')->orWhere('status', 'trashed');
                    });
                },
            ]);

            if ($request->filled('search')) {
                $query->where('name', 'like', "%{$request->input('search')}%");
            }

            if ($request->boolean('all')) {
                $folders = $query->orderBy('name')->get();
                return FolderResource::collection($folders);
            }

            $perPage = (int) $request->input('per_page', 10);
            $paginated = $query->orderBy('name')->paginate($perPage);
            return FolderResource::collection($paginated);
        }

        $folders = $folderService->getFolderTree();

        return response()->json([
            'data' => $folders
        ]);
    }

    // CREATE: Menambah folder baru (Mendukung pembuatan didalam Folder Induk)
    public function store(StoreFolderRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user(); 
        
        $isAdmin = (isset($user->role->name) && $user->role->name === 'Admin') || $user->role === 'Admin';

        // 1. Jika membuat subfolder (isi parent_folder_id), wariskan unit_id dari folder induk
        if (!empty($data['parent_folder_id'])) {
            $parentFolder = Folder::find($data['parent_folder_id']);
            if ($parentFolder) {
                $data['unit_id'] = $parentFolder->unit_id;
            }
        }

        // 2. Penanganan unit_id jika folder tingkat teratas (root folder)
        if (empty($data['unit_id'])) {
            if ($isAdmin) {
                // Admin: wajib pilih unit_id jika buat root folder
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors'  => ['unit_id' => ['Folder tidak bisa dibuat tanpa unit_id untuk Admin.']]
                ], 422);
            } else {
                // Pengelola: unit_id otomatis = unit milik Pengelola
                $data['unit_id'] = $user->unit_id; 
                
                if (empty($data['unit_id'])) {
                    return response()->json([
                        'message' => 'User ini tidak memiliki unit_id yang valid.'
                    ], 403);
                }
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

    // DELETE: Menghapus folder secara REKURSIF beserta subfolder & dokumennya
    public function destroy(Folder $folder, FolderService $folderService)
    {
        $folderService->deleteFolderRecursive($folder);
        
        return response()->json([
            'message' => 'Folder beserta seluruh subfolder dan dokumen di dalamnya berhasil dihapus.'
        ], 200);
    }
}