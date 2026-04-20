# Agendar

<p align="center">
    <img src="/assets/img/MU6.png" alt="Agendar" width="100%">
</p>

El módulo **Agendar** permite registrar agendas de trabajo dentro del sistema. En este apartado el usuario puede definir fechas, periodos, solicitante, unidad o departamento y actividades relacionadas al trabajo programado.

Este módulo es uno de los procesos operativos principales del sistema.

## Que permite este módulo

Según los permisos del usuario autenticado, este módulo puede permitir:

- Crear una nueva agenda
- Visualizar agendas registradas
- Editar agendas
- Eliminar agendas
- Enviar agendas
- Registrar actividades asociadas a una agenda
- Visualizar detalle de agenda

## Ingreso al módulo

Para ingresar al modulo:

1. Ubique la opción **Agendar** en el menu lateral.
2. Haga clic sobre la opción.
3. El sistema mostrara la vista de registro o gestión de agendas.

Si la opción no aparece en el menú, el usuario no cuenta con permiso para acceder a este módulo.

## Registro de una agenda

<p align="center">
    <img src="/assets/img/MU6a.png" alt="Crear agendar" width="100%">
</p>

Para registrar una nueva agenda, el usuario debe completar la información solicitada.

Según la implementacion actual del sistema, normalmente se registra:

- Fecha
- Unidad o departamento
- Solicitante
- Fecha desde
- Fecha hasta
- Hora de inicio
- Hora de finalización

### Consideraciones importantes

- La fecha final debe ser mayor o igual a la fecha inicial
- Las fechas deben pertenecer al mismo mes, según la validación actual del sistema
- Algunos campos pueden autocompletarse segun el rol del usuario

## Registro de actividades dentro de la agenda

Una agenda puede incluir actividades relacionadas al trabajo programado.

Estas actividades permiten describir con mayor detalle las tareas previstas, incluyendo información como:

- Descripción o actividad
- Fechas relacionadas
- Horario
- Departamento asociado

## Consultar agendas registradas

El modulo permite visualizar agendas previamente registradas.

Desde esta consulta el usuario puede:

- Revisar informacion general
- Abrir una agenda existente
- Editar registros
- Eliminar una agenda
- Previsualizar la agenda

## Editar una agenda

Para modificar una agenda ya registrada:

1. Busque la agenda en el listado disponible.
2. Seleccione la opción de editar o abrir.
3. Actualice la información necesaria.
4. Guarde los cambios.

### Recomendación

Antes de guardar, revise especialmente:

- Fechas
- Horario
- Solicitante
- Unidad o departamento
- Actividades registradas

## Eliminar una agenda

El sistema puede permitir eliminar agendas, dependiendo del permiso asignado al usuario y de las reglas internas del módulo.

Antes de eliminar una agenda, se recomienda confirmar que el registro ya no sera utilizado en procesos posteriores.

## Enviar agenda

En algunos flujos del sistema, la agenda puede ser enviada una vez registrada.

Esta accion puede representar el paso de una agenda a un estado posterior de control o seguimiento.

## Problemas frecuentes

### No puedo ver el módulo Agendar

Posibles causas:

- No tiene permiso para ver agenda
- Su rol no incluye acceso a este módulo

### No puedo guardar la agenda

Posibles causas:

- Faltan campos obligatorios
- Las fechas no cumplen la validacion requerida
- El horario no fue completado correctamente

### No puedo editar o eliminar una agenda

Posibles causas:

- No cuenta con permisos suficientes
- La agenda ya se encuentra en un estado no editable
- Existen restricciones de negocio aplicadas al registro

## Buenas prácticas

- Verifique siempre las fechas antes de guardar
- Asegure que el solicitante corresponda al registro correcto
- Use descripciones claras en las actividades
- No elimine agendas sin confirmar que ya no seran necesarias
- Revise cuidadosamente la información antes de enviar una agenda

## Siguiente paso recomendado

Una vez comprendido el registro de agendas, se recomienda continuar con la sección **Informe de Agenda**, ya que permite registrar las actividades ejecutadas y completar el flujo operativo del sistema.

- [Informe de Agenda](/{{route}}/{{version}}/informe-agenda)
