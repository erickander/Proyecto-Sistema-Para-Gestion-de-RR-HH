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
        if (! Schema::hasTable('tbl_auditoria')) {
            Schema::create('tbl_auditoria', function (Blueprint $table) {
                $table->id('id_auditoria');
                $table->foreignId('id_usuario')->nullable()->constrained('tbl_usuarios', 'id_usuario')->nullOnDelete();
                $table->string('modulo', 80)->nullable();
                $table->string('accion', 120);
                $table->text('detalle')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->enum('nivel', ['INFO', 'WARNING', 'ERROR'])->default('INFO');
                $table->dateTime('fecha_evento');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_auditoria');
    }
};
