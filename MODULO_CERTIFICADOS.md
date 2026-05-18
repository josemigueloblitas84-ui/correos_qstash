# Modulo de Certificados

## 1. Resumen general

Este documento describe el modulo de certificados del proyecto, desde la idea inicial hasta el estado actual.

Hoy el modulo permite:

- que un admin suba plantillas PDF
- que el admin construya el certificado en un canvas con `Fabric.js`
- que el admin guarde la estructura en base de datos
- que el usuario vea un boton en el topbar para emitir certificados
- que el usuario emita una sola vez un certificado por plantilla
- que el usuario vea una previsualizacion y un QR publico de verificacion
- que el usuario descargue su PDF hasta 5 veces
- que el QR no descargue nada ni pida login, sino que muestre una vista publica de verificacion

## 2. Evolucion del modulo

### 2.1 Primera idea

Al inicio se queria trabajar directamente encima del PDF:

- mover textos
- mover firma
- mover QR
- redimensionar bloques

Luego se vio que editar directamente el contenido interno del PDF no era una buena idea.

### 2.2 Constructor visual

Se migro la edicion a un constructor visual:

- el PDF queda solo como referencia
- el certificado real se arma en un lienzo en blanco
- el lienzo usa `Fabric.js`

Con eso se pudo:

- agregar textos
- agregar campos dinamicos
- agregar firmas
- agregar QR
- agregar imagenes
- mover y redimensionar todo
- aplicar estilos de texto

### 2.3 Persistencia

Primero se penso guardar la estructura en JSON sin base de datos.  
Despues se migro a base de datos, que es el estado actual.

### 2.4 Generacion PDF

Se intento reconstruir el PDF final con `dompdf`, pero la fidelidad visual no era suficiente, sobre todo para:

- firmas
- posiciones exactas
- escalas
- tipografias

Por eso el flujo final se movio a:

- `Fabric.js` para construir
- `jsPDF` para exportar

Eso da una salida mucho mas fiel al canvas.

### 2.5 Flujo final del usuario

El flujo actual ya no es:

- crear PDF y descargar de inmediato

Ahora es:

1. el admin prepara la plantilla
2. el usuario emite el certificado
3. el sistema guarda la emision en `certificado_emitidos`
4. se muestra una previsualizacion
5. se muestra un QR publico de verificacion
6. el usuario puede descargar su PDF hasta 5 veces desde el sistema
7. el QR solo verifica y muestra que el certificado fue emitido

## 3. Librerias y dependencias usadas

### Backend

- `chillerlan/php-qrcode`
  - se usa para generar el QR real
  - se personalizo con salida SVG propia

- `spatie/laravel-permission`
  - ya existe en el proyecto
  - se usa para el permiso `descargar certificado`

- `App\Models\User`
  - es el unico modelo usado para este modulo

### Frontend

- `Fabric.js`
  - constructor visual del certificado
  - objetos de texto, firma, imagen y QR

- `jsPDF`
  - exportacion del certificado a PDF

- `SweetAlert2`
  - mensajes del editor y del flujo de certificados

- Google Fonts
  - `Montserrat`
  - `Poppins`
  - `Lato`
  - `Open Sans`
  - `Roboto Slab`

### Cosas evaluadas y descartadas

- `creagia/laravel-sign-pad`
  - no se uso
  - la firma final se resolvio con canvas y carga de imagen

- `Simple QrCode`
  - se considero
  - pero el flujo actual usa `chillerlan/php-qrcode`

- `dompdf` como motor principal del PDF final
  - descartado para el modulo de certificados por fidelidad visual

## 4. Base de datos

El modulo usa 4 tablas principales y una migracion extra para descargas.

### 4.1 `certificado_plantillas`

Guarda la plantilla base del certificado.

Campos:

- `id`
- `nombre`
- `archivo_pdf`
- `json_estructura`
- `tamano_hoja`
- `orientacion`
- `estado`
- `created_at`
- `updated_at`

Notas:

- `archivo_pdf` apunta al PDF de referencia
- `json_estructura` guarda respaldo de la estructura
- la fuente real de edicion prioriza `certificado_campos`

### 4.2 `certificado_campos`

Guarda cada bloque del constructor.

Campos:

- `id`
- `certificado_plantilla_id`
- `tipo`
- `nombre_campo`
- `texto_default`
- `valor_dinamico`
- `pos_x`
- `pos_y`
- `ancho`
- `alto`
- `font_family`
- `font_size`
- `font_weight`
- `font_style`
- `text_align`
- `color`
- `line_height`
- `char_spacing`
- `orden`
- `metadata`
- `created_at`
- `updated_at`

Tipos de campo:

- `texto`
- `campo_dinamico`
- `firma`
- `qr`
- `imagen`

`metadata` guarda principalmente:

- `underline`
- `image_src`
- `qr_value`
- `qr_style`

### 4.3 `certificado_emitidos`

Guarda cada emision real de un certificado para un usuario.

Campos base:

- `id`
- `certificado_plantilla_id`
- `user_id`
- `codigo_hash`
- `nombre_generado`
- `ci_generado`
- `horas_generadas`
- `archivo_pdf`
- `fecha_emision`
- `estado`
- `created_at`
- `updated_at`

Campos agregados despues:

- `cantidad_descargas`
- `descargado_en`

Notas:

- `codigo_hash` es unico
- el QR publico apunta a este hash
- `cantidad_descargas` se limita a 5 en el flujo actual del sistema

### 4.4 `certificado_verificaciones`

Guarda cada consulta publica hecha sobre un hash.

Campos:

- `id`
- `certificado_emitido_id`
- `hash_consultado`
- `ip`
- `user_agent`
- `fecha_verificacion`
- `resultado`
- `created_at`
- `updated_at`

Resultados posibles:

- `valido`
- `invalido`
- `revocado`

### 4.5 Relaciones

- `certificado_plantillas` 1:N `certificado_campos`
- `certificado_plantillas` 1:N `certificado_emitidos`
- `users` 1:N `certificado_emitidos`
- `certificado_emitidos` 1:N `certificado_verificaciones`

## 5. Migraciones involucradas

Archivos:

- `database/migrations/2026_05_14_103037_create_certificado_plantillas_table.php`
- `database/migrations/2026_05_14_103043_create_certificado_campos_table.php`
- `database/migrations/2026_05_14_103054_create_certificado_emitidos_table.php`
- `database/migrations/2026_05_14_103101_create_certificado_verificaciones_table.php`
- `database/migrations/2026_05_15_120000_add_single_use_columns_to_certificado_emitidos_table.php`

## 6. Datos dinamicos usados

Los campos dinamicos actuales son:

- `nombre_completo`
- `ci`
- `horas_institucionales`

Origen:

- `nombre_completo` -> `users.name`
- `ci` -> `users.cod_estudiante`
- `horas_institucionales` -> `ControlHorasService`

El formato final de horas en certificado es:

- `HH:MM`

Ejemplo:

- `120:30:00` se convierte a `120:30`

## 7. Arquitectura actual del modulo

Este modulo no usa modelos Eloquent nuevos para certificados.  
La logica trabaja con:

- `Controller`
- `Service`
- `Request`
- `DB::table(...)`

Solo se usa el modelo:

- `App\Models\User`

## 8. Archivos activos del modulo

### 8.1 Controllers

- `app/Http/Controllers/Certificado/CertificadoPlantillaController.php`

Responsabilidades:

- listado admin de plantillas
- formulario create
- subida de PDF
- edicion de plantilla
- guardado de estructura
- preview de certificado
- preview de QR
- tabla de certificados del usuario
- pagina de emision
- payload de emision
- guardado del PDF emitido
- pagina de descarga autenticada
- payload de descarga autenticada
- vista publica de verificacion

### 8.2 Requests

- `app/Http/Requests/Certificado/CertificadoPlantillaStoreRequest.php`
- `app/Http/Requests/Certificado/CertificadoStructureStoreRequest.php`

#### `CertificadoPlantillaStoreRequest`

Valida:

- `nombre`
- `archivo_pdf`

#### `CertificadoStructureStoreRequest`

Valida:

- configuracion de pagina
- elementos del canvas
- posiciones y tamaños
- estilos de texto
- configuracion del QR

### 8.3 Services

- `app/Services/Certificado/CertificadoPlantillaService.php`

Es el nucleo del modulo.  
Se encarga de:

- listar plantillas
- decidir si hay plantilla disponible para el topbar
- listar certificados del usuario
- guardar plantillas
- obtener PDF de referencia
- cargar estructura
- guardar estructura
- reconstruir estructura desde `certificado_campos`
- rellenar campos dinamicos
- emitir certificados
- guardar PDF emitido
- controlar descargas autenticadas
- generar datos de verificacion
- construir QR

### 8.4 Clases QR custom

- `app/Services/Certificado/Qr/CertificadoQrOptions.php`
- `app/Services/Certificado/Qr/CertificadoQrSvgOutput.php`

#### `CertificadoQrOptions`

Agrega opciones custom para el QR:

- `cornerFrameShape`
- `cornerDotShape`
- `cornerTopLeft`
- `cornerTopRight`
- `cornerBottomLeft`

#### `CertificadoQrSvgOutput`

Personaliza la salida SVG del QR para:

- controlar forma de esquinas
- controlar puntos internos de esquina
- ocultar o mostrar ciertas esquinas
- convertir patrones de alineacion internos para que no se vean como cuadrados grandes

### 8.5 Vistas blade

#### Admin

- `resources/views/certificados/index.blade.php`
  - tabla de plantillas

- `resources/views/certificados/create.blade.php`
  - formulario para subir PDF

- `resources/views/certificados/edit.blade.php`
  - constructor principal
  - canvas + panel lateral + PDF de referencia

#### Usuario

- `resources/views/certificados/user-index.blade.php`
  - tabla de certificados del usuario
  - acciones:
    - `Emitir certificado`
    - `Descargar PDF`

- `resources/views/certificados/generate.blade.php`
  - pantalla para emitir una plantilla
  - muestra:
    - boton emitir
    - QR de verificacion
    - previsualizacion del certificado

- `resources/views/certificados/download-issued.blade.php`
  - reconstruye el certificado emitido
  - genera el PDF
  - permite la descarga autenticada
  - incrementa el contador de descargas

#### Publica

- `resources/views/certificados/verify.blade.php`
  - vista publica del QR
  - no requiere login
  - no descarga nada
  - solo muestra:
    - estado del certificado
    - nombre
    - CI
    - horas
    - fecha
    - plantilla

### 8.6 Assets

- `public/assets/js/certificados.js`
- `public/assets/css/certificados.css`

## 9. Archivos integrados fuera del modulo

### Header / Topbar

- `resources/views/plantilla/header.blade.php`

Aqui aparece el boton:

- `Emitir certificado`

Reglas:

- solo se muestra con `@can('descargar certificado')`
- si no hay plantilla valida con bloque QR, muestra `Sin certificado`

### AppServiceProvider

- `app/Providers/AppServiceProvider.php`

Usa `View::composer('plantilla.header', ...)` para compartir:

- `dashboardCertificate`

Ese arreglo decide si el topbar muestra:

- enlace a `mis-certificados.index`
- o el estado `Sin certificado`

## 10. Rutas actuales

### Publica

- `GET /certificados/verificar/{hash}`
  - nombre: `certificados.verificar`

### Admin autenticado

Grupo `certificados.*`:

- `GET /certificados`
- `GET /certificados/create`
- `POST /certificados/qr-preview`
- `POST /certificados/plantillas`
- `GET /certificados/plantillas/{archivo}`
- `GET /certificados/plantillas/{archivo}/edit`
- `GET /certificados/plantillas/{archivo}/preview`
- `POST /certificados/plantillas/{archivo}/estructura`

### Usuario autenticado con permiso

Requieren:

- `auth`
- `verified`
- `permission:descargar certificado`

Rutas:

- `GET /mis-certificados`
  - `mis-certificados.index`

- `GET /mis-certificados/{archivo}/emitir`
  - `mis-certificados.emitir`

- `GET /mis-certificados/{archivo}/payload`
  - `mis-certificados.payload`

- `POST /mis-certificados/emisiones/{emitido}/archivo`
  - `mis-certificados.emitidos.archivo.store`

- `GET /mis-certificados/emisiones/{emitido}/descargar-pdf`
  - `mis-certificados.emitidos.descargar`

- `GET /mis-certificados/emisiones/{emitido}/payload-descarga`
  - `mis-certificados.emitidos.payload`

## 11. Flujo funcional actual

### 11.1 Flujo del admin

1. entra a `Certificados`
2. sube una plantilla PDF
3. entra a `Editar`
4. arma el certificado en canvas
5. agrega:
   - texto
   - campo dinamico
   - firma
   - QR
   - imagen
6. ajusta:
   - tamaño de hoja
   - orientacion
   - estilos
   - posicion
   - tamaño
7. guarda estructura
8. puede abrir `Vista previa PDF`

### 11.2 Flujo del usuario

1. tiene permiso `descargar certificado`
2. ve en el topbar el boton `Emitir certificado`
3. entra a la tabla `Mis certificados`
4. ve las plantillas activas con QR
5. pulsa `Emitir certificado`
6. el sistema:
   - toma sus datos reales
   - genera el hash unico
   - guarda en `certificado_emitidos`
   - construye el QR publico
   - guarda el PDF emitido en storage
7. se muestra:
   - QR de verificacion
   - previsualizacion
8. vuelve a la tabla
9. el boton `Emitir certificado` queda bloqueado como `Emitido`
10. se habilita `Descargar PDF`
11. puede descargar hasta 5 veces

### 11.3 Flujo del QR

1. se escanea el QR
2. abre la ruta publica `/certificados/verificar/{hash}`
3. el sistema busca el hash
4. registra el acceso en `certificado_verificaciones`
5. muestra solo una pagina publica de validacion

Importante:

- el QR no pide login
- el QR no descarga nada
- el QR no muestra el sistema interno

## 12. Reglas del flujo actual

### Emision

- una emision por usuario por plantilla
- si ya existe una emision valida, se reutiliza

### Descarga autenticada

- se hace desde el sistema, no desde el QR
- maximo 5 veces por emision

### Verificacion publica

- no descarga PDF
- no pasa por login
- solo verifica

## 13. Editor visual

El editor principal esta en:

- `resources/views/certificados/edit.blade.php`
- `public/assets/js/certificados.js`
- `public/assets/css/certificados.css`

### Elementos soportados

- texto
- campo dinamico
- firma
- QR
- imagen

### Controles de hoja

- tamaño:
  - `A4`
  - `Carta`

- orientacion:
  - `Horizontal`
  - `Vertical`

### PDF de referencia

El PDF ya no se edita directamente.  
Se muestra abajo/derecha como guia visual, dentro de un `iframe`.

### Campos dinamicos

Se muestran de forma limpia en el canvas, no con `{{ }}`.

Representan:

- nombre completo
- CI
- horas institucionales

### Textos

Soportan:

- saltos de linea con Enter
- alineacion
- fuente
- tamaño
- color
- negrita
- cursiva
- subrayado
- interlineado
- espaciado entre letras

### Firmas

Se pueden:

- dibujar en canvas
- subir como imagen

Se exportan como imagen real y se conservan mejor en PDF que en la etapa inicial.

### Imagenes

Se cargan desde cualquier ubicacion local del usuario.

### QR en el editor

El bloque QR soporta personalizacion visual:

- color principal
- color de fondo
- color del ojo
- patron:
  - `round`
  - `square`
- forma del marco de esquina:
  - `none`
  - `square`
  - `rounded`
  - `circle`
- forma del punto de esquina:
  - `none`
  - `square`
  - `circle`
- visibilidad de esquinas:
  - superior izquierda
  - superior derecha
  - inferior izquierda
- margen
- escala

Tiene previsualizacion real via backend.

## 14. Generacion del PDF

### Estado actual

El PDF final del modulo de certificados no usa una blade HTML para componer el documento final.

La estrategia actual es:

1. backend prepara el payload
2. frontend reconstruye elementos con `Fabric.js`
3. frontend exporta con `jsPDF`

Esto se usa en:

- `generate.blade.php`
- `download-issued.blade.php`

### Ventajas

- mucha mas fidelidad al canvas
- mejor control de firma
- mejor control de QR
- mejor respeto de posiciones

## 15. Datos que se guardan por tipo de elemento

### Texto

- contenido
- posicion
- tamaño
- estilo

### Campo dinamico

- nombre del campo dinamico
- posicion
- tamaño
- estilo

### Firma

- imagen resultante
- posicion
- tamaño

### QR

- posicion
- tamaño
- `qr_value`
- `qr_style`

### Imagen

- `image_src`
- posicion
- tamaño

## 16. Permisos

El permiso requerido para el flujo del usuario es exactamente:

- `descargar certificado`

Se usa en:

- middleware de rutas `mis-certificados.*`
- `@can('descargar certificado')` en el topbar

## 17. Storage usado

### Plantillas

Se guardan en:

- `storage/app/public/certificados/plantillas`

### Certificados emitidos

Se guardan en:

- `storage/app/public/certificados/emitidos`

## 17.1 Configuracion de IP local para pruebas con QR

Para que el QR pudiera abrirse correctamente desde un celular dentro de la misma red, se ajusto la configuracion local del proyecto para no depender de `localhost`.

Se hizo lo siguiente:

- en `.env` se cambio `APP_URL` para usar la IP local de la maquina
- en este caso se uso:
  - `http://192.168.100.89:8000`
- Laravel se levanto escuchando en red local con:
  - `php artisan serve --host=0.0.0.0 --port=8000`
- se habilito el puerto `8000` en el firewall de Windows
- el celular y la PC debian estar conectados a la misma red WiFi

Esto permitio que:

- el QR dejara de apuntar a `localhost`
- el celular pudiera abrir la URL publica de verificacion
- la ruta `/certificados/verificar/{hash}` funcionara correctamente fuera del navegador de la PC

## 18. Archivos eliminados o descartados

Estos ya no forman parte del flujo activo:

- `resources/views/certificados/pdf.blade.php`
  - eliminada
  - pertenecia a la etapa `dompdf`

- `resources/views/certificados/download.blade.php`
  - eliminada en una etapa intermedia

- logica vieja de descarga directa desde QR
  - descartada

- descarga real desde el QR
  - descartada
  - ahora el QR solo verifica

- multiseleccion de usuarios para emitir certificados masivos
  - fue considerada
  - no es el flujo final actual

## 19. Cosas importantes a recordar

### 19.1 El QR es obligatorio para emision real

La plantilla debe tener al menos un bloque `qr` guardado en `certificado_campos`.

Si no existe, la plantilla:

- no debe aparecer como disponible para emitir

### 19.2 El topbar no toma cualquier plantilla

Solo considera plantillas:

- activas
- con bloque QR

### 19.3 La vista publica no debe autenticarse

La ruta:

- `/certificados/verificar/{hash}`

debe seguir siendo publica.

### 19.4 El contador de IDs en `certificado_campos`

Se corrigio para:

- actualizar campos existentes
- crear solo los nuevos
- borrar los quitados

No debe regenerar IDs si el admin guarda sin cambios.

## 20. Flujo tecnico resumido

### Admin

- `create.blade.php`
- `index.blade.php`
- `edit.blade.php`
- `CertificadoPlantillaController`
- `CertificadoPlantillaService`

### Usuario

- `header.blade.php`
- `user-index.blade.php`
- `generate.blade.php`
- `download-issued.blade.php`
- `CertificadoPlantillaController`
- `CertificadoPlantillaService`

### Publico

- `verify.blade.php`
- `verifyCertificate()`
- `getVerificationData()`

## 21. Archivos clave del modulo

### Backend

- `app/Http/Controllers/Certificado/CertificadoPlantillaController.php`
- `app/Http/Requests/Certificado/CertificadoPlantillaStoreRequest.php`
- `app/Http/Requests/Certificado/CertificadoStructureStoreRequest.php`
- `app/Services/Certificado/CertificadoPlantillaService.php`
- `app/Services/Certificado/Qr/CertificadoQrOptions.php`
- `app/Services/Certificado/Qr/CertificadoQrSvgOutput.php`
- `app/Providers/AppServiceProvider.php`
- `routes/web.php`

### Views

- `resources/views/certificados/index.blade.php`
- `resources/views/certificados/create.blade.php`
- `resources/views/certificados/edit.blade.php`
- `resources/views/certificados/user-index.blade.php`
- `resources/views/certificados/generate.blade.php`
- `resources/views/certificados/download-issued.blade.php`
- `resources/views/certificados/verify.blade.php`
- `resources/views/plantilla/header.blade.php`

### Frontend

- `public/assets/js/certificados.js`
- `public/assets/css/certificados.css`

### Base de datos

- `database/migrations/2026_05_14_103037_create_certificado_plantillas_table.php`
- `database/migrations/2026_05_14_103043_create_certificado_campos_table.php`
- `database/migrations/2026_05_14_103054_create_certificado_emitidos_table.php`
- `database/migrations/2026_05_14_103101_create_certificado_verificaciones_table.php`
- `database/migrations/2026_05_15_120000_add_single_use_columns_to_certificado_emitidos_table.php`

## 22. Conclusiones

El modulo termino evolucionando hacia una arquitectura mixta:

- base de datos para plantillas, campos, emisiones y verificaciones
- canvas con `Fabric.js` para construir visualmente
- `jsPDF` para exportar con fidelidad
- `chillerlan/php-qrcode` para QR custom
- `spatie/laravel-permission` para controlar acceso del usuario

La idea final ya no es editar un PDF como documento vivo, sino:

- usar el PDF como referencia
- guardar una estructura propia del sistema
- emitir certificados reales por usuario
- verificar publicamente con QR

## 23. Estado actual recomendado

Si se sigue ampliando el modulo, los siguientes puntos naturales serian:

- manejar multiples plantillas activas por categoria
- definir mejor reglas de reemision
- mejorar reportes de verificaciones
- agregar revocacion manual de certificados
- agregar filtros por usuario, fecha y estado en certificados emitidos
