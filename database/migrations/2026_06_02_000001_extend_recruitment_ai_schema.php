<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_vacantes', function (Blueprint $table) {
            if (! Schema::hasColumn('tbl_vacantes', 'requisitos')) {
                $table->text('requisitos')->nullable()->after('descripcion');
            }

            if (! Schema::hasColumn('tbl_vacantes', 'fecha_cierre')) {
                $table->dateTime('fecha_cierre')->nullable()->after('fecha_publicacion');
            }
        });

        Schema::table('tbl_analisis_ia', function (Blueprint $table) {
            if (! Schema::hasColumn('tbl_analisis_ia', 'habilidades_detectadas')) {
                $table->text('habilidades_detectadas')->nullable()->after('observaciones');
            }

            if (! Schema::hasColumn('tbl_analisis_ia', 'estudios_detectados')) {
                $table->text('estudios_detectados')->nullable()->after('habilidades_detectadas');
            }

            if (! Schema::hasColumn('tbl_analisis_ia', 'experiencia_detectada')) {
                $table->text('experiencia_detectada')->nullable()->after('estudios_detectados');
            }

            if (! Schema::hasColumn('tbl_analisis_ia', 'idiomas_score')) {
                $table->decimal('idiomas_score', 5, 2)->default(0)->after('educacion_score');
            }

            if (! Schema::hasColumn('tbl_analisis_ia', 'resultado_json')) {
                $table->longText('resultado_json')->nullable()->after('debilidades');
            }
        });

        Schema::table('tbl_usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('tbl_usuarios', 'correo')) {
                $table->string('correo', 150)->nullable()->after('nombre_usuario');
            }
        });

        Schema::table('tbl_empleados', function (Blueprint $table) {
            if (! Schema::hasColumn('tbl_empleados', 'direccion')) {
                $table->string('direccion', 255)->nullable()->after('telefono');
            }

            if (! Schema::hasColumn('tbl_empleados', 'fecha_salida')) {
                $table->date('fecha_salida')->nullable()->after('fecha_ingreso');
            }
        });
    }

    public function down(): void
    {
        //
    }
};
