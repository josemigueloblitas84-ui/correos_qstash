<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionSistema extends Model
{
    protected $table = 'configuracion_sistema';

    protected $fillable = [
        'nombre_institucion',
        'logo_principal',
        'logo_pdf',
        'correo_institucional',
        'celular_institucional',
    ];
}
