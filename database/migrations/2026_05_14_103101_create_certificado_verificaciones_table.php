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
        Schema::create('certificado_verificaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('certificado_emitido_id')
                ->constrained('certificado_emitidos')
                ->cascadeOnDelete();

            $table->string('hash_consultado', 120);
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('fecha_verificacion')->nullable();

            $table->enum('resultado', ['valido', 'invalido', 'revocado'])->default('valido');

            $table->timestamps();

            $table->index('hash_consultado');
            $table->index('resultado');
            $table->index('fecha_verificacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificado_verificaciones');
    }
};
