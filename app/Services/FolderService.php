<?php

namespace App\Services;

use App\Models\Folder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FolderService
{
    /**
     * Mengambil hirarki folder menggunakan Recursive Query (CTE)
     */
    public function getFolderTree(?string $rootFolderId = null): array
    {
        $sql = "
            WITH RECURSIVE folder_tree AS (
                -- Anchor member: Ambil folder root (tanpa parent)
                SELECT id, name, parent_folder_id, unit_id, created_by, created_at, 1 AS depth
                FROM folders
                WHERE " . ($rootFolderId ? "id = :root_id" : "parent_folder_id IS NULL") . "

                UNION ALL

                -- Recursive member: Ambil anak-anak folder secara berulang
                SELECT f.id, f.name, f.parent_folder_id, f.unit_id, f.created_by, f.created_at, ft.depth + 1
                FROM folders f
                INNER JOIN folder_tree ft ON f.parent_folder_id = ft.id
            )
            SELECT * FROM folder_tree ORDER BY depth, name;
        ";

        $bindings = $rootFolderId ? ['root_id' => $rootFolderId] : [];

        return DB::select($sql, $bindings);
    }

    /**
     * Hapus folder, seluruh anak/subfolder, serta semua file dokumen secara Berulang.
     */
    public function deleteFolderRecursive(Folder $folder): void
    {
        DB::transaction(function () use ($folder) {
            $this->recursiveDeleteProcess($folder);
        });
    }

    /**
     * Helper privat untuk eksekusi berulang hapus
     */
    private function recursiveDeleteProcess(Folder $folder): void
    {
        // 1. Ambil semua subfolder (children)
        $subfolders = Folder::where('parent_folder_id', $folder->id)->get();

        // 2. Jalankan hapus berulang untuk setiap subfolder lebih dulu (bottom-up)
        foreach ($subfolders as $subfolder) {
            $this->recursiveDeleteProcess($subfolder);
        }

        // 3. Hapus seluruh dokumen dan file fisiknya yang ada di folder ini
        if ($folder->documents()->exists()) {
            foreach ($folder->documents as $document) {
                if ($document->file_path && Storage::exists($document->file_path)) {
                    Storage::delete($document->file_path);
                }
                $document->delete(); // Memicu SoftDeletes / Delete pada Model Document
            }
        }

        // 4. Hapus folder ini sendiri (Memicu FolderObserver audit log)
        $folder->delete();
    }
}