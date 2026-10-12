# Matriz de trazabilidad — Incremento 1

Fecha de actualización: 11/10/2026

## Requisitos funcionales

| ID | Requisito | Componentes | Pruebas | Rama | Estado |
|---|---|---|---|---|---|
| RF1 | Registrar solicitud con solicitante, categoría, descripción y prioridad | `ServicioSolicitudes::registrar`, `RegistrarSolicitudRequest`, `SolicitudController@store`, `create.blade.php` | PU-03, PI-01, PA-01 | feature/dominio-solicitudes, feature/interfaz-web | Implementado |
| RF2 | Generar código único | `GeneradorCodigo`, `ServicioSolicitudes::nuevoCodigo` | PU-01, PU-02, PU-03, PU-07, PI-01, PA-01 | feature/dominio-solicitudes | Implementado |
| RF3 | Consultar solicitud por código | `ServicioSolicitudes::consultar`, `ConsultarSolicitudRequest`, `SolicitudController@consultar/show`, `consultar.blade.php` | PE-02u, PI-01, PE-02, PE-02b, PE-02c, PA-01 | feature/dominio-solicitudes, feature/interfaz-web | Implementado |
| RF4 | Asignar técnico | `ServicioSolicitudes::asignarTecnico`, `AsignarTecnicoRequest`, `SolicitudController@asignarTecnico`, `config/solicitudes.php` | PU-04, PU-08, PI-02, PA-01 | feature/dominio-solicitudes, feature/interfaz-web | Implementado |
| RF5 | Actualizar estado | `Estado` (transiciones), `ServicioSolicitudes::cambiarEstado`, `CambiarEstadoRequest`, `SolicitudController@cambiarEstado` | PU-04, PU-05, PU-06, PI-02, PI-03, PA-01 | feature/dominio-solicitudes, feature/interfaz-web | Implementado |
| RF6 | Conservar historial básico de cambios | `Solicitud::registrarCambio`, `RepositorioJson`, tabla de historial en `show.blade.php` | PU-03, PU-04, PU-06, PU-09, PI-02, PA-01 | feature/dominio-solicitudes, feature/interfaz-web | Implementado |
| RF7 | Validar campos obligatorios | `RegistrarSolicitudRequest` + validación del servicio (doble capa) | PE-01u, PE-01, PE-01b | feature/dominio-solicitudes, feature/interfaz-web | Implementado |
| RF8 | Mensajes comprensibles ante datos incorrectos | `Mensajes`, excepciones de dominio, resumen de errores y mensajes por campo en vistas | PU-05, PU-08, PE-01u, PE-02u, PE-01, PE-02, PE-02c, PI-03 | feature/dominio-solicitudes, feature/interfaz-web | Implementado |

## Prácticas obligatorias

| ID | Práctica | Evidencia |
|---|---|---|
| PR1 | Repositorio de control de versiones | Repositorio Git (enlace en la entrega) |
| PR2 | Rama principal protegida | Regla de protección de `main` en GitHub (captura) |
| PR3 | Ramas de funcionalidad | `feature/dominio-solicitudes`, `feature/interfaz-web`, `feature/pruebas`, `docs/entregables` |
| PR4 | Al menos 5 commits significativos | `git log` — ver `docs/historial-cambios.md` |
| PR5 | Mensajes de commit descriptivos | Formato `tipo(alcance): descripción` |
| PR6 | Revisión por otra persona antes de integrar | Pull Requests aprobados por el revisor invitado (capturas) |
| PR7 | README con instrucciones | `README.md` |
| PR8 | Sin credenciales en el código | `.env` en `.gitignore`; `.env.example` sin secretos; `APP_KEY` generada localmente |

## Pruebas mínimas

| Pedido | Casos que lo cubren |
|---|---|
| 3 pruebas unitarias | PU-01 a PU-09 (+ PE-01u, PE-02u) |
| 2 pruebas de integración | PI-01, PI-02 |
| 1 prueba de aceptación del flujo completo | PA-01 |
| 1 caso de error por datos incompletos | PE-01 (y PE-01u) |
| 1 caso de consulta con código inexistente | PE-02 (y PE-02u) |
