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
        if (! Schema::hasTable('tbl_analisis_ia')) {
            Schema::create('tbl_analisis_ia', function (Blueprint $table) {
                $table->id('id_analisis');
                $table->foreignId('id_postulacion')->unique()->constrained('tbl_postulaciones', 'id_postulacion')->cascadeOnDelete();
                $table->string('modelo_utilizado', 120)->nullable();
                $table->string('version_modelo', 80)->nullable();
                $table->decimal('puntuacion_general', 5, 2)->default(0);
                $table->decimal('puntaje_cv', 5, 2)->default(0);
                $table->decimal('puntaje_test', 5, 2)->default(0);
                $table->decimal('compatibilidad', 5, 2)->default(0);
                $table->decimal('experiencia_score', 5, 2)->default(0);
                $table->decimal('habilidades_score', 5, 2)->default(0);
                $table->decimal('educacion_score', 5, 2)->default(0);
                $table->decimal('idiomas_score', 5, 2)->default(0);
                $table->text('habilidades_detectadas')->nullable();
                $table->text('estudios_detectados')->nullable();
                $table->text('experiencia_detectada')->nullable();
                $table->text('fortalezas')->nullable();
                $table->text('debilidades')->nullable();
                $table->string('recomendacion', 50)->default('Considerar');
                $table->text('analisis_test')->nullable();
                $table->text('observaciones')->nullable();
                $table->longText('resultado_json')->nullable();
                $table->dateTime('fecha_analisis');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_analisis_ia');
    }
};
