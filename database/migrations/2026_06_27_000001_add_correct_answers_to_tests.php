<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tbl_test_preguntas') || ! Schema::hasTable('tbl_test_respuestas')) {
            return;
        }

        Schema::table('tbl_test_preguntas', function (Blueprint $table) {
            if (! Schema::hasColumn('tbl_test_preguntas', 'respuesta_correcta')) {
                $table->string('respuesta_correcta', 255)->nullable();
            }
        });

        Schema::table('tbl_test_respuestas', function (Blueprint $table) {
            if (! Schema::hasColumn('tbl_test_respuestas', 'es_correcta')) {
                $table->boolean('es_correcta')->default(false);
            }

            if (! Schema::hasColumn('tbl_test_respuestas', 'puntaje_test')) {
                $table->decimal('puntaje_test', 5, 2)->default(0);
            }
        });

        DB::table('tbl_test_preguntas')
            ->whereNull('respuesta_correcta')
            ->orderBy('id_pregunta')
            ->get()
            ->each(function ($pregunta) {
                $opciones = json_decode($pregunta->opciones ?? '[]', true);

                if (is_array($opciones) && count($opciones) > 0) {
                    DB::table('tbl_test_preguntas')
                        ->where('id_pregunta', $pregunta->id_pregunta)
                        ->update(['respuesta_correcta' => $opciones[0]]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('tbl_test_respuestas', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_test_respuestas', 'puntaje_test')) {
                $table->dropColumn('puntaje_test');
            }

            if (Schema::hasColumn('tbl_test_respuestas', 'es_correcta')) {
                $table->dropColumn('es_correcta');
            }
        });

        Schema::table('tbl_test_preguntas', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_test_preguntas', 'respuesta_correcta')) {
                $table->dropColumn('respuesta_correcta');
            }
        });
    }
};
