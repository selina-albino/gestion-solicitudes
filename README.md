# Gestión de Solicitudes — Incremento 1

Aplicación web en **Laravel 12** para registrar solicitudes de soporte, consultarlas por código, asignar un técnico y actualizar su estado, conservando un historial de cambios.

## Funcionalidades

| # | Funcionalidad | Dónde verla |
|---|---|---|
| RF1 | Registrar solicitud (solicitante, categoría, descripción, prioridad) | `/solicitudes/nueva` |
| RF2 | Código único `SOL-AAAAMMDD-XXXXXX` | Se muestra al registrar |
| RF3 | Consultar por código | `/solicitudes/consultar` |
| RF4 | Asignar técnico | Página de la solicitud |
| RF5 | Actualizar estado | Página de la solicitud |
| RF6 | Historial de cambios | Página de la solicitud |
| RF7 | Validar campos obligatorios | Todos los formularios |
| RF8 | Mensajes comprensibles ante datos incorrectos | Todos los formularios |

### Estados y transiciones

```
Registrada ──(asignar técnico)──► Asignada ──► En proceso ──► Resuelta ──► Cerrada
     │                               │             │             │
     └──────────► Cancelada ◄────────┴─────────────┘             └──► En proceso (reabrir)
```

Cerrada y Cancelada son finales. Si se intenta un cambio no permitido, el sistema explica a qué estados sí se puede pasar.

## Requisitos

- PHP 8.2 o superior (extensiones: mbstring, openssl, tokenizer, xml, ctype, json, fileinfo)
- Composer 2
- Git

No necesita base de datos: los datos se guardan en `storage/app/solicitudes.json`.

## Instalación y ejecución

```bash
git clone <URL-DEL-REPOSITORIO> gestion-solicitudes
cd gestion-solicitudes
composer install
cp .env.example .env          # en Windows: copy .env.example .env
php artisan key:generate
php artisan serve
```

Abrir http://localhost:8000

## Pruebas

```bash
php artisan test                         # todas
php artisan test --testsuite=Unitarias
php artisan test --testsuite=Integracion
php artisan test --testsuite=Aceptacion
```

Las pruebas usan un archivo JSON temporal por prueba; no modifican los datos reales.
El detalle de cada caso está en [`docs/casos-de-prueba.md`](docs/casos-de-prueba.md).

## Estructura

```
app/Solicitudes/            Dominio (sin dependencias de Laravel)
  ServicioSolicitudes.php   Casos de uso: registrar, consultar, asignar, cambiar estado
  Estado.php                Estados y transiciones permitidas
  GeneradorCodigo.php       Código único
  RepositorioJson.php       Persistencia en archivo JSON
  Mensajes.php              Mensajes de validación (compartidos por web y dominio)
app/Http/Controllers/SolicitudController.php
app/Http/Requests/          Validación de formularios
resources/views/            Vistas Blade
tests/Unit | Feature | Acceptance
docs/                       Casos de prueba, matriz de trazabilidad, historial
```

## Flujo de trabajo

- `main` está **protegida**: no se hace push directo; todo entra por Pull Request.
- Cada cambio se hace en una rama `feature/<nombre>` o `docs/<nombre>`.
- Cada Pull Request necesita **la aprobación de otra persona (revisor invitado)** antes de integrarse.
- Mensajes de commit con el formato `tipo(alcance): descripción` (`feat`, `fix`, `test`, `docs`, `chore`).

Configuración de la protección en GitHub: *Settings → Branches → Add branch ruleset* sobre `main`, activando *Require a pull request before merging* con *Required approvals: 1*.

## Credenciales

- No hay credenciales en el código. La clave de la aplicación se genera localmente con `php artisan key:generate` y vive en `.env`.
- `.env` está en `.gitignore`; solo se versiona `.env.example`, sin valores secretos.
