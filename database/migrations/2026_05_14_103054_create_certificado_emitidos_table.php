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
        Schema::create('certificado_emitidos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('certificado_plantilla_id')
                ->constrained('certificado_plantillas')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('codigo_hash', 120)->unique();

            $table->string('nombre_generado', 150);
            $table->string('ci_generado', 50)->nullable();
            $table->string('horas_generadas', 30)->nullable();

            $table->string('archivo_pdf')->nullable();
            $table->timestamp('fecha_emision')->nullable();

            $table->enum('estado', ['emitido', 'revocado'])->default('emitido');

            $table->timestamps();

            $table->index('estado');
            $table->index('fecha_emision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificado_emitidos');
    }
};
