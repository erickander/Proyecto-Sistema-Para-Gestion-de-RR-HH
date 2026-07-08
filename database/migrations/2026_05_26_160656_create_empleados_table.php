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
        if (! Schema::hasTable('tbl_empleados')) {
            Schema::create('tbl_empleados', function (Blueprint $table) {
                $table->id('id_empleado');
                $table->foreignId('id_usuario')->unique()->constrained('tbl_usuarios', 'id_usuario')->cascadeOnDelete();
                $table->foreignId('id_departamento')->nullable()->constrained('tbl_departamentos', 'id_departamento')->nullOnDelete();
                $table->string('cedula', 20)->unique();
                $table->string('nombres', 120);
                $table->string('apellidos', 120);
                $table->string('correo', 150)->unique();
                $table->string('telefono', 30)->nullable();
                $table->string('direccion', 255)->nullable();
                $table->string('cargo', 100);
                $table->decimal('salario_base', 10, 2)->default(0);
                $table->date('fecha_ingreso');
                $table->date('fecha_salida')->nullable();
                $table->enum('estado', ['ACTIVO', 'INACTIVO', 'VACACIONES', 'TERMINADO'])->default('ACTIVO');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_empleados');
    }
};
