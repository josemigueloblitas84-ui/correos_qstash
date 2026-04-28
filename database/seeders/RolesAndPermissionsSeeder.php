<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'asignar permiso especial',
            'eliminar usuarios',
            'ver permisos',
            'crear permisos',
            'editar permisos',
            'eliminar permisos',
            'ver roles',
            'crear roles',
            'editar roles',
            'eliminar roles',
            'ver logs',
            'configurar sistema',
            'qstash',
            'ver agenda',
            'ver departamentos',
            'ver tipos de personal',
            'ver informe agenda',
            'ver reporte agenda informe',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdminRole = Role::findOrCreate('SuperAdministrador', 'web');
        $superAdminRole->syncPermissions(Permission::query()->pluck('name')->all());

        Role::findOrCreate('Usuario', 'web');
        Role::findOrCreate('Validador', 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
