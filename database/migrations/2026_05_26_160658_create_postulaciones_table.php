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
        Schema::create('tbl_postulaciones', function (Blueprint $table) {
            $table->id('id_postulacion');
            $table->foreignId('id_candidato')->constrained('tbl_candidatos', 'id_candidato')->cascadeOnDelete();
            $table->foreignId('id_vacante')->constrained('tbl_vacantes', 'id_vacante')->cascadeOnDelete();
            $table->date('fecha_postulacion');
            $table->enum('estado', ['recibida', 'en_revision', 'entrevista', 'rechazada', 'contratada'])->default('recibida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_postulaciones');
    }
};
