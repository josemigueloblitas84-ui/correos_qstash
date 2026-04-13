<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_actividades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agenda_id')
                ->constrained('agendas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->date('fecha_del')->nullable();
            $table->date('fecha_hasta')->nullable();

            $table->string('hora_desde_actividad', 20)->nullable();
            $table->string('hora_hasta_actividad', 20)->nullable();

            $table->string('archivo', 100)->nullable();

            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->date('fecha_registro')->nullable();

            $table->text('actividad')->nullable();
            $table->text('observacion')->nullable();

            $table->foreignId('departamento_id')
                ->nullable()
                ->constrained('departamentos')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->enum('tipo_actividad', ['D', 'S']);

            $table->integer('estado')->default(0);
            $table->text('detalle_estado')->nullable();
            $table->integer('actividad_principal')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_actividades');
    }
};
