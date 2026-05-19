# Implementacion Multi-Tenant con Tenancy for Laravel

Este documento resume, paso a paso, como se implemento el esquema multi-tenant en el proyecto usando `stancl/tenancy`.

La idea principal del sistema es separar dos contextos:

- **Central**: administra las instituciones/tenants, dominios y accesos hacia cada institucion.
- **Tenant**: representa una institucion concreta. Cada tenant trabaja con su propia base de datos y sus propias rutas funcionales.

---

## 1. Instalar Tenancy for Laravel

Se instalo el paquete oficial de Tenancy for Laravel:

```bash
composer require stancl/tenancy
```

En `composer.json` quedo registrado:

```json
"stancl/tenancy": "^3.10"
```

Este paquete permite que Laravel cambie automaticamente de base de datos segun el dominio que se esta usando.

---

## 2. Publicar archivos de configuracion y migraciones

Despues de instalar el paquete, se publicaron los archivos base:

```bash
php artisan tenancy:install
```

Esto genera archivos importantes como:

- `config/tenancy.php`
- `app/Providers/TenancyServiceProvider.php`
- migraciones centrales para `tenants` y `domains`
- archivo de rutas tenant si no existia

Luego se actualizo el autoload/configuracion:

```bash
composer dump-autoload
php artisan config:clear
php artisan route:clear
```

---

## 3. Configurar el modelo Tenant

Se creo/configuro el modelo:

```php
app/Models/Tenant.php
```

El modelo extiende de Tenancy y permite que cada institucion tenga base de datos propia:

```php
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, SoftDeletes;
}
```

Puntos importantes:

- `HasDatabase`: permite crear y usar una base de datos por tenant.
- `HasDomains`: relaciona el tenant con uno o mas dominios.
- `SoftDeletes`: permite desactivar instituciones sin eliminar fisicamente el registro.

Tambien se definieron columnas personalizadas:

```php
public static function getCustomColumns(): array
{
    return [
        'id',
        'nombre',
        'deleted_at',
    ];
}
```

Esto permite guardar datos propios de la institucion directamente en la tabla `tenants`.

---

## 4. Configurar `config/tenancy.php`

En `config/tenancy.php` se definio el modelo tenant:

```php
'tenant_model' => Tenant::class,
```

Tambien se configuraron los dominios centrales:

```php
'central_domains' => (function () {
    $appHost = parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST);
    $fallbackHosts = array_diff(['127.0.0.1', 'localhost', env('CENTRAL_DOMAIN')], [$appHost]);

    return array_values(array_unique(array_filter([
        ...$fallbackHosts,
        $appHost,
    ])));
})(),
```

Esto sirve para que Laravel sepa que dominios pertenecen al sistema central y cuales pertenecen a tenants.

Ejemplo:

- `localhost` o `127.0.0.1`: Central.
- `fundacion2.localhost`: Tenant.
- `fundacion3.localhost`: Tenant.

Tambien se configuro el cambio automatico de base de datos:

```php
'bootstrappers' => [
    Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
],
```

El mas importante aqui es:

```php
DatabaseTenancyBootstrapper::class
```

Ese bootstrapper cambia la conexion activa a la base de datos del tenant cuando se detecta el dominio.

---

## 5. Configurar migraciones tenant

Las migraciones que pertenecen a cada institucion se movieron/crearon en:

```text
database/migrations/tenant
```

Ejemplos:

- `users`
- `roles`
- `permissions`
- `departamentos`
- `sedes`
- `agendas`
- `agenda_actividades`
- `activity_log`
- tablas de reportes e informes

En `config/tenancy.php` se configuro que las migraciones tenant se ejecuten desde esa carpeta:

```php
'migration_parameters' => [
    '--force' => true,
    '--path' => [database_path('migrations/tenant')],
    '--realpath' => true,
],
```

Para ejecutar migraciones en todos los tenants:

```bash
php artisan tenants:migrate
```

Para ver tenants disponibles:

```bash
php artisan tenants:list
```

---

## 6. Configurar migraciones centrales

Las tablas centrales se mantienen en:

```text
database/migrations
```

Tablas centrales importantes:

- `tenants`: guarda las instituciones.
- `domains`: guarda los dominios asociados a cada tenant.
- `tenant_login_tokens`: guarda tokens temporales para conectarse desde Central hacia un tenant.

La tabla `tenants` contiene:

```php
$table->string('id')->primary();
$table->string('nombre', 150);
$table->timestamps();
$table->json('data')->nullable();
```

La tabla `tenant_login_tokens` contiene:

```php
$table->char('token_hash', 64)->unique();
$table->string('tenant_id');
$table->unsignedBigInteger('central_user_id')->nullable();
$table->string('central_user_name');
$table->string('central_user_email');
$table->timestamp('expires_at');
$table->timestamp('used_at')->nullable();
```

Esta tabla se usa para permitir el acceso temporal desde Central a una institucion.

---

## 7. Separar rutas Central y rutas Tenant

Se separaron las rutas en dos archivos:

```text
routes/web.php
routes/tenant.php
```

### Rutas centrales

`routes/web.php` contiene las rutas del sistema Central.

Aqui se administra:

- listado de instituciones
- creacion de instituciones
- edicion de instituciones
- desactivacion de instituciones
- conexion hacia una institucion
- creacion de administrador tenant

Las rutas centrales se registran solo sobre los dominios centrales:

```php
foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group($centralRoutes);
}
```

### Rutas tenant

`routes/tenant.php` contiene las rutas internas de cada institucion.

Estas rutas usan middleware de Tenancy:

```php
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // rutas del tenant
});
```

El middleware:

```php
InitializeTenancyByDomain::class
```

detecta el dominio y activa el tenant correcto.

El middleware:

```php
PreventAccessFromCentralDomains::class
```

evita que rutas tenant se abran desde el dominio central.

---

## 8. Registrar las rutas tenant

En:

```php
app/Providers/TenancyServiceProvider.php
```

se registra automaticamente `routes/tenant.php`:

```php
protected function mapRoutes()
{
    $this->app->booted(function () {
        if (file_exists(base_path('routes/tenant.php'))) {
            Route::namespace(static::$controllerNamespace)
                ->group(base_path('routes/tenant.php'));

            Route::getRoutes()->refreshNameLookups();
            Route::getRoutes()->refreshActionLookups();
        }
    });
}
```

Tambien se puso el middleware de Tenancy con prioridad alta:

```php
protected function makeTenancyMiddlewareHighestPriority()
```

Esto es importante porque Tenancy debe inicializarse antes de que los controladores consulten la base de datos.

---

## 9. Crear instituciones como tenants

La gestion de instituciones se hizo desde:

```php
app/Http/Controllers/Agenda/InstitucionController.php
app/Services/Agenda/InstitucionService.php
```

Cuando se crea una institucion, se crea un tenant:

```php
$tenant = Tenant::create([
    'id' => $data['id'],
    'nombre' => $data['nombre'],
]);
```

Luego se le asigna un dominio:

```php
$tenant->domains()->create([
    'domain' => $data['domain'],
]);
```

Al crearse el tenant, Tenancy ejecuta los jobs configurados:

```php
Events\TenantCreated::class => [
    JobPipeline::make([
        Jobs\CreateDatabase::class,
        Jobs\MigrateDatabase::class,
    ])
]
```

Eso hace dos cosas:

1. Crea la base de datos del tenant.
2. Ejecuta las migraciones tenant dentro de esa base de datos.

---

## 10. Crear administrador dentro de una institucion

Desde Central se puede crear un administrador para una institucion.

El metodo esta en:

```php
app/Services/Agenda/InstitucionService.php
```

Metodo:

```php
createAdministrator()
```

Primero se busca el tenant:

```php
$tenant = $this->findById($id);
```

Luego se ejecuta codigo dentro de la base de datos del tenant:

```php
$administrator = $tenant->run(function () use ($data) {
    app(RolesAndPermissionsSeeder::class)->run();

    $user = new User();
    $user->name = $data['name'];
    $user->email = $data['email'];
    $user->password = Hash::make($data['password']);
    $user->email_verified_at = now();
    $user->estado = 1;
    $user->save();

    $user->assignRole('SuperAdministrador');
});
```

La parte clave es:

```php
$tenant->run(...)
```

Eso fuerza a Laravel a trabajar dentro de la base de datos del tenant seleccionado.

---

## 11. Conectarse desde Central hacia una institucion

Para conectarse desde Central a una institucion se implemento un enlace temporal.

Archivos principales:

```php
app/Services/Central/TenantLoginLinkService.php
app/Http/Controllers/Auth/TenantCentralLoginController.php
database/migrations/2026_05_15_010000_create_tenant_login_tokens_table.php
```

### Crear token temporal

Desde Central se genera un token:

```php
$token = Str::random(80);
```

Pero en la base de datos no se guarda el token original, se guarda un hash:

```php
'token_hash' => hash('sha256', $token),
```

Tambien se guarda:

- tenant destino
- usuario central
- nombre del usuario central
- correo del usuario central
- fecha de expiracion

El token expira rapido:

```php
'expires_at' => now()->addMinute(),
```

Luego se arma la URL del tenant:

```php
return $this->buildTenantUrl($domain, $request, '/central-login/' . $token);
```

### Consumir token en el tenant

En `routes/tenant.php` se agrego:

```php
Route::get('/central-login/{token}', TenantCentralLoginController::class)
    ->name('tenant.central-login');
```

Cuando el usuario entra a esa URL, `TenantCentralLoginController`:

1. Hashea el token recibido.
2. Busca el token en la base central usando `tenancy()->central(...)`.
3. Valida que no este usado.
4. Valida que no este expirado.
5. Crea o actualiza el usuario en la base del tenant.
6. Le asigna rol `SuperAdministrador`.
7. Marca el token como usado.
8. Inicia sesion en el tenant.
9. Guarda en sesion que viene desde Central.

La consulta del token se hace en Central:

```php
$loginToken = tenancy()->central(function () use ($tokenHash, $currentTenantId) {
    return DB::table('tenant_login_tokens')
        ->where('token_hash', $tokenHash)
        ->where('tenant_id', $currentTenantId)
        ->whereNull('used_at')
        ->where('expires_at', '>', now())
        ->first();
});
```

La sesion tenant se inicia asi:

```php
Auth::guard('web')->login($user);
$request->session()->regenerate();
```

Y se guarda la marca de acceso desde Central:

```php
$request->session()->put([
    'central_impersonation' => true,
    'central_impersonation_name' => $loginToken->central_user_name,
    'central_impersonation_email' => $loginToken->central_user_email,
]);
```

---

## 12. Mostrar indicador cuando se entra desde Central

En el header:

```php
resources/views/plantilla/header.blade.php
```

se muestra un badge cuando el usuario entro desde Central:

```php
@if (tenancy()->initialized && session('central_impersonation'))
    <span class="badge text-bg-warning">
        Conectado desde Central
    </span>
@endif
```

Antes existia un boton **Desconectar**, pero se elimino porque se parecia demasiado a **Cerrar Sesion** y podia confundir.

Ahora se deja solo:

- indicador: **Conectado desde Central**
- accion real: **Cerrar Sesion**

---

## 13. Ocultar el usuario central en la vista de usuarios

Cuando un SuperAdministrador central entra a un tenant, el sistema necesita crear o reutilizar un usuario dentro de la base tenant para poder autenticarlo.

Ese usuario debe existir en backend, pero no debe aparecer en la tabla de usuarios de la institucion.

Para eso se agrego una columna tenant:

```php
database/migrations/tenant/2026_05_19_000000_add_is_central_user_to_users_table.php
```

Columna:

```php
$table->boolean('is_central_user')
    ->default(false)
    ->after('email');
```

En el modelo `User` se agrego el cast:

```php
'is_central_user' => 'boolean',
```

En `TenantCentralLoginController`, cuando el usuario viene desde Central, se marca:

```php
$user->is_central_user = true;
```

Y si el usuario ya existe:

```php
$attributes['is_central_user'] = true;
```

Luego en el listado de usuarios:

```php
app/Http/Controllers/Permisos/UserController.php
```

se filtra:

```php
->when(Schema::hasColumn('users', 'is_central_user'), function ($query) {
    $query->where('users.is_central_user', false);
})
```

Tambien se agrego un filtro por correo de sesion central como respaldo:

```php
->when(session('central_impersonation_email'), function ($query, $email) {
    $query->where('users.email', '<>', $email);
})
```

El mismo filtro se aplico al selector de personal asignado en:

```php
app/Services/Permisos/UserService.php
```

Asi el usuario central:

- existe en backend
- puede iniciar sesion en el tenant
- mantiene permisos de SuperAdministrador
- no aparece en el listado visual de usuarios
- no aparece en selectores de asignacion de personal

---

## 14. Comandos usados para migraciones

Migraciones centrales:

```bash
php artisan migrate
```

Migraciones tenant:

```bash
php artisan tenants:migrate
```

En esta implementacion se ejecuto:

```bash
php artisan tenants:migrate
```

y corrio correctamente en:

- `fundacion2`
- `fundacion3`

---

## 15. Flujo completo del sistema

El flujo queda asi:

1. El usuario entra al dominio central.
2. Central muestra instituciones.
3. El SuperAdministrador central crea una institucion.
4. Al crear la institucion, se crea un tenant.
5. Tenancy crea la base de datos del tenant.
6. Tenancy ejecuta migraciones tenant.
7. Central registra el dominio del tenant.
8. Desde Central se puede crear administrador para ese tenant.
9. Desde Central se puede presionar **Conectar**.
10. Central genera un token temporal.
11. Central redirige al dominio del tenant con `/central-login/{token}`.
12. El tenant valida el token consultando la base central.
13. El tenant crea o actualiza el usuario central en su propia base.
14. El tenant inicia sesion con ese usuario.
15. El header muestra **Conectado desde Central**.
16. El usuario central queda oculto en el listado visual de usuarios.
17. Para salir, se usa **Cerrar Sesion**.

---

## 16. Archivos principales modificados o creados

Configuracion:

- `composer.json`
- `config/tenancy.php`
- `app/Providers/TenancyServiceProvider.php`

Modelo tenant:

- `app/Models/Tenant.php`

Rutas:

- `routes/web.php`
- `routes/tenant.php`

Central:

- `app/Http/Controllers/Agenda/InstitucionController.php`
- `app/Services/Agenda/InstitucionService.php`
- `app/Services/Central/TenantLoginLinkService.php`
- `database/migrations/2026_05_15_010000_create_tenant_login_tokens_table.php`

Tenant login:

- `app/Http/Controllers/Auth/TenantCentralLoginController.php`

Usuarios:

- `app/Models/User.php`
- `app/Http/Controllers/Permisos/UserController.php`
- `app/Services/Permisos/UserService.php`
- `database/migrations/tenant/2026_05_19_000000_add_is_central_user_to_users_table.php`

Vista:

- `resources/views/plantilla/header.blade.php`

---

## 17. Puntos importantes para mantener

- Las tablas compartidas del sistema central deben ir en `database/migrations`.
- Las tablas propias de cada institucion deben ir en `database/migrations/tenant`.
- Las rutas de Central deben ir en `routes/web.php`.
- Las rutas de instituciones deben ir en `routes/tenant.php`.
- Para ejecutar codigo dentro de un tenant desde Central se usa:

```php
$tenant->run(function () {
    // codigo dentro de la base tenant
});
```

- Para consultar la base Central desde un tenant se usa:

```php
tenancy()->central(function () {
    // codigo dentro de la base central
});
```

- Para migrar todos los tenants:

```bash
php artisan tenants:migrate
```

- Para crear nuevos tenants correctamente, siempre se debe crear:

1. Registro en `tenants`.
2. Dominio en `domains`.
3. Base de datos tenant.
4. Migraciones tenant.

En este proyecto, los pasos 3 y 4 se automatizan con el evento `TenantCreated`.

