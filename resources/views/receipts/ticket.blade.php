<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comprobante de Ticket - {{ $order->id }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .container { padding: 40px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); font-size: 16px; line-height: 24px; color: #555; }
        .header { text-align: center; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #3b82f6; }
        .content { margin-top: 30px; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; padding-top: 20px; }
        .qr { margin-top: 20px; text-align: center; }
        .tickets { font-size: 32px; font-weight: bold; color: #000; letter-spacing: 2px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">RIFFAS <span style="color:#000">SaaS</span></div>
            <p>Official Participation Receipt</p>
        </div>

        <div class="content">
            <table>
                <tr>
                    <td><strong>Raffle:</strong></td>
                    <td>{{ $order->raffle->title }}</td>
                </tr>
                <tr>
                    <td><strong>Order ID:</strong></td>
                    <td>#{{ $order->id }}</td>
                </tr>
                <tr>
                    <td><strong>Name:</strong></td>
                    <td>{{ $order->user->name }}</td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td>{{ $order->user->email }}</td>
                </tr>
                <tr>
                    <td><strong>Payment Date:</strong></td>
                    <td>{{ now()->format('d/m/Y H:i') }}</td>
                </tr>
            </table>

            <div style="margin-top: 40px; text-align: center;">
                <p>YOUR PURCHASED NUMBERS:</p>
                <div class="tickets">
                    {{ $order->tickets->pluck('number')->implode(', ') }}
                </div>
            </div>

            <div class="qr">
                <img src="data:image/svg+xml;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(150)->generate($verificationUrl ?? route('raffles.show', $order->raffle->slug))) }}" />
                <p style="font-size: 10px;">Scan the code to verify this receipt.</p>
            </div>
        </div>

        <div class="footer">
            <p>This is an automatically generated official document.</p>
            <p>Thanks for participating.</p>
            <p style="font-family: monospace; font-size: 8px;">HASH: {{ $receiptHash ?? hash('sha256', $order->id . $order->created_at) }}</p>
        </div>
    </div>
</body>
</html>
