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
9. Se mantienen los permisos existentes.
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
