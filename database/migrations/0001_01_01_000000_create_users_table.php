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

        if (! Schema::hasTable('tbl_usuarios')) {
            Schema::create('tbl_usuarios', function (Blueprint $table) {
                $table->id('id_usuario');
                $table->foreignId('id_rol')->constrained('tbl_roles', 'id_rol');
                $table->string('nombre_usuario', 50)->unique();
                $table->string('correo', 150)->nullable();
                $table->string('password_hash');
                $table->enum('estado', ['ACTIVO', 'INACTIVO', 'BLOQUEADO'])->default('ACTIVO');
                $table->timestamp('fecha_creacion')->nullable()->useCurrent();
                $table->dateTime('ultimo_acceso')->nullable();
            });
        }

        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('tbl_usuarios');
        Schema::dropIfExists('tbl_roles');
    }
};
