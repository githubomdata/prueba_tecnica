# Mesa de Incidentes

Aplicación Laravel deliberadamente pequeña para una prueba técnica Full Stack con énfasis backend. Esta carpeta es el único material de trabajo que debe recibir el candidato.

## Stack fijado

- PHP 8.3.14
- Laravel 12.69.2
- Livewire 3.8.9
- Laravel Sanctum 4.3.3
- MySQL 8.0.30
- PHPUnit 11.5.56
- Vite 6.4.3
- Node.js 25.2.1 y npm 11.5.2 en el equipo preparado

Las versiones PHP y JavaScript quedan fijadas en `composer.lock` y `package-lock.json`. El proyecto no necesita Docker.

## Estado inicial

La aplicación permite iniciar sesión, listar cinco incidentes por página, crear y editar incidentes y cerrar sesión. La autorización se aplica en backend mediante `IncidentePolicy` y el atributo `puede_editar`. El aspirante debe revisar y corregir la configuración inicial para que el editor pueda modificar datos y el lector solo pueda consultar.

La base de datos y `PrioridadIncidente` ya contienen prioridades. El flujo inicial usa el valor `media` por defecto. El soporte de prioridad solicitado en [ENUNCIADO.md](ENUNCIADO.md) está intencionalmente pendiente.

## Acceso preparado

Las cuentas disponibles después de ejecutar el seeder son las siguientes. Los perfiles indicados corresponden al comportamiento esperado al completar el ejercicio; la configuración inicial de permisos debe revisarse durante la práctica.

| Perfil | Correo | Contraseña |
|---|---|---|
| Editor | `editor@mesa.test` | `Evaluacion2026!` |
| Solo lectura | `lector@mesa.test` | `Evaluacion2026!` |

Son cuentas ficticias exclusivas de esta evaluación.

## Preparación inicial del operador

La preparación se realiza antes de la entrevista. No entregue al candidato credenciales MySQL administrativas.

Prepare cada práctica con los datos del seeder versionado para evitar que una base utilizada anteriormente conserve correcciones de otra sesión. La configuración inicial de permisos forma parte del ejercicio y su corrección corresponde al aspirante.

1. Cree exclusivamente las bases `evaluacion_fullstack` y `evaluacion_fullstack_testing`.
2. Cree un usuario MySQL local limitado a esas dos bases y configure sus credenciales en `.env` y, si se usa, `.env.testing`.
3. Ejecute:

```powershell
Copy-Item .env.example .env
php artisan key:generate
composer install --no-interaction
npm ci
php artisan migrate:fresh --seed --force
npm run build
php artisan test
```

Antes de `migrate:fresh`, compruebe en `.env` que `DB_DATABASE=evaluacion_fullstack`. La suite tiene una defensa adicional: solo comienza con `APP_ENV=testing` y `DB_DATABASE=evaluacion_fullstack_testing`.

En este equipo Laragon puede servir el proyecto por su host local. Como alternativa:

```powershell
php artisan serve
```

## Comandos de verificación

```powershell
php artisan about
php artisan route:list
php artisan test
npm run build
vendor\bin\pint --test
```

Los tests crean sus propios datos y nunca dependen del seeder. El correo usa el driver `log` o `array`; no realiza envíos externos.

## Estructura relevante

- `app/Actions/GuardarIncidente.php`: autorización, validación y persistencia reutilizable.
- `app/Enums/PrioridadIncidente.php`: valores de prioridad ya disponibles.
- `app/Livewire/Incidentes`: formulario y listado.
- `app/Policies/IncidentePolicy.php`: permisos de edición.
- `database/seeders/EvaluacionSeeder.php`: datos ficticios deterministas.
- `tests/Feature/IncidentesBaseTest.php`: regresiones de la funcionalidad inicial.
- `tests/Feature/PrioridadIncidenteTest.php`: clase preparada para el ejercicio.

## Reglas de seguridad

- No cambie los nombres de las bases por los de otro sistema.
- No copie `.env`, datos, código o credenciales de aplicaciones corporativas.
- No ejecute migraciones o pruebas hasta comprobar la base de destino.
- No publique el ejercicio ni entregas de candidatos.
- Mantenga fuera de esta carpeta la rúbrica, la solución y las pruebas privadas.

## Inicio de la práctica

El operador debe dejar abierta esta carpeta, confirmar que `git status` está limpio y entregar al candidato [ENUNCIADO.md](ENUNCIADO.md). La instalación, el seeding y el build no forman parte de los 35 minutos.

## Segunda fase de evaluación

La Fase 1 mantiene sus 35 minutos y sus ejercicios pendientes. Después comienza una Fase 2 de 30 minutos para ampliar la API REST suministrada. Las dos fases suman 65 minutos de desarrollo; la preparación y la explicación de la primera fase quedan fuera de ese tiempo.

La API base ya contiene login, logout, listado, detalle, creación y actualización parcial. Su contrato está en [docs/API_BASE.md](docs/API_BASE.md) y las solicitudes de ejemplo en [la colección Postman](docs/Mesa_Incidentes.postman_collection.json). El filtro por prioridad y el endpoint específico de prioridad permanecen pendientes para el aspirante.

El listado ya recibe `ListarIncidentesRequest`, cuya autorización devuelve `false` inicialmente: responde 403 con token válido para ambos perfiles hasta que el aspirante la complete como parte del primer requerimiento. El método `actualizarPrioridad()` y su Request se entregan como plantillas pendientes; la nueva ruta todavía no está registrada y responde 404 inicialmente.

La instalación inicial aplica también la migración de tokens de Sanctum. Para una base ya preparada se aplica únicamente esa migración, después de verificar el destino, siguiendo las instrucciones privadas del operador. No se debe reinicializar una base con trabajo del aspirante.

Antes de iniciar la segunda fase, el operador comprueba que ambas cuentas pueden obtener tokens y consultar el detalle de un incidente, que solo el editor puede escribir mediante las operaciones base y que existe la tabla de tokens. El 403 inicial del listado es intencional y forma parte del trabajo del aspirante. La preparación entre fases se realiza con el procedimiento del operador guardado fuera de esta carpeta. No se ejecuta `migrate:fresh`, no se vuelve a ejecutar el seeder y no se cambian archivos del aspirante durante esa transición.

La autorización es la misma para Livewire y API: depende de `IncidentePolicy` y de la cuenta persistida. La API utiliza exclusivamente tokens Bearer de Sanctum, con vencimiento de dos horas; la sesión web se conserva independiente.

`tests/Feature/ApiBaseTest.php` verifica la infraestructura suministrada. Las nueve pruebas anteriores y estas regresiones se ejecutan con `php artisan test`. La configuración utiliza las credenciales de `.env.testing`; no fuerza un usuario administrativo. La comprobación de entorno y base efectiva se realiza antes de que `RefreshDatabase` pueda migrar. Estas pruebas no constituyen un requisito adicional para el aspirante en la Fase 2.

Las pruebas de login y revocación comprueban el token mediante el detalle, que ya está disponible. Para el listado, la suite base admite el 403 JSON de la plantilla inicial; si se habilita, exige el contrato paginado suministrado. Que esta suite pase acredita la infraestructura comprobada, pero la aceptación de la Fase 2 requiere verificar por HTTP los dos requerimientos, incluido el listado con respuesta 200.
