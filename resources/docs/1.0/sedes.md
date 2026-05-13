# Gestión de Sedes

<p align="center">
    <img src="/assets/img/mu13.png" alt="Sedes" width="100%">
</p>

El módulo de sedes permite administrar las ubicaciones registradas en el sistema. Estas sedes pueden utilizarse posteriormente para asociarlas a instituciones y a usuarios.

## Que permite este módulo

Segun los permisos del usuario autenticado, este módulo puede permitir:

- Visualizar sedes registradas
- Crear nuevas sedes
- Editar sedes existentes
- Eliminar sedes

## Ingreso al módulo

Para ingresar a este módulo:

1. Ubique la opción **Sedes** en el menu lateral.
2. Haga clic sobre la opcion.
3. El sistema mostrara el listado de sedes registradas.

Si la opción no aparece en el menu, significa que el usuario no tiene permiso para visualizar este módulo.

## Listado de sedes

La vista principal muestra una tabla con la informacion de las sedes.

Normalmente se visualiza:

- Nombre de la sede
- Fecha de creación
- Botones de accion disponibles segun permisos

Desde esta vista puede buscar registros, revisar informacion existente y abrir las acciones de edición o eliminación.

@can('crear sedes')
## Crear una sede

Para registrar una nueva sede:

1. Ingrese al módulo **Sedes**.
2. Haga clic en **Nueva Sede**.
3. Complete el campo **Nombre**.
4. Presione **Guardar**.

### Validaciones importantes

- El nombre es obligatorio
- El nombre debe tener al menos 3 caracteres
- El nombre no puede superar 150 caracteres
- El nombre no debe repetirse

### Resultado esperado

La sede quedara registrada y aparecera en el listado principal.
@endcan

@can('editar sedes')
## Editar una sede

Para modificar una sede existente:

1. Ubique la sede en el listado.
2. Haga clic en **Editar**.
3. Actualice el nombre.
4. Presione **Actualizar**.

### Resultado esperado

La información quedara actualizada en la tabla.
@endcan

@can('eliminar sedes')
## Eliminar una sede

Para eliminar una sede:

1. Ubique la sede en el listado.
2. Haga clic en **Eliminar**.
3. Confirme la accion.

### Importante

La eliminación solicita confirmacion antes de ejecutarse. Revise cuidadosamente el registro seleccionado antes de continuar.
@endcan

## Relación con instituciones

Las sedes funcionan como un catalogo base. Una vez registradas, pueden ser vinculadas a una o varias instituciones desde el módulo **Instituciones**.

Por esta razon, si necesita organizar correctamente las ubicaciones disponibles en el sistema, primero debe registrar las sedes y luego asignarlas a la institucion correspondiente.

## Problemas frecuentes

### No puedo ver el modulo de sedes

Posibles causas:

- No tiene permiso para ver sedes
- El rol asignado no incluye acceso a este módulo

### Puedo ver sedes, pero no crear o editar

Posibles causas:

- Solo tiene permisos de consulta
- No cuenta con permisos de modificación

### No encuentro una sede al asignarla en instituciones

Posibles causas:

- La sede no ha sido creada todavia
- La información aun no se ha actualizado en el listado

## Buenas practicas

- Use nombres claros para identificar cada sede
- Evite duplicar registros con nombres similares
- Mantenga actualizado el catalogo antes de trabajar con instituciones y usuarios

## Siguiente paso recomendado

Despues de revisar este módulo, se recomienda continuar con **Instituciones** para comprender como se relacionan ambas entidades.

- [Instituciones](/{{route}}/{{version}}/instituciones)
