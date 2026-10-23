# Matriz de trazabilidad — v1.1

Fecha de actualización: 22/10/2026 · Cambios respecto a v1.0 marcados con **(v1.1)**.

## Requisitos funcionales

| ID | Requisito | Componentes | Pruebas | Defectos | Estado |
|---|---|---|---|---|---|
| RF1 | Registrar solicitud con solicitante, categoría, descripción y prioridad | `ServicioSolicitudes::registrar`, `RegistrarSolicitudRequest`, `SolicitudController@store`, `create.blade.php` | PU-03, PI-01, PA-01 | — | Implementado |
| RF2 | Generar código único | `GeneradorCodigo`, `ServicioSolicitudes::nuevoCodigo` | PU-01, PU-02, PU-03, PU-07, PI-01, PA-01 | — | Implementado |
| RF3 | Consultar solicitud por código | `ServicioSolicitudes::consultar`, `ConsultarSolicitudRequest`, `SolicitudController@consultar/show`, `consultar.blade.php` | PE-02u, PI-01, PE-02, PE-02b, PE-02c, PA-01 | — | Implementado |
| RF4 | Asignar técnico | `ServicioSolicitudes::asignarTecnico`, `AsignarTecnicoRequest`, `SolicitudController@asignarTecnico`, `config/solicitudes.php` | PU-04, PU-08, PI-02, PA-01 | — | Implementado |
| RF5 | Actualizar estado | `Estado`, `ServicioSolicitudes::cambiarEstado`, `CambiarEstadoRequest`, `SolicitudController@cambiarEstado` | PU-04, PU-05, PU-06, PI-02, PI-03, PA-01, PD-03 | D-03 (corregido) | Implementado |
| RF6 | Conservar historial básico de cambios | `Solicitud::registrarCambio`, `RepositorioJson`, historial en `show.blade.php` | PU-03, PU-04, PU-06, PU-09, PI-02, PA-01, PD-02 | D-02 (corregido) | Implementado |
| RF7 | Validar campos obligatorios | `RegistrarSolicitudRequest` + validación del servicio | PE-01u, PE-01, PE-01b, PD-03 | D-03 (corregido) | Implementado |
| RF8 | Mensajes comprensibles ante datos incorrectos | `Mensajes`, excepciones de dominio, vistas | PU-05, PU-08, PE-01u, PE-02u, PE-01, PE-02, PE-02c, PI-03, PD-05 | D-05 (corregido), D-04 (abierto) | Implementado con defecto abierto |
| RF9 **(v1.1)** | Marcar como vencida la solicitud crítica no atendida (no pasó a En proceso) en 4 horas | `PoliticaVencimiento`, `Estado::esperaAtencion`, `Solicitud::estaVencida`, `ServicioSolicitudes::consultar/marcarVencidas/limiteAtencion`, `show.blade.php`, `config/solicitudes.php` | PU-10 a PU-18, PI-04, PA-02 | — | Implementado |
| RF10 **(v1.1)** | Revisar el vencimiento en forma automática y periódica | Comando `solicitudes:marcar-vencidas` y su programación en `routes/console.php` | PU-15, PI-05, PI-06, PA-02 | — | Implementado |
| RF11 **(v1.1)** | Calcular indicadores de seguimiento | `Indicadores`, comando `solicitudes:indicadores` | PU-19 | — | Implementado |

## Requisitos no funcionales

| ID | Requisito | Componentes | Pruebas | Defectos | Estado |
|---|---|---|---|---|---|
| RNF1 **(v1.1)** | Fechas y plazos en hora de Bolivia (America/La_Paz) | `config/app.php`, `.env.example`, `show.blade.php` | PD-01, PI-04 | D-01 (corregido) | Implementado |
| RNF2 **(v1.1)** | No perder datos cuando hay guardados simultáneos (usuarios + tarea programada) | `RepositorioJson::guardar` con bloqueo | PD-02 | D-02 (corregido) | Implementado |
| RNF3 **(v1.1)** | Compatibilidad con datos guardados por la v1.0 | `Solicitud::fromArray` | PU-18 | — | Implementado |
| RNF4 **(v1.1)** | El aviso de vencida no depende solo del color | Texto «VENCIDA» en `show.blade.php` | PI-04, PA-02 | — | Implementado |

## Prácticas obligatorias

| ID | Práctica | Evidencia |
|---|---|---|
| PR1 | Repositorio de control de versiones | Repositorio Git (enlace en la entrega) |
| PR2 | Rama principal protegida | Regla de protección de `main` en GitHub |
| PR3 | Ramas de funcionalidad | `feature/dominio-solicitudes`, `feature/interfaz-web`, `feature/pruebas`, `docs/entregables`, **`feature/vencimiento-criticas` (v1.1)** |
| PR4 | Al menos 5 commits significativos | `git log` |
| PR5 | Mensajes de commit descriptivos | Formato `tipo(alcance): descripción` |
| PR6 | Revisión antes de integrar | Pull Requests con comentario de autorrevisión. **(v1.1)** Corregido (D-06): la v1.0 decía «aprobados por el revisor invitado», pero el trabajo fue individual y no hubo revisor. |
| PR7 | README con instrucciones | `README.md` |
| PR8 | Sin credenciales en el código | `.env` en `.gitignore`; `.env.example` sin secretos |
| PR9 **(v1.1)** | Integración continua | `.github/workflows/ci.yml`: sintaxis, pruebas, PHPStan, dependencias, credenciales |

## Defecto → requisito → prueba de regresión

| Defecto | Requisito afectado | Prueba que lo detecta | Estado |
|---|---|---|---|
| D-01 Horas en UTC | RNF1, RF9 | PD-01 | Corregido |
| D-02 Pérdida de datos en guardados simultáneos | RNF2, RF6 | PD-02 | Corregido |
| D-03 Comentario largo aceptado por el servicio | RF5, RF7 | PD-03 | Corregido |
| D-04 Archivo dañado → error 500 | RF8 | Prueba exploratoria (`docs/evidencias/pruebas-exploratorias.txt`) | Abierto |
| D-05 Mensaje inexistente → error interno | RF8 | PD-05 | Corregido |
| D-06 Documentos afirmaban revisión por otra persona | PR6 | Revisión de documentos | Corregido |

## Cobertura de requisitos

- v1.0 frente a los requisitos actualizados: 8 de 15 requisitos con pruebas que pasan (53 %).
- v1.1: 15 de 15 (100 %). Todo requisito tiene al menos una prueba y toda prueba apunta a un requisito.
