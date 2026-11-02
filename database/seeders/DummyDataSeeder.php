<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Categories ───
        $categoryNames = [
            'Kontrak',
            'Laporan Keuangan',
            'Surat Masuk',
            'Surat Keluar',
            'Notulen Rapat',
            'Proposal',
            'Berita Acara',
        ];

        $categories = [];
        foreach ($categoryNames as $name) {
            $categories[$name] = Category::firstOrCreate(
                ['name' => $name],
                [
                    'id' => Str::uuid()->toString(),
                    'created_by' => 'Admin',
                    'created_dt' => now(),
                ]
            );
        }

        // ─── Tags ───
        $tagNames = [
            'sewa', 'gedung', 'vendor', 'pengadaan',
            'keuangan', 'pajak', 'sdm', 'karyawan',
            'legal', 'sengketa', 'rapat', 'evaluasi',
            'surat-masuk', 'surat-keluar', 'urgent', 'arsip',
        ];

        $tags = [];
        foreach ($tagNames as $name) {
            $tags[$name] = Tag::firstOrCreate(
                ['name' => $name],
                [
                    'created_by' => 'Admin',
                    'updated_by' => 'Admin',
                ]
            );
        }

        // ─── Ambil folder & user yang sudah ada ───
        $folders = \App\Models\Folder::pluck('id', 'name')->toArray();
        $users = \App\Models\User::pluck('id', 'name')->toArray();

        $firstFolderId = array_values($folders)[0] ?? null;
        $adminId = $users['Admin'] ?? $users['Admin Doksli'] ?? array_values($users)[0] ?? null;
        $pengelolaId = $users['Pengelola Legal'] ?? $adminId;
        $pengunnaId = $users['Pengguna'] ?? $adminId;

        if (!$firstFolderId || !$adminId) {
            $this->command->warn('Tidak ada folder/user, skip seeding documents.');
            return;
        }

        $arsipFolderId = $folders['Arsip 2026'] ?? $firstFolderId;
        $laporanFolderId = $folders['Laporan oktober'] ?? $firstFolderId;

        // ─── Documents ───
        $docsData = [
            [
                'name' => 'Kontrak Sewa Gedung Kantor Pusat 2026',
                'folder_id' => $arsipFolderId,
                'category' => 'Kontrak',
                'owner_id' => $pengelolaId,
                'tags' => ['sewa', 'gedung'],
                'note' => 'Kontrak sewa gedung kantor pusat periode Jan-Des 2026',
            ],
            [
                'name' => 'Perjanjian Kerja Sama Vendor Scanning Dokumen',
                'folder_id' => $arsipFolderId,
                'category' => 'Kontrak',
                'owner_id' => $pengelolaId,
                'tags' => ['vendor', 'pengadaan'],
                'note' => 'PKS dengan PT Scan Digital Indonesia',
            ],
            [
                'name' => 'Laporan Keuangan Kuartal III 2026',
                'folder_id' => $laporanFolderId,
                'category' => 'Laporan Keuangan',
                'owner_id' => $adminId,
                'tags' => ['keuangan', 'pajak'],
                'note' => 'Laporan keuangan Q3 2026 audited',
            ],
            [
                'name' => 'Notulen Rapat Evaluasi Kinerja Oktober 2026',
                'folder_id' => $laporanFolderId,
                'category' => 'Notulen Rapat',
                'owner_id' => $adminId,
                'tags' => ['rapat', 'evaluasi'],
                'note' => 'Notulen rapat evaluasi bulanan',
            ],
            [
                'name' => 'Surat Masuk Dinas Kearsipan No. 045/2026',
                'folder_id' => $arsipFolderId,
                'category' => 'Surat Masuk',
                'owner_id' => $pengunnaId,
                'tags' => ['surat-masuk', 'arsip'],
                'note' => 'Surat dari Dinas Perpustakaan dan Kearsipan',
            ],
            [
                'name' => 'Proposal Pengadaan Sistem Arsip Digital',
                'folder_id' => $arsipFolderId,
                'category' => 'Proposal',
                'owner_id' => $pengelolaId,
                'tags' => ['pengadaan', 'vendor'],
                'note' => 'Proposal pengadaan sistem manajemen arsip digital',
            ],
            [
                'name' => 'Berita Acara Serah Terima Dokumen Lama',
                'folder_id' => $arsipFolderId,
                'category' => 'Berita Acara',
                'owner_id' => $adminId,
                'tags' => ['arsip', 'legal'],
                'note' => 'BA serah terima dokumen fisik ke digital',
            ],
            [
                'name' => 'Surat Keluar Konfirmasi Audit ANRI',
                'folder_id' => $laporanFolderId,
                'category' => 'Surat Keluar',
                'owner_id' => $pengunnaId,
                'tags' => ['surat-keluar', 'arsip'],
                'note' => 'Konfirmasi jadwal audit dari ANRI',
            ],
        ];

        foreach ($docsData as $docData) {
            $categoryId = $categories[$docData['category']]->id;

            $doc = Document::firstOrCreate(
                ['name' => $docData['name']],
                [
                    'folder_id' => $docData['folder_id'],
                    'category_id' => $categoryId,
                    'owner_id' => $docData['owner_id'],
                    'status' => 'active',
                ]
            );

            // Sync tags
            $tagIds = [];
            foreach ($docData['tags'] as $tagName) {
                if (isset($tags[$tagName])) {
                    $tagIds[] = $tags[$tagName]->id;
                }
            }
            $doc->tags()->syncWithoutDetaching($tagIds);

            // Buat version jika belum ada
            if ($doc->versions()->count() === 0) {
                DocumentVersion::create([
                    'document_id' => $doc->id,
                    'version_number' => 1,
                    'file_url' => 'documents/' . $doc->id . '/dummy-v1.pdf',
                    'checksum' => hash('sha256', $docData['name'] . '-v1'),
                    'size_bytes' => rand(102400, 5242880), // 100KB - 5MB
                    'note' => $docData['note'],
                    'uploaded_by' => $docData['owner_id'],
                ]);
            }
        }

        // Tambah versi kedua untuk 2 dokumen pertama
        $firstTwo = Document::orderBy('created_at')->take(2)->get();
        foreach ($firstTwo as $doc) {
            if ($doc->versions()->count() < 2) {
                DocumentVersion::create([
                    'document_id' => $doc->id,
                    'version_number' => 2,
                    'file_url' => 'documents/' . $doc->id . '/revisi-v2.pdf',
                    'checksum' => hash('sha256', $doc->name . '-v2-revisi'),
                    'size_bytes' => rand(153600, 6291456),
                    'note' => 'Revisi setelah review',
                    'uploaded_by' => $adminId,
                ]);
            }
        }

        $this->command->info('Dummy data seeded:');
        $this->command->info('  Categories: ' . Category::count());
        $this->command->info('  Tags: ' . Tag::count());
        $this->command->info('  Documents: ' . Document::count());
        $this->command->info('  DocumentVersions: ' . DocumentVersion::count());
    }
}

