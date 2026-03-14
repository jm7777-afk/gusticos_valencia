<?php
require_once '../includes/funciones.php';
verificar_sesion_admin();

$titulo_pagina = 'Productos - Gustico\'s Admin';

// Eliminar producto
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $pdo->prepare("DELETE FROM productos WHERE id_producto = ?")->execute([$id]);
    header('Location: productos.php?mensaje=eliminado');
    exit;
}

$productos = $pdo->query("SELECT p.*, c.nombre as categoria 
                          FROM productos p 
                          LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                          ORDER BY p.id_producto DESC")->fetchAll();
$mensaje = isset($_GET['mensaje']) ? 'Operación realizada correctamente' : '';

include __DIR__ . '/includes/header.php';
?>

<div class="top-bar">
    <h1>Productos</h1>
    <a href="producto_nuevo.php" class="btn btn-morado"><i class="fas fa-plus"></i> Nuevo Producto</a>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p): ?>
                <tr>
                    <td>#<?php echo $p['id_producto']; ?></td>
                    <td><strong><?php echo $p['nombre']; ?></strong></td>
                    <td><?php echo $p['categoria'] ?? 'Sin categoría'; ?></td>
                    <td>
                        <div class="precio-normal">$<?php echo number_format($p['precio'], 2); ?></div>
                        <?php if (!empty($p['precio_mayor'])): ?>
                        <small class="precio-mayor-destacado">Mayor: $<?php echo number_format($p['precio_mayor'], 2); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="estado-stock estado-<?php echo $p['estado_stock']; ?>">
                            <?php 
                            $estados = [
                                'disponible' => 'Disponible',
                                'pocas' => 'Pocas unidades',
                                'agotado' => 'Agotado'
                            ];
                            echo $estados[$p['estado_stock']];
                            ?>
                        </span>
                    </td>
                    <td class="acciones">
                        <a href="producto_editar.php?id=<?php echo $p['id_producto']; ?>" class="editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="?eliminar=<?php echo $p['id_producto']; ?>" class="eliminar" onclick="return confirm('¿Eliminar este producto?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include __DIR__ . '/includes/footer.php';
?>