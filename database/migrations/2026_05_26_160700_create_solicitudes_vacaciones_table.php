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
        if (! Schema::hasTable('tbl_solicitudes_vacaciones')) {
            Schema::create('tbl_solicitudes_vacaciones', function (Blueprint $table) {
                $table->id('id_solicitud');
                $table->foreignId('id_empleado')->constrained('tbl_empleados', 'id_empleado')->cascadeOnDelete();
                $table->date('fecha_inicio');
                $table->date('fecha_fin');
                $table->text('motivo')->nullable();
                $table->enum('tipo_solicitud', ['VACACIONES', 'PERMISO'])->default('VACACIONES');
                $table->enum('estado', ['PENDIENTE', 'APROBADA', 'RECHAZADA'])->default('PENDIENTE');
                $table->foreignId('revisado_por')->nullable()->constrained('tbl_usuarios', 'id_usuario')->nullOnDelete();
                $table->dateTime('fecha_solicitud');
                $table->dateTime('fecha_revision')->nullable();
                $table->text('observaciones')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_solicitudes_vacaciones');
    }
};
