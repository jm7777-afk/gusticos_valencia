<?php
require_once '../includes/funciones.php';


if (isset($_SESSION['cliente_id'])) {
    header('Location: perfil.php');
    exit;
}

$titulo_pagina = 'Registro - Gustico\'s';
$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $contrasena = $_POST['contrasena'];
    $confirmar = $_POST['confirmar_contrasena'];
    
    if ($contrasena !== $confirmar) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($contrasena) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Este email ya está registrado";
        } else {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO clientes (nombre, email, telefono, direccion, contrasena) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$nombre, $email, $telefono, $direccion, $hash])) {
                $exito = "Cuenta creada exitosamente. Ya puedes iniciar sesión.";
            } else {
                $error = "Error al crear la cuenta";
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="card" style="max-width: 550px; margin: 50px auto;">
        <h1 style="text-align: center; color: var(--morado); margin-bottom: 30px;">Crear una cuenta</h1>
        
        <?php if ($error): ?>
        <div style="background: #FFEBEE; color: var(--rojo); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($exito): ?>
        <div style="background: #E8F5E9; color: var(--verde-oscuro); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo $exito; ?>
            <div style="margin-top: 15px;">
                <a href="login.php" class="btn btn-verde">Iniciar Sesión</a>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!$exito): ?>
        <form method="POST">
            <div class="form-group">
                <label>Nombre completo</label>
                <input type="text" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" name="telefono" required>
            </div>
            
            <div class="form-group">
                <label>Dirección</label>
                <textarea name="direccion" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label>Contraseña (mínimo 6 caracteres)</label>
                <input type="password" name="contrasena" required>
            </div>
            
            <div class="form-group">
                <label>Confirmar contraseña</label>
                <input type="password" name="confirmar_contrasena" required>
            </div>
            
            <button type="submit" class="btn btn-morado" style="width: 100%;">Registrarse</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            ¿Ya tienes cuenta? <a href="login.php" style="color: var(--morado);">Inicia sesión aquí</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>