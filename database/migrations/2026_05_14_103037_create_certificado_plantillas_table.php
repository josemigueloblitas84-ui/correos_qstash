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
        Schema::create('certificado_plantillas', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 150);
            $table->string('archivo_pdf')->nullable();
            $table->json('json_estructura')->nullable();

            $table->enum('tamano_hoja', ['a4', 'carta'])->default('a4');
            $table->enum('orientacion', ['horizontal', 'vertical'])->default('horizontal');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');

            $table->timestamps();

            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificado_plantillas');
    }
};
