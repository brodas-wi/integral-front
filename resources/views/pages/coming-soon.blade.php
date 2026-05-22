<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Próximamente</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css'])
</head>

<body>
    <div class="coming-soon">
        <div class="coming-soon-content">
            <div class="coming-soon-icon">
                <i class="ri-time-line"></i>
            </div>
            <h1>Próximamente</h1>
            <p>Estamos trabajando en algo increíble. ¡Vuelve pronto!</p>
            <a href="{{ url('/') }}" class="error-btn">Volver al inicio</a>
        </div>
    </div>
</body>

</html>
