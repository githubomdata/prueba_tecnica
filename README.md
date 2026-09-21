# Mesa de Incidentes

Aplicación Laravel deliberadamente pequeña para una prueba técnica Full Stack con énfasis backend. Esta carpeta es el único material de trabajo que debe recibir el candidato.

## Stack fijado

- PHP 8.3.14
- Laravel 12.69.2
- Livewire 3.8.9
- MySQL 8.0.30
- PHPUnit 11.5.56
- Vite 6.4.3
- Node.js 25.2.1 y npm 11.5.2 en el equipo preparado

Las versiones PHP y JavaScript quedan fijadas en `composer.lock` y `package-lock.json`. El proyecto no necesita Docker.

## Estado inicial

La aplicación permite iniciar sesión, listar cinco incidentes por página, crear y editar incidentes y cerrar sesión. La autorización se aplica en backend mediante `IncidentePolicy`: el editor modifica datos y el lector solo consulta.

La base de datos y `PrioridadIncidente` ya contienen prioridades. El flujo inicial usa el valor `media` por defecto. El soporte de prioridad solicitado en [ENUNCIADO.md](ENUNCIADO.md) está intencionalmente pendiente.

## Acceso preparado

Después de ejecutar el seeder:

| Perfil | Correo | Contraseña |
|---|---|---|
| Editor | `editor@mesa.test` | `Evaluacion2026!` |
| Solo lectura | `lector@mesa.test` | `Evaluacion2026!` |

Son cuentas ficticias exclusivas de esta evaluación.

## Preparación inicial del operador

La preparación se realiza antes de la entrevista. No entregue al candidato credenciales MySQL administrativas.

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
