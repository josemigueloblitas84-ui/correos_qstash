# Reporte de Agenda e Informe

<p align="center">
    <img src="/assets/img/MU8.png" alt="Reporte de Informe de Agenda" width="100%">
</p>

El modulo **Reporte de Agenda e Informe** permite consultar, filtrar, visualizar e imprimir la información registrada en agendas e informes.

Este módulo facilita el seguimiento de actividades, la revisión de cumplimiento y, en algunos casos, la validación de información segun el perfil del usuario.

## Que permite este módulo

Dependiendo de los permisos y del perfil del usuario, este módulo puede permitir:

- Consultar agendas registradas
- Consultar informes registrados
- Aplicar filtros por fecha y equipo
- Visualizar información consolidada
- Imprimir agendas
- Imprimir informes
- Validar informes, cuando corresponda

## Ingreso al módulo

Para ingresar a este módulo:

1. Ubique la opción **Reporte de Agenda/Informe** en el menu lateral.
2. Seleccione la opción.
3. El sistema mostrara la vista de consulta y filtros.

Si esta opción no aparece, el usuario no cuenta con acceso a este módulo.

## Tipos de consulta

El módulo permite trabajar con dos grandes grupos de información:

- Agenda
- Informe

Cada uno presenta su propia consulta según el tipo de búsqueda seleccionado.

## Filtros disponibles

<p align="center">
    <img src="/assets/img/MU8a.png" alt="Filtros Reporte de Informe de Agenda" width="100%">
</p>

Los filtros permiten acotar la información a consultar. Dependiendo de la pantalla, se pueden utilizar criterios como:

- Fecha desde
- Fecha hasta
- Equipo o departamento
- Tipo de búsqueda

### Recomendación

Antes de buscar, revise cuidadosamente los filtros seleccionados para evitar resultados incompletos o vacios.

## Consulta de agendas

Cuando el usuario selecciona la búsqueda de agendas, el sistema puede mostrar:

- Fecha registrada
- Nombre del usuario
- Equipo
- Rango de fechas agendadas
- Opción de ó o impresión

Esta consulta permite revisar las agendas registradas dentro de un periodo determinado.

## Consulta de informes

Cuando el usuario selecciona la búsqueda de informes, el sistema puede mostrar:

- Fecha de actividad
- Nombre del usuario
- Equipo
- Opción de visualización
- Estado de validación, si corresponde

Esta consulta permite revisar la información reportada en el módulo de informe de agenda.

## Visualizar e imprimir

El sistema permite abrir vistas previas para:

- Imprimir agendas
- Imprimir informes

Estas opciones son utiles para revisión, respaldo o presentación formal de la información.

@can('viewDocReporteValidation')
## Validación de informes

<p align="center">
    <img src="/assets/img/MU8b.png" alt="Validación Reporte de Informe de Agenda" width="100%">
</p>

En algunos casos, ciertos usuarios pueden validar informes.

Esta funcionalidad depende del perfil o configuración del usuario y permite marcar un informe como revisado o validado dentro del flujo del sistema.

### Importante

No todos los usuarios podran validar informes. Esta acción esta restringida segun configuración interna del sistema.
@endcan

## Problemas frecuentes

### El reporte no muestra resultados

Posibles causas:

- Rango de fechas incorrecto
- Filtros demasiado restrictivos
- No existen registros para el criterio seleccionado
- El usuario no tiene acceso a toda la información

### No puedo visualizar o imprimir

Posibles causas:

- El registro no existe
- El filtro no corresponde al registro buscado
- Falta algun permiso asociado al módulo

### No aparece la opción de validar

Posibles causas:

- El usuario no tiene habilitada esa funcionalidad
- El informe ya fue validado
- El perfil actual no permite realizar validaciones

## Buenas practicas

- Use filtros claros y consistentes
- Valide primero el rango de fechas
- Confirme si esta consultando agenda o informe
- Revise la información antes de imprimir
- No valide información sin haberla revisado previamente

## Importancia del módulo

Este módulo concentra la información final registrada en agendas e informes, por lo que es clave para procesos de control, seguimiento y análisis operativo.

## Siguiente paso recomendado

Despues de revisar reportes, se recomienda consultar el **Historial de Actividad** para comprender mejor la trazabilidad de las acciones ejecutadas dentro del sistema.

- [Historial de Actividad](/{{route}}/{{version}}/historial-actividad)
