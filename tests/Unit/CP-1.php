
<?php

use App\Models\Curso;
use App\Models\Grupo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('Registrar un grupo con toda su información y su curso', function () {

    // Precondición: Curso IF6100 registrado
    $curso = new Curso();
    $curso->sigla = 'IF6100';
    $curso->nombreCurso = 'Análisis y Diseño de Sistemas';
    $curso->creditos = 4;
    $curso->save();

    // Paso 1: Crear el grupo 01 asociado al curso IF6100
    $grupo = new Grupo();
    $grupo->numeroCiclo = 2;
    $grupo->ano = 2026;
    $grupo->numeroGrupo = 1;
    $grupo->abierto = true;
    $grupo->curso()->associate($curso);
    $grupo->save();

    // Paso 2: Recuperar el grupo de la base de datos
    $grupoRecuperado = Grupo::find($grupo->id);

    // Paso 3: Leer la relación con el curso
    $cursoRecuperado = $grupoRecuperado->curso;

    // Verificar los cuatro atributos del grupo
    expect($grupoRecuperado->numeroCiclo)->toBe(2);
    expect($grupoRecuperado->ano)->toBe(2026);
    expect($grupoRecuperado->numeroGrupo)->toBe(1);
    expect($grupoRecuperado->abierto)->toBeTrue();

    // Verificar que abierto sea booleano
    expect($grupoRecuperado->abierto)->toBeBool();

    // Verificar el curso asociado
    expect($cursoRecuperado->sigla)->toBe('IF6100');
    expect($cursoRecuperado->creditos)->toBe(4);

    // Poscondición: La tabla grupos tiene un registro
    $this->assertDatabaseCount('grupos', 1);

});
