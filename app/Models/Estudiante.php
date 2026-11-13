<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estudiante extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function consejero(): BelongsTo
    {
        return $this->belongsTo(
            Docente::class,
            'consejero_id'
        );
    }

    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(
            Grupo::class,
            'estudiantes_matriculados',
            'estudiante_id',
            'grupo_id'
        )
        ->using(EstudianteMatriculado::class)
        ->withPivot('nota')
        ->withTimestamps();
    }

    // Compatibilidad con el método anterior.
    public function grupo(): BelongsToMany
    {
        return $this->grupos();
    }

    public function matricula(): HasMany
    {
        return $this->hasMany(
            EstudianteMatriculado::class,
            'estudiante_id'
        );
    }
}
