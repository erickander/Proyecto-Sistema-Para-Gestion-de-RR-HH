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
        if (! Schema::hasTable('tbl_nominas')) {
            Schema::create('tbl_nominas', function (Blueprint $table) {
                $table->id('id_nomina');
                $table->foreignId('id_empleado')->constrained('tbl_empleados', 'id_empleado')->cascadeOnDelete();
                $table->date('periodo_inicio');
                $table->date('periodo_fin');
                $table->decimal('salario_base', 10, 2)->default(0);
                $table->decimal('horas_extra', 10, 2)->default(0);
                $table->decimal('monto_horas_extra', 10, 2)->default(0);
                $table->decimal('descuentos', 10, 2)->default(0);
                $table->decimal('total_pagar', 10, 2)->default(0);
                $table->enum('estado', ['BORRADOR', 'PENDIENTE', 'APROBADA', 'PAGADA'])->default('PENDIENTE');
                $table->dateTime('fecha_generacion')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_nominas');
    }
};
