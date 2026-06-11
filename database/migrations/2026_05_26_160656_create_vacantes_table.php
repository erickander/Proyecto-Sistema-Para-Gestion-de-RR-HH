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
        Schema::create('tbl_vacantes', function (Blueprint $table) {
            $table->id('id_vacante');
            $table->string('titulo', 150);
            $table->text('descripcion');
            $table->decimal('salario', 10, 2)->nullable();
            $table->text('requisitos')->nullable();
            $table->enum('estado', ['borrador', 'publicada', 'cerrada', 'cancelada'])->default('borrador');
            $table->date('fecha_publicacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_vacantes');
    }
};
