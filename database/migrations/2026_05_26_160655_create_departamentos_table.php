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
        if (! Schema::hasTable('tbl_departamentos')) {
            Schema::create('tbl_departamentos', function (Blueprint $table) {
                $table->id('id_departamento');
                $table->string('nombre_departamento', 100)->unique();
                $table->text('descripcion')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_departamentos');
    }
};
