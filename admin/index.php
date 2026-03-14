<?php
require_once '../includes/funciones.php';  // ← Línea 1
;
verificar_sesion_admin(); // o verificar_sesion()
// resto del código
// ← Línea 2 (ahora debería funcionar)

$titulo_pagina = 'Dashboard - Gustico\'s Admin';

// Estadísticas
$total_clientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$total_productos = $pdo->query("SELECT COUNT(*) FROM productos WHERE activo = 1")->fetchColumn();

$hoy = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as monto FROM pedidos WHERE DATE(fecha_pedido) = ?");
$stmt->execute([$hoy]);
$pedidos_hoy = $stmt->fetch();

$pendientes = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado_pedido IN ('recibido', 'preparando')")->fetchColumn();
$stock_bajo = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock <= 5 AND stock > 0")->fetchColumn();

$ventas_recientes = $pdo->query("SELECT * FROM pedidos ORDER BY fecha_pedido DESC LIMIT 5")->fetchAll();
$productos_criticos = $pdo->query("SELECT * FROM productos WHERE stock <= 5 ORDER BY stock ASC LIMIT 5")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="top-bar">
    <h1>Dashboard</h1>
    <span class="date"><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y H:i'); ?></span>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Clientes</div>
        <div class="stat-number"><?php echo $total_clientes; ?></div>
        <div class="stat-footer">registrados</div>
    </div>
    <div class="stat-card verde">
        <div class="stat-label">Productos</div>
        <div class="stat-number"><?php echo $total_productos; ?></div>
        <div class="stat-footer">en catálogo</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pedidos Hoy</div>
        <div class="stat-number"><?php echo $pedidos_hoy['total']; ?></div>
        <div class="stat-footer">$<?php echo number_format($pedidos_hoy['monto'], 2); ?></div>
    </div>
    <div class="stat-card rojo">
        <div class="stat-label">Pendientes</div>
        <div class="stat-number"><?php echo $pendientes; ?></div>
        <div class="stat-footer">por procesar</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Stock Bajo</div>
        <div class="stat-number <?php echo $stock_bajo > 0 ? 'text-danger' : ''; ?>"><?php echo $stock_bajo; ?></div>
        <div class="stat-footer">productos críticos</div>
    </div>
</div>

<!-- Grid de contenido -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <!-- Pedidos Recientes -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-history"></i> Pedidos Recientes</h2>
            <a href="pedidos.php">Ver todos</a>
        </div>
        
        <?php if (empty($ventas_recientes)): ?>
            <p style="text-align: center; padding: 40px;">No hay pedidos recientes</p>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas_recientes as $p): ?>
                        <tr>
                            <td><strong><?php echo $p['numero_pedido'] ?? '#' . $p['id_pedido']; ?></strong></td>
                            <td><?php echo $p['nombre_cliente']; ?></td>
                            <td>$<?php echo number_format($p['total'], 2); ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $p['estado_pedido'] == 'recibido' ? 'warning' : 
                                        ($p['estado_pedido'] == 'entregado' ? 'success' : 'info'); 
                                ?>">
                                    <?php echo ucfirst($p['estado_pedido']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Stock Crítico -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-exclamation-triangle"></i> Stock Crítico</h2>
            <a href="productos.php">Gestionar</a>
        </div>
        
        <?php if (empty($productos_criticos)): ?>
            <p style="text-align: center; padding: 40px;">Todo bien, stock suficiente</p>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos_criticos as $p): ?>
                        <tr>
                            <td><?php echo $p['nombre']; ?></td>
                            <td><span class="badge badge-warning"><?php echo $p['stock']; ?> uds</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include __DIR__ . '/includes/footer.php';
?>