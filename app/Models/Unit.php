<?php

namespace App\Models;

use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description'])]
class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use HasFactory, HasUuids;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
