<?php
require_once '../includes/funciones.php';

verificar_sesion();

$titulo_pagina = 'Clientes - Gustico\'s Admin';

$busqueda = $_GET['busqueda'] ?? '';

$sql = "SELECT * FROM clientes WHERE 1=1";
$params = [];

if (!empty($busqueda)) {
    $sql .= " AND (nombre LIKE ? OR email LIKE ? OR telefono LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

$sql .= " ORDER BY id_cliente DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="top-bar">
    <h1>Clientes</h1>
</div>

<div class="card">
    <form method="GET" style="margin-bottom: 20px;">
        <div style="display: flex; gap: 10px;">
            <input type="text" name="busqueda" placeholder="Buscar por nombre, email o teléfono..." value="<?php echo htmlspecialchars($busqueda); ?>" style="flex: 1;">
            <button type="submit" class="btn btn-morado">Buscar</button>
        </div>
    </form>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Puntos</th>
                    <th>Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                <tr>
                    <td>#<?php echo $c['id_cliente']; ?></td>
                    <td><strong><?php echo htmlspecialchars($c['nombre']); ?></strong></td>
                    <td><?php echo $c['email']; ?></td>
                    <td><?php echo $c['telefono'] ?? 'N/A'; ?></td>
                    <td><?php echo $c['direccion'] ?? 'N/A'; ?></td>
                    <td><?php echo $c['puntos']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($c['creado'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'includes/footer.php';
?>