<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();

            $table->date('fecha');

            $table->foreignId('cod_unidad')
                ->constrained('departamentos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('cod_solicitante')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('fecha_desde');
            $table->date('fecha_hasta');

            $table->string('hora_desde', 20);
            $table->string('hora_hasta', 20);

            $table->foreignId('cod_usuario')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unsignedTinyInteger('cerrado')->default(0);

            $table->char('estado_agenda', 1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
