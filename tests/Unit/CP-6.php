
<?php

use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\EstudianteMatriculado;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Un grupo con 15 estudiantes y un docente se abre', function () {

    // Precondición: Curso registrado
    $curso = new Curso();
    $curso->sigla = 'IF6100';
    $curso->nombreCurso = 'Análisis y Diseño de Sistemas';
    $curso->creditos = 4;
    $curso->save();

    // Grupo 01 cerrado
    $grupo = new Grupo();
    $grupo->numeroCiclo = 2;
    $grupo->ano = 2026;
    $grupo->numeroGrupo = 1;
    $grupo->abierto = false;
    $grupo->curso()->associate($curso);
    $grupo->save();

    // Docente 1001
    $docente = new Docente();
    $docente->codeEmpleado = 1001;
    $docente->nombreEmpleado = 'Ana';
    $docente->apellidosEmpleado = 'Mora Vargas';
    $docente->save();

    $grupo->asignarDocente($docente);

    // Generar 15 estudiantes con factory
    $estudiantes = Estudiante::factory()
    ->count(15)
    ->sequence(fn ($sequence) => [
        'carne' => 'C' . str_pad($sequence->index + 1, 5, '0', STR_PAD_LEFT),
        'nombre' => 'Estudiante',
        'apellidos' => 'Prueba'
    ])
    ->make();

    foreach ($estudiantes as $estudiante) {
        $estudiante->consejero()->associate($docente);
        $estudiante->save();

        $matricula = new EstudianteMatriculado();
        $matricula->estudiante()->associate($estudiante);
        $matricula->grupo()->associate($grupo);
        $matricula->save();
    }

    // Consultar si puede abrirse
    expect($grupo->puedeAbrirse())->toBeTrue();

    // Abrir el grupo
    $grupo->abrir();

    // Recargar desde la base de datos
    $grupo->refresh();

    // Verificaciones
    expect($grupo->abierto)->toBeTrue();

    expect($grupo->estudiantesMatriculados()->count())->toBe(15);

    expect($grupo->docentes()->count())->toBe(1);

    // Poscondición
    $this->assertDatabaseHas('grupos', [
        'id' => $grupo->id,
        'abierto' => true
    ]);
});
