<?php
require_once '../includes/funciones.php';
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Para depuración - activar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Manejar preflight de CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$response = ['success' => false, 'message' => ''];

try {
    $accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
    
    switch ($accion) {
        case 'enviar':
            // Verificar que el cliente esté logueado
            if (!isset($_SESSION['cliente_id'])) {
                $response['message'] = 'Debes iniciar sesión';
                break;
            }
            
            $rating = intval($_POST['rating'] ?? 0);
            $comentario = trim($_POST['comentario'] ?? '');
            
            if ($rating < 1 || $rating > 5) {
                $response['message'] = 'Calificación inválida (debe ser 1-5)';
                break;
            }
            
            if (empty($comentario)) {
                $response['message'] = 'El comentario no puede estar vacío';
                break;
            }
            
            // Insertar comentario
            $stmt = $pdo->prepare("INSERT INTO comentarios (id_cliente, cliente_nombre, rating, comentario, estado) 
                                   VALUES (?, ?, ?, ?, 'pendiente')");
            $resultado = $stmt->execute([
                $_SESSION['cliente_id'],
                $_SESSION['cliente_nombre'],
                $rating,
                $comentario
            ]);
            
            if ($resultado) {
                $response['success'] = true;
                $response['message'] = '¡Gracias por tu opinión! Será publicada después de revisión.';
            } else {
                $response['message'] = 'Error al guardar el comentario';
            }
            break;
            
        case 'listar':
            $stmt = $pdo->query("SELECT c.*, cl.nombre as cliente_nombre 
                                 FROM comentarios c
                                 JOIN clientes cl ON c.id_cliente = cl.id_cliente
                                 WHERE c.estado = 'aprobado' 
                                 ORDER BY c.fecha DESC 
                                 LIMIT 10");
            $comentarios = $stmt->fetchAll();
            $response['success'] = true;
            $response['data'] = $comentarios;
            break;
            
        default:
            $response['message'] = 'Acción no válida';
    }
    
} catch (Exception $e) {
    $response['message'] = 'Error del servidor: ' . $e->getMessage();
}

echo json_encode($response);
?>