<?php
// Activar errores para ver qué pasa (temporal)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar buffer para capturar cualquier error
ob_start();

try {
    require_once '../includes/funciones.php';
    session_start();
    
    // Limpiar buffer antes de enviar JSON
    ob_clean();
    
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }
    
    $response = ['success' => false, 'message' => '', 'data' => null];
    
    $accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
    
    if ($accion === 'calcular') {
        $items = isset($_POST['items']) ? json_decode($_POST['items'], true) : [];
        
        if (empty($items)) {
            $response = ['success' => true, 'data' => ['productos' => [], 'total' => 0]];
        } else {
            $resultado = calcularCarrito($items, $pdo);
            $response = [
                'success' => true,
                'data' => [
                    'productos' => $resultado['productos'],
                    'total' => $resultado['total']
                ]
            ];
        }
    } else {
        $response['message'] = 'Acción no válida';
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

ob_end_flush();
?>