<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_postulaciones', function (Blueprint $table) {
            if (! Schema::hasColumn('tbl_postulaciones', 'token_test')) {
                $table->string('token_test', 80)->nullable()->unique();
            }

            if (! Schema::hasColumn('tbl_postulaciones', 'fecha_test')) {
                $table->dateTime('fecha_test')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tbl_postulaciones', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_postulaciones', 'fecha_test')) {
                $table->dropColumn('fecha_test');
            }

            if (Schema::hasColumn('tbl_postulaciones', 'token_test')) {
                $table->dropColumn('token_test');
            }
        });
    }
};
