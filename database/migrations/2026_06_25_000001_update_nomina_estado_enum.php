<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('tbl_nominas', 'estado')) {
            DB::statement("ALTER TABLE tbl_nominas MODIFY estado ENUM('BORRADOR','GENERADA','PENDIENTE','APROBADA','PAGADA') NOT NULL DEFAULT 'PENDIENTE'");
            DB::table('tbl_nominas')->where('estado', 'GENERADA')->update(['estado' => 'PENDIENTE']);
            DB::statement("ALTER TABLE tbl_nominas MODIFY estado ENUM('BORRADOR','PENDIENTE','APROBADA','PAGADA') NOT NULL DEFAULT 'PENDIENTE'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('tbl_nominas', 'estado')) {
            DB::statement("ALTER TABLE tbl_nominas MODIFY estado ENUM('BORRADOR','GENERADA','PENDIENTE','APROBADA','PAGADA') NOT NULL DEFAULT 'GENERADA'");
            DB::table('tbl_nominas')->where('estado', 'PENDIENTE')->update(['estado' => 'GENERADA']);
            DB::statement("ALTER TABLE tbl_nominas MODIFY estado ENUM('BORRADOR','GENERADA','APROBADA','PAGADA') NOT NULL DEFAULT 'GENERADA'");
        }
    }
};
