<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Solicitudes') · Gestión de Solicitudes</title>
    <style>
        :root {
            --fondo: #f6f7f9;
            --superficie: #ffffff;
            --texto: #1f2933;      /* contraste 14.6:1 sobre blanco */
            --texto-suave: #52606d; /* 6.6:1 */
            --borde: #cbd2d9;
            --primario: #1d4ed8;   /* 6.7:1 sobre blanco */
            --error: #b42318;      /* 6.4:1 */
            --error-fondo: #fef3f2;
            --exito: #067647;      /* 5.8:1 */
            --exito-fondo: #ecfdf3;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; background: var(--fondo); color: var(--texto); line-height: 1.5; }
        header { background: var(--superficie); border-bottom: 1px solid var(--borde); }
        nav { max-width: 860px; margin: 0 auto; padding: .75rem 1rem; display: flex; gap: 1.25rem; align-items: center; flex-wrap: wrap; }
        nav strong { margin-right: auto; }
        nav a { color: var(--primario); }
        main { max-width: 860px; margin: 1.5rem auto; padding: 0 1rem; }
        h1 { font-size: 1.5rem; margin: 0 0 1rem; }
        h2 { font-size: 1.15rem; margin: 0 0 .75rem; }
        .tarjeta { background: var(--superficie); border: 1px solid var(--borde); border-radius: 8px; padding: 1.25rem; margin-bottom: 1.25rem; }
        .campo { margin-bottom: 1rem; }
        label { display: block; font-weight: 600; margin-bottom: .25rem; }
        .ayuda { color: var(--texto-suave); font-size: .9rem; margin: .25rem 0 0; }
        input, select, textarea { width: 100%; padding: .55rem .6rem; border: 1px solid var(--borde); border-radius: 6px; font: inherit; color: inherit; background: var(--superficie); }
        textarea { min-height: 7rem; resize: vertical; }
        [aria-invalid="true"] { border: 2px solid var(--error); }
        .error-campo { color: var(--error); font-size: .9rem; margin: .25rem 0 0; }
        .error-campo::before { content: "⚠ "; }
        button { background: var(--primario); color: #fff; border: 0; border-radius: 6px; padding: .6rem 1.1rem; font: inherit; font-weight: 600; cursor: pointer; }
        button:focus-visible, a:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible { outline: 3px solid var(--primario); outline-offset: 2px; }
        .aviso { border-radius: 6px; padding: .75rem 1rem; margin-bottom: 1.25rem; border-left: 4px solid; }
        .aviso-exito { background: var(--exito-fondo); border-color: var(--exito); color: var(--exito); }
        .aviso-error { background: var(--error-fondo); border-color: var(--error); color: var(--error); }
        .aviso ul { margin: .25rem 0 0; padding-left: 1.25rem; }
        dl.datos { display: grid; grid-template-columns: max-content 1fr; gap: .4rem 1rem; margin: 0; }
        dl.datos dt { font-weight: 600; color: var(--texto-suave); }
        dl.datos dd { margin: 0; }
        .codigo { font-family: ui-monospace, monospace; font-size: 1.1rem; letter-spacing: .03em; }
        .grilla-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem; }
        table { width: 100%; border-collapse: collapse; font-size: .95rem; }
        th, td { text-align: left; padding: .5rem; border-bottom: 1px solid var(--borde); vertical-align: top; }
        th { color: var(--texto-suave); }
        .tabla-scroll { overflow-x: auto; }
    </style>
</head>
<body>
<header>
    <nav aria-label="Principal">
        <strong>Gestión de Solicitudes</strong>
        <a href="{{ route('solicitudes.create') }}">Nueva solicitud</a>
        <a href="{{ route('solicitudes.buscar') }}">Consultar solicitud</a>
    </nav>
</header>
<main>
    @if (session('exito'))
        <div class="aviso aviso-exito" role="status">✔ {{ session('exito') }}</div>
    @endif

    @if ($errors->any())
        <div class="aviso aviso-error" role="alert">
            <strong>Revise los siguientes datos:</strong>
            <ul>
                @foreach ($errors->all() as $mensaje)
                    <li>{{ $mensaje }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('contenido')
</main>
</body>
</html>
