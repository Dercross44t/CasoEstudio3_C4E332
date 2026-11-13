<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Docente extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(
            Grupo::class,
            'docente_grupo',
            'docente_id',
            'grupo_id'
        );
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(
            Estudiante::class,
            'consejero_id'
        );
    }
}
