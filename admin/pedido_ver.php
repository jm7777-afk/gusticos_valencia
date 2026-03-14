<?php
require_once '../includes/funciones.php';

verificar_sesion();

$titulo_pagina = 'Detalle de Pedido - Gustico\'s Admin';
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT p.*, c.nombre as cliente_nombre, c.email, c.telefono, c.direccion 
                       FROM pedidos p 
                       LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                       WHERE p.id_pedido = ?");
$stmt->execute([$id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: pedidos.php');
    exit;
}

$detalles = $pdo->prepare("SELECT * FROM detalle_pedido WHERE id_pedido = ?");
$detalles->execute([$id]);
$detalles = $detalles->fetchAll();

// Cambiar estado
if (isset($_POST['cambiar_estado'])) {
    $nuevo_estado = $_POST['estado'];
    $pdo->prepare("UPDATE pedidos SET estado_pedido = ? WHERE id_pedido = ?")->execute([$nuevo_estado, $id]);
    header("Location: pedido_ver.php?id=$id");
    exit;
}

include 'includes/header.php';
?>

<div class="top-bar">
    <h1>Detalle de Pedido</h1>
    <a href="pedidos.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <!-- Información del pedido -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-info-circle"></i> Información del Pedido</h2>
        </div>
        
        <table>
            <tr>
                <th style="width: 150px;">Número:</th>
                <td><?php echo $pedido['numero_pedido'] ?? '#' . $pedido['id_pedido']; ?></td>
            </tr>
            <tr>
                <th>Fecha:</th>
                <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
            </tr>
            <tr>
                <th>Estado:</th>
                <td>
                    <span class="badge badge-<?php 
                        echo $pedido['estado_pedido'] == 'recibido' ? 'warning' : 
                            ($pedido['estado_pedido'] == 'entregado' ? 'success' : 'info'); 
                    ?>">
                        <?php echo ucfirst($pedido['estado_pedido']); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Método de pago:</th>
                <td><?php echo $pedido['metodo_pago'] ?? 'No especificado'; ?></td>
            </tr>
            <tr>
                <th>Total:</th>
                <td><strong>$<?php echo number_format($pedido['total'], 2); ?></strong></td>
            </tr>
        </table>
    </div>
    
    <!-- Información del cliente -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-user"></i> Cliente</h2>
        </div>
        
        <table>
            <tr>
                <th style="width: 100px;">Nombre:</th>
                <td><?php echo $pedido['nombre_cliente']; ?></td>
            </tr>
            <tr>
                <th>Teléfono:</th>
                <td><?php echo $pedido['telefono_cliente']; ?></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><?php echo $pedido['email'] ?? 'No especificado'; ?></td>
            </tr>
            <tr>
                <th>Dirección:</th>
                <td><?php echo $pedido['direccion_entrega'] ?? 'No especificada'; ?></td>
            </tr>
        </table>
        
        <?php if ($pedido['ubicacion_maps']): ?>
        <div style="margin-top: 15px;">
            <a href="<?php echo $pedido['ubicacion_maps']; ?>" target="_blank" class="btn btn-outline btn-sm">
                <i class="fas fa-map-marker-alt"></i> Ver ubicación
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Productos -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h2><i class="fas fa-box"></i> Productos</h2>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $d): ?>
                <tr>
                    <td><?php echo $d['nombre_producto']; ?></td>
                    <td><?php echo $d['cantidad']; ?></td>
                    <td>$<?php echo number_format($d['precio_unitario'], 2); ?></td>
                    <td>$<?php echo number_format($d['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Cambiar estado -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h2><i class="fas fa-sync-alt"></i> Cambiar Estado</h2>
    </div>
    
    <form method="POST" style="display: flex; gap: 10px; align-items: center;">
        <select name="estado" class="form-group" style="width: auto; margin: 0;">
            <option value="recibido" <?php echo $pedido['estado_pedido'] == 'recibido' ? 'selected' : ''; ?>>Recibido</option>
            <option value="preparando" <?php echo $pedido['estado_pedido'] == 'preparando' ? 'selected' : ''; ?>>Preparando</option>
            <option value="listo" <?php echo $pedido['estado_pedido'] == 'listo' ? 'selected' : ''; ?>>Listo</option>
            <option value="entregado" <?php echo $pedido['estado_pedido'] == 'entregado' ? 'selected' : ''; ?>>Entregado</option>
            <option value="cancelado" <?php echo $pedido['estado_pedido'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
        </select>
        <button type="submit" name="cambiar_estado" class="btn btn-verde">Actualizar Estado</button>
    </form>
</div>

<?php
include 'includes/footer.php';
?>