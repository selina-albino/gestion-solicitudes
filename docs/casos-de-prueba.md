# Casos y resultados de prueba — v1.1

**Cómo ejecutar:** `php artisan test` (ver README).
**Evidencia:** pegar la salida completa en `docs/evidencias/resultado-pruebas.txt` y una captura de pantalla.

## Resumen

| Tipo | Mínimo pedido | Implementadas |
|---|---|---|
| Unitarias | 3 | 12 |
| Integración | 2 | 2 (+1 complementaria) |
| Aceptación (flujo completo) | 1 | 1 |
| Error por datos incompletos | 1 | 1 unitaria + 1 de integración (+1 complementaria) |
| Consulta con código inexistente | 1 | 1 unitaria + 1 de integración (+2 complementarias) |

## Casos

| ID | Tipo | Requisito | Archivo / método | Datos de entrada | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|---|---|
| PU-01 | Unitaria | RF2 | `GeneradorCodigoTest::test_pu01_…` | Fecha 11/10/2026 | Código `SOL-20261011-XXXXXX` con formato válido | ✔ Pasó |
| PU-02 | Unitaria | RF2 | `GeneradorCodigoTest::test_pu02_…` | 1000 generaciones | 1000 códigos distintos | ✔ Pasó |
| PU-03 | Unitaria | RF1, RF2, RF6 | `ServicioSolicitudesTest::test_pu03_…` | Datos válidos | Estado *Registrada*, sin técnico, 1 entrada de historial, guardada | ✔ Pasó |
| PU-04 | Unitaria | RF4, RF5, RF6 | `ServicioSolicitudesTest::test_pu04_…` | Técnico "Ana Quiroga" | Estado *Asignada*, historial registrada → asignada | ✔ Pasó |
| PU-05 | Unitaria | RF5, RF8 | `ServicioSolicitudesTest::test_pu05_…` | Registrada → Resuelta | Rechazo con mensaje "No se puede pasar de Registrada a Resuelta…" | ✔ Pasó |
| PU-06 | Unitaria | RF5, RF6 | `ServicioSolicitudesTest::test_pu06_…` | Asignada → En proceso + comentario | Estado *En proceso*, comentario en historial | ✔ Pasó |
| PU-07 | Unitaria | RF2 | `ServicioSolicitudesTest::test_pu07_…` | 2 colisiones simuladas | Reintenta y guarda con código libre | ✔ Pasó |
| PU-08 | Unitaria | RF4, RF8 | `ServicioSolicitudesTest::test_pu08_…` | Técnico fuera de la lista | Rechazo con mensaje claro | ✔ Pasó |
| PU-09 | Unitaria | RF6 | `RepositorioJsonTest::test_pu09_…` | Solicitud con historial | Se recupera idéntica desde el archivo JSON | ✔ Pasó |
| PE-01u | Error datos incompletos (unitaria) | RF7, RF8 | `ServicioSolicitudesTest::test_pe01_…` | Solicitante vacío, sin categoría ni prioridad, descripción "corta" | Un mensaje por campo; no se guarda nada | ✔ Pasó |
| PE-02u | Código inexistente (unitaria) | RF3, RF8 | `ServicioSolicitudesTest::test_pe02_…` | `sol-20260101-zzzzzz` | "No existe ninguna solicitud con el código SOL-20260101-ZZZZZZ…" | ✔ Pasó |
| PI-01 | Integración | RF1, RF2, RF3 | `RegistrarSolicitudTest::test_pi01_…` | POST con datos válidos | Redirige a la solicitud, guarda en JSON, la página muestra código y datos | ✔ Pasó |
| PI-02 | Integración | RF4, RF5, RF6 | `ActualizarSolicitudTest::test_pi02_…` | PATCH técnico + PATCH estado | Técnico y estado guardados; historial: Registro → Asignación → Cambio de estado | ✔ Pasó |
| PI-03 | Integración (complementaria) | RF5, RF8 | `ActualizarSolicitudTest::test_cambio_de_estado_no_permitido_…` | Registrada → Cerrada | Mensaje explicativo; estado sin cambios | ✔ Pasó |
| PE-01 | Error datos incompletos | RF7, RF8 | `DatosIncompletosTest::test_pe01_…` | Formulario vacío | 4 mensajes en español, nada guardado, mensajes visibles en pantalla | ✔ Pasó |
| PE-01b | Error datos (complementaria) | RF7, RF8 | `DatosIncompletosTest::test_valores_fuera_de_la_lista_…` | Categoría "cocina", prioridad "urgentisima" | Rechazo de ambos campos | ✔ Pasó |
| PE-02 | Código inexistente | RF3, RF8 | `CodigoInexistenteTest::test_pe02_…` | `SOL-20260101-ZZZZZZ` | Vuelve a la consulta con el mensaje "No existe ninguna solicitud…" | ✔ Pasó |
| PE-02b | Código inexistente (complementaria) | RF3, RF8 | `CodigoInexistenteTest::test_abrir_la_url_…` | URL directa con código inexistente | Mismo mensaje | ✔ Pasó |
| PE-02c | Formato de código (complementaria) | RF3, RF8 | `CodigoInexistenteTest::test_codigo_con_formato_…` | `12345` | Mensaje con ejemplo de formato | ✔ Pasó |
| PA-01 | Aceptación | RF1–RF8 | `FlujoCompletoTest::test_pa01_…` | Registrar → consultar → asignar → En proceso → Resuelta → Cerrada | Cada paso muestra su confirmación; historial con 5 entradas en orden; ya no admite cambios | ✔ Pasó |

> Resultado v1.0 (11/10/2026): 21 pruebas, todas pasaron. Salida en `docs/evidencias/resultado-pruebas.txt`.

## Casos agregados en v1.1 (vencimiento, indicadores y defectos)

| ID | Tipo | Requisito | Archivo / método | Datos de entrada | Resultado esperado | v1.0 | v1.1 |
|---|---|---|---|---|---|---|---|
| PU-10 | Unitaria | RF9 | `Unit\VencimientoTest::test_pu10_…` | Crítica registrada 08:00, consulta 12:01 | Vencida, plazo 12:00, estado sin cambios, historial *Vencimiento* | ✘ | ✔ |
| PU-11 | Unitaria (valores límite) | RF9 | `Unit\VencimientoTest::test_pu11_…` | Consultas a 3:59, 4:00 y 4:00:01 | No vence en 3:59 ni 4:00; vence en 4:00:01 | ✘ | ✔ |
| PU-12 | Unitaria | RF9 | `Unit\VencimientoTest::test_pu12_…` | En proceso a las 3:30 | Nunca vence; guarda hora de atención | ✘ | ✔ |
| PU-13 | Unitaria | RF9 | `Unit\VencimientoTest::test_pu13_…` | Baja, media y alta, 3 días sin atender | Ninguna vence; sin plazo | ✘ | ✔ |
| PU-14 | Unitaria | RF9 | `Unit\VencimientoTest::test_pu14_…` | Crítica asignada a los 30 min, sin En proceso | Vence a las 5 h (asignar no es atender) | ✘ | ✔ |
| PU-15 | Unitaria | RF9, RF10 | `Unit\VencimientoTest::test_pu15_…` | Dos revisiones masivas seguidas | Marca solo la vencida y no repite el historial | ✘ | ✔ |
| PU-16 | Unitaria | RF9 | `Unit\VencimientoTest::test_pu16_…` | Crítica cancelada a la 1 h | No vence | ✘ | ✔ |
| PU-17 | Unitaria | RF9, RF5 | `Unit\VencimientoTest::test_pu17_…` | Vencida que luego se atiende y cierra | Cierra normalmente y conserva la marca | ✘ | ✔ |
| PU-18 | Unitaria | RNF3 | `Unit\VencimientoTest::test_pu18_…` | Registro JSON de la v1.0 | Se lee sin error | ✘ | ✔ |
| PU-19 | Unitaria | RF11 | `Unit\IndicadoresTest::test_pu19_…` | 4 solicitudes con tiempos conocidos | Abiertas 2/0/2/0, respuesta 120 min, 1 reabierta, cumplimiento 50 % | n/a | ✔ |
| PI-04 | Integración | RF9, RNF4 | `Feature\VencimientoTest::test_pi04_…` | Crítica, se adelanta el reloj 4 h 1 min | La página muestra plazo y luego «VENCIDA:»; JSON con `vencida_en` | ✘ | ✔ |
| PI-05 | Integración | RF10 | `Feature\VencimientoTest::test_pi05_…` | Crítica + alta, comando antes y después de 5 h | 0 y luego 1 marcada; la alta no | ✘ | ✔ |
| PI-06 | Integración | RF10 | `Feature\VencimientoTest::test_pi06_…` | `schedule:list` | Comando programado `* * * * *` | ✘ | ✔ |
| PA-02 | Aceptación | RF9, RF10 | `Acceptance\VencimientoCriticasTest::test_pa02_…` | Dos críticas 08:00; una atendida 09:00; revisión 12:30 | Una a tiempo, otra vencida; la vencida se cierra y conserva la marca | ✘ | ✔ |
| PD-01 | Regresión de defecto | RNF1 (D-01) | `Feature\VencimientoTest::test_pd01_…` | Registro a las 14:00 UTC | Se guarda `-04:00` y se muestra 10:00 | ✘ | ✔ |
| PD-02 | Regresión de defecto | RNF2 (D-02) | `Unit\DefectosCorregidosTest::test_pd02_…` | 6 procesos × 20 guardados simultáneos | Quedan las 120 solicitudes | ✘ | ✔ |
| PD-03 | Regresión de defecto | RF5, RF7 (D-03) | `Unit\DefectosCorregidosTest::test_pd03_…` | Comentario de 256 caracteres | Rechazo sin cambios; 255 se acepta | ✘ | ✔ |
| PD-05 | Regresión de defecto | RF8 (D-05) | `Unit\DefectosCorregidosTest::test_pd05_…` | Clave de mensaje inexistente | Mensaje genérico, sin error | ✘ | ✔ |

> Regresión v1.1 (22/10/2026): **39 pruebas, 39 aprobadas** (189 verificaciones). Salida en `docs/evidencias/regresion-v1.1.txt`.
> Las mismas 38 pruebas ejecutadas sobre el código v1.0: 21 aprobadas y 17 fallidas (todas las nuevas). Salida en `docs/evidencias/pruebas-nuevas-contra-v1.0.txt`.
