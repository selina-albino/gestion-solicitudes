# Guía para completar la entrega

El repositorio ya trae las ramas de funcionalidad con sus commits. Falta publicarlo en GitHub, proteger `main`, integrar cada rama por Pull Request con revisión, correr las pruebas y juntar evidencias.

## 1. Publicar el repositorio (en GitHub)

1. Crear un repositorio vacío en GitHub (sin README).
2. En la compu, dentro de la carpeta del proyecto:

```bash
git remote add origin https://github.com/<usuario>/<repositorio>.git
git push -u origin main
git push origin feature/dominio-solicitudes feature/interfaz-web feature/pruebas docs/entregables
```

## 2. Invitar a un revisor (en GitHub)

La consigna pide revisión por otra persona antes de integrar. GitHub no permite aprobar un Pull Request propio, así que se invita a un compañero o compañera como revisor:

*Settings → Collaborators → Add people* → su usuario de GitHub.

## 3. Proteger `main` (en GitHub)

*Settings → Branches → Add branch ruleset* (o *Add rule*):
- Branch: `main`
- ✅ Require a pull request before merging → Required approvals: **1**
- ✅ Block force pushes

Sacar captura de la regla.

## 4. Revisar e integrar (en GitHub, en este orden)

| PR | Rama → `main` |
|---|---|
| 1 | `feature/dominio-solicitudes` |
| 2 | `feature/interfaz-web` |
| 3 | `feature/pruebas` |
| 4 | `docs/entregables` |

Para cada uno: *Pull requests → New pull request* → base `main`, compare la rama → *Create pull request* → en *Reviewers* elegir al revisor.
El revisor entra a *Files changed*, deja un comentario y pulsa *Review changes → Approve*. Recién ahí: *Merge pull request* (opción *Create a merge commit*). Captura de cada aprobación.

## 5. Correr las pruebas (en la compu)

```bash
git checkout main
git pull
composer install
cp .env.example .env
php artisan key:generate
php artisan test > docs/evidencias/resultado-pruebas.txt
```

Actualizar la columna *Resultado obtenido* en `docs/casos-de-prueba.md` (⏳ → ✔ Pasó) y subirlo en una rama `docs/resultados-pruebas` con su PR.

## 6. Evidencia del historial

```bash
git log --graph --oneline --all > docs/evidencias/historial-git.txt
```

Más capturas de la pestaña *Commits* y *Pull requests (Closed)* de GitHub.

## 7. Qué entregar

- Enlace del repositorio (o el archivo `.bundle` / `.zip`)
- `README.md`
- `docs/casos-de-prueba.md` + `docs/evidencias/resultado-pruebas.txt`
- `docs/matriz-trazabilidad.md`
- `docs/evidencias/historial-git.txt` + capturas de PRs aprobados y de la protección de `main`
