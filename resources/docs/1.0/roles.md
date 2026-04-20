# Gestion de Roles

<p align="center">
    <img src="/assets/img/MU4.png" alt="Roles" width="100%">
</p>


El modulo de roles permite definir grupos de acceso dentro del sistema. Cada rol representa un conjunto de permisos que luego puede ser asignado a uno o varios usuarios.

Esto facilita la administración del sistema, ya que permite gestionar accesos de forma ordenada y centralizada.

## Que es un rol

Un rol es una agrupación de permisos. En lugar de asignar cada permiso manualmente a cada usuario, se puede asignar un rol que ya contenga los permisos necesarios para una funcion determinada.

Por ejemplo, un rol puede estar orientado a:

- Administración general
- Gestion de usuarios
- Registro de agenda
- Consulta de reportes
- Validación o seguimiento

## Que permite este módulo

Dependiendo de los permisos asignados al usuario autenticado, este módulo puede permitir:

- Visualizar roles existentes
- Crear roles
- Editar roles
- Eliminar roles
- Asociar permisos a cada rol

## Ingreso al módulo

Para ingresar al módulo de roles:

1. Ubique la opción **Roles** en el menu lateral.
2. Seleccione la opcion.
3. El sistema mostrara el listado de roles registrados.

Si la opción no esta visible, el usuario no cuenta con permiso para acceder a este módulo.

## Listado de roles

En la vista principal se muestran los roles existentes dentro del sistema.

Este listado permite:

- Identificar rapidamente los roles registrados
- Revisar nombres de roles
- Acceder a opciones de edición o eliminación, según permisos

@can('crear roles')
## Crear un rol

<p align="center">
    <img src="/assets/img/MU4a.png" alt="Crear Roles" width="100%">
</p>

Para registrar un nuevo rol:

1. Ingrese al módulo **Roles**.
2. Seleccione la opción para crear un nuevo rol.
3. Ingrese el nombre del rol.
4. Seleccione los permisos que formaran parte del rol.
5. Guarde la información.

### Resultado esperado

El nuevo rol quedara registrado y disponible para ser asignado a usuarios.
@endcan

@can('editar roles')
## Editar un rol

Para modificar un rol existente:

1. Ubique el rol en el listado.
2. Seleccione la opción de editar.
3. Actualice su nombre o los permisos asociados.
4. Guarde los cambios.

### Recomendación

Si el rol ya esta en uso, revise cuidadosamente los cambios antes de guardarlos, ya que cualquier modificación puede impactar en varios usuarios al mismo tiempo.
@endcan

@can('eliminar roles')
## Eliminar un rol

El sistema puede permitir eliminar roles, según permisos del usuario autenticado.

Antes de eliminar un rol, se recomienda verificar:

- Si esta asignado a usuarios
- Si afecta procesos operativos
- Si existe otro rol que lo reemplazara
@endcan

## Relación entre roles y usuarios

Los roles se asignan a usuarios para definir su acceso general al sistema.

Un usuario con un rol determinado podra heredar los permisos incluidos en ese rol. Esto simplifica el control de acceso y evita configuraciones repetitivas.

## Relación entre roles y permisos

Cada rol puede contener uno o varios permisos. Esto permite definir con mayor precision las capacidades del usuario.

Por ejemplo, un rol puede tener permisos para:

- Ver usuarios
- Crear usuarios
- Editar usuarios
- Ver agenda
- Ver reportes

## Problemas frecuentes

### No puedo crear un rol

Posibles causas:

- No tiene permiso de creación de roles
- El rol requiere permisos adicionales
- Existe alguna restricción administrativa

### No puedo editar un rol

Posibles causas:

- No cuenta con permiso de edición
- El rol puede estar restringido según reglas internas

### Cambie un rol y varios usuarios se vieron afectados

Esto es normal, ya que los usuarios heredan permisos desde el rol. Cualquier cambio en el rol puede modificar el acceso de todos los usuarios asociados.

## Buenas practicas

- Use nombres de rol claros y faciles de identificar
- Agrupe permisos de forma coherente
- Evite crear demasiados roles similares
- Revise el impacto antes de modificar roles ya asignados
- Prefiera administrar accesos por roles antes que por permisos especiales individuales

## Siguiente paso recomendado

Una vez comprendido el uso de roles, se recomienda revisar el módulo **Permisos**, ya que este define las acciones específicas que luego componen cada rol.

- [Permisos](/{{route}}/{{version}}/permisos)
