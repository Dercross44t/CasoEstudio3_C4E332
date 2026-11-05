<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    /** @use HasFactory<\Database\Factories\DocenteFactory> */
    use HasFactory;

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class);
    }

    public function estudiante()
    {
        return $this->hasMany(Estudiante::class);
    }
}
