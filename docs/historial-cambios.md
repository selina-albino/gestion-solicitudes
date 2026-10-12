# Historial de cambios (evidencia)

Generado con `git log` el 11/10/2026. Después de integrar los PRs en GitHub, regenerar con:

```bash
git log --graph --oneline --all > docs/evidencias/historial-git.txt
```

## Ramas

```
docs/entregables
feature/dominio-solicitudes
feature/interfaz-web
feature/pruebas
main
```

## Commits

| Commit | Rama de origen | Mensaje |
|---|---|---|
| `1823fdb` | main | chore: estructura base de Laravel 12 sin credenciales |
| `73a27b0` | feature/dominio-solicitudes | feat(dominio): modelo de solicitud, estados, código único y repositorio JSON |
| `a8db2d0` | feature/dominio-solicitudes | feat(dominio): servicio con registro, consulta, asignación de técnico y cambio de estado |
| `2d7b0a9` | feature/interfaz-web | feat(web): rutas, controlador y validación de formularios |
| `89d6a41` | feature/interfaz-web | feat(web): vistas de registro, consulta y detalle con historial |
| `3dcacd1` | feature/pruebas | test: pruebas unitarias del generador, servicio y repositorio |
| `7dfc370` | feature/pruebas | test: integración, casos de error y aceptación del flujo completo |
| `47b56c6` | docs/entregables | docs: README con instalación, ejecución, pruebas y flujo del equipo |
| `ea6ddc6` | docs/entregables | docs: casos de prueba, matriz de trazabilidad y guía de entrega |

## Detalle

```
commit 1823fdb
Autor: Selina
Fecha: 11/10/2026 22:25

    chore: estructura base de Laravel 12 sin credenciales

    Esqueleto mínimo del proyecto. Usa sesión y caché en archivos (sin base de datos). .env excluido del repositorio; .env.example sin secretos.


 .env.example                            | 20 ++++++++++++++
 .gitignore                              | 15 +++++++++++
 README.md                               |  4 +++
 app/Http/Controllers/Controller.php     |  8 ++++++
 app/Providers/AppServiceProvider.php    | 18 +++++++++++++
 artisan                                 | 16 ++++++++++++
 bootstrap/app.php                       | 18 +++++++++++++
 bootstrap/cache/.gitignore              |  2 ++
 bootstrap/providers.php                 |  5 ++++
 composer.json                           | 46 +++++++++++++++++++++++++++++++++
 phpunit.xml                             | 32 +++++++++++++++++++++++
 public/index.php                        | 17 ++++++++++++
 routes/console.php                      |  3 +++
 storage/app/.gitignore                  |  2 ++
 storage/framework/cache/.gitignore      |  3 +++
 storage/framework/cache/data/.gitignore |  2 ++
 storage/framework/sessions/.gitignore   |  2 ++
 storage/framework/testing/.gitignore    |  2 ++
 storage/framework/views/.gitignore      |  2 ++
 storage/logs/.gitignore                 |  2 ++
 20 files changed, 219 insertions(+)
commit 73a27b0
Autor: Selina
Fecha: 11/10/2026 22:25

    feat(dominio): modelo de solicitud, estados, código único y repositorio JSON

    - Estados con transiciones permitidas (Registrada, Asignada, En proceso, Resuelta, Cerrada, Cancelada).
- Código SOL-AAAAMMDD-XXXXXX sin caracteres ambiguos.
- Persistencia en storage/app/solicitudes.json con bloqueo de escritura.
- Lista de técnicos configurable. Cubre RF2 y RF6.


 app/Solicitudes/Categoria.php                      | 23 ++++++++
 app/Solicitudes/Estado.php                         | 52 +++++++++++++++++
 app/Solicitudes/Excepciones/DatosInvalidos.php     | 15 +++++
 .../Excepciones/OperacionNoPermitida.php           |  8 +++
 .../Excepciones/SolicitudNoEncontrada.php          | 13 +++++
 app/Solicitudes/GeneradorCodigo.php                | 40 +++++++++++++
 app/Solicitudes/Prioridad.php                      | 21 +++++++
 app/Solicitudes/RepositorioJson.php                | 68 ++++++++++++++++++++++
 app/Solicitudes/RepositorioSolicitudes.php         | 12 ++++
 app/Solicitudes/Solicitud.php                      | 62 ++++++++++++++++++++
 config/solicitudes.php                             | 22 +++++++
 11 files changed, 336 insertions(+)
commit a8db2d0
Autor: Selina
Fecha: 11/10/2026 22:25

    feat(dominio): servicio con registro, consulta, asignación de técnico y cambio de estado

    Valida campos obligatorios, reintenta si el código ya existe, explica transiciones no permitidas y registra cada cambio en el historial. Mensajes en español centralizados en Mensajes. Cubre RF1–RF8 a nivel de dominio.


 app/Providers/AppServiceProvider.php    |  19 ++-
 app/Solicitudes/Mensajes.php            |  38 ++++++
 app/Solicitudes/ServicioSolicitudes.php | 223 ++++++++++++++++++++++++++++++++
 3 files changed, 279 insertions(+), 1 deletion(-)
commit 2d7b0a9
Autor: Selina
Fecha: 11/10/2026 22:25

    feat(web): rutas, controlador y validación de formularios

    Form Requests con los mismos mensajes del dominio. Código inexistente redirige a la consulta con mensaje claro; errores de transición vuelven a la ficha.


 app/Http/Controllers/SolicitudController.php    | 114 ++++++++++++++++++++++++
 app/Http/Requests/AsignarTecnicoRequest.php     |  27 ++++++
 app/Http/Requests/CambiarEstadoRequest.php      |  29 ++++++
 app/Http/Requests/ConsultarSolicitudRequest.php |  40 +++++++++
 app/Http/Requests/RegistrarSolicitudRequest.php |  33 +++++++
 routes/web.php                                  |  21 +++++
 6 files changed, 264 insertions(+)
commit 89d6a41
Autor: Selina
Fecha: 11/10/2026 22:25

    feat(web): vistas de registro, consulta y detalle con historial

    Formularios accesibles (labels, aria-invalid, resumen de errores con role=alert), colores con contraste AA y selector que solo ofrece estados permitidos.


 resources/views/layouts/app.blade.php           | 82 +++++++++++++++++++++
 resources/views/solicitudes/consultar.blade.php | 20 +++++
 resources/views/solicitudes/create.blade.php    | 58 +++++++++++++++
 resources/views/solicitudes/show.blade.php      | 98 +++++++++++++++++++++++++
 4 files changed, 258 insertions(+)
commit 3dcacd1
Autor: Selina
Fecha: 11/10/2026 22:25

    test: pruebas unitarias del generador, servicio y repositorio

    PU-01 a PU-09 más casos de datos incompletos (PE-01u) y código inexistente (PE-02u).


 tests/Unit/GeneradorCodigoTest.php     |  37 +++++++++
 tests/Unit/RepositorioEnMemoria.php    |  37 +++++++++
 tests/Unit/RepositorioJsonTest.php     |  47 +++++++++++
 tests/Unit/ServicioSolicitudesTest.php | 140 +++++++++++++++++++++++++++++++++
 4 files changed, 261 insertions(+)
commit 7dfc370
Autor: Selina
Fecha: 11/10/2026 22:25

    test: integración, casos de error y aceptación del flujo completo

    PI-01 registro por HTTP, PI-02 asignación y estado, PE-01 formulario vacío, PE-02 código inexistente, PA-01 flujo registrar → cerrar. Cada prueba usa un JSON temporal.


 tests/Acceptance/FlujoCompletoTest.php    | 71 +++++++++++++++++++++++++++++++
 tests/Feature/ActualizarSolicitudTest.php | 64 ++++++++++++++++++++++++++++
 tests/Feature/CodigoInexistenteTest.php   | 38 +++++++++++++++++
 tests/Feature/DatosIncompletosTest.php    | 59 +++++++++++++++++++++++++
 tests/Feature/RegistrarSolicitudTest.php  | 36 ++++++++++++++++
 tests/TestCase.php                        | 38 +++++++++++++++++
 6 files changed, 306 insertions(+)
commit 47b56c6
Autor: Selina
Fecha: 11/10/2026 22:25

    docs: README con instalación, ejecución, pruebas y flujo del equipo

    Incluye estados y transiciones, protección de main, convención de commits y política de credenciales.


 README.md | 91 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++---
 1 file changed, 88 insertions(+), 3 deletions(-)
commit ea6ddc6
Autor: Selina
Fecha: 11/10/2026 22:25

    docs: casos de prueba, matriz de trazabilidad y guía de entrega

    Matriz RF1–RF8 y prácticas PR1–PR8 vinculadas a componentes, pruebas y ramas.


 docs/casos-de-prueba.md     | 41 ++++++++++++++++++++++++++
 docs/evidencias/.gitkeep    |  1 +
 docs/guia-entrega.md        | 70 +++++++++++++++++++++++++++++++++++++++++++++
 docs/matriz-trazabilidad.md | 39 +++++++++++++++++++++++++
 4 files changed, 151 insertions(+)
```
