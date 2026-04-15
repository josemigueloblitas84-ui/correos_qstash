<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades_validadas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('registro_horas_validada_id');
            $table->unsignedBigInteger('actividad_id');

            $table->foreign('registro_horas_validada_id', 'av_registro_fk')
                ->references('id')
                ->on('registro_horas_validadas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('actividad_id', 'av_actividad_fk')
                ->references('id')
                ->on('agenda_actividades')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades_validadas');
    }
};
