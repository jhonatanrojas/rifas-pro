<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación de comprobante #{{ $order->id }}</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #0f172a; color: #e2e8f0; }
        .wrap { max-width: 860px; margin: 0 auto; padding: 32px 20px 56px; }
        .card { background: rgba(15, 23, 42, 0.92); border: 1px solid rgba(255,255,255,.08); border-radius: 24px; padding: 28px; box-shadow: 0 20px 60px rgba(0,0,0,.25); }
        .eyebrow { text-transform: uppercase; letter-spacing: .2em; font-size: 12px; color: #38bdf8; font-weight: 700; }
        h1 { margin: 10px 0 0; font-size: 32px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 24px; }
        .box { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); border-radius: 18px; padding: 18px; }
        .label { font-size: 11px; text-transform: uppercase; letter-spacing: .16em; color: #94a3b8; margin-bottom: 8px; }
        .value { font-size: 16px; font-weight: 700; color: #f8fafc; word-break: break-word; }
        .muted { color: #94a3b8; }
        .pill { display: inline-flex; padding: 6px 12px; border-radius: 999px; background: rgba(16,185,129,.12); color: #34d399; font-size: 12px; font-weight: 700; margin-top: 16px; }
        .footer { margin-top: 24px; font-size: 12px; color: #94a3b8; }
        a { color: #7dd3fc; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="eyebrow">Comprobante verificado</div>
            <h1>Orden #{{ $order->id }}</h1>
            <p class="muted">Este comprobante fue generado y firmado digitalmente para validar la compra.</p>

            <div class="pill">Firma válida</div>

            <div class="grid">
                <div class="box">
                    <div class="label">Rifa</div>
                    <div class="value">{{ $order->raffle->title }}</div>
                </div>
                <div class="box">
                    <div class="label">Cliente</div>
                    <div class="value">{{ $order->user->name }}</div>
                    <div class="muted">{{ $order->user->email }}</div>
                </div>
                <div class="box">
                    <div class="label">Números</div>
                    <div class="value">{{ $order->tickets->pluck('number')->implode(', ') }}</div>
                </div>
                <div class="box">
                    <div class="label">Monto</div>
                    <div class="value">{{ $order->total }} {{ $order->currency }}</div>
                </div>
                <div class="box">
                    <div class="label">Estado</div>
                    <div class="value">{{ $order->status }}</div>
                </div>
                <div class="box">
                    <div class="label">Hash</div>
                    <div class="value" style="font-family: monospace; font-size: 12px;">{{ $receiptHash }}</div>
                </div>
            </div>

            <div class="footer">
                @if ($receiptUrl)
                    <p><a href="{{ $receiptUrl }}" target="_blank" rel="noopener">Abrir PDF del comprobante</a></p>
                @endif
                <p>Verificado el {{ $verifiedAt->format('d/m/Y H:i') }}.</p>
            </div>
        </div>
    </div>
</body>
</html>
