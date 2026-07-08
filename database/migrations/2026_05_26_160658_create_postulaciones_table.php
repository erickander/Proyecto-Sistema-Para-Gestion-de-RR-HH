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
        if (! Schema::hasTable('tbl_postulaciones')) {
            Schema::create('tbl_postulaciones', function (Blueprint $table) {
                $table->id('id_postulacion');
                $table->foreignId('id_candidato')->constrained('tbl_candidatos', 'id_candidato')->cascadeOnDelete();
                $table->foreignId('id_vacante')->constrained('tbl_vacantes', 'id_vacante')->cascadeOnDelete();
                $table->dateTime('fecha_postulacion');
                $table->enum('estado', ['RECIBIDA', 'EN_REVISION', 'APROBADA', 'RECHAZADA'])->default('RECIBIDA');
                $table->text('observaciones')->nullable();
                $table->string('token_test', 80)->nullable()->unique();
                $table->dateTime('fecha_test')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_postulaciones');
    }
};
