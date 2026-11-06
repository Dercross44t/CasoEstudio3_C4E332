
<?php

use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Docente;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Un grupo rechaza un tercer docente', function () {

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

    // Registrar los tres docentes
    $docente1001 = new Docente();
    $docente1001->codeEmpleado = 1001;
    $docente1001->nombreEmpleado = 'Ana';
    $docente1001->apellidosEmpleado = 'Mora Vargas';
    $docente1001->save();

    $docente1002 = new Docente();
    $docente1002->codeEmpleado = 1002;
    $docente1002->nombreEmpleado = 'Luis';
    $docente1002->apellidosEmpleado = 'Rojas Solano';
    $docente1002->save();

    $docente1003 = new Docente();
    $docente1003->codeEmpleado = 1003;
    $docente1003->nombreEmpleado = 'Carla';
    $docente1003->apellidosEmpleado = 'Jiménez Araya';
    $docente1003->save();

    // Asignar los primeros dos docentes
    $grupo->asignarDocente($docente1001);
    $grupo->asignarDocente($docente1002);

    // Intentar asignar un tercer docente
    $excepcion = null;

    try {
        $grupo->asignarDocente($docente1003);
    } catch (\DomainException $e) {
        $excepcion = $e;
    }

    // Verificar excepción
    expect($excepcion)->toBeInstanceOf(\DomainException::class);

    // Poscondiciones
    expect($grupo->docentes()->count())->toBe(2);

    expect(
        $grupo->docentes()
              ->where('docentes.id', $docente1003->id)
              ->exists()
    )->toBeFalse();

    $this->assertDatabaseCount('docente_grupo', 2);
});
