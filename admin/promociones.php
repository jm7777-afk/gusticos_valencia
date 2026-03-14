<?php
require_once '../includes/funciones.php';
verificar_sesion_admin();

$titulo_pagina = 'Promociones - Gustico\'s Admin';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['guardar'])) {
        $codigo = strtoupper($_POST['codigo']);
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $tipo = $_POST['tipo'];
        $valor = $_POST['valor'];
        $puntos_bonus = $_POST['puntos_bonus'] ?? 0;
        $aplica_mayor = isset($_POST['aplica_mayor']) ? 1 : 0;
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];
        
        $stmt = $pdo->prepare("INSERT INTO promociones (codigo, nombre, descripcion, tipo, valor, puntos_bonus, aplica_mayor, fecha_inicio, fecha_fin) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$codigo, $nombre, $descripcion, $tipo, $valor, $puntos_bonus, $aplica_mayor, $fecha_inicio, $fecha_fin]);
    }
    
    if (isset($_POST['toggle'])) {
        $id = $_POST['id'];
        $activo = $_POST['activo'];
        $pdo->prepare("UPDATE promociones SET activo = ? WHERE id_promocion = ?")->execute([$activo, $id]);
    }
}

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $pdo->prepare("DELETE FROM promociones WHERE id_promocion = ?")->execute([$id]);
    header('Location: promociones.php');
    exit;
}

$promociones = $pdo->query("SELECT * FROM promociones ORDER BY fecha_fin DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="top-bar">
    <h1>Promociones</h1>
    <button class="btn btn-morado" onclick="mostrarForm()">+ Nueva Promoción</button>
</div>

<div id="formNuevo" style="display: none; margin-bottom: 30px;">
    <div class="card">
        <h2>Nueva Promoción</h2>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Código *</label>
                    <input type="text" name="codigo" required placeholder="Ej: BIENVENIDA10">
                </div>
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="2"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Tipo</label>
                    <select name="tipo">
                        <option value="porcentaje">Porcentaje (%)</option>
                        <option value="fijo">Monto Fijo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Valor</label>
                    <input type="number" name="valor" step="0.01" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Puntos bonus</label>
                    <input type="number" name="puntos_bonus" value="0" min="0">
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="aplica_mayor" value="1">
                        Aplica para compras al mayor
                    </label>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Fecha inicio</label>
                    <input type="datetime-local" name="fecha_inicio" required>
                </div>
                <div class="form-group">
                    <label>Fecha fin</label>
                    <input type="datetime-local" name="fecha_fin" required>
                </div>
            </div>
            
            <button type="submit" name="guardar" class="btn btn-verde">Guardar Promoción</button>
            <button type="button" class="btn btn-outline" onclick="cerrarForm()">Cancelar</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Valor</th>
                    <th>Puntos</th>
                    <th>Válido hasta</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promociones as $p): 
                    $activa = ($p['activo'] && $p['fecha_fin'] >= date('Y-m-d H:i:s'));
                ?>
                <tr>
                    <td><strong><?php echo $p['codigo']; ?></strong></td>
                    <td><?php echo $p['nombre']; ?></td>
                    <td><?php echo $p['descripcion']; ?></td>
                    <td>
                        <?php if ($p['tipo'] == 'porcentaje'): ?>
                            <?php echo $p['valor']; ?>%
                        <?php else: ?>
                            $<?php echo number_format($p['valor'], 2); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $p['puntos_bonus'] > 0 ? $p['puntos_bonus'] . ' pts' : '-'; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($p['fecha_fin'])); ?></td>
                    <td>
                        <span class="badge <?php echo $activa ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $activa ? 'Activa' : 'Inactiva'; ?>
                        </span>
                    </td>
                    <td class="acciones">
                        <a href="?eliminar=<?php echo $p['id_promocion']; ?>" class="eliminar" onclick="return confirm('¿Eliminar?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function mostrarForm() {
        document.getElementById('formNuevo').style.display = 'block';
    }
    
    function cerrarForm() {
        document.getElementById('formNuevo').style.display = 'none';
    }
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>