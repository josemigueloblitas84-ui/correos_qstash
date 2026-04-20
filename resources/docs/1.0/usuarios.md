# Gestión de Usuarios

<p align="center">
    <img src="/assets/img/MU3.png" alt="Usuarios" width="100%">
</p>


El módulo de usuarios permite administrar las cuentas registradas en el sistema. Desde este apartado se pueden consultar usuarios existentes, crear nuevos registros, editar informacion y controlar su estado segun los permisos del usuario autenticado.

## Que permite este módulo

Según el nivel de acceso asignado, este módulo puede permitir:

- Visualizar usuarios registrados
- Crear usuarios
- Editar información de usuarios
- Activar o desactivar usuarios
- Asignar roles
- Asignar permisos especiales
- Asignar personal relacionado

## Ingreso al módulo

Para ingresar a este módulo:

1. Ubique la opcion **Usuarios** en el menu lateral.
2. Haga clic sobre la opción.
3. El sistema mostrara el listado de usuarios disponibles.

Si la opción no aparece en el menú, significa que el usuario no tiene permiso para visualizar este módulo.

## Listado de usuarios

En la vista principal del módulo se puede consultar la información general de los usuarios registrados.

Dependiendo de la configuración, pueden mostrarse datos como:

- Nombre
- Correo electronico
- Rol asignado
- Estado del usuario
- Departamento
- Tipo de personal

El listado sirve como punto de control para revisar rapidamente la información registrada.

@can('crear usuarios')
## Crear un usuario

<p align="center">
    <img src="/assets/img/MU3a.png" alt="Registrar un nuevo usuario" width="100%">
</p>

Para registrar un nuevo usuario:

1. Ingrese al módulo **Usuarios**.
2. Seleccione la opción para crear un nuevo usuario.
3. Complete los campos requeridos.
4. Asigne el rol correspondiente, si aplica.
5. Guarde la información.

### Resultado esperado

El nuevo usuario debera quedar registrado y disponible en el listado general.
@endcan

@can('editar usuarios')
## Editar un usuario

Para modificar la información de un usuario existente:

1. Ubique el usuario en el listado.
2. Seleccione la opción de editar.
3. Modifique los datos necesarios.
4. Guarde los cambios.

### Recomendación

Antes de guardar, verifique cuidadosamente:

- Nombre del usuario
- Correo electronico
- Departamento
- Tipo de personal
- Rol asignado
@endcan

@can('eliminar usuarios')
## Activar o desactivar un usuario

El sistema permite controlar el estado de un usuario para habilitar o restringir su acceso.

Esto es util cuando:

- Un usuario ya no debe ingresar al sistema
- Se requiere suspender temporalmente el acceso
- Se desea reactivar una cuenta previamente deshabilitada

### Importante

Desactivar un usuario no siempre significa eliminar su informacion, sino impedir o restringir su uso dentro del sistema segun la implementación del módulo.
@endcan

@can('editar usuarios')
## Asignar rol a un usuario

<p align="center">
    <img src="/assets/img/MU3b.png" alt="Asignar roles" width="100%">
</p>

La asignacion de roles permite definir el nivel de acceso general del usuario.

Para hacerlo:

1. Ingrese a la opción Gestionar del usuario.
2. Busque la sección de roles.
3. Seleccione el rol correspondiente.
4. Guarde los cambios.

### Resultado esperado

El usuario heredara los permisos asociados al rol asignado.
@endcan

@can('asignar permiso especial')
## Asignar permisos especiales

Ademas del rol, el sistema puede permitir asignar permisos especiales directamente al usuario.

Esto se utiliza cuando:

- Un usuario necesita acceso excepcional a una funcion
- Se requiere una autorización puntual que no pertenece a su rol general

### Recomendación

Use permisos especiales solo cuando sea realmente necesario. Siempre que sea posible, es mejor trabajar mediante roles para mantener el control mas ordenado.
@endcan

@can('editar usuarios')
## Asignar personal relacionado

En algunos casos el sistema permite vincular personal asignado a un usuario. Esta funcionalidad puede utilizarse para procesos de control, seguimiento o validación.

Su disponibilidad depende del permiso del usuario autenticado.
@endcan

## Problemas frecuentes

### No puedo ver el módulo de usuarios

Posibles causas:

- no tiene permiso para visualizar usuarios
- el rol asignado no incluye este módulo

### Puedo ver usuarios, pero no crear o editar

Posibles causas:

- Solo tiene permiso de consulta
- No cuenta con permiso de creación o edición

### No puedo asignar roles o permisos especiales

Posibles causas:

- No tiene permiso suficiente
- La acción esta reservada a administradores o usuarios autorizados

## Buenas practicas

- Mantenga actualizada la información de cada usuario
- No cree usuarios duplicados
- Asigne roles de forma clara y coherente
- Use permisos especiales solo si es estrictamente necesario
- Revise el estado del usuario antes de reportar errores de acceso

## Siguiente paso recomendado

Despues de comprender la gestion de usuarios, se recomienda continuar con las secciones de **Roles** y **Permisos**, ya que ambas estan directamente relacionadas con el control de acceso del sistema.

- [Roles](/{{route}}/{{version}}/roles)
