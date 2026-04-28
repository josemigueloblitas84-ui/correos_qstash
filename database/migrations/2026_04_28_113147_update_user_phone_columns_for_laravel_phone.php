<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('celular', 30)
                ->nullable()
                ->change();

            $table->string('celular_country', 2)
                ->nullable()
                ->after('celular');

            $table->string('telefono_contacto', 30)
                ->nullable()
                ->change();

            $table->string('telefono_contacto_country', 2)
                ->nullable()
                ->after('telefono_contacto');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'celular_country',
                'telefono_contacto_country',
            ]);

            $table->integer('celular')
                ->nullable()
                ->change();

            $table->integer('telefono_contacto')
                ->nullable()
                ->change();
        });
    }
};
