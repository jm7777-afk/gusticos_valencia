<?php
require_once '../includes/funciones.php';


// Verificar que el cliente esté logueado
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit;
}

$id_cliente = $_SESSION['cliente_id'];
$titulo_pagina = 'Mi Perfil - Gustico\'s';

// Obtener datos del cliente
$stmt_cliente = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
$stmt_cliente->execute([$id_cliente]);
$cliente = $stmt_cliente->fetch();

if (!$cliente) {
    // Si no existe el cliente, cerrar sesión
    session_destroy();
    header('Location: login.php?error=sesion_invalida');
    exit;
}

// Obtener pedidos del cliente
$stmt_pedidos = $pdo->prepare("SELECT * FROM pedidos WHERE id_cliente = ? ORDER BY fecha_pedido DESC LIMIT 10");
$stmt_pedidos->execute([$id_cliente]);
$pedidos = $stmt_pedidos->fetchAll();

// Obtener historial de puntos
$stmt_puntos = $pdo->prepare("SELECT * FROM puntos WHERE id_cliente = ? ORDER BY fecha DESC LIMIT 10");
$stmt_puntos->execute([$id_cliente]);
$historial_puntos = $stmt_puntos->fetchAll();

// Procesar actualización de datos
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar'])) {
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    
    $stmt_update = $pdo->prepare("UPDATE clientes SET nombre = ?, telefono = ?, direccion = ? WHERE id_cliente = ?");
    if ($stmt_update->execute([$nombre, $telefono, $direccion, $id_cliente])) {
        $_SESSION['cliente_nombre'] = $nombre;
        $mensaje = "Datos actualizados correctamente";
        // Recargar datos del cliente
        $stmt_cliente->execute([$id_cliente]);
        $cliente = $stmt_cliente->fetch();
    }
}

include 'includes/header.php';
?>

<div class="container">
    <!-- Mensaje de éxito -->
    <?php if ($mensaje): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
    </div>
    <?php endif; ?>
    
    <!-- Header del perfil -->
    <div style="background: linear-gradient(135deg, var(--morado), var(--verde)); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>¡Hola, <?php echo htmlspecialchars($cliente['nombre']); ?>!</h1>
            <p><?php echo $cliente['email']; ?></p>
        </div>
        <div style="background: white; color: var(--morado); padding: 15px 25px; border-radius: 40px; font-weight: 700;">
            <i class="fas fa-star" style="color: gold;"></i> <?php echo $cliente['puntos_totales'] ?? 0; ?> puntos
        </div>
    </div>
    
    <!-- Grid de contenido -->
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
        <!-- Columna izquierda: Datos personales -->
        <div class="card">
            <h2 style="color: var(--morado); margin-bottom: 20px;">Mis Datos</h2>
            
            <form method="POST">
                <div class="form-group">
                    <label>Nombre completo</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?php echo $cliente['email']; ?>" disabled class="form-control" style="background: #f5f5f5;">
                </div>
                
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" value="<?php echo $cliente['telefono']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Dirección</label>
                    <textarea name="direccion" rows="3"><?php echo $cliente['direccion']; ?></textarea>
                </div>
                
                <button type="submit" name="actualizar" class="btn btn-morado" style="width: 100%;">Actualizar datos</button>
            </form>
            
            <div style="margin-top: 20px;">
                <a href="logout.php" class="btn btn-outline" style="width: 100%;">Cerrar sesión</a>
            </div>
        </div>
        
        <!-- Columna derecha: Pedidos y puntos -->
        <div>
            <!-- SECCIÓN DE PUNTOS -->
            <div class="puntos-card" style="background: linear-gradient(135deg, #FFD700, #FFA500); padding: 25px; border-radius: 15px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="color: var(--morado-oscuro); margin-bottom: 10px;">🌟 Tus Puntos</h3>
                        <p style="font-size: 3rem; font-weight: 800; color: var(--morado-oscuro);">
                            <?php echo $cliente['puntos_totales'] ?? 0; ?>
                        </p>
                    </div>
                    <div style="font-size: 4rem;">🏆</div>
                </div>
                <p style="color: var(--morado-oscuro); margin-top: 10px;">
                    Acumula puntos y canjéalos por productos
                </p>
            </div>
            
            <!-- HISTORIAL DE PUNTOS -->
            <div class="card" style="margin-bottom: 20px;">
                <h3 style="color: var(--morado); margin-bottom: 15px;">Historial de Puntos</h3>
                
                <?php if (empty($historial_puntos)): ?>
                <p style="text-align: center; padding: 30px; color: var(--texto-secundario);">
                    Aún no tienes movimientos de puntos
                </p>
                <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Puntos</th>
                                <th>Concepto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial_puntos as $p): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($p['fecha'])); ?></td>
                                <td class="<?php echo $p['puntos'] > 0 ? 'text-success' : 'text-danger'; ?>" style="font-weight: 700;">
                                    <?php echo $p['puntos'] > 0 ? '+' : ''; ?><?php echo $p['puntos']; ?>
                                </td>
                                <td><?php echo $p['referencia'] ?? ucfirst($p['tipo']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- ÚLTIMOS PEDIDOS -->
            <div class="card">
                <h3 style="color: var(--morado); margin-bottom: 15px;">Últimos Pedidos</h3>
                
                <?php if (empty($pedidos)): ?>
                <p style="text-align: center; padding: 30px; color: var(--texto-secundario);">
                    No tienes pedidos aún
                </p>
                <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $p): ?>
                            <tr>
                                <td><strong><?php echo $p['numero_pedido']; ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($p['fecha_pedido'])); ?></td>
                                <td>$<?php echo number_format($p['total'], 2); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo $p['estado_pedido'] == 'entregado' ? 'badge-success' : 'badge-warning'; 
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
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>