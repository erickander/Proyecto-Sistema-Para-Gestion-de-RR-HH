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
        if (! Schema::hasTable('tbl_vacantes')) {
            Schema::create('tbl_vacantes', function (Blueprint $table) {
                $table->id('id_vacante');
                $table->foreignId('id_departamento')->nullable()->constrained('tbl_departamentos', 'id_departamento')->nullOnDelete();
                $table->string('titulo', 150);
                $table->text('descripcion');
                $table->text('requisitos')->nullable();
                $table->string('tipo_contrato', 50)->nullable();
                $table->decimal('salario_ofrecido', 10, 2)->nullable();
                $table->enum('estado', ['ABIERTA', 'CERRADA', 'PAUSADA'])->default('ABIERTA');
                $table->dateTime('fecha_publicacion')->nullable();
                $table->dateTime('fecha_cierre')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_vacantes');
    }
};
