<?php
require_once '../includes/funciones.php';
verificar_sesion();

$titulo_pagina = 'Carrusel - Gustico\'s Admin';

// Función para subir imagen
function subirImagenCarrusel($archivo) {
    $target_dir = __DIR__ . '/assets/uploads/carrusel/';
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $extension = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($extension, $allowed)) {
        return ['error' => 'Formato no permitido'];
    }
    
    $nombre = time() . '_' . uniqid() . '.' . $extension;
    $target_file = $target_dir . $nombre;
    
    if (move_uploaded_file($archivo["tmp_name"], $target_file)) {
        return ['exito' => true, 'archivo' => '/assets/uploads/carrusel/' . $nombre];
    }
    
    return ['error' => 'Error al subir el archivo'];
}

// Guardar nueva imagen
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar'])) {
    $titulo = $_POST['titulo'];
    $subtitulo = $_POST['subtitulo'];
    $link = $_POST['link'] ?? '';
    $orden = $_POST['orden'] ?? 0;
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $resultado = subirImagenCarrusel($_FILES['imagen']);
        if ($resultado['exito']) {
            $stmt = $pdo->prepare("INSERT INTO carrusel (titulo, subtitulo, imagen, link, orden) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$titulo, $subtitulo, $resultado['archivo'], $link, $orden]);
            header('Location: carrusel.php?mensaje=creado');
            exit;
        }
    }
}

// Actualizar orden (AJAX)
if (isset($_POST['actualizar_orden'])) {
    $ordenes = json_decode($_POST['ordenes'], true);
    foreach ($ordenes as $item) {
        $pdo->prepare("UPDATE carrusel SET orden = ? WHERE id_item = ?")->execute([$item['orden'], $item['id']]);
    }
    echo json_encode(['success' => true]);
    exit;
}

// Eliminar imagen
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("SELECT imagen FROM carrusel WHERE id_item = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetch();
    
    if ($img) {
        $archivo = __DIR__  . $img['imagen'];
        if (file_exists($archivo)) {
            unlink($archivo);
        }
    }
    
    $pdo->prepare("DELETE FROM carrusel WHERE id_item = ?")->execute([$id]);
    header('Location: carrusel.php?mensaje=eliminado');
    exit;
}

// Activar/desactivar
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $pdo->prepare("UPDATE carrusel SET activo = NOT activo WHERE id_item = ?")->execute([$id]);
    header('Location: carrusel.php');
    exit;
}

$items = $pdo->query("SELECT * FROM carrusel ORDER BY orden ASC, id_item DESC")->fetchAll();

$mensaje = '';
if (isset($_GET['mensaje'])) {
    switch ($_GET['mensaje']) {
        case 'creado': $mensaje = 'Imagen agregada correctamente'; break;
        case 'eliminado': $mensaje = 'Imagen eliminada correctamente'; break;
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="top-bar">
    <h1>Carrusel Principal</h1>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
</div>
<?php endif; ?>

<!-- Formulario para nueva imagen -->
<div class="card">
    <h2 style="margin-bottom: 20px;">Agregar Nueva Imagen</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label>Título *</label>
                <input type="text" name="titulo" required>
            </div>
            <div class="form-group">
                <label>Orden</label>
                <input type="number" name="orden" value="0" min="0">
            </div>
        </div>
        
        <div class="form-group">
            <label>Subtítulo</label>
            <textarea name="subtitulo" rows="2"></textarea>
        </div>
        
        <div class="form-group">
            <label>Link (opcional)</label>
            <input type="url" name="link" placeholder="https://ejemplo.com">
        </div>
        
        <div class="form-group">
            <label>Imagen *</label>
            <input type="file" name="imagen" accept="image/*" required>
            <small>Formatos permitidos: JPG, PNG, GIF, WEBP. Máximo 5MB</small>
        </div>
        
        <button type="submit" name="guardar" class="btn btn-morado">Subir Imagen</button>
    </form>
</div>

<!-- Listado de imágenes -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h2><i class="fas fa-images"></i> Imágenes Actuales</h2>
        <small>Arrastra para reordenar</small>
    </div>
    
    <div class="table-responsive">
        <table id="tabla-carrusel">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Imagen</th>
                    <th>Título</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="carrusel-items">
                <?php foreach ($items as $item): ?>
                <tr data-id="<?php echo $item['id_item']; ?>" class="carrusel-item">
                    <td class="drag-handle" style="cursor: grab;"><i class="fas fa-grip-vertical"></i> <?php echo $item['orden']; ?></td>
                    <td><img src="<?php echo $item['imagen']; ?>" style="width: 100px; height: 60px; object-fit: cover; border-radius: 5px;"></td>
                    <td><?php echo $item['titulo']; ?></td>
                    <td>
                        <span class="badge <?php echo $item['activo'] ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $item['activo'] ? 'Activo' : 'Inactivo'; ?>
                        </span>
                    </td>
                    <td class="acciones">
                        <a href="?toggle=<?php echo $item['id_item']; ?>" class="ver" title="<?php echo $item['activo'] ? 'Desactivar' : 'Activar'; ?>">
                            <i class="fas <?php echo $item['activo'] ? 'fa-eye' : 'fa-eye-slash'; ?>"></i>
                        </a>
                        <a href="?eliminar=<?php echo $item['id_item']; ?>" class="eliminar" onclick="return confirm('¿Eliminar esta imagen?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Script para drag & drop reordenar -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    const tbody = document.getElementById('carrusel-items');
    if (tbody) {
        new Sortable(tbody, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: function() {
                const items = document.querySelectorAll('.carrusel-item');
                const ordenes = [];
                items.forEach((item, index) => {
                    ordenes.push({
                        id: item.dataset.id,
                        orden: index
                    });
                });
                
                fetch('carrusel.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'actualizar_orden=1&ordenes=' + JSON.stringify(ordenes)
                }).then(() => {
                    location.reload();
                });
            }
        });
    }
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>