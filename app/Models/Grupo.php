<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    /** @use HasFactory<\Database\Factories\GrupoFactory> */
    use HasFactory;

    protected $casts = [
        'abierto' => 'boolean',
    ];
    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function docentes()
    {
        return $this->belongsToMany(Docente::class);
    }

     public function estudiantesMatriculados()
    {
        return $this->hasMany(EstudianteMatriculado::class);
    }

    
public function asignarDocente(Docente $docente)
{
    if ($this->docentes()->whereKey($docente->id)->exists()) {
        throw new \DomainException('El docente ya está asignado al grupo.');
    }

    if ($this->docentes()->count() >= 2) {
        throw new \DomainException('El grupo no puede tener más de dos docentes.');
    }

    $this->docentes()->attach($docente->id);
}

public function puedeAbrirse(): bool
{
    return $this->docentes()->count() >= 1
        && $this->docentes()->count() <= 2
        && $this->estudiantesMatriculados()->count() >= 15;
}

public function abrir()
{
    if (!$this->puedeAbrirse()) {
        throw new \DomainException('El grupo no cumple los requisitos para abrirse.');
    }

    $this->abierto = true;
    $this->save();
}

}
