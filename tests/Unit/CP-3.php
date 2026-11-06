
<?php

use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Docente;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Un grupo acepta dos docentes', function () {

    // Precondición: Curso y grupo 01 registrados
    $curso = new Curso();
    $curso->sigla = 'IF6100';
    $curso->nombreCurso = 'Análisis y Diseño de Sistemas';
    $curso->creditos = 4;
    $curso->save();

    $grupo = new Grupo();
    $grupo->numeroCiclo = 2;
    $grupo->ano = 2026;
    $grupo->numeroGrupo = 1;
    $grupo->abierto = false;
    $grupo->curso()->associate($curso);
    $grupo->save();

    // Docente 1001
    $docente1001 = new Docente();
    $docente1001->codeEmpleado = 1001;
    $docente1001->nombreEmpleado = 'Ana';
    $docente1001->apellidosEmpleado = 'Mora Vargas';
    $docente1001->save();

    // Docente 1002
    $docente1002 = new Docente();
    $docente1002->codeEmpleado = 1002;
    $docente1002->nombreEmpleado = 'Luis';
    $docente1002->apellidosEmpleado = 'Rojas Solano';
    $docente1002->save();

    // Asignar docentes al grupo
    $grupo->asignarDocente($docente1001);
    $grupo->asignarDocente($docente1002);

    // Leer los docentes asociados
    $docentes = $grupo->docentes;

    // Verificaciones
    expect($docentes)->toHaveCount(2);

    expect(
        $docentes->pluck('codeEmpleado')->sort()->values()->all()
    )->toBe([1001, 1002]);

    // Verificar relación inversa
    expect(
        $docente1001->grupos()->whereKey($grupo->id)->exists()
    )->toBeTrue();

    // Poscondición
    $this->assertDatabaseCount('docente_grupo', 2);
});
