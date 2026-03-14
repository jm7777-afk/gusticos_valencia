<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #7B1FA2, #4CAF50);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }
        .container {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            max-width: 500px;
        }
        h1 { font-size: 4rem; margin-bottom: 20px; }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: white;
            color: #7B1FA2;
            text-decoration: none;
            border-radius: 40px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <h2>Página no encontrada</h2>
        <p>Lo sentimos, la página que buscas no existe.</p>
        <a href="<?php echo SITE_URL; ?>">Volver al inicio</a>
    </div>
</body>
</html>