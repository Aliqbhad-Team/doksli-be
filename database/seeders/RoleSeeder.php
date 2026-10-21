<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Migrasi nama lama ke baru jika ada (staff -> Pengelola, user -> Pengguna)
        $legacyMap = [
            'staff' => ['name' => 'Pengelola', 'description' => 'Pengelola'],
            'user'  => ['name' => 'Pengguna', 'description' => 'Pengguna'],
        ];
        foreach ($legacyMap as $old => $new) {
            $existing = Role::where('name', $old)->first();
            if ($existing && ! Role::where('name', $new['name'])->exists()) {
                $existing->update($new);
            }
        }

        foreach ([
            ['name' => 'admin', 'description' => 'Administrator'],
            ['name' => 'Pengelola', 'description' => 'Pengelola'],
            ['name' => 'Pengguna', 'description' => 'Pengguna'],
        ] as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
