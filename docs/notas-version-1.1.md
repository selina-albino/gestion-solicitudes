# Notas de versión — Mesa de Servicios v1.1

Fecha: 22/10/2026 · Rama: `feature/vencimiento-criticas` · Versión anterior: v1.0 (incremento 1)

## Nuevo

- **Vencimiento de solicitudes críticas (RF9).** Una solicitud de prioridad *Crítica* que no pasa a *En proceso* dentro de 4 horas desde su registro queda marcada como **vencida**.
  - La página de la solicitud muestra el **plazo de atención** y, si venció, un aviso con el texto «VENCIDA».
  - El historial registra la acción *Vencimiento* con la hora límite.
  - Vencer no cambia el estado: la solicitud sigue su ciclo normal y la marca queda como registro del incumplimiento.
  - Asignar un técnico **no** detiene el plazo; solo empezar a atender (En proceso) o cancelar.
  - El plazo se configura en `.env` con `SOLICITUDES_HORAS_VENCIMIENTO` (4 por defecto).
- **Revisión automática (RF10).** Comando `php artisan solicitudes:marcar-vencidas`, programado cada minuto. En el servidor se activa con `php artisan schedule:work` (o el cron de `schedule:run`). Además, cada consulta revisa el vencimiento, así la marca es correcta aunque la tarea no esté corriendo.
- **Indicadores (RF11).** Comando `php artisan solicitudes:indicadores`: solicitudes abiertas por estado, tiempo promedio de respuesta, reabiertas y cumplimiento del plazo en críticas.

## Corregido

| Defecto | Problema en v1.0 | Corrección |
|---|---|---|
| D-01 | Las fechas se guardaban y mostraban en UTC: 4 horas adelantadas respecto a Bolivia. | Zona horaria `America/La_Paz` (`config/app.php`, `APP_TIMEZONE`). |
| D-02 | Dos guardados simultáneos podían perder solicitudes (en una prueba de carga se perdió el 99,5 %). | El archivo queda bloqueado durante toda la lectura, modificación y escritura. |
| D-03 | El servicio aceptaba comentarios de más de 255 caracteres (solo validaba el formulario). | El servicio también valida el largo. |
| D-05 | Pedir un mensaje de validación inexistente producía un error interno. | Devuelve un mensaje genérico. |

## Cambios que afectan a quien instala

- Agregar a `.env`: `APP_TIMEZONE=America/La_Paz` y `SOLICITUDES_HORAS_VENCIMIENTO=4` (ya están en `.env.example`).
- Para la revisión automática, dejar corriendo `php artisan schedule:work`.
- **Datos existentes:** las solicitudes guardadas por la v1.0 se leen sin migración (los campos nuevos quedan vacíos). Sus horas se guardaron en UTC; se muestran bien convertidas a hora de Bolivia.
- La interfaz `RepositorioSolicitudes` tiene un método nuevo, `todas()`. Cualquier implementación propia debe agregarlo.

## Pruebas

- Regresión: **39 de 39 pruebas aprobadas** (21 originales + 18 nuevas). Salida en `docs/evidencias/regresion-v1.1.txt`.
- Las 17 pruebas nuevas de vencimiento y defectos **fallan contra la v1.0**, lo que confirma que detectan el cambio (`docs/evidencias/pruebas-nuevas-contra-v1.0.txt`).
- Análisis estático PHPStan nivel 5: sin errores.

## Problemas conocidos

- D-04: si el archivo JSON se daña, la aplicación muestra un error 500 genérico.
- El plazo cuenta horas corridas: no descuenta noches, fines de semana ni feriados.
- No hay una pantalla con la lista de solicitudes vencidas; se ven por código o con los comandos.
