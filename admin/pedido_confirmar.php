<?php
require_once '../includes/funciones.php';
verificar_sesion_admin();

$titulo_pagina = 'Confirmar Pedido - Gustico\'s Admin';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: pedidos.php');
    exit;
}

// Obtener datos del pedido
$stmt_pedido = $pdo->prepare("SELECT p.*, c.nombre as cliente_nombre, c.telefono, c.puntos_totales 
                              FROM pedidos p 
                              LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                              WHERE p.id_pedido = ?");
$stmt_pedido->execute([$id]);
$pedido = $stmt_pedido->fetch();

if (!$pedido) {
    header('Location: pedidos.php');
    exit;
}

// Obtener detalles del pedido
$stmt_detalles = $pdo->prepare("SELECT * FROM detalle_pedido WHERE id_pedido = ?");
$stmt_detalles->execute([$id]);
$detalles = $stmt_detalles->fetchAll();

$mensaje = '';
$error = '';

// Procesar confirmación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar'])) {
    try {
        $pdo->beginTransaction();
        
        // Calcular puntos (10 puntos por cada $10)
        $puntos_ganados = floor($pedido['total'] / 10) * 10;
        
        // Actualizar pedido
        $update = $pdo->prepare("UPDATE pedidos SET 
                                 estado_confirmacion = 'confirmado',
                                 fecha_confirmacion = NOW(),
                                 id_admin_confirmador = ?,
                                 puntos_ganados = ?,
                                 mensaje_cliente = ?
                                 WHERE id_pedido = ?");
        $update->execute([$_SESSION['usuario_id'], $puntos_ganados, $_POST['mensaje_cliente'] ?? '', $id]);
        
        // Actualizar puntos del cliente si existe
        if ($pedido['id_cliente']) {
            // Actualizar puntos totales
            $pdo->prepare("UPDATE clientes SET puntos_totales = puntos_totales + ? WHERE id_cliente = ?")
                ->execute([$puntos_ganados, $pedido['id_cliente']]);
            
            // Registrar en historial de puntos
            $pdo->prepare("INSERT INTO puntos (id_cliente, puntos, tipo, referencia) VALUES (?, ?, 'compra', ?)")
                ->execute([$pedido['id_cliente'], $puntos_ganados, $pedido['numero_pedido'] ?? 'Pedido #' . $id]);
        }
        
        // Generar mensaje para el cliente
        $mensaje_cliente = "🍦 *GUSTICO'S* 🍦\n\n";
        $mensaje_cliente .= "✅ *PEDIDO CONFIRMADO* ✅\n";
        $mensaje_cliente .= "N°: " . ($pedido['numero_pedido'] ?? '#' . $id) . "\n\n";
        $mensaje_cliente .= "Tu pedido está siendo preparado.\n";
        $mensaje_cliente .= "⏳ *Tiempo estimado:* 35-45 minutos\n\n";
        $mensaje_cliente .= "🚚 Te notificaremos cuando el delivery salga.\n";
        $mensaje_cliente .= "📱 *Sucursal:* " . ($pedido['sucursal'] ?? 'Principal') . "\n";
        
        if (!empty($_POST['mensaje_cliente'])) {
            $mensaje_cliente .= "\n📝 *Nota:* " . $_POST['mensaje_cliente'] . "\n";
        }
        
        // Generar comanda para delivery
        $comanda = "🚚 *COMANDA DELIVERY - GUSTICO'S* 🚚\n\n";
        $comanda .= "══════════════════════\n";
        $comanda .= "📍 *PEDIDO CONFIRMADO*\n";
        $comanda .= "══════════════════════\n";
        $comanda .= "📋 N°: " . ($pedido['numero_pedido'] ?? '#' . $id) . "\n";
        $comanda .= "🕐 Confirmado: " . date('d/m/Y H:i') . "\n\n";
        
        $comanda .= "══════════════════════\n";
        $comanda .= "👤 *CLIENTE*\n";
        $comanda .= "══════════════════════\n";
        $comanda .= "Nombre: " . $pedido['nombre_cliente'] . "\n";
        $comanda .= "Teléfono: " . $pedido['telefono_cliente'] . "\n";
        $comanda .= "Dirección: " . ($pedido['direccion_entrega'] ?? 'No especificada') . "\n";
        
        if (!empty($pedido['referencias'])) {
            $comanda .= "Referencias: " . $pedido['referencias'] . "\n";
        }
        
        if (!empty($pedido['ubicacion_maps'])) {
            $comanda .= "🗺️ Ubicación: " . $pedido['ubicacion_maps'] . "\n";
        }
        
        $comanda .= "\n══════════════════════\n";
        $comanda .= "📦 *PRODUCTOS*\n";
        $comanda .= "══════════════════════\n";
        
        foreach ($detalles as $d) {
            $comanda .= "• " . $d['nombre_producto'] . " x" . $d['cantidad'] . "\n";
        }
        
        $comanda .= "\n══════════════════════\n";
        $comanda .= "💰 *TOTAL*\n";
        $comanda .= "══════════════════════\n";
        $comanda .= "💵 Total USD: $" . number_format($pedido['total'], 2) . "\n";
        
        if (!empty($pedido['tasa_bcv'])) {
            $total_bs = $pedido['total'] * $pedido['tasa_bcv'];
            $comanda .= "💶 Total Bs: Bs " . number_format($total_bs, 2, ',', '.') . "\n";
        }
        
        $comanda .= "💳 Método de pago: " . ucfirst($pedido['metodo_pago'] ?? 'No especificado') . "\n";
        
        // Guardar comanda en BD
        $numero_comanda = 'CMD-' . date('Ymd') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
        $stmt_comanda = $pdo->prepare("INSERT INTO comandas (id_pedido, numero_comanda, mensaje) VALUES (?, ?, ?)");
        $stmt_comanda->execute([$id, $numero_comanda, $comanda]);
        
        $pdo->commit();
        
        // Links de WhatsApp
        $whatsapp_cliente = $pedido['telefono_cliente'] ? "https://wa.me/{$pedido['telefono_cliente']}?text=" . urlencode($mensaje_cliente) : '';
        $whatsapp_delivery = "https://wa.me/584244179135?text=" . urlencode($comanda);
        
        $mensaje = "✅ Pedido confirmado correctamente.<br><br>";
        $mensaje .= "<div style='display: flex; gap: 10px; flex-wrap: wrap;'>";
        
        if ($whatsapp_cliente) {
            $mensaje .= "<a href='{$whatsapp_cliente}' target='_blank' class='btn btn-success' style='padding: 10px 20px;'>
                            <i class='fab fa-whatsapp'></i> Notificar al Cliente
                        </a>";
        }
        
        $mensaje .= "<a href='{$whatsapp_delivery}' target='_blank' class='btn btn-warning' style='padding: 10px 20px; background: #FF9800; color: white;'>
                        <i class='fab fa-whatsapp'></i> Enviar Comanda a Delivery
                    </a>";
        $mensaje .= "</div>";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error al confirmar: " . $e->getMessage();
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="top-bar">
    <h1>Confirmar Pedido #<?php echo $pedido['numero_pedido'] ?? $id; ?></h1>
    <a href="pedidos.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-success" style="background: #E8F5E9; color: #2E7D32; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
    <?php echo $mensaje; ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
</div>
<?php endif; ?>

<?php if ($pedido['estado_confirmacion'] == 'pendiente'): ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <!-- Detalles del pedido -->
    <div class="card">
        <h2>Detalles del Pedido</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $d): ?>
                <tr>
                    <td><?php echo $d['nombre_producto']; ?></td>
                    <td><?php echo $d['cantidad']; ?></td>
                    <td>$<?php echo number_format($d['precio_unitario'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h3 style="margin-top: 20px;">Total: $<?php echo number_format($pedido['total'], 2); ?></h3>
    </div>
    
    <!-- Datos del cliente -->
    <div class="card">
        <h2>Cliente</h2>
        <p><strong>Nombre:</strong> <?php echo $pedido['nombre_cliente']; ?></p>
        <p><strong>Teléfono:</strong> <?php echo $pedido['telefono_cliente']; ?></p>
        <p><strong>Dirección:</strong> <?php echo $pedido['direccion_entrega']; ?></p>
        <?php if (!empty($pedido['referencias'])): ?>
        <p><strong>Referencias:</strong> <?php echo $pedido['referencias']; ?></p>
        <?php endif; ?>
        <?php if ($pedido['id_cliente']): ?>
        <p><strong>Puntos actuales:</strong> <?php echo $pedido['puntos_totales'] ?? 0; ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Formulario de confirmación -->
<div class="card" style="margin-top: 20px;">
    <h2>Confirmar Pedido</h2>
    
    <form method="POST">
        <div class="form-group">
            <label>Mensaje para el cliente (opcional)</label>
            <textarea name="mensaje_cliente" rows="3" class="form-control" 
                      placeholder="Ej: Tu pedido estará listo en 30 minutos...">Tu pedido ha sido confirmado y está siendo preparado.</textarea>
        </div>
        
        <div class="form-group">
            <p><strong>Puntos a ganar:</strong> <?php echo floor($pedido['total'] / 10) * 10; ?> puntos</p>
        </div>
        
        <button type="submit" name="confirmar" class="btn btn-verde" style="font-size: 1.2rem; padding: 15px;">
            ✅ CONFIRMAR PEDIDO
        </button>
    </form>
</div>

<?php else: ?>
<div class="card">
    <h2>Pedido ya procesado</h2>
    <p>Este pedido ya fue confirmado el <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_confirmacion'])); ?></p>
    <?php if ($pedido['puntos_ganados'] > 0): ?>
    <p><strong>Puntos otorgados:</strong> <?php echo $pedido['puntos_ganados']; ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
include __DIR__ . '/includes/footer.php';
?>