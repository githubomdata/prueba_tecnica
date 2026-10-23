# API base de Mesa de Incidentes

La API suministrada contiene las operaciones base para autenticarse, consultar, crear y editar incidentes. Este documento distingue el estado inicial de las plantillas del comportamiento esperado al completar los dos requerimientos de la Fase 2 definidos en `ENUNCIADO.md`.

## Estado inicial de las plantillas

- `ListarIncidentesRequest` ya es un parámetro de `IncidenteController::index()`, pero su autorización y sus reglas están pendientes. Como `authorize()` devuelve `false`, `GET /incidentes` responde **403 con un token válido**, tanto para el editor como para el lector, con o sin `prioridad`. Completar esa plantilla y el filtro forma parte del primer requerimiento. Sin un token válido, la ruta protegida responde 401.
- `ActualizarPrioridadIncidenteRequest` también tiene autorización y reglas pendientes. El método `IncidenteController::actualizarPrioridad()` se entrega vacío y su ruta todavía no está registrada: `PATCH /incidentes/{id}/prioridad` responde **404 inicialmente**. Completar el Request, el método y la ruta forma parte del segundo requerimiento.

El 403 inicial del listado es intencional y corresponde al trabajo del aspirante. El operador comprueba el login, el detalle y los permisos de escritura de las operaciones base antes de comenzar la fase. Las URL de esta sección son relativas a `/api/v1`.

## URL y cabeceras

Con `php artisan serve`, la URL base es `http://127.0.0.1:8000/api/v1`. Si se utiliza el host local de Laragon, sustituye el origen por el de ese sitio y conserva `/api/v1`.

Envía `Accept: application/json` y, cuando haya cuerpo JSON, `Content-Type: application/json`. Los endpoints protegidos requieren `Authorization: Bearer {token}`. La sesión web no sustituye el token y el logout de API revoca únicamente el token utilizado.

## Endpoints base y estado de entrega

| Método | Ruta relativa a la URL base | Acceso esperado | Éxito esperado | Estado de entrega |
|---|---|---|---|---|
| POST | `/login` | Credenciales locales | 200 | Disponible |
| POST | `/logout` | Token válido | 200 | Disponible |
| GET | `/incidentes` | Editor y lector | 200 | 403 con token válido hasta completar la autorización del Request |
| GET | `/incidentes/{id}` | Editor y lector | 200 | Disponible |
| POST | `/incidentes` | Editor | 201 | Disponible |
| PATCH | `/incidentes/{id}` | Editor | 200 | Disponible |

Los perfiles de esta tabla corresponden a los permisos persistidos que el operador comprueba antes de comenzar. En el listado, esos perfiles podrán consultar al completar su autorización como parte del primer requerimiento.

## Login y logout

Las cuentas ficticias son `editor@mesa.test` y `lector@mesa.test`; ambas utilizan la contraseña `Evaluacion2026!`.

```http
POST /api/v1/login
Content-Type: application/json
Accept: application/json

{"email":"editor@mesa.test","password":"Evaluacion2026!"}
```

Respuesta 200, con valores ilustrativos:

```json
{
  "token_type": "Bearer",
  "token": "{token_emitido}",
  "expires_at": "2026-10-20T17:00:00.000000Z",
  "data": {"id": 1, "name": "Elena Editora", "email": "editor@mesa.test"}
}
```

Cada login emite un token válido durante dos horas. Guárdalo para esa sesión de evaluación; no lo publiques. El login admite seis intentos por minuto por dirección IP. Credenciales incorrectas devuelven 401; un correo inválido o campos obligatorios ausentes, 422.

```http
POST /api/v1/logout
Authorization: Bearer {token}
Accept: application/json
```

Respuesta 200: `{"message":"Sesión de API cerrada."}`. Ese token deja de autenticar y los demás tokens de la cuenta siguen vigentes hasta su revocación o vencimiento.

## Listado y detalle

La siguiente solicitud de listado responde 403 con token válido mientras la autorización de `ListarIncidentesRequest` esté pendiente. El comportamiento de colección descrito a continuación es el esperado al completarla; la validación del filtro y su aplicación también corresponden al primer requerimiento.

```http
GET /api/v1/incidentes?page=1
Authorization: Bearer {token}
Accept: application/json
```

La colección se ordena por `id` descendente y devuelve cinco incidentes por página. Utiliza `links` para navegar y `meta` para conocer la página y el total. Una colección vacía mantiene esas claves y devuelve `data: []`.

El detalle se consulta con `GET /api/v1/incidentes/{id}` y las mismas cabeceras. Devuelve un objeto bajo `data`; si no existe, responde 404.

Ejemplo de recurso, con valores ilustrativos:

```json
{
  "data": {
    "id": 1,
    "codigo": "INC-2026-001",
    "titulo": "Incidente ficticio",
    "descripcion": "Descripción del incidente de evaluación.",
    "estado": "abierto",
    "prioridad": "media",
    "prioridad_etiqueta": "Media",
    "responsable": {"id": 1, "nombre": "Ana Torres"},
    "created_at": "2026-10-20T15:00:00.000000Z",
    "updated_at": "2026-10-20T15:00:00.000000Z"
  }
}
```

El listado utiliza el mismo recurso dentro de `data: [...]` y añade `links` (`first`, `last`, `prev`, `next`) y `meta` (`current_page`, `from`, `last_page`, `links`, `path`, `per_page`, `to`, `total`). Las fechas se devuelven en ISO 8601 y UTC.

## Campos de creación y edición

| Campo | Crear | PATCH | Regla |
|---|---|---|---|
| `titulo` | Obligatorio | Opcional; si se envía, obligatorio | Texto, máximo 180 caracteres |
| `descripcion` | Obligatorio | Opcional; si se envía, obligatorio | Texto, de 10 a 5000 caracteres |
| `estado` | Obligatorio | Opcional; si se envía, obligatorio | `abierto`, `en_proceso` o `resuelto` |
| `responsable_id` | Obligatorio | Opcional; si se envía, obligatorio | Entero, persona existente y activa |

Los atributos no enviados en un PATCH se conservan. La API base ignora los campos que no forman parte de estas reglas, como `id`, `codigo`, `prioridad` o `puede_editar`. El código lo genera el servidor. En la creación base, la base de datos asigna prioridad `media`; la actualización base conserva la prioridad existente.

Selecciona un `responsable_id` válido a partir de un incidente de los datos preparados; no se suministra un endpoint adicional de personas.

```http
POST /api/v1/incidentes
Authorization: Bearer {token_editor}
Content-Type: application/json
Accept: application/json

{
  "titulo": "Servicio sin respuesta",
  "descripcion": "El servicio ficticio no responde desde la red interna.",
  "estado": "abierto",
  "responsable_id": 1
}
```

La creación devuelve 201, el recurso bajo `data` y la cabecera `Location` con la URL del detalle.

```http
PATCH /api/v1/incidentes/1
Authorization: Bearer {token_editor}
Content-Type: application/json
Accept: application/json

{"estado":"en_proceso"}
```

La actualización devuelve 200 con el recurso actualizado.

## Errores

| Código | Significado |
|---|---|
| 401 | Token ausente, inválido, revocado o expirado; también credenciales incorrectas en login |
| 403 | La solicitud autenticada no supera la autorización; en el listado inicial también ocurre porque `authorize()` está pendiente |
| 404 | Recurso o ruta inexistente |
| 405 | Método HTTP no disponible en esa ruta |
| 422 | Error de validación |
| 429 | Límite de intentos de login alcanzado; respeta `Retry-After` |
| 500 | Error interno con mensaje genérico |

Los errores devuelven `message`. La validación añade `errors`, con un arreglo de mensajes por campo, conforme al formato estándar de Laravel. Las rutas `/api/*` responden JSON incluso si se omite `Accept`, y no incluyen trazas, contraseñas ni detalles de infraestructura.

La colección `Mesa_Incidentes.postman_collection.json` permite configurar `base_url` (el origen, sin `/api/v1`) y `token` una sola vez. Los logins guardan el token automáticamente y la creación guarda `incidente_id`. Las dos solicitudes de la carpeta de ampliaciones son ejemplos para comprobar el trabajo posterior: no representan funcionalidad ya suministrada.
