<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tbl_tests_vacantes')) {
            Schema::create('tbl_tests_vacantes', function (Blueprint $table) {
                $table->id('id_test');
                $table->integer('id_vacante')->index();
                $table->string('titulo', 150);
                $table->text('descripcion')->nullable();
                $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! Schema::hasTable('tbl_test_preguntas')) {
            Schema::create('tbl_test_preguntas', function (Blueprint $table) {
                $table->id('id_pregunta');
                $table->integer('id_test')->index();
                $table->text('pregunta');
                $table->string('tipo', 30)->default('ABIERTA');
                $table->json('opciones')->nullable();
                $table->decimal('puntaje_maximo', 5, 2)->default(100);
                $table->unsignedInteger('orden')->default(1);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! Schema::hasTable('tbl_test_respuestas')) {
            Schema::create('tbl_test_respuestas', function (Blueprint $table) {
                $table->id('id_respuesta');
                $table->integer('id_postulacion')->index();
                $table->integer('id_pregunta')->index();
                $table->longText('respuesta');
                $table->decimal('puntaje_ia', 5, 2)->nullable();
                $table->text('observacion_ia')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->unique(['id_postulacion', 'id_pregunta'], 'unique_respuesta_postulacion_pregunta');
            });
        }

        Schema::table('tbl_analisis_ia', function (Blueprint $table) {
            if (! Schema::hasColumn('tbl_analisis_ia', 'puntuacion_general')) {
                $table->decimal('puntuacion_general', 5, 2)->default(0);
            }

            if (! Schema::hasColumn('tbl_analisis_ia', 'compatibilidad')) {
                $table->decimal('compatibilidad', 5, 2)->default(0);
            }

            if (! Schema::hasColumn('tbl_analisis_ia', 'puntaje_cv')) {
                $table->decimal('puntaje_cv', 5, 2)->default(0);
            }

            if (! Schema::hasColumn('tbl_analisis_ia', 'puntaje_test')) {
                $table->decimal('puntaje_test', 5, 2)->default(0);
            }

            if (! Schema::hasColumn('tbl_analisis_ia', 'analisis_test')) {
                $table->text('analisis_test')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tbl_analisis_ia', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_analisis_ia', 'analisis_test')) {
                $table->dropColumn('analisis_test');
            }

            if (Schema::hasColumn('tbl_analisis_ia', 'puntaje_test')) {
                $table->dropColumn('puntaje_test');
            }

            if (Schema::hasColumn('tbl_analisis_ia', 'puntaje_cv')) {
                $table->dropColumn('puntaje_cv');
            }
        });

        Schema::dropIfExists('tbl_test_respuestas');
        Schema::dropIfExists('tbl_test_preguntas');
        Schema::dropIfExists('tbl_tests_vacantes');
    }
};
