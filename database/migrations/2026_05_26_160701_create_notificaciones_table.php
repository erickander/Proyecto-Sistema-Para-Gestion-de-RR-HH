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
        if (! Schema::hasTable('tbl_notificaciones')) {
            Schema::create('tbl_notificaciones', function (Blueprint $table) {
                $table->id('id_notificacion');
                $table->foreignId('id_usuario')->constrained('tbl_usuarios', 'id_usuario')->cascadeOnDelete();
                $table->string('titulo', 150);
                $table->text('mensaje');
                $table->enum('tipo', ['SISTEMA', 'RRHH', 'IA', 'NOMINA', 'VACACIONES'])->default('SISTEMA');
                $table->boolean('leida')->default(false);
                $table->dateTime('fecha_envio');
                $table->dateTime('fecha_lectura')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_notificaciones');
    }
};
