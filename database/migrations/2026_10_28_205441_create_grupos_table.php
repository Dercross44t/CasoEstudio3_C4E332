<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('grupos', function (Blueprint $table) {
        $table->id();
        $table->integer('numeroCiclo');
        $table->integer('ano');
        $table->integer('numeroGrupo');
        $table->boolean('abierto');

        $table->foreignId('curso_id')
              ->constrained('cursos');

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('grupos');
}
};
