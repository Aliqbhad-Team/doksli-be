<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        Unit::firstOrCreate(
            ['name' => 'Legal'],
            ['description' => 'Departemen Hukum dan Kepatuhan']
        );

        Unit::firstOrCreate(
            ['name' => 'Finance'],
            ['description' => 'Departemen Keuangan dan Akuntansi']
        );
    }
}
