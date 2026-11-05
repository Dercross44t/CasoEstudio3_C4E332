<?php

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\EstudianteMatriculado;
use App\Models\Grupo;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guarda un grupo con su curso docente estudiante y matricula y permite recuperarlos', function () {
    $curso = new Curso();
    $curso->sigla = 'IF6100';
    $curso->nombreCurso = 'Análisis y Diseño de Sistemas';
    $curso->creditos = 4;
    $curso->save();

    $docente = new Docente();
    $docente->codeEmpleado = 101;
    $docente->nombreEmpleado = 'Carlos';
    $docente->apellidosEmpleado = 'Mora';
    $docente->save();

    $grupo = new Grupo();
    $grupo->numeroCiclo = 2;
    $grupo->ano = 2026;
    $grupo->numeroGrupo = 1;
    $grupo->abierto = true;
    $grupo->curso()->associate($curso);
    $grupo->save();

    $estudiante = new Estudiante();
    $estudiante->carne = 'C00001';
    $estudiante->nombre = 'Estudiante 1';
    $estudiante->apellidos = 'Apellido 1';
    $estudiante->consejero()->associate($docente);
    $estudiante->save();

    $matricula = new EstudianteMatriculado();
    $matricula->nota = 85.0;
    $matricula->estudiante()->associate($estudiante);
    $matricula->grupo()->associate($grupo);
    $matricula->save();

    // Verificar registros almacenados en la base de datos.
    $this->assertDatabaseHas('cursos', ['id' => $curso->id, 'sigla' => 'IF6100']);
    $this->assertDatabaseHas('docentes', ['id' => $docente->id, 'codeEmpleado' => 101]);
    $this->assertDatabaseHas('grupos', [
        'id' => $grupo->id,
        'curso_id' => $curso->id,
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1,
    ]);
    $this->assertDatabaseHas('estudiantes', [
        'id' => $estudiante->id,
        'carne' => 'C00001',
        'docente_id' => $docente->id,
    ]);
    $this->assertDatabaseHas('estudiante_matriculados', [
        'estudiante_id' => $estudiante->id,
        'grupo_id' => $grupo->id,
        'nota' => 85.0,
    ]);

    // Consultar de nuevo desde la base de datos y comprobar relaciones.
    $grupoGuardado = Grupo::with('curso', 'estudiantesMatriculados.estudiante.consejero')
        ->findOrFail($grupo->id);

    expect($grupoGuardado->curso->sigla)->toBe('IF6100')
        ->and($grupoGuardado->estudiantesMatriculados)->toHaveCount(1)
        ->and((float) $grupoGuardado->estudiantesMatriculados->first()->nota)->toBe(85.0)
        ->and($grupoGuardado->estudiantesMatriculados->first()->estudiante->consejero->codeEmpleado)->toBe(101);
});
