<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_horas_validadas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cod_usuario')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('cod_usuario_validador')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->decimal('total_hora', 8, 2)->nullable();

            $table->string('actividad_masivo')->nullable();
            $table->string('actividad_pasivo')->nullable();

            $table->timestamp('fecha_reg')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_horas_validadas');
    }
};
