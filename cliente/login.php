<?php
require_once '../includes/funciones.php';

if (isset($_SESSION['cliente_id'])) {
    header('Location: perfil.php');
    exit;
}

$titulo_pagina = 'Iniciar Sesión - Gustico\'s';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];
    
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = ? AND activo = 1");
    $stmt->execute([$email]);
    $cliente = $stmt->fetch();
    
    if ($cliente && password_verify($contrasena, $cliente['contrasena'])) {
        $_SESSION['cliente_id'] = $cliente['id_cliente'];
        $_SESSION['cliente_nombre'] = $cliente['nombre'];
        $_SESSION['cliente_email'] = $cliente['email'];
        header('Location: perfil.php');
        exit;
    } else {
        $error = "Email o contraseña incorrectos";
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="card" style="max-width: 450px; margin: 50px auto;">
        <h1 style="text-align: center; color: var(--morado); margin-bottom: 30px;">Iniciar Sesión</h1>
        
        <?php if ($error): ?>
        <div style="background: #FFEBEE; color: var(--rojo); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="contrasena" required>
            </div>
            
            <button type="submit" class="btn btn-morado" style="width: 100%;">Iniciar Sesión</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            ¿No tienes cuenta? <a href="registro.php" style="color: var(--morado);">Regístrate aquí</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>