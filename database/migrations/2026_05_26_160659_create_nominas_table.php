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
        Schema::create('tbl_nominas', function (Blueprint $table) {
            $table->id('id_nomina');
            $table->foreignId('id_empleado')->constrained('tbl_empleados', 'id_empleado')->cascadeOnDelete();
            $table->decimal('sueldo_base', 10, 2)->default(0);
            $table->decimal('horas_extras', 10, 2)->default(0);
            $table->decimal('bonificaciones', 10, 2)->default(0);
            $table->decimal('descuentos', 10, 2)->default(0);
            $table->decimal('iess', 10, 2)->default(0);
            $table->decimal('sueldo_neto', 10, 2)->default(0);
            $table->date('fecha_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_nominas');
    }
};
