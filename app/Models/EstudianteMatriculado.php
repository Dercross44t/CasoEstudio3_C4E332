<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstudianteMatriculado extends Model
{
    /** @use HasFactory<\Database\Factories\EstudianteMatriculadoFactory> */
    use HasFactory;

    public function grupo()
    {
    return $this->belongsTo(Grupo::class);
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }
}
