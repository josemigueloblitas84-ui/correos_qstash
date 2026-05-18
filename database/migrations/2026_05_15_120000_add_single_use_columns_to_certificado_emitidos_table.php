<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificado_emitidos', function (Blueprint $table) {
            $table->unsignedInteger('cantidad_descargas')->default(0)->after('archivo_pdf');
            $table->timestamp('descargado_en')->nullable()->after('cantidad_descargas');
        });
    }

    public function down(): void
    {
        Schema::table('certificado_emitidos', function (Blueprint $table) {
            $table->dropColumn([
                'cantidad_descargas',
                'descargado_en',
            ]);
        });
    }
};
