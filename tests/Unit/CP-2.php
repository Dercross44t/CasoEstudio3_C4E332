
<?php

use App\Models\Curso;
use App\Models\Grupo;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Un curso tiene varios grupos y se distinguen los abiertos', function () {

    // Precondición: Curso IF6100 registrado
    $curso = new Curso();
    $curso->sigla = 'IF6100';
    $curso->nombreCurso = 'Análisis y Diseño de Sistemas';
    $curso->creditos = 4;
    $curso->save();

    // Crear los grupos 1, 2 y 3
    for ($i = 1; $i <= 3; $i++) {
        $grupo = new Grupo();
        $grupo->numeroCiclo = 2;
        $grupo->ano = 2026;
        $grupo->numeroGrupo = $i;
        $grupo->abierto = ($i <= 2);
        $grupo->curso()->associate($curso);
        $grupo->save();
    }

    // Consultar los grupos del curso
    $grupos = $curso->grupos;

    // Filtrar los grupos abiertos
    $gruposAbiertos = $grupos->where('abierto', true);

    // Verificaciones
    expect($grupos)->toHaveCount(3);
    expect($gruposAbiertos)->toHaveCount(2);

    expect(
        $gruposAbiertos->pluck('numeroGrupo')->sort()->values()->all()
    )->toBe([1, 2]);

    // Poscondición
    expect(
        Grupo::where('curso_id', $curso->id)->count()
    )->toBe(3);

    $this->assertDatabaseCount('grupos', 3);
});
