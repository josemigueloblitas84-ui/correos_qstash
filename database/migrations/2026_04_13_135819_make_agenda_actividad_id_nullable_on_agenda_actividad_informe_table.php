<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_actividad_informe', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agenda_actividad_id');
        });

        Schema::table('agenda_actividad_informe', function (Blueprint $table) {
            $table->foreignId('agenda_actividad_id')
                ->nullable()
                ->after('id')
                ->constrained('agenda_actividades')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agenda_actividad_informe', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agenda_actividad_id');
        });

        Schema::table('agenda_actividad_informe', function (Blueprint $table) {
            $table->foreignId('agenda_actividad_id')
                ->constrained('agenda_actividades')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }
};
