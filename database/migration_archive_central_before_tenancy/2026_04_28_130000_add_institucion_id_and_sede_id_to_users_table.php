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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('institucion_id')
                ->nullable()
                ->after('tipo_personal_id')
                ->constrained('instituciones');

            $table->foreignId('sede_id')
                ->nullable()
                ->after('institucion_id')
                ->constrained('sedes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sede_id');
            $table->dropConstrainedForeignId('institucion_id');
        });
    }
};
