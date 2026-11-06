
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

    // Crear curso
    $curso = new Curso();
    $curso->sigla = 'IF6100';
    $curso->nombreCurso = 'Análisis y Diseño de Sistemas';
    $curso->creditos = 4;
    $curso->save();

    // Crear docente 1001
    $docente = new Docente();
    $docente->codeEmpleado = 1001;
    $docente->nombreEmpleado = 'Ana';
    $docente->apellidosEmpleado = 'Mora Vargas';
    $docente->save();

    // Crear grupo
    $grupo = new Grupo();
    $grupo->numeroCiclo = 2;
    $grupo->ano = 2026;
    $grupo->numeroGrupo = 1;
    $grupo->abierto = false;
    $grupo->curso()->associate($curso);
    $grupo->save();

    // Asociar docente al grupo
    $grupo->asignarDocente($docente);

    // Crear estudiante
    $estudiante = new Estudiante();
    $estudiante->carne = 'C12345';
    $estudiante->nombre = 'María';
    $estudiante->apellidos = 'Solís Quesada';
    $estudiante->consejero()->associate($docente);
    $estudiante->save();

    // Crear matrícula
    $matricula = new EstudianteMatriculado();
    $matricula->nota = 85.0;
    $matricula->estudiante()->associate($estudiante);
    $matricula->grupo()->associate($grupo);
    $matricula->save();

    // Verificar registros en la base de datos
    $this->assertDatabaseHas('cursos', [
        'id' => $curso->id,
        'sigla' => 'IF6100'
    ]);

    $this->assertDatabaseHas('docentes', [
        'id' => $docente->id,
        'codeEmpleado' => 1001
    ]);

    $this->assertDatabaseHas('grupos', [
        'id' => $grupo->id,
        'curso_id' => $curso->id,
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1,
        'abierto' => false
    ]);

    $this->assertDatabaseHas('estudiantes', [
        'id' => $estudiante->id,
        'carne' => 'C12345',
        'docente_id' => $docente->id
    ]);

    $this->assertDatabaseHas('estudiante_matriculados', [
        'estudiante_id' => $estudiante->id,
        'grupo_id' => $grupo->id,
        'nota' => 85.0
    ]);

    $this->assertDatabaseHas('docente_grupo', [
        'docente_id' => $docente->id,
        'grupo_id' => $grupo->id
    ]);

    // Recuperar el grupo de la base de datos
    $grupoGuardado = Grupo::with(
        'curso',
        'docentes',
        'estudiantesMatriculados.estudiante.consejero'
    )->findOrFail($grupo->id);

    // Verificar relaciones
    expect($grupoGuardado->curso->sigla)->toBe('IF6100');

    expect($grupoGuardado->docentes)->toHaveCount(1);

    expect(
        $grupoGuardado->docentes->first()->codeEmpleado
    )->toBe(1001);

    expect(
        $grupoGuardado->estudiantesMatriculados
    )->toHaveCount(1);

    expect(
        (float) $grupoGuardado
            ->estudiantesMatriculados
            ->first()
            ->nota
    )->toBe(85.0);

    expect(
        $grupoGuardado
            ->estudiantesMatriculados
            ->first()
            ->estudiante
            ->consejero
            ->codeEmpleado
    )->toBe(1001);

});
