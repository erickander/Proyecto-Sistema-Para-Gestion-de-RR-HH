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
        Schema::create('tbl_empleados', function (Blueprint $table) {
            $table->id('id_empleado');
            $table->foreignId('id_usuario')->unique()->constrained('tbl_usuarios', 'id_usuario')->cascadeOnDelete();
            $table->foreignId('id_departamento')->constrained('tbl_departamentos', 'id_departamento');
            $table->string('cedula', 20)->unique();
            $table->string('nombres', 120);
            $table->string('apellidos', 120);
            $table->date('fecha_nacimiento')->nullable();
            $table->text('direccion')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('correo')->unique();
            $table->string('cargo', 100);
            $table->decimal('salario_base', 10, 2)->default(0);
            $table->date('fecha_ingreso');
            $table->enum('estado', ['activo', 'inactivo', 'vacaciones', 'desvinculado'])->default('activo');
            $table->string('foto')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_empleados');
    }
};
