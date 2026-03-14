<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gustico's - Redirigiendo...</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #6A0DAD, #4CAF50);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: white;
        }
        .contenedor {
            text-align: center;
            padding: 30px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .logo {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
        }
        .logo span {
            color: #FFD700;
        }
        .spinner {
            border: 5px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 5px solid white;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .mensaje {
            font-size: 1.2rem;
            margin: 20px 0;
        }
        .btn {
            background: white;
            color: #6A0DAD;
            padding: 10px 30px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-top: 20px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <div class="logo">Gustico<span>'s</span></div>
        <div class="spinner"></div>
        <div class="mensaje">Redirigiendo a nuestra tienda...</div>
        <p>Si no eres redirigido automáticamente, haz clic en el botón:</p>
        <a href="/gusticos/cliente/index.php" class="btn">Ir a la tienda ahora</a>
    </div>
    
    <script>
        // Redirigir después de 2 segundos
        setTimeout(function() {
            window.location.href = '/gusticos/cliente/index.php';
        }, 2000);
    </script>
</body>
</html>