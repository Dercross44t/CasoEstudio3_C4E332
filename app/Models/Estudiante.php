<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    /** @use HasFactory<\Database\Factories\EstudianteFactory> */
    use HasFactory;

    public function consejero()
        {
        return $this->belongsTo(Docente::class);
    }

    public function matricula()
    {
        return $this->hasMany(EstudianteMatriculado::class);
    }

    public function grupo()
    {
        return $this->belongsToMany(Grupo::class, 'estudiante_matriculados')->withPivot('nota');
    }
}
