<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificado emitido</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f7fb;
            --card: #ffffff;
            --ink: #0f172a;
            --muted: #64748b;
            --ok: #1d4ed8;
            --ok-bg: #dbeafe;
            --bad: #b91c1c;
            --bad-bg: #fee2e2;
            --border: #dbe4ee;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(180deg, #eff6ff 0%, var(--bg) 100%);
            color: var(--ink);
        }

        .wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .card {
            width: 100%;
            max-width: 760px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 1.25rem;
            box-shadow: 0 22px 60px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        .hero {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.12), rgba(16, 185, 129, 0.08));
            text-align: center;
        }

        .status-icon {
            width: 88px;
            height: 88px;
            margin: 1.5rem auto 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 4px solid currentColor;
            background: rgba(255, 255, 255, 0.92);
        }

        .status-icon svg {
            width: 46px;
            height: 46px;
            display: block;
        }

        .status-icon.ok {
            color: #16a34a;
            box-shadow: 0 14px 30px rgba(22, 163, 74, 0.18);
            animation: statusPop 0.75s ease-out both, statusFloat 2.8s ease-in-out 0.8s infinite;
        }

        .status-icon.bad {
            color: var(--bad);
            box-shadow: 0 14px 30px rgba(185, 28, 28, 0.12);
            animation: statusPop 0.75s ease-out both;
        }

        @keyframes statusPop {
            0% {
                opacity: 0;
                transform: translateY(-10px) scale(0.82);
            }

            70% {
                opacity: 1;
                transform: translateY(0) scale(1.06);
            }

            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes statusFloat {
            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-4px);
            }
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 0.9rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.92rem;
        }

        .badge.ok {
            color: var(--ok);
            background: var(--ok-bg);
        }

        .badge.bad {
            color: var(--bad);
            background: var(--bad-bg);
        }

        .hero h1 {
            margin: 1rem 0 0.25rem;
            font-size: 1.7rem;
        }

        .hero p {
            margin: 0;
            color: var(--muted);
        }

        .content {
            padding: 1.5rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .item {
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 1rem;
            background: #fbfdff;
        }

        .item .label {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--muted);
            margin-bottom: 0.35rem;
        }

        .item .value {
            font-size: 1.05rem;
            font-weight: 700;
            word-break: break-word;
        }

        .footer {
            padding: 0 1.5rem 1.5rem;
            color: var(--muted);
            font-size: 0.92rem;
        }

        @media (max-width: 640px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .hero h1 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <section class="card">
            <div class="hero">
                <span class="badge {{ $valido ? 'ok' : 'bad' }}">
                    {{ $valido ? 'Certificado emitido' : 'Certificado no valido' }}
                </span>

                <div class="status-icon {{ $valido ? 'ok' : 'bad' }}">
                    @if ($valido)
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path
                                d="M20 6L9 17l-5-5"
                                stroke="currentColor"
                                stroke-width="2.6"
                                stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    @else
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path
                                d="M15 9l-6 6M9 9l6 6"
                                stroke="currentColor"
                                stroke-width="2.6"
                                stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    @endif
                </div>

                <h1>
                    {{ $valido ? 'Este certificado fue emitido por el sistema' : 'No pudimos validar este certificado' }}
                </h1>

                <p>
                    {{ $valido ? 'Los datos consultados corresponden a una emision registrada en el sistema.' : 'El hash consultado no existe o el certificado ya no esta disponible.' }}
                </p>
            </div>

            <div class="content">
                <div class="grid">
                    <div class="item">
                        <div class="label">Hash</div>
                        <div class="value">{{ $hash }}</div>
                    </div>

                    <div class="item">
                        <div class="label">Estado</div>
                        <div class="value">{{ strtoupper($estado) }}</div>
                    </div>

                    <div class="item">
                        <div class="label">Nombre</div>
                        <div class="value">{{ $nombre ?: 'No disponible' }}</div>
                    </div>

                    <div class="item">
                        <div class="label">CI</div>
                        <div class="value">{{ $ci ?: 'No disponible' }}</div>
                    </div>

                    <div class="item">
                        <div class="label">Horas</div>
                        <div class="value">{{ $horas ?: 'No disponible' }}</div>
                    </div>

                    <div class="item">
                        <div class="label">Fecha de emision</div>
                        <div class="value">
                            {{ $fecha_emision ? \Carbon\Carbon::parse($fecha_emision)->format('d/m/Y H:i') : 'No disponible' }}
                        </div>
                    </div>

                    <div class="item" style="grid-column: 1 / -1;">
                        <div class="label">Plantilla</div>
                        <div class="value">{{ $plantilla ?: 'No disponible' }}</div>
                    </div>
                </div>
            </div>

            <div class="footer">
                Esta pagina solo muestra el estado de emision del certificado consultado.
            </div>
        </section>
    </div>
</body>
</html>
