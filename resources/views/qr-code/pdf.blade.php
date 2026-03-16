<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance QR Code</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; }
        .header { text-align: center; margin-bottom: 28px; }
        .qr { text-align: center; margin: 40px 0; }
        .details { margin-top: 20px; }
        .details p { margin: 4px 0; }
        .footer { text-align: center; margin-top: 40px; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Company Attendance QR Code</h1>
        <p>Scan this code to validate attendance on the mobile device.</p>
    </div>

    <div class="qr">
        {!! $qrSvg !!}
    </div>

    <div class="details">
        <p><strong>Token:</strong> {{ $qrCode->token }}</p>
        <p><strong>Generated At:</strong> {{ $qrCode->created_at->format('Y-m-d H:i:s') }}</p>
        @if($qrCode->expires_at)
            <p><strong>Expires At:</strong> {{ $qrCode->expires_at->format('Y-m-d H:i:s') }}</p>
        @endif
    </div>

    <div class="footer">
        <p>Please place this QR code in a visible location at the entrance.</p>
    </div>
</body>
</html>