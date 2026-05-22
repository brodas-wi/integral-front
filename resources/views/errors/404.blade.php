<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css'])
</head>
<body>
<div class="error-page">
    <div class="error-content">
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Página no encontrada</h2>
        <p class="error-description">La página que buscas no existe o ha sido movida.</p>
        <a href="{{ url('/') }}" class="error-btn">Volver al inicio</a>
    </div>
</div>
</body>
</html>
