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
        Schema::create('institucion_sede', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')
                ->constrained('instituciones')
                ->cascadeOnDelete();
            $table->foreignId('sede_id')
                ->constrained('sedes')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['institucion_id', 'sede_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institucion_sede');
    }
};
