<?php
require_once '../includes/funciones.php';
verificar_sesion_admin();

$titulo_pagina = 'Nuevo Producto - Gustico\'s Admin';

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $medida = $_POST['medida'];
    $precio = $_POST['precio'];
    $precio_mayor = !empty($_POST['precio_mayor']) ? $_POST['precio_mayor'] : null;
    $estado_stock = $_POST['estado_stock'];
    $categoria_id = $_POST['categoria_id'] ?: null;
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Subir imagen
    $imagen = '';
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
            $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, medida, precio, precio_mayor, estado_stock, id_categoria, imagen, activo) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $descripcion, $medida, $precio, $precio_mayor, $estado_stock, $categoria_id, $imagen, $activo]);
            header('Location: productos.php?mensaje=creado');
            exit;
        } catch (Exception $e) {
            $error = "Error al crear el producto: " . $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="top-bar">
    <h1>Nuevo Producto</h1>
    <a href="productos.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<?php if ($error): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
</div>
<?php endif; ?>

<div class="card">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label>Nombre del producto *</label>
                <input type="text" name="nombre" required>
            </div>
            <div class="form-group">
                <label>Medida</label>
                <input type="text" name="medida" placeholder="Ej: 11 x 15, 1L, Unidad">
            </div>
        </div>
        
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" rows="4"></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Precio regular ($) *</label>
                <input type="number" name="precio" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label>Precio al mayor ($) (opcional)</label>
                <input type="number" name="precio_mayor" step="0.01" min="0">
                <small>Para compras por mayor</small>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Estado de stock *</label>
                <select name="estado_stock" required>
                    <option value="disponible">Disponible</option>
                    <option value="pocas">Pocas unidades</option>
                    <option value="agotado">Agotado</option>
                </select>
            </div>
            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria_id">
                    <option value="">Sin categoría</option>
                    <?php foreach ($categorias as $c): ?>
                    <option value="<?php echo $c['id_categoria']; ?>"><?php echo $c['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Imagen del producto</label>
                <input type="file" name="imagen" accept="image/*">
                <small>Formatos: JPG, PNG, GIF, WEBP (máx 5MB)</small>
            </div>
        </div>
        
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="activo" checked>
                Producto activo (visible en tienda)
            </label>
        </div>
        
        <button type="submit" class="btn btn-verde">Guardar Producto</button>
    </form>
</div>

<?php
include __DIR__ . '/includes/footer.php';
?>