<?php
require_once '../includes/funciones.php';
verificar_sesion_admin();

$estado = $_GET['estado'] ?? 'pendiente';

// Acciones
if (isset($_GET['aprobar'])) {
    $id = $_GET['aprobar'];
    $pdo->prepare("UPDATE comentarios SET estado = 'aprobado' WHERE id_comentario = ?")->execute([$id]);
    header('Location: comentarios.php?estado=' . $estado);
    exit;
}

if (isset($_GET['rechazar'])) {
    $id = $_GET['rechazar'];
    $pdo->prepare("UPDATE comentarios SET estado = 'rechazado' WHERE id_comentario = ?")->execute([$id]);
    header('Location: comentarios.php?estado=' . $estado);
    exit;
}

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $pdo->prepare("DELETE FROM comentarios WHERE id_comentario = ?")->execute([$id]);
    header('Location: comentarios.php?estado=' . $estado);
    exit;
}

$comentarios = $pdo->prepare("
    SELECT c.*, cl.nombre as cliente_nombre, cl.email 
    FROM comentarios c
    JOIN clientes cl ON c.id_cliente = cl.id_cliente
    WHERE c.estado = ?
    ORDER BY c.fecha DESC
");
$comentarios->execute([$estado]);
$comentarios = $comentarios->fetchAll();

$pendientes = $pdo->query("SELECT COUNT(*) FROM comentarios WHERE estado = 'pendiente'")->fetchColumn();
$aprobados = $pdo->query("SELECT COUNT(*) FROM comentarios WHERE estado = 'aprobado'")->fetchColumn();
$rechazados = $pdo->query("SELECT COUNT(*) FROM comentarios WHERE estado = 'rechazado'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentarios - Gustico's Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: white;
            padding: 10px;
            border-radius: 40px;
        }
        .tab {
            flex: 1;
            padding: 10px;
            text-align: center;
            border-radius: 40px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .tab.active {
            background: var(--purple);
            color: white;
        }
        .tab .count {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 40px;
            margin-left: 8px;
        }
        .comentario-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: var(--shadow);
        }
        .comentario-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .rating {
            color: gold;
            font-size: 1.2rem;
        }
        .comentario-texto {
            font-size: 1rem;
            line-height: 1.6;
            margin: 15px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .comentario-acciones {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .badge-estado {
            padding: 3px 10px;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .badge-pendiente { background: #FFE082; color: #FF6F00; }
        .badge-aprobado { background: #80E27E; color: #087f23; }
        .badge-rechazado { background: #FFCDD2; color: #D32F2F; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h1>Comentarios</h1>
        </div>
        
        <div class="tabs">
            <div class="tab <?php echo $estado == 'pendiente' ? 'active' : ''; ?>" onclick="location.href='?estado=pendiente'">
                Pendientes <span class="count"><?php echo $pendientes; ?></span>
            </div>
            <div class="tab <?php echo $estado == 'aprobado' ? 'active' : ''; ?>" onclick="location.href='?estado=aprobado'">
                Aprobados <span class="count"><?php echo $aprobados; ?></span>
            </div>
            <div class="tab <?php echo $estado == 'rechazado' ? 'active' : ''; ?>" onclick="location.href='?estado=rechazado'">
                Rechazados <span class="count"><?php echo $rechazados; ?></span>
            </div>
        </div>
        
        <?php if (empty($comentarios)): ?>
        <div class="card text-center" style="padding: 40px;">
            <p>No hay comentarios en esta categoría</p>
        </div>
        <?php endif; ?>
        
        <?php foreach ($comentarios as $c): ?>
        <div class="comentario-card">
            <div class="comentario-header">
                <div>
                    <strong><?php echo htmlspecialchars($c['cliente_nombre']); ?></strong>
                    <div class="rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star" style="color: <?php echo $i <= $c['rating'] ? 'gold' : '#ccc'; ?>;"></i>
                        <?php endfor; ?>
                    </div>
                </div>
                <div>
                    <span class="badge-estado badge-<?php echo $c['estado']; ?>"><?php echo ucfirst($c['estado']); ?></span>
                    <small style="color: var(--gray-text-light);"><?php echo date('d/m/Y H:i', strtotime($c['fecha'])); ?></small>
                </div>
            </div>
            
            <div class="comentario-texto">
                <?php echo nl2br(htmlspecialchars($c['comentario'])); ?>
            </div>
            
            <div class="comentario-acciones">
                <?php if ($c['estado'] == 'pendiente'): ?>
                    <a href="?aprobar=<?php echo $c['id_comentario']; ?>&estado=<?php echo $estado; ?>" class="btn btn-success">Aprobar</a>
                    <a href="?rechazar=<?php echo $c['id_comentario']; ?>&estado=<?php echo $estado; ?>" class="btn btn-outline">Rechazar</a>
                <?php endif; ?>
                <a href="?eliminar=<?php echo $c['id_comentario']; ?>&estado=<?php echo $estado; ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar este comentario?')">Eliminar</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>