<?php
require_once '../includes/funciones.php';
verificar_sesion();

$titulo_pagina = 'Configuración - Gustico\'s Admin';

// Guardar configuración
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar'])) {
    foreach ($_POST as $clave => $valor) {
        if ($clave != 'guardar') {
            // Verificar si la clave existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM configuracion WHERE clave = ?");
            $stmt->execute([$clave]);
            if ($stmt->fetchColumn() > 0) {
                $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?")->execute([$valor, $clave]);
            } else {
                $pdo->prepare("INSERT INTO configuracion (clave, valor) VALUES (?, ?)")->execute([$clave, $valor]);
            }
        }
    }
    header('Location: configuracion.php?mensaje=guardado');
    exit;
}

// Obtener configuración actual
$config = [];
$stmt = $pdo->query("SELECT * FROM configuracion ORDER BY clave");
while ($row = $stmt->fetch()) {
    $config[$row['clave']] = $row['valor'];
}

// Valores por defecto
$whatsapp = $config['whatsapp'] ?? '+584244179135';
$tasa_bcv = $config['tasa_bcv'] ?? '40.00';
$email_negocio = $config['email_negocio'] ?? 'ventas@gusticos.com';
$telefono_contacto = $config['telefono_contacto'] ?? '0412-1234567';
$direccion = $config['direccion'] ?? 'LOS SAMANES, VALENCIA EDO CARABOBO';
$horario = $config['horario'] ?? 'Lunes a Domingo 2:00 PM - 10:00 PM';
$instagram = $config['instagram'] ?? '@gusticos.helados';

$mensaje = '';
if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'guardado') {
    $mensaje = 'Configuración guardada correctamente';
}

include __DIR__ . '/includes/header.php';
?>

<div class="top-bar">
    <h1>Configuración</h1>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
</div>
<?php endif; ?>

<form method="POST">
    <!-- Tasa de Cambio -->
    <div class="card">
        <h2><i class="fas fa-dollar-sign" style="margin-right: 10px;"></i> Tasa de Cambio</h2>
        <div style="background: linear-gradient(135deg, var(--morado), var(--verde)); color: white; padding: 25px; border-radius: 12px; margin-bottom: 25px;">
            <div style="font-size: 0.9rem; opacity: 0.9;">TASA BCV ACTUAL</div>
            <div style="font-size: 3rem; font-weight: 800;">Bs <?php echo number_format($tasa_bcv, 2); ?></div>
            <div>por 1 USD</div>
        </div>
        
        <div class="form-group">
            <label>Tasa BCV (Bs/USD)</label>
            <input type="number" name="tasa_bcv" step="0.01" min="0" value="<?php echo $tasa_bcv; ?>" required>
            <small>Esta tasa se usará para convertir precios en la tienda</small>
        </div>
    </div>
    
    <!-- Contacto -->
    <div class="card" style="margin-top: 20px;">
        <h2><i class="fas fa-phone" style="margin-right: 10px;"></i> Contacto</h2>
        
        <div class="form-row">
            <div class="form-group">
                <label><i class="fab fa-whatsapp"></i> WhatsApp</label>
                <input type="text" name="whatsapp" value="<?php echo $whatsapp; ?>" placeholder="584244179135">
                <small>Formato internacional sin + (ej: 584244179135)</small>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-phone"></i> Teléfono de contacto</label>
                <input type="text" name="telefono_contacto" value="<?php echo $telefono_contacto; ?>" placeholder="0412-1234567">
            </div>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email de contacto</label>
            <input type="email" name="email_negocio" value="<?php echo $email_negocio; ?>" placeholder="ventas@gusticos.com">
        </div>
    </div>
    
    <!-- Información del negocio -->
    <div class="card" style="margin-top: 20px;">
        <h2><i class="fas fa-store" style="margin-right: 10px;"></i> Información del Negocio</h2>
        
        <div class="form-group">
            <label><i class="fas fa-map-marker-alt"></i> Dirección</label>
            <textarea name="direccion" rows="3"><?php echo $direccion; ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-clock"></i> Horario</label>
                <input type="text" name="horario" value="<?php echo $horario; ?>" placeholder="Lunes a Domingo 2:00 PM - 10:00 PM">
            </div>
            
            <div class="form-group">
                <label><i class="fab fa-instagram"></i> Instagram</label>
                <input type="text" name="instagram" value="<?php echo $instagram; ?>" placeholder="@gusticos.helados">
            </div>
        </div>
    </div>
    
    <!-- Botón guardar -->
    <div style="text-align: center; margin: 30px 0;">
        <button type="submit" name="guardar" class="btn btn-verde" style="padding: 15px 50px;">
            <i class="fas fa-save"></i> Guardar Configuración
        </button>
    </div>
</form>

<?php
include __DIR__ . '/includes/footer.php';
?>