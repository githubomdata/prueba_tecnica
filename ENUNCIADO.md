# Práctica: soporte de prioridades

Tiempo: **35 minutos**.

Se requiere completar el soporte de prioridades del módulo de Incidentes.

Las prioridades disponibles son Baja, Media, Alta y Crítica. La base de datos y el enum ya están preparados.

Debes permitir seleccionar y modificar la prioridad, mostrarla en el listado y filtrar por ella. Respeta la arquitectura existente, agrega al menos una prueba automatizada, ejecútala y realiza un commit.

## Criterios de aceptación

1. Crear y editar permite seleccionar las cuatro prioridades.
2. Backend rechaza prioridad vacía o inválida.
3. La prioridad se guarda correctamente.
4. La edición conserva inicialmente la prioridad existente.
5. El listado muestra una etiqueta legible.
6. El filtro admite “Todas” y cada prioridad.
7. Cambiar el filtro vuelve a la primera página.
8. Se muestra un mensaje cuando no hay resultados.
9. El usuario lector puede consultar incidentes, pero no crearlos ni editarlos. Revisa y corrige la configuración inicial necesaria para garantizarlo.
10. Se añade y ejecuta al menos una prueba de comportamiento.
11. Revisas tus cambios y realizas un commit.

No se solicita una API nueva, LDAP, migraciones nuevas, Docker, dependencias nuevas ni un rediseño visual.

## Entrega

Al terminar, detén la programación y prepárate para explicar durante cinco minutos:

- qué funciona y qué quedó pendiente;
- qué arquitectura reutilizaste y cómo compartirías la regla con una API;
- qué prueba ejecutaste y cuál añadirías;
- qué falta para considerar terminado el cambio.

## Herramientas

Se permite documentación oficial local y autocompletado convencional. No se permiten asistentes generativos, ayuda externa, instalación de dependencias ni acceso a servicios corporativos. Si el entorno falla, informa al evaluador; el tiempo de reparación no se imputará automáticamente a la práctica.

# FASE 2 — AMPLIACIÓN DE API REST CON LARAVEL

## Objetivo y tiempo

Amplía la API REST base de Mesa de Incidentes que se entrega junto con las plantillas pendientes descritas en esta fase. Esta fase comienza después de terminar la primera evaluación y de que el operador compruebe el entorno y los perfiles de acceso.

Dispones de **30 minutos**, destinados exclusivamente a los dos requerimientos siguientes. La primera fase conserva sus 35 minutos: ambas suman 65 minutos de desarrollo, sin incluir la preparación del entorno ni la explicación prevista al terminar la primera fase.

Los entregables técnicos de la primera fase corresponden a esa fase. En esta segunda fase no se exige escribir pruebas automatizadas ni documentación.

## API y recursos suministrados

La API suministrada contiene los siguientes seis endpoints base. Sus operaciones ya están implementadas; el listado incorpora una plantilla Request pendiente de completar como parte del primer requerimiento:

| Método | Endpoint | Funcionalidad disponible |
|---|---|---|
| POST | `/api/v1/login` | Autentica las credenciales y entrega un token Bearer |
| POST | `/api/v1/logout` | Revoca el token utilizado en esa solicitud |
| GET | `/api/v1/incidentes` | Listado de cinco incidentes por página; autorización del Request pendiente |
| GET | `/api/v1/incidentes/{id}` | Consulta el detalle de un incidente |
| POST | `/api/v1/incidentes` | Crea un incidente con los campos del flujo base |
| PATCH | `/api/v1/incidentes/{id}` | Actualiza parcialmente los campos del flujo base |

El contrato de la API suministrada está en [docs/API_BASE.md](docs/API_BASE.md). También se entrega una colección importable en [docs/Mesa_Incidentes.postman_collection.json](docs/Mesa_Incidentes.postman_collection.json), con solicitudes base y ejemplos de las dos ampliaciones pendientes.

## Autenticación disponible

La API utiliza Laravel Sanctum. Obtén un token con las cuentas ficticias de evaluación:

| Perfil en esta fase | Correo | Contraseña |
|---|---|---|
| Editor | `editor@mesa.test` | `Evaluacion2026!` |
| Solo lectura | `lector@mesa.test` | `Evaluacion2026!` |

Ejemplo de autenticación:

```http
POST /api/v1/login
Content-Type: application/json
Accept: application/json

{
  "email": "editor@mesa.test",
  "password": "Evaluacion2026!"
}
```

La respuesta contiene `token_type`, `token`, `expires_at` y los datos públicos de la cuenta. Usa el valor de `token` en las solicitudes protegidas:

```http
GET /api/v1/incidentes?page=1
Authorization: Bearer {token}
Accept: application/json
```

Los permisos esperados en esta fase permiten consultar a ambos perfiles y crear o actualizar solo al editor. El listado responde 403 con token válido hasta completar la autorización de su Request; esta parte corresponde al primer requerimiento. Si fallan el login, el detalle o los permisos de las operaciones de escritura base, informa al evaluador antes de comenzar.

## Estructura que puedes consultar y reutilizar

| Archivo | Responsabilidad |
|---|---|
| `routes/api.php` | Rutas versionadas y protección `auth:sanctum` |
| `app/Http/Controllers/Api/V1/AuthController.php` | Emisión y revocación de tokens |
| `app/Http/Controllers/Api/V1/IncidenteController.php` | Operaciones base de incidentes |
| `app/Http/Requests/Api/LoginRequest.php` | Validación del login |
| `app/Http/Requests/Api/GuardarIncidenteRequest.php` | Autorización y validación de escritura de la API |
| `app/Http/Requests/Api/ListarIncidentesRequest.php` | Plantilla pendiente para la autorización y validación del filtro por prioridad |
| `app/Http/Requests/Api/ActualizarPrioridadIncidenteRequest.php` | Plantilla pendiente para la autorización y validación de la actualización exclusiva de prioridad |
| `app/Actions/GuardarIncidente.php` | Reglas compartidas y persistencia del flujo base |
| `app/Models/Incidente.php` | Modelo y relación con la persona responsable |
| `app/Enums/PrioridadIncidente.php` | Valores y etiquetas de prioridad |
| `app/Policies/IncidentePolicy.php` | Permisos aplicados en backend |
| `app/Http/Resources/IncidenteResource.php` | Representación JSON de los incidentes |

### Plantillas y puntos de trabajo

Los archivos `ListarIncidentesRequest.php` y `ActualizarPrioridadIncidenteRequest.php` se entregan como plantillas para completar: `authorize()` devuelve `false` y `rules()` devuelve un arreglo vacío. Ambos Request ya están declarados como parámetros en sus métodos del controlador. Completa su autorización y sus reglas de validación. El listado responderá 403 para usuarios autenticados hasta que completes la autorización de `ListarIncidentesRequest`.

Los comentarios `TODO (Fase 2, requerimiento 1)` en `IncidenteController::index()` señalan dónde ampliar el listado. El método `IncidenteController::actualizarPrioridad()` se entrega vacío, con `ActualizarPrioridadIncidenteRequest` y el incidente como parámetros. Los comentarios `TODO (Fase 2, requerimiento 2)` en ese método y dentro del grupo protegido de `routes/api.php` señalan dónde completar la actualización y registrar la ruta específica de prioridad. La implementación de ambos requerimientos corresponde al aspirante.

## Requerimiento 1 — Filtrar incidentes por prioridad

Tiempo orientativo: **12 minutos**.

Amplía el listado existente, método `GET`, para admitir el parámetro opcional `prioridad`:

```http
GET /api/v1/incidentes?prioridad=alta&page=1
Authorization: Bearer {token}
Accept: application/json
```

Criterios de aceptación:

1. Sin `prioridad`, se conserva el comportamiento del listado suministrado.
2. Con una prioridad válida, se devuelven únicamente los incidentes que coinciden.
3. Los valores admitidos son los definidos en `PrioridadIncidente`: `baja`, `media`, `alta` y `critica`.
4. Si se proporciona un valor inválido, la respuesta es HTTP 422 con los errores de validación correspondientes.
5. El filtro se aplica a la consulta antes de paginar; la página conserva cinco registros como máximo y los totales corresponden al conjunto filtrado.
6. La respuesta mantiene la estructura `data`, `links` y `meta`; sin coincidencias devuelve `data: []` y total cero.
7. La consulta correcta responde 200 y continúa exigiendo autenticación: sin token válido responde 401. Ambos perfiles pueden consultar.

## Requerimiento 2 — Actualizar exclusivamente la prioridad

Tiempo orientativo: **18 minutos**.

Implementa y registra un nuevo endpoint específico, método `PATCH`:

```http
PATCH /api/v1/incidentes/1/prioridad
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json

{
  "prioridad": "alta"
}
```

Sustituye `1` por el identificador de un incidente existente.

Criterios de aceptación:

1. La ruta respeta el prefijo `/api/v1` y utiliza la autenticación Sanctum ya suministrada.
2. Sin token válido responde 401.
3. Utiliza `IncidentePolicy` o el mecanismo de autorización existente: el editor puede modificar la prioridad y el lector recibe 403. Los permisos se determinan en backend.
4. `prioridad` es obligatoria y debe pertenecer al enum existente; un campo vacío, ausente o inválido produce 422.
5. Actualiza únicamente la prioridad del incidente solicitado. Los campos adicionales enviados por el cliente no modifican otros atributos.
6. Responde 200 con el incidente actualizado, usando `IncidenteResource` y la estructura JSON existente.
7. Un identificador que no corresponde a un incidente devuelve 404.

## Alcance y entrega de esta fase

La funcionalidad suministrada comprende las seis operaciones base, la autenticación, la Policy, los modelos y el recurso JSON. El trabajo pendiente se limita a los dos requerimientos: completar la autorización y validación de sus Request, el filtro del listado y el método y la ruta de actualización exclusiva de prioridad.

No necesitas instalar Laravel, dependencias ni Sanctum; implementar autenticación; crear controladores base desde cero; reconstruir el CRUD; modificar el diseño visual de Livewire; crear documentación Swagger; implementar funcionalidades adicionales; ni escribir pruebas automatizadas.

Entrega las dos funcionalidades integradas en el proyecto y compruébalas mediante solicitudes HTTP con Postman, curl u otra herramienta ya disponible. No se impone una herramienta específica ni se exige una explicación escrita extensa. Se mantienen las reglas de uso de herramientas y ayuda externa de la prueba.
