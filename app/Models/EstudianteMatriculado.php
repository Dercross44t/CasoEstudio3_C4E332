<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstudianteMatriculado extends Pivot
{
    use HasFactory;

    protected $table = 'estudiantes_matriculados';

    // La tabla original tiene una columna id autoincremental.
    public $incrementing = true;

    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'nota' => 'float',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
}
