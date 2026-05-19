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
        Schema::create('agenda_actividad_informe', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agenda_actividad_id')
                ->constrained('agenda_actividades')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('agenda_id')
                ->constrained('agendas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('fecha_actividad');

            $table->text('actividad');

            $table->foreignId('departamento_id')
                ->constrained('departamentos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('tipo_actividad', ['D', 'S', 'P']);

            $table->foreignId('usuario_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unsignedTinyInteger('estado')->default(0);

            $table->text('detalle_estado')->nullable();

            $table->string('reprogramado', 20)->nullable();

            $table->date('fecha_actualizacion')->nullable();

            $table->unsignedTinyInteger('validada_encargado')->default(0);

            $table->foreignId('usuario_actualizador_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_actividad_informe');
    }
};
