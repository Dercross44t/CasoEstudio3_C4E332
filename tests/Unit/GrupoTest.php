
<?php

use PHPUnit\Framework\TestCase;
use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\EstudianteMatriculado;

class GrupoTest extends TestCase
{
    public function test_grupo_contiene_toda_su_informacion(): void
    {
        // Crear el curso
        $curso = new Curso();
        $curso->sigla = 'IF6100';
        $curso->nombreCurso = 'Análisis y Diseño de Sistemas';
        $curso->creditos = 4;

        // Crear docente 1001
        $docente1 = new Docente();
        $docente1->codeEmpleado = 1001;
        $docente1->nombreEmpleado = 'Ana';
        $docente1->apellidosEmpleado = 'Mora Vargas';

        // Crear docente 1002
        $docente2 = new Docente();
        $docente2->codeEmpleado = 1002;
        $docente2->nombreEmpleado = 'Luis';
        $docente2->apellidosEmpleado = 'Rojas Solano';

        // Crear el grupo
        $grupo = new Grupo();
        $grupo->numeroCiclo = 2;
        $grupo->ano = 2026;
        $grupo->numeroGrupo = 1;
        $grupo->abierto = false;

        // Asociar curso y docentes en memoria
        $grupo->setRelation('curso', $curso);

        $grupo->setRelation(
            'docentes',
            collect([$docente1, $docente2])
        );

        // Crear 15 estudiantes matriculados
        $matriculas = collect();

        for ($i = 1; $i <= 15; $i++) {
            $estudiante = new Estudiante();
            $estudiante->carne = 'C' . str_pad($i, 5, '0', STR_PAD_LEFT);
            $estudiante->nombre = 'Estudiante ' . $i;
            $estudiante->apellidos = 'Apellido ' . $i;

            // Asignar consejero
            $estudiante->setRelation('consejero', $docente1);

            // Crear matrícula
            $matricula = new EstudianteMatriculado();
            $matricula->nota = 85.0;

            $matricula->setRelation('estudiante', $estudiante);
            $matricula->setRelation('grupo', $grupo);

            $matriculas->push($matricula);
        }

        $grupo->setRelation(
            'estudiantesMatriculados',
            $matriculas
        );

        // Verificar atributos del grupo
        $this->assertInstanceOf(Grupo::class, $grupo);
        $this->assertEquals(2, $grupo->numeroCiclo);
        $this->assertEquals(2026, $grupo->ano);
        $this->assertEquals(1, $grupo->numeroGrupo);
        $this->assertFalse($grupo->abierto);

        // Verificar curso
        $this->assertEquals('IF6100', $grupo->curso->sigla);

        $this->assertEquals(
            'Análisis y Diseño de Sistemas',
            $grupo->curso->nombreCurso
        );

        $this->assertEquals(4, $grupo->curso->creditos);

        // Verificar docentes
        $this->assertCount(2, $grupo->docentes);

        $this->assertEquals(
            1001,
            $grupo->docentes->first()->codeEmpleado
        );

        $this->assertEquals(
            1002,
            $grupo->docentes->last()->codeEmpleado
        );

        // Verificar estudiantes
        $this->assertCount(
            15,
            $grupo->estudiantesMatriculados
        );

        // Verificar notas y consejero
        foreach ($grupo->estudiantesMatriculados as $matricula) {
            $this->assertEquals(85.0, $matricula->nota);

            $this->assertInstanceOf(
                Estudiante::class,
                $matricula->estudiante
            );

            $this->assertSame(
                $docente1,
                $matricula->estudiante->consejero
            );
        }
    }
}
