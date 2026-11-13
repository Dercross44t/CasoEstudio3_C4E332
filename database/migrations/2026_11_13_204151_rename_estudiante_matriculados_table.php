<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename(
            'estudiante_matriculados',
            'estudiantes_matriculados'
        );
    }

    public function down(): void
    {
        Schema::rename(
            'estudiantes_matriculados',
            'estudiante_matriculados'
        );
    }
};
