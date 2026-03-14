<?php
require_once '../includes/funciones.php';
verificar_sesion_admin();

$titulo_pagina = 'Editar Producto - Gustico\'s Admin';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: productos.php?error=id_invalido');
    exit;
}

$producto = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
$producto->execute([$id]);
$producto = $producto->fetch();

if (!$producto) {
    header('Location: productos.php?error=no_encontrado');
    exit;
}

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar'])) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $medida = $_POST['medida'];
    $precio = $_POST['precio'];
    $precio_mayor = !empty($_POST['precio_mayor']) ? $_POST['precio_mayor'] : null;
    $estado_stock = $_POST['estado_stock'];
    $categoria_id = $_POST['categoria_id'] ?: null;
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Subir imagen
    $imagen = $producto['imagen'];
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $resultado = subirImagen($_FILES['imagen'], 'productos');
        if ($resultado['exito']) {
            $imagen = $resultado['archivo'];
        } else {
            $error = $resultado['error'];
        }
    }
    
    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("UPDATE productos SET nombre=?, descripcion=?, medida=?, precio=?, precio_mayor=?, estado_stock=?, id_categoria=?, imagen=?, activo=? WHERE id_producto=?");
            $stmt->execute([$nombre, $descripcion, $medida, $precio, $precio_mayor, $estado_stock, $categoria_id, $imagen, $activo, $id]);
            $mensaje = "Producto actualizado correctamente";
            
            // Recargar producto
            $producto = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
            $producto->execute([$id]);
            $producto = $producto->fetch();
        } catch (Exception $e) {
            $error = "Error al actualizar: " . $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="top-bar">
    <h1>Editar Producto: <?php echo htmlspecialchars($producto['nombre']); ?></h1>
    <a href="productos.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
</div>
<?php endif; ?>

<div class="card">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="actualizar" value="1">
        
        <div class="form-row">
            <div class="form-group">
                <label>Nombre del producto *</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
            </div>
            <div class="form-group">
                <label>Medida</label>
                <input type="text" name="medida" value="<?php echo htmlspecialchars($producto['medida'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" rows="4"><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Precio regular ($) *</label>
                <input type="number" name="precio" step="0.01" min="0" value="<?php echo $producto['precio']; ?>" required>
            </div>
            <div class="form-group">
                <label>Precio al mayor ($)</label>
                <input type="number" name="precio_mayor" step="0.01" min="0" value="<?php echo $producto['precio_mayor']; ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Estado de stock *</label>
                <select name="estado_stock" required>
                    <option value="disponible" <?php echo $producto['estado_stock'] == 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                    <option value="pocas" <?php echo $producto['estado_stock'] == 'pocas' ? 'selected' : ''; ?>>Pocas unidades</option>
                    <option value="agotado" <?php echo $producto['estado_stock'] == 'agotado' ? 'selected' : ''; ?>>Agotado</option>
                </select>
            </div>
            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria_id">
                    <option value="">Sin categoría</option>
                    <?php foreach ($categorias as $c): ?>
                    <option value="<?php echo $c['id_categoria']; ?>" <?php echo $producto['id_categoria'] == $c['id_categoria'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label>Cambiar imagen</label>
            <input type="file" name="imagen" accept="image/*">
            <?php if (!empty($producto['imagen'])): ?>
            <div style="margin-top: 10px;">
                <img src="<?php echo $producto['imagen']; ?>" style="max-width: 100px;">
            </div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="activo" <?php echo $producto['activo'] ? 'checked' : ''; ?>>
                Producto activo
            </label>
        </div>
        
        <button type="submit" class="btn btn-verde">Actualizar Producto</button>
    </form>
</div>

<?php
include __DIR__ . '/includes/footer.php';
?>