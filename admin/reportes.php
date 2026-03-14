<?php
require_once '../includes/funciones.php';
verificar_sesion_admin();

$fecha_inicio = $_GET['inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fin'] ?? date('Y-m-d');

// Resumen general
$resumen = $pdo->prepare("
    SELECT 
        COUNT(*) as total_pedidos,
        COALESCE(SUM(total), 0) as ventas_totales,
        AVG(total) as ticket_promedio,
        COUNT(DISTINCT id_cliente) as clientes_unicos
    FROM pedidos 
    WHERE DATE(fecha_pedido) BETWEEN ? AND ?
");
$resumen->execute([$fecha_inicio, $fecha_fin]);
$resumen = $resumen->fetch();

// Ventas por día
$ventas_diarias = $pdo->prepare("
    SELECT 
        DATE(fecha_pedido) as fecha,
        COUNT(*) as pedidos,
        COALESCE(SUM(total), 0) as total
    FROM pedidos 
    WHERE DATE(fecha_pedido) BETWEEN ? AND ?
    GROUP BY DATE(fecha_pedido)
    ORDER BY fecha
");
$ventas_diarias->execute([$fecha_inicio, $fecha_fin]);

// Productos más vendidos
$top_productos = $pdo->prepare("
    SELECT 
        p.nombre,
        SUM(d.cantidad) as cantidad,
        SUM(d.subtotal) as total
    FROM detalle_pedido d
    JOIN productos p ON d.id_producto = p.id_producto
    JOIN pedidos ped ON d.id_pedido = ped.id_pedido
    WHERE DATE(ped.fecha_pedido) BETWEEN ? AND ?
    GROUP BY d.id_producto
    ORDER BY total DESC
    LIMIT 10
");
$top_productos->execute([$fecha_inicio, $fecha_fin]);

// Ventas por método de pago
$metodos_pago = $pdo->prepare("
    SELECT 
        metodo_pago,
        COUNT(*) as cantidad,
        COALESCE(SUM(total), 0) as total
    FROM pedidos 
    WHERE DATE(fecha_pedido) BETWEEN ? AND ?
    GROUP BY metodo_pago
");
$metodos_pago->execute([$fecha_inicio, $fecha_fin]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Gustico's</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #FAFAFA; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .filtros { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
        .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .numero { font-size: 2rem; font-weight: 800; color: #7B1FA2; }
        table { width: 100%; background: white; border-radius: 12px; overflow: hidden; }
        th { background: #FAFAFA; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .btn { background: #7B1FA2; color: white; padding: 10px 20px; border: none; border-radius: 40px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Reportes</h1>
        
        <div class="filtros">
            <form method="GET">
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <input type="date" name="inicio" value="<?php echo $fecha_inicio; ?>" style="padding: 8px;">
                    <input type="date" name="fin" value="<?php echo $fecha_fin; ?>" style="padding: 8px;">
                    <button type="submit" class="btn">Aplicar filtros</button>
                </div>
            </form>
        </div>
        
        <div class="grid">
            <div class="card">
                <div>Pedidos</div>
                <div class="numero"><?php echo $resumen['total_pedidos']; ?></div>
            </div>
            <div class="card">
                <div>Ventas totales</div>
                <div class="numero">$<?php echo number_format($resumen['ventas_totales'], 2); ?></div>
            </div>
            <div class="card">
                <div>Ticket promedio</div>
                <div class="numero">$<?php echo number_format($resumen['ticket_promedio'], 2); ?></div>
            </div>
            <div class="card">
                <div>Clientes únicos</div>
                <div class="numero"><?php echo $resumen['clientes_unicos']; ?></div>
            </div>
        </div>
        
        <h2>Ventas diarias</h2>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Pedidos</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas_diarias as $v): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($v['fecha'])); ?></td>
                    <td><?php echo $v['pedidos']; ?></td>
                    <td>$<?php echo number_format($v['total'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2 style="margin-top: 30px;">Productos más vendidos</h2>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_productos as $p): ?>
                <tr>
                    <td><?php echo $p['nombre']; ?></td>
                    <td><?php echo $p['cantidad']; ?></td>
                    <td>$<?php echo number_format($p['total'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>