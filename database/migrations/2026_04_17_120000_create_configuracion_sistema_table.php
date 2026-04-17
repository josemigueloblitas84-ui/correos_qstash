<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_institucion', 150);
            $table->string('logo_principal', 255)->nullable();
            $table->string('logo_pdf', 255)->nullable();
            $table->string('correo_institucional', 150)->nullable();
            $table->string('celular_institucional', 30)->nullable();
            $table->timestamps();
        });

        DB::table('configuracion_sistema')->insert([
            'nombre_institucion' => 'Fundacion UNIFRANZ',
            'logo_principal' => 'assets/img/logoFundacionTrans.png',
            'logo_pdf' => 'assets/img/logoFundacionTrans.png',
            'correo_institucional' => null,
            'celular_institucional' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_sistema');
    }
};
