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
        if (! Schema::hasTable('tbl_roles')) {
            Schema::create('tbl_roles', function (Blueprint $table) {
                $table->id('id_rol');
                $table->string('nombre_rol', 50)->unique();
                $table->text('descripcion')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
