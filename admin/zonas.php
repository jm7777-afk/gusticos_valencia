<?php
require_once '../includes/funciones.php';
verificar_sesion();

$titulo_pagina = 'Zonas de Envío - Gustico\'s Admin';

// Procesar guardar nueva zona
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar'])) {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $orden = $_POST['orden'] ?? 0;
    
    $stmt = $pdo->prepare("INSERT INTO zonas_envio (nombre, precio_envio, orden) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $precio, $orden]);
    header('Location: zonas.php?mensaje=creado');
    exit;
}

// Procesar actualizar zona
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    $orden = $_POST['orden'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE zonas_envio SET nombre = ?, precio_envio = ?, activo = ?, orden = ? WHERE id_zona = ?");
    $stmt->execute([$nombre, $precio, $activo, $orden, $id]);
    header('Location: zonas.php?mensaje=actualizado');
    exit;
}

// Procesar eliminar zona
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $pdo->prepare("DELETE FROM zonas_envio WHERE id_zona = ?")->execute([$id]);
    header('Location: zonas.php?mensaje=eliminado');
    exit;
}

// Obtener todas las zonas
$zonas = $pdo->query("SELECT * FROM zonas_envio ORDER BY orden ASC, nombre ASC")->fetchAll();

// Mensajes de feedback
$mensaje = '';
if (isset($_GET['mensaje'])) {
    switch ($_GET['mensaje']) {
        case 'creado': $mensaje = 'Zona creada correctamente'; break;
        case 'actualizado': $mensaje = 'Zona actualizada correctamente'; break;
        case 'eliminado': $mensaje = 'Zona eliminada correctamente'; break;
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="top-bar">
    <h1>Zonas de Envío</h1>
    <button class="btn btn-morado" onclick="mostrarFormulario()"><i class="fas fa-plus"></i> Nueva Zona</button>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
</div>
<?php endif; ?>

<!-- Formulario para nueva zona (oculto inicialmente) -->
<div id="form-nuevo" style="display: none; margin-bottom: 30px;">
    <div class="card">
        <h2 style="margin-bottom: 20px;">Nueva Zona de Envío</h2>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre de la zona *</label>
                    <input type="text" name="nombre" placeholder="Ej: Zona Norte" required>
                </div>
                <div class="form-group">
                    <label>Precio de envío ($) *</label>
                    <input type="number" name="precio" step="0.01" min="0" placeholder="3.99" required>
                </div>
                <div class="form-group">
                    <label>Orden</label>
                    <input type="number" name="orden" value="0" min="0">
                </div>
            </div>
            <button type="submit" name="guardar" class="btn btn-verde">Guardar Zona</button>
            <button type="button" class="btn btn-outline" onclick="ocultarFormulario()">Cancelar</button>
        </form>
    </div>
</div>

<!-- Listado de zonas -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Zona</th>
                    <th>Precio de envío</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($zonas as $z): ?>
                <tr>
                    <td><?php echo $z['orden']; ?></td>
                    <td><strong><?php echo htmlspecialchars($z['nombre']); ?></strong></td>
                    <td>$<?php echo number_format($z['precio_envio'], 2); ?></td>
                    <td>
                        <span class="badge <?php echo $z['activo'] ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $z['activo'] ? 'Activo' : 'Inactivo'; ?>
                        </span>
                    </td>
                    <td class="acciones">
                        <button class="editar" onclick="editarZona(<?php echo $z['id_zona']; ?>, '<?php echo $z['nombre']; ?>', <?php echo $z['precio_envio']; ?>, <?php echo $z['activo']; ?>, <?php echo $z['orden']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="?eliminar=<?php echo $z['id_zona']; ?>" class="eliminar" onclick="return confirm('¿Eliminar esta zona?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de edición -->
<div id="modal-editar" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <h2 style="margin-bottom: 20px;">Editar Zona</h2>
        <form method="POST" id="form-editar">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label>Nombre de la zona</label>
                <input type="text" name="nombre" id="edit_nombre" required>
            </div>
            <div class="form-group">
                <label>Precio de envío ($)</label>
                <input type="number" name="precio" id="edit_precio" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label>Orden</label>
                <input type="number" name="orden" id="edit_orden" min="0" value="0">
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="activo" id="edit_activo" value="1" style="width: auto;">
                <label style="margin: 0;">Zona activa</label>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="actualizar" class="btn btn-verde">Actualizar</button>
                <button type="button" class="btn btn-outline" onclick="cerrarModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function mostrarFormulario() {
        document.getElementById('form-nuevo').style.display = 'block';
        window.scrollTo({ top: document.getElementById('form-nuevo').offsetTop - 100, behavior: 'smooth' });
    }
    
    function ocultarFormulario() {
        document.getElementById('form-nuevo').style.display = 'none';
    }
    
    function editarZona(id, nombre, precio, activo, orden) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_precio').value = precio;
        document.getElementById('edit_orden').value = orden;
        document.getElementById('edit_activo').checked = activo == 1;
        document.getElementById('modal-editar').style.display = 'flex';
    }
    
    function cerrarModal() {
        document.getElementById('modal-editar').style.display = 'none';
    }
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>