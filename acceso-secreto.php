<?php
// ============================================
// ARCHIVO PUENTE PARA ACCEDER AL ADMIN
// ============================================

// Define una contraseña secreta
$password_secreto = "MiClave2025"; // Cámbiala por una segura

// Si no hay contraseña en la URL, mostrar formulario
if (!isset($_GET['clave']) || $_GET['clave'] !== $password_secreto) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Acceso Restringido</title>
        <style>
            body { font-family: Arial; background: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
            .acceso { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
            input { padding: 10px; width: 200px; margin: 10px 0; }
            button { background: #6A0DAD; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        </style>
    </head>
    <body>
        <div class="acceso">
            <h2>🔐 Área Restringida</h2>
            <form method="GET">
                <input type="password" name="clave" placeholder="Ingresa la clave" required>
                <button type="submit">Acceder</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Si la contraseña es correcta, redirige al admin
header('Location: /gusticos/admin/login.php');
exit;
?>