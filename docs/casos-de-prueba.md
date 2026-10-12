# Casos y resultados de prueba

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
| PI-01 | Integración | RF1, RF2, RF3 | `RegistrarSolicitudTest::test_pi01_…` | POST con datos válidos | Redirige a la solicitud, guarda en JSON, la página muestra código y datos | ⏳ Ejecutar |
| PI-02 | Integración | RF4, RF5, RF6 | `ActualizarSolicitudTest::test_pi02_…` | PATCH técnico + PATCH estado | Técnico y estado guardados; historial: Registro → Asignación → Cambio de estado | ⏳ Ejecutar |
| PI-03 | Integración (complementaria) | RF5, RF8 | `ActualizarSolicitudTest::test_cambio_de_estado_no_permitido_…` | Registrada → Cerrada | Mensaje explicativo; estado sin cambios | ⏳ Ejecutar |
| PE-01 | Error datos incompletos | RF7, RF8 | `DatosIncompletosTest::test_pe01_…` | Formulario vacío | 4 mensajes en español, nada guardado, mensajes visibles en pantalla | ⏳ Ejecutar |
| PE-01b | Error datos (complementaria) | RF7, RF8 | `DatosIncompletosTest::test_valores_fuera_de_la_lista_…` | Categoría "cocina", prioridad "urgentisima" | Rechazo de ambos campos | ⏳ Ejecutar |
| PE-02 | Código inexistente | RF3, RF8 | `CodigoInexistenteTest::test_pe02_…` | `SOL-20260101-ZZZZZZ` | Vuelve a la consulta con el mensaje "No existe ninguna solicitud…" | ⏳ Ejecutar |
| PE-02b | Código inexistente (complementaria) | RF3, RF8 | `CodigoInexistenteTest::test_abrir_la_url_…` | URL directa con código inexistente | Mismo mensaje | ⏳ Ejecutar |
| PE-02c | Formato de código (complementaria) | RF3, RF8 | `CodigoInexistenteTest::test_codigo_con_formato_…` | `12345` | Mensaje con ejemplo de formato | ⏳ Ejecutar |
| PA-01 | Aceptación | RF1–RF8 | `FlujoCompletoTest::test_pa01_…` | Registrar → consultar → asignar → En proceso → Resuelta → Cerrada | Cada paso muestra su confirmación; historial con 5 entradas en orden; ya no admite cambios | ⏳ Ejecutar |

> Las pruebas marcadas ✔ se verificaron el 11/10/2026 (12/12 unitarias). Las marcadas ⏳ requieren Laravel instalado: después de `composer install`, correr `php artisan test` y reemplazar ⏳ por ✔ Pasó / ✘ Falló.
