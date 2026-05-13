# Gestión de Instituciones

<p align="center">
    <img src="/assets/img/MU12.png" alt="Instituciones" width="100%">
</p>

El módulo de instituciones permite registrar, consultar, editar y eliminar instituciones dentro del sistema. Ademas, permite asignar sedes disponibles a cada institución para organizar correctamente la ubicación de los usuarios.

## Que permite este módulo

Segun los permisos asignados, este módulo puede permitir:

- Visualizar instituciones registradas
- Crear nuevas instituciones
- Editar el nombre de una institución
- Eliminar instituciones
- Asignar sedes a una institución

## Ingreso al módulo

Para ingresar a este módulo:

1. Ubique la opcion **Instituciones** en el menu lateral.
2. Haga clic sobre la opción.
3. El sistema mostrara el listado general de instituciones.

Si la opcion no aparece en el menú, el usuario no cuenta con permiso para acceder a este módulo.

## Listado de instituciones

La vista principal muestra una tabla con la informacion general de cada institución.

Normalmente se visualiza:

- Nombre de la institución
- Sedes asociadas
- Fecha de creación
- Botones de acción disponibles segun permisos

Desde esta tabla se puede buscar informacion, cambiar de página y ejecutar acciones de gestión.

@can('crear instituciones')
## Crear una institución

Para registrar una nueva institución:

1. Ingrese al módulo **Instituciones**.
2. Haga clic en el boton **Nueva Institucion**.
3. Complete el campo **Nombre**.
4. Presione **Guardar**.

### Validaciones importantes

- El nombre es obligatorio
- El nombre debe tener al menos 3 caracteres
- El nombre no puede superar 150 caracteres
- El nombre no debe repetirse

### Resultado esperado

La institución quedara registrada y aparecera en el listado principal.
@endcan

@can('editar instituciones')
## Editar una institución

Para modificar una institucion existente:

1. Ubique la institucion en el listado.
2. Haga clic en **Editar**.
3. Actualice el nombre.
4. Presione **Actualizar**.

### Resultado esperado

El sistema guardara los cambios y actualizara la información en la tabla.
@endcan

@can('asignar sedes a instituciones')
## Asignar sedes a una institucion

Esta funcionalidad permite vincular una o varias sedes a una institución.

Para hacerlo:

1. Ubique la institución en el listado.
2. Haga clic en **Asignar Sedes**.
3. Revise el nombre de la institución mostrado en la ventana.
4. Marque o desmarque las sedes necesarias.
5. Presione **Guardar**.

### Como funciona la asignación

- El sistema muestra todas las sedes registradas
- Las sedes ya asociadas aparecen marcadas
- Puede asignar varias sedes a la misma institucion
- Si no existen sedes registradas, no habra opciones disponibles para asignar

### Restricción importante

No se pueden quitar sedes que ya esten asignadas a usuarios dentro de esa institución. Si intenta hacerlo, el sistema mostrara un mensaje de validación y no guardara el cambio.

### Resultado esperado

La tabla se actualizara mostrando las sedes relacionadas con cada institución.
@endcan

@can('eliminar instituciones')
## Eliminar una institución

Para eliminar una institución:

1. Ubique la institución en el listado.
2. Haga clic en **Eliminar**.
3. Confirme la acción.

### Importante

La eliminación solicita confirmación antes de ejecutarse. Revise bien la institucion seleccionada antes de continuar.
@endcan

## Problemas frecuentes

### No puedo ver el módulo de instituciones

Posibles causas:

- No tiene permiso para ver instituciones
- El rol asignado no incluye acceso al módulo

### Puedo ver instituciones, pero no crear o editar

Posibles causas:

- Tiene permiso de consulta, pero no de modificación
- El rol no incluye permisos de creación o edición

### No puedo asignar sedes a una institución

Posibles causas:

- No tiene permiso para asignar sedes a instituciones
- No existen sedes registradas todavia
- Esta intentando quitar una sede que ya esta siendo usada por usuarios

## Buenas practicas

- Registre nombres claros y consistentes para cada institución
- Revise si la institución ya existe antes de crear una nueva
- Asigne las sedes solo cuando realmente correspondan
- Verifique las relaciones con usuarios antes de retirar sedes

## Siguiente paso recomendado

Despues de comprender este módulo, se recomienda continuar con **Sedes**, ya que ambos módulos trabajan de manera relacionada.

- [Sedes](/{{route}}/{{version}}/sedes)
