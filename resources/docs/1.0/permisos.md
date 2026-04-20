# Gestión de Permisos

<p align="center">
    <img src="/assets/img/MU5.png" alt="Permisos" width="100%">
</p>

## Objetivo

El módulo de permisos permite administrar las autorizaciones especificas del sistema. Cada permiso representa una acción o acceso concreto y puede ser asignado a roles o en algunos casos, directamente a usuarios.

Este módulo es fundamental para controlar con precisión lo que cada usuario puede ver o hacer dentro del sistema.

## Que es un permiso

Un permiso es una autorización puntual dentro del sistema.

Ejemplos comunes de permisos son:

- Ver usuarios
- Crear usuarios
- Editar usuarios
- Eliminar usuarios
- Ver roles
- Ver permisos
- Ver agenda
- Ver informe agenda
- Ver reporte agenda informe

## Para que sirven los permisos

Los permisos sirven para:

- Controlar visibilidad de módulos
- Permitir o restringir acciones especificas
- Definir capacidades por rol
- Otorgar accesos especiales a usuarios concretos

## Ingreso al módulo

Para ingresar al módulo de permisos:

1. Ubique la opción **Permisos** en el menú lateral.
2. Haga clic en la opción.
3. El sistema mostrara el listado de permisos registrados.

Si la opción no aparece en el menu, el usuario no tiene acceso autorizado a este modulo.

## Listado de permisos

En esta vista se visualizan los permisos existentes dentro del sistema.

El listado permite:

- Consultar nombres de permisos
- Revisar permisos disponibles
- Ingresar a opciones de edición o eliminación, si estan habilitadas

@can('crear permisos')
## Crear un permiso

<p align="center">
    <img src="/assets/img/MU5a.png" alt="Crear permisos" width="100%">
</p>

Para registrar un nuevo permiso:

1. Ingrese al módulo **Permisos**.
2. Seleccione la opción de crear nuevo permiso.
3. Ingrese el nombre del permiso.
4. Guarde la información.

### Recomendación

Utilice nombres de permiso claros, directos y consistentes. Lo ideal es que el nombre exprese exactamente la acción que permite realizar.

Ejemplos recomendados:

- Ver usuarios
- Crear roles
- Editar permisos
- Ver informe agenda
@endcan

@can('editar permisos')
## Editar un permiso

Para modificar un permiso existente:

1. Ubique el permiso en el listado.
2. Seleccione la opción de editar.
3. Actualice la información necesaria.
4. Guarde los cambios.

### Importante

Modificar el nombre o el uso de un permiso puede afectar:

- Roles que ya lo usan
- Usuarios con permisos especiales
- Visibilidad de módulos y botones del sistema
@endcan

@can('eliminar permisos')
## Eliminar un permiso

El sistema puede permitir eliminar permisos, segun el nivel de autorización del usuario.

Antes de eliminar un permiso, revise si ese permiso esta siendo utilizado por:

- Roles existentes
- Usuarios con permisos especiales
- Módulos importantes del sistema
@endcan

## Permisos y roles

Los permisos se asignan principalmente a roles. De esta manera, cuando un usuario recibe un rol, hereda automáticamente los permisos que ese rol contiene.

Este es el metodo recomendado para administrar acceso en la mayoria de los casos.

## Permisos especiales en usuarios

Ademas del rol, algunos usuarios pueden recibir permisos especiales de forma directa.

Esto se utiliza cuando:

- Un usuario necesita una autorización puntual
- Se requiere acceso adicional sin cambiar su rol
- Se desea otorgar una capacidad temporal o específica

## Problemas frecuentes

### No puedo ver el modulo de permisos

Posibles causas:

- No tiene permiso de visualización
- Su rol no incluye acceso al módulo

### Puedo ver permisos pero no crearlos o editarlos

Posibles causas:

- Solo tiene acceso de consulta
- No cuenta con permisos de administración

### Un usuario ve una opción que otro no ve

Esto puede ocurrir porque:

- Tienen roles diferentes
- Uno tiene permisos especiales adicionales
- No cuentan con la misma configuración de acceso

## Buenas practicas

- Use nombres consistentes y faciles de entender
- No cree permisos duplicados
- Administre accesos preferentemente por roles
- Use permisos especiales solo en casos excepcionales
- Revise siempre el impacto antes de modificar o eliminar permisos

## Siguiente paso recomendado

Despues de revisar los permisos, el siguiente paso natural es conocer los modulos operativos del sistema, como **Agendar** e **Informe de agenda**.

- [Agendar](/{{route}}/{{version}}/agendar)
