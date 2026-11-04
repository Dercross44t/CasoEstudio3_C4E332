
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

        // Crear los docentes
        $docente1 = new Docente();
        $docente1->codeEmpleado = 101;
        $docente1->nombreEmpleado = 'Carlos';
        $docente1->apellidosEmpleado = 'Mora';

        $docente2 = new Docente();
        $docente2->codeEmpleado = 102;
        $docente2->nombreEmpleado = 'María';
        $docente2->apellidosEmpleado = 'Vargas';

        // Crear el grupo
        $grupo = new Grupo();
        $grupo->numeroCiclo = 2;
        $grupo->ano = 2026;
        $grupo->numeroGrupo = 1;
        $grupo->abierto = true;

        // Asociar curso y docentes
        $grupo->setRelation('curso', $curso);
        $grupo->setRelation(
            'docente',
            collect([$docente1, $docente2])
        );

        // Crear 15 estudiantes matriculados
        $matriculas = collect();

        for ($i = 1; $i <= 15; $i++) {
            $estudiante = new Estudiante();
            $estudiante->carne = 'C' . str_pad($i, 5, '0', STR_PAD_LEFT);
            $estudiante->nombre = 'Estudiante ' . $i;
            $estudiante->apellidos = 'Apellido ' . $i;

            // Asignar profesor consejero
            $estudiante->setRelation('consejero', $docente1);

            // Crear matrícula con su nota
            $matricula = new EstudianteMatriculado();
            $matricula->nota = 85.0;

            $matricula->setRelation('estudiante', $estudiante);

            $matriculas->push($matricula);
        }

        $grupo->setRelation(
            'estudiantesMatriculado',
            $matriculas
        );

        // Verificar información del grupo
        $this->assertInstanceOf(Grupo::class, $grupo);
        $this->assertEquals(2, $grupo->numeroCiclo);
        $this->assertEquals(2026, $grupo->ano);
        $this->assertEquals(1, $grupo->numeroGrupo);
        $this->assertTrue($grupo->abierto);

        // Verificar el curso
        $this->assertEquals('IF6100', $grupo->curso->sigla);
        $this->assertEquals(
            'Análisis y Diseño de Sistemas',
            $grupo->curso->nombreCurso
        );

        // Verificar los dos docentes
        $this->assertCount(2, $grupo->docente);

        // Verificar los 15 estudiantes
        $this->assertCount(
            15,
            $grupo->estudiantesMatriculado
        );

        // Verificar nota y profesor consejero
        foreach ($grupo->estudiantesMatriculado as $matricula) {
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
