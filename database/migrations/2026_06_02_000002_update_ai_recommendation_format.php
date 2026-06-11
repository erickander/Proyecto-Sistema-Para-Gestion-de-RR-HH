<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tbl_analisis_ia MODIFY recomendacion VARCHAR(50) NOT NULL DEFAULT 'Considerar'");
        }
    }

    public function down(): void
    {
        //
    }
};
