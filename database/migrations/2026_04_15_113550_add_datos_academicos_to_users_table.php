<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('validador')
                ->default(false)
                ->after('tipo_personal_id');

            $table->string('cod_estudiante', 30)
                ->nullable()
                ->after('validador');

            $table->integer('cantidad_horas_totales')
                ->nullable()
                ->after('cod_estudiante');

            $table->integer('celular')
                ->nullable()
                ->after('cantidad_horas_totales');

            $table->integer('telefono_contacto')
                ->nullable()
                ->after('celular');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'validador',
                'cod_estudiante',
                'cantidad_horas_totales',
                'celular',
                'telefono_contacto',
            ]);
        });
    }
};
