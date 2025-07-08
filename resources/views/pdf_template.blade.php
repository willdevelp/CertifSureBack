<!-- filepath: d:\CertifSure\back_end\resources\views\pdf_template.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .qr-code {
            text-align: center;
            margin-bottom: 20px;
        }
        .reference-number {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="qr-code">
        <img src="{{ $qrCodePath }}" alt="QR Code">
    </div>
    <div class="reference-number">
        Numéro de Référence : {{ $referenceNumber }}
    </div>
</body>
</html>