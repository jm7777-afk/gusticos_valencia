<?php
require_once '../includes/funciones.php';

header('Content-Type: application/json');

$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'listar':
        $stmt = $pdo->query("SELECT * FROM zonas_envio WHERE activo = 1 ORDER BY orden ASC");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        break;
        
    case 'calcular':
        $zona_id = intval($_GET['zona_id']);
        $stmt = $pdo->prepare("SELECT precio_envio FROM zonas_envio WHERE id_zona = ?");
        $stmt->execute([$zona_id]);
        $zona = $stmt->fetch();
        
        if ($zona) {
            echo json_encode(['success' => true, 'precio' => $zona['precio_envio']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Zona no encontrada']);
        }
        break;
}
?>