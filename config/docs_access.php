<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Roles con acceso total al manual
    |--------------------------------------------------------------------------
    |
    | Estos roles podran visualizar todas las paginas y todos los bloques
    | protegidos del manual, sin depender del mapeo por permisos.
    |
    */
    'super_admin_roles' => [
        'SuperAdministrador',
    ],

    /*
    |--------------------------------------------------------------------------
    | Visibilidad por defecto
    |--------------------------------------------------------------------------
    |
    | Si se crea una nueva pagina del manual y no se registra aqui, solo los
    | superadministradores la podran ver. Esto evita exponer contenido nuevo
    | por error a roles que no correspondan.
    |
    */
    'default_visibility' => false,

    /*
    |--------------------------------------------------------------------------
    | Acceso por pagina
    |--------------------------------------------------------------------------
    |
    | "public" permite acceso a cualquier usuario autenticado.
    | "any_permissions" permite acceso si el usuario tiene cualquiera de los
    | permisos indicados.
    | "roles" permite acceso por nombre de rol.
    |
    */
    'pages' => [
        'overview' => [
            'public' => true,
        ],
        'acceso-al-sistema' => [
            'public' => true,
        ],
        'panel-principal' => [
            'public' => true,
        ],
        'preguntas-frecuentes' => [
            'public' => true,
        ],
        'buenas-practicas' => [
            'public' => true,
        ],
        'usuarios' => [
            'roles' => ['SuperAdministrador'],
        ],
        'roles' => [
            'roles' => ['SuperAdministrador'],
        ],
        'permisos' => [
            'roles' => ['SuperAdministrador'],
        ],
        'agendar' => [
            'any_permissions' => ['ver agenda'],
        ],
        'informe-agenda' => [
            'public' => true,
        ],
        'reporte-agenda-informe' => [
            'public' => true,
        ],
        'historial-actividad' => [
            'any_permissions' => ['ver logs'],
        ],
    ],
];
