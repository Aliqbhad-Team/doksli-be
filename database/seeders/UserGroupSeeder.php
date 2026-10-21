<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserGroupSeeder extends Seeder
{
    public function run(): void
    {
        $pengelola = User::where('email', 'pengelola.legal@gmail.com')->first();
        $pengguna = User::where('email', 'pengguna@gmail.com')->first();

        $grupAudit = Group::where('name', 'Grup Audit')->first();
        $grupReviewer = Group::where('name', 'Grup Reviewer')->first();

        // Assign pengelola to Grup Audit + Grup Reviewer
        if ($pengelola && $grupAudit) {
            $pengelola->groups()->syncWithoutDetaching([$grupAudit->id]);
        }

        if ($pengelola && $grupReviewer) {
            $pengelola->groups()->syncWithoutDetaching([$grupReviewer->id]);
        }

        // Assign pengguna to Grup Audit
        if ($pengguna && $grupAudit) {
            $pengguna->groups()->syncWithoutDetaching([$grupAudit->id]);
        }
    }
}
