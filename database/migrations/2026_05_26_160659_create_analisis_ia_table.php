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
        Schema::create('tbl_analisis_ia', function (Blueprint $table) {
            $table->id('id_analisis');
            $table->foreignId('id_postulacion')->unique()->constrained('tbl_postulaciones', 'id_postulacion')->cascadeOnDelete();
            $table->decimal('puntaje_ia', 5, 2)->default(0);
            $table->text('habilidades_detectadas')->nullable();
            $table->text('experiencia_detectada')->nullable();
            $table->text('recomendacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->dateTime('fecha_analisis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_analisis_ia');
    }
};
