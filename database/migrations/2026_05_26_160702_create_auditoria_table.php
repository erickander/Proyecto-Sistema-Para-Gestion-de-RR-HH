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
        Schema::create('tbl_auditoria', function (Blueprint $table) {
            $table->id('id_auditoria');
            $table->foreignId('id_usuario')->nullable()->constrained('tbl_usuarios', 'id_usuario')->nullOnDelete();
            $table->string('accion', 120);
            $table->text('descripcion')->nullable();
            $table->string('ip', 45)->nullable();
            $table->dateTime('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_auditoria');
    }
};
