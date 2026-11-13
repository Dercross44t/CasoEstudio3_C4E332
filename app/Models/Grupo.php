<?php

namespace App\Models;

use DomainException;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grupo extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'abierto' => 'boolean',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(
            Docente::class,
            'docente_grupo',
            'grupo_id',
            'docente_id'
        );
    }

    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(
            Estudiante::class,
            'estudiantes_matriculados',
            'grupo_id',
            'estudiante_id'
        )
        ->using(EstudianteMatriculado::class)
        ->withPivot('nota')
        ->withTimestamps();
    }

    public function estudiantesMatriculados(): HasMany
    {
        return $this->hasMany(
            EstudianteMatriculado::class,
            'grupo_id'
        );
    }

    public function asignarDocente(Docente $docente): void
    {
        if (
            $this->docentes()
                ->whereKey($docente->id)
                ->exists()
        ) {
            throw new DomainException(
                'El docente ya está asignado al grupo.'
            );
        }

        if ($this->docentes()->count() >= 2) {
            throw new DomainException(
                'El grupo no puede tener más de dos docentes.'
            );
        }

        $this->docentes()->attach($docente->id);
    }

    public function puedeAbrirse(): bool
    {
        $cantidadDocentes = $this->docentes()->count();

        $cantidadEstudiantes =
            $this->estudiantes()->count();

        return $cantidadDocentes >= 1
            && $cantidadDocentes <= 2
            && $cantidadEstudiantes >= 15;
    }

    public function abrir(): void
    {
        if (!$this->puedeAbrirse()) {
            throw new DomainException(
                'El grupo no cumple los requisitos para abrirse.'
            );
        }

        $this->abierto = true;
        $this->save();
    }

    public function registrarNota(
        Estudiante $estudiante,
        float $nota
    ): void {
        if (!is_finite($nota) || $nota < 0 || $nota > 10) {
            throw new InvalidArgumentException(
                'La nota debe estar entre 0 y 10.'
            );
        }

        if (
            !$this->estudiantes()
                ->whereKey($estudiante->id)
                ->exists()
        ) {
            throw new DomainException(
                'El estudiante no está matriculado en el grupo.'
            );
        }

        $this->estudiantes()->updateExistingPivot(
            $estudiante->id,
            ['nota' => $nota]
        );
    }
}
