<?php
require_once '../includes/funciones.php';
verificar_sesion_admin();

$titulo_pagina = 'Pedidos - Gustico\'s Admin';

$filtro = $_GET['estado'] ?? 'pendiente';
$filtros_validos = ['pendiente', 'confirmado', 'rechazado'];

if (!in_array($filtro, $filtros_validos)) {
    $filtro = 'pendiente';
}

// Consulta con filtro
$sql = "SELECT p.*, c.nombre as cliente_nombre 
        FROM pedidos p 
        LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
        WHERE p.estado_confirmacion = ? 
        ORDER BY p.fecha_pedido DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$filtro]);
$pedidos = $stmt->fetchAll();

$pendientes = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado_confirmacion = 'pendiente'")->fetchColumn();
$confirmados = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado_confirmacion = 'confirmado'")->fetchColumn();

include __DIR__ . '/includes/header.php';
?>

<div class="top-bar">
    <h1>Pedidos</h1>
</div>

<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card <?php echo $filtro == 'pendiente' ? 'activo' : ''; ?>" 
         onclick="window.location.href='?estado=pendiente'" style="cursor: pointer;">
        <div class="stat-label">Pendientes</div>
        <div class="stat-number"><?php echo $pendientes; ?></div>
    </div>
    <div class="stat-card verde <?php echo $filtro == 'confirmado' ? 'activo' : ''; ?>" 
         onclick="window.location.href='?estado=confirmado'" style="cursor: pointer;">
        <div class="stat-label">Confirmados</div>
        <div class="stat-number"><?php echo $confirmados; ?></div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pedidos)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px;">No hay pedidos</td>
                </tr>
                <?php endif; ?>
                
                <?php foreach ($pedidos as $p): ?>
                <tr>
                    <td><strong><?php echo $p['numero_pedido'] ?? '#' . $p['id_pedido']; ?></strong></td>
                    <td><?php echo date('d/m H:i', strtotime($p['fecha_pedido'])); ?></td>
                    <td><?php echo $p['nombre_cliente']; ?></td>
                    <td>$<?php echo number_format($p['total'], 2); ?></td>
                    <td>
                        <span class="estado-stock estado-<?php echo $p['estado_confirmacion']; ?>">
                            <?php echo ucfirst($p['estado_confirmacion']); ?>
                        </span>
                    </td>
                    <td class="acciones">
                        <?php if ($p['estado_confirmacion'] == 'pendiente'): ?>
                        <a href="pedido_confirmar.php?id=<?php echo $p['id_pedido']; ?>" class="ver">
                            <i class="fas fa-check-circle"></i> Confirmar
                        </a>
                        <?php else: ?>
                        <a href="pedido_ver.php?id=<?php echo $p['id_pedido']; ?>" class="ver">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .stat-card.activo {
        border: 3px solid var(--morado);
        transform: scale(1.02);
    }
</style>

<?php
include __DIR__ . '/includes/footer.php';
?>