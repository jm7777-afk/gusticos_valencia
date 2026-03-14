<?php
require_once '../includes/funciones.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$no_verificar_sesion = true; // Para que el header no verifique sesión
$titulo_pagina = 'Login - Gustico\'s Admin';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    
    if ($usuario && $contrasena) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND activo = 1");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($contrasena, $user['contrasena'])) {
            $_SESSION['usuario_id'] = $user['id_usuario'];
            $_SESSION['usuario_nombre'] = $user['nombre'];
            $_SESSION['usuario_rol'] = $user['rol'];
            
            // Actualizar último acceso
            $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?")
                ->execute([$user['id_usuario']]);
            
            header('Location: index.php');
            exit;
        } else {
            $error = "Usuario o contraseña incorrectos";
        }
    } else {
        $error = "Complete todos los campos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/gusticos/assets/css/admin.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--morado), var(--verde));
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h2>Gustico<span>'s</span></h2>
            <p style="color: var(--texto-secundario); margin-top: 10px;">Panel de Administración</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="usuario" placeholder="Ingrese su usuario" required autofocus>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="contrasena" placeholder="Ingrese su contraseña" required>
            </div>
            <button type="submit" class="btn btn-morado" style="width: 100%;">Iniciar Sesión</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px; color: var(--texto-secundario);">
            <small>Usuario: admin | Contraseña: admin123</small>
        </div>
    </div>
</body>
</html>