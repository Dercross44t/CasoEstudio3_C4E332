
<?php

use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Estudiante;
use App\Models\Docente;
use App\Models\EstudianteMatriculado;
use Illuminate\Foundation\Testing\RefreshDatabase;

mutates(Grupo::class);

test('CP-07 un grupo sin docentes no puede abrirse', function () {

    $curso = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    $grupo = Grupo::factory()->create([
        'curso_id' => $curso->id,
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1,
        'abierto' => false,
    ]);

    $estudiantes = Estudiante::factory()
        ->count(20)
        ->create();

    foreach ($estudiantes as $estudiante) {
        $grupo->estudiantes()->attach($estudiante->id);
    }

    expect($grupo->docentes()->count())->toBe(0);
    expect($grupo->estudiantes()->count())->toBe(20);
    expect($grupo->abierto)->toBeFalse();

    expect($grupo->puedeAbrirse())->toBeFalse();

    $excepcion = null;

    try {
        $grupo->abrir();
    } catch (DomainException $e) {
        $excepcion = $e;
    }

    expect($excepcion)->toBeInstanceOf(
        DomainException::class
    );

    $grupo->refresh();

    expect($grupo->abierto)->toBeFalse();

    $this->assertDatabaseHas('grupos', [
        'id' => $grupo->id,
        'abierto' => false,
    ]);
});


test('CP-08 un estudiante queda ligado a su profesor consejero', function () {

    
    $docente = Docente::factory()->create([
        'codEmpleado' => 1001,
        'nombreEmpleado' => 'Ana',
        'apellidosEmpleado' => 'Mora Vargas',
    ]);

   
    $estudiante = Estudiante::factory()->create([
        'carne' => 'C12345',
        'nombre' => 'María',
        'apellidos' => 'Solís Quesada',
        'consejero_id' => $docente->id,
    ]);

    $consejero = $estudiante->consejero;

    expect($consejero)->not->toBeNull();
    expect($consejero->codEmpleado)->toBe(1001);
 
    $estudiantes = $docente->estudiantes;

    expect($estudiantes)->toHaveCount(1);

    expect($estudiantes->first()->carne)
        ->toBe('C12345');

    $this->assertDatabaseHas('estudiantes', [
        'id' => $estudiante->id,
        'carne' => 'C12345',
        'consejero_id' => $docente->id,
    ]);
});


test('CP-09 cambiar de consejero reemplaza al anterior, no lo suma', function () {

    $docente1001 = Docente::factory()->create([
        'codEmpleado' => 1001,
        'nombreEmpleado' => 'Ana',
        'apellidosEmpleado' => 'Mora Vargas',
    ]);

    $docente1002 = Docente::factory()->create([
        'codEmpleado' => 1002,
        'nombreEmpleado' => 'Luis',
        'apellidosEmpleado' => 'Rojas Solano',
    ]);

    $estudiante = Estudiante::factory()->create([
        'carne' => 'C12345',
        'nombre' => 'María',
        'apellidos' => 'Solís Quesada',
        'consejero_id' => $docente1001->id,
    ]);

    expect($estudiante->consejero->codEmpleado)->toBe(1001);

    $estudiante->consejero()->associate($docente1002);
    $estudiante->save();

    $docente1001->refresh();
    $docente1002->refresh();
    $estudiante->refresh();

    expect($estudiante->consejero->codEmpleado)->toBe(1002);

    expect($docente1001->estudiantes)->toHaveCount(0);

    expect($docente1002->estudiantes)->toHaveCount(1);

    expect($docente1002->estudiantes->first()->carne)
        ->toBe('C12345');

    expect($estudiante->consejero_id)->toBe($docente1002->id);

    $this->assertDatabaseHas('estudiantes', [
        'id' => $estudiante->id,
        'carne' => 'C12345',
        'consejero_id' => $docente1002->id,
    ]);

    $this->assertDatabaseCount('estudiantes', 1);
});


test('CP-10 al matricular la nota queda vacía', function () {

    $curso = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    $grupo = Grupo::factory()->create([
        'curso_id' => $curso->id,
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1,
        'abierto' => false,
    ]);

    $docente = Docente::factory()->create([
        'codEmpleado' => 1001,
        'nombreEmpleado' => 'Ana',
        'apellidosEmpleado' => 'Mora Vargas',
    ]);

    $estudiante = Estudiante::factory()->create([
        'carne' => 'C12345',
        'nombre' => 'María',
        'apellidos' => 'Solís Quesada',
        'consejero_id' => $docente->id,
    ]);

    expect($grupo->estudiantes()->count())->toBe(0);

    $grupo->estudiantes()->attach($estudiante->id);

    $matricula = $grupo->estudiantes()
        ->first()
        ->pivot;

    expect($matricula)->toBeInstanceOf(
        EstudianteMatriculado::class
    );

    expect($matricula->nota)->toBeNull();

    expect($matricula->grupo_id)->toBe($grupo->id);

    expect($matricula->estudiante_id)->toBe($estudiante->id);

    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->id,
        'estudiante_id' => $estudiante->id,
        'nota' => null,
    ]);

    $this->assertDatabaseCount('estudiantes_matriculados', 1);
});


test('CP-11 registrar la nota al finalizar el curso', function () {

    $curso = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    $grupo = Grupo::factory()->create([
        'curso_id' => $curso->id,
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1,
        'abierto' => false,
    ]);

    $docente = Docente::factory()->create([
        'codEmpleado' => 1001,
        'nombreEmpleado' => 'Ana',
        'apellidosEmpleado' => 'Mora Vargas',
    ]);

    $estudiante = Estudiante::factory()->create([
        'carne' => 'C12345',
        'nombre' => 'María',
        'apellidos' => 'Solís Quesada',
        'consejero_id' => $docente->id,
    ]);

    $otroEstudiante = Estudiante::factory()->create([
        'consejero_id' => $docente->id,
    ]);

    $grupo->estudiantes()->attach([
        $estudiante->id,
        $otroEstudiante->id,
    ]);

    $matriculaInicial = EstudianteMatriculado::where(
        'grupo_id', $grupo->id
    )->where(
        'estudiante_id', $estudiante->id
    )->firstOrFail();

    expect($matriculaInicial->nota)->toBeNull();

    $grupo->registrarNota($estudiante, 8.5);

    $matricula = EstudianteMatriculado::where(
        'grupo_id', $grupo->id
    )->where(
        'estudiante_id', $estudiante->id
    )->firstOrFail();

    expect($matricula->nota)->toBe(8.5);

    expect($matricula->nota)->toBeFloat();

    $otraMatricula = EstudianteMatriculado::where(
        'grupo_id', $grupo->id
    )->where(
        'estudiante_id', $otroEstudiante->id
    )->firstOrFail();

    expect($otraMatricula->nota)->toBeNull();

    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->id,
        'estudiante_id' => $estudiante->id,
        'nota' => 8.5,
    ]);

    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->id,
        'estudiante_id' => $otroEstudiante->id,
        'nota' => null,
    ]);

    $this->assertDatabaseCount('estudiantes_matriculados', 2);
});


test('CP-12 rechazar notas fuera del rango permitido', function (float $nota) {

    $curso = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    $grupo = Grupo::factory()->create([
        'curso_id' => $curso->id,
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1,
        'abierto' => false,
    ]);

    $docente = Docente::factory()->create([
        'codEmpleado' => 1001,
        'nombreEmpleado' => 'Ana',
        'apellidosEmpleado' => 'Mora Vargas',
    ]);

    $estudiante = Estudiante::factory()->create([
        'carne' => 'C12345',
        'nombre' => 'María',
        'apellidos' => 'Solís Quesada',
        'consejero_id' => $docente->id,
    ]);

    $grupo->estudiantes()->attach($estudiante->id);

    $matriculaInicial = EstudianteMatriculado::where(
        'grupo_id', $grupo->id
    )->where(
        'estudiante_id', $estudiante->id
    )->firstOrFail();

    expect($matriculaInicial->nota)->toBeNull();

    expect(
        fn () => $grupo->registrarNota($estudiante, $nota)
    )->toThrow(InvalidArgumentException::class);

    $matricula = EstudianteMatriculado::where(
        'grupo_id', $grupo->id
    )->where(
        'estudiante_id', $estudiante->id
    )->firstOrFail();

    expect($matricula->nota)->toBeNull();

    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->id,
        'estudiante_id' => $estudiante->id,
        'nota' => null,
    ]);

    $this->assertDatabaseCount('estudiantes_matriculados', 1);

})->with([-1, 10.5]);


test('CP-13 un estudiante no puede matricularse dos veces en el mismo grupo', function () {

    $curso = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    $grupo = Grupo::factory()->create([
        'curso_id' => $curso->id,
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1,
        'abierto' => false,
    ]);

    $docente = Docente::factory()->create([
        'codEmpleado' => 1001,
        'nombreEmpleado' => 'Ana',
        'apellidosEmpleado' => 'Mora Vargas',
    ]);

    $estudiante = Estudiante::factory()->create([
        'carne' => 'C12345',
        'nombre' => 'María',
        'apellidos' => 'Solís Quesada',
        'consejero_id' => $docente->id,
    ]);

    $grupo->estudiantes()->attach($estudiante->id);

    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->id,
        'estudiante_id' => $estudiante->id,
        'nota' => null,
    ]);

    expect(
        fn () => $grupo->estudiantes()->attach($estudiante->id)
    )->toThrow(\Illuminate\Database\QueryException::class);

    $cantidadMatriculas = EstudianteMatriculado::where(
        'grupo_id', $grupo->id
    )->where(
        'estudiante_id', $estudiante->id
    )->count();

    expect($cantidadMatriculas)->toBe(1);

    expect($grupo->estudiantes()->count())->toBe(1);

    $this->assertDatabaseCount('estudiantes_matriculados', 1);
});


test('Grupo convierte el atributo abierto a booleano', function () {

    $curso = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    $grupo = Grupo::factory()->create([
        'curso_id' => $curso->id,
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1,
        'abierto' => false,
    ]);

    expect($grupo->fresh()->abierto)->toBeFalse();
    expect($grupo->fresh()->abierto)->toBeBool();

    $grupo->abierto = true;
    $grupo->save();

    expect($grupo->fresh()->abierto)->toBeTrue();
    expect($grupo->fresh()->abierto)->toBeBool();
});
