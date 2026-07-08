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
        if (! Schema::hasTable('tbl_candidatos')) {
            Schema::create('tbl_candidatos', function (Blueprint $table) {
                $table->id('id_candidato');
                $table->string('nombres', 120);
                $table->string('apellidos', 120);
                $table->string('cedula', 20)->nullable();
                $table->string('correo', 150);
                $table->string('telefono', 30)->nullable();
                $table->string('direccion', 255)->nullable();
                $table->string('cv_url')->nullable();
                $table->dateTime('fecha_registro')->nullable();
                $table->enum('estado', ['ACTIVO', 'DESCARTADO', 'CONTRATADO'])->default('ACTIVO');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_candidatos');
    }
};
