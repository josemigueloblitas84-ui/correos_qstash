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
        Schema::create('certificado_campos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('certificado_plantilla_id')
                ->constrained('certificado_plantillas')
                ->cascadeOnDelete();

            $table->enum('tipo', ['texto', 'campo_dinamico', 'firma', 'qr', 'imagen']);
            $table->string('nombre_campo', 100)->nullable();
            $table->text('texto_default')->nullable();
            $table->string('valor_dinamico', 100)->nullable();

            $table->decimal('pos_x', 10, 2)->default(0);
            $table->decimal('pos_y', 10, 2)->default(0);
            $table->decimal('ancho', 10, 2)->default(0);
            $table->decimal('alto', 10, 2)->default(0);

            $table->string('font_family', 100)->nullable();
            $table->decimal('font_size', 8, 2)->nullable();
            $table->string('font_weight', 20)->nullable();
            $table->string('font_style', 20)->nullable();
            $table->string('text_align', 20)->nullable();
            $table->string('color', 20)->nullable();
            $table->decimal('line_height', 8, 2)->nullable();
            $table->integer('char_spacing')->nullable();

            $table->unsignedInteger('orden')->default(0);
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('tipo');
            $table->index('valor_dinamico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificado_campos');
    }
};
