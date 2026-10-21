<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $legalUnit = Unit::where('name', 'Legal')->first();
        $financeUnit = Unit::where('name', 'Finance')->first();

        if ($legalUnit) {
            Group::firstOrCreate(
                ['name' => 'Grup Audit'],
                ['description' => 'Tim audit legal dan compliance']
            );

            Group::firstOrCreate(
                ['name' => 'Grup Reviewer'],
                ['description' => 'Tim review dokumen legal']
            );
        }

        if ($financeUnit) {
            Group::firstOrCreate(
                ['name' => 'Grup Manager'],
                ['description' => 'Manager finance']
            );
        }
    }
}
