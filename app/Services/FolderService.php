<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

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
}