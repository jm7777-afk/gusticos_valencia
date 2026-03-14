<?php
require_once __DIR__ . '/../config/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// FUNCIONES DE VERIFICACIÓN DE SESIÓN
// ============================================

/**
 * Verifica que el usuario sea administrador
 */
function verificar_sesion_admin() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /../admin/login.php');
        exit;
    }
}

/**
 * Alias para compatibilidad
 */
function verificar_sesion() {
    verificar_sesion_admin();
}

/**
 * Verifica que el cliente esté logueado
 */
function verificar_cliente() {
    if (!isset($_SESSION['cliente_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /../cliente/login.php');
        exit;
    }
}

// ============================================
// FUNCIONES DE CONFIGURACIÓN
// ============================================

function obtenerWhatsApp($pdo) {
    try {
        $stmt = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'whatsapp'");
        $res = $stmt->fetch();
        return $res ? $res['valor'] : '+584244179135';
    } catch (Exception $e) {
        return '+584244179135';
    }
}

function obtenerTasaBVC($pdo) {
    try {
        $stmt = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'tasa_bcv'");
        $res = $stmt->fetch();
        return $res ? floatval($res['valor']) : 40.00;
    } catch (Exception $e) {
        return 40.00;
    }
}

// ============================================
// FUNCIONES DE SUBIDA DE ARCHIVOS
// ============================================

/**
 * Sube una imagen al servidor
 * @param array $archivo El archivo $_FILES
 * @param string $carpeta Subcarpeta dentro de assets/uploads/
 * @return array Resultado con 'exito' y 'archivo' o 'error'
 */
function subirImagen($archivo, $carpeta = 'productos') {
    // Definir ruta de subida
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/../assets/uploads/' . $carpeta . '/';
    
    // Crear carpeta si no existe
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Validar que sea un archivo válido
    if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'No se recibió el archivo correctamente'];
    }
    
    // Validar tamaño (máximo 5MB)
    if ($archivo['size'] > 5 * 1024 * 1024) {
        return ['error' => 'La imagen es demasiado grande (máx 5MB)'];
    }
    
    // Obtener extensión
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    // Extensiones permitidas
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($extension, $allowed)) {
        return ['error' => 'Formato no permitido. Use: jpg, jpeg, png, gif, webp'];
    }
    
    // Generar nombre único
    $nombre = time() . '_' . uniqid() . '.' . $extension;
    $ruta_completa = $upload_dir . $nombre;
    
    // Mover el archivo
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        return [
            'exito' => true,
            'archivo' => '/../assets/uploads/' . $carpeta . '/' . $nombre
        ];
    } else {
        return ['error' => 'Error al guardar el archivo'];
    }
}

// ============================================
// FUNCIONES DE STOCK Y BADGES
// ============================================

function getStockBadge($stock) {
    if ($stock <= 0) {
        return ['texto' => 'AGOTADO', 'clase' => 'badge-agotado'];
    } elseif ($stock <= 5) {
        return ['texto' => '⚠️ Últimas!', 'clase' => 'badge-pocas'];
    } else {
        return ['texto' => 'Disponible', 'clase' => 'badge-disponible'];
    }
}

// ============================================
// FUNCIÓN CALCULAR CARRITO - VERSIÓN CORREGIDA
// ============================================

function calcularCarrito($items, $pdo) {
    $total = 0;
    $productos = [];
    
    // Validar entrada
    if (empty($items) || !is_array($items)) {
        error_log("calcularCarrito: Items vacíos o no es array");
        return ['productos' => [], 'total' => 0];
    }
    
    // Filtrar items con cantidad > 0
    $items_filtrados = array_filter($items, function($cantidad) {
        return $cantidad > 0;
    });
    
    if (empty($items_filtrados)) {
        return ['productos' => [], 'total' => 0];
    }
    
    $ids = array_keys($items_filtrados);
    
    if (empty($ids)) {
        return ['productos' => [], 'total' => 0];
    }
    
    // Crear placeholders para la consulta SQL
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    try {
        $sql = "SELECT id_producto, nombre, medida, precio, precio_mayor, estado_stock 
                FROM productos 
                WHERE id_producto IN ($placeholders) AND activo = 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);
        $productosDB = $stmt->fetchAll();
        
        if (empty($productosDB)) {
            error_log("calcularCarrito: No se encontraron productos en BD");
            return ['productos' => [], 'total' => 0];
        }
        
        foreach ($productosDB as $p) {
            $id = $p['id_producto'];
            $cantidad = isset($items_filtrados[$id]) ? (int)$items_filtrados[$id] : 0;
            
            if ($cantidad > 0) {
                // Verificar stock antes de procesar
                if ($p['estado_stock'] == 'agotado') {
                    continue; // Saltar productos agotados
                }
                
                // Usar precio mayor si cantidad >= 12 y existe precio mayor
                $precio_usado = floatval($p['precio']);
                if ($cantidad >= 12 && !empty($p['precio_mayor']) && floatval($p['precio_mayor']) > 0) {
                    $precio_usado = floatval($p['precio_mayor']);
                }
                
                $subtotal = $precio_usado * $cantidad;
                $total += $subtotal;
                
                $productos[] = [
                    'id' => (int)$p['id_producto'],
                    'nombre' => $p['nombre'],
                    'medida' => $p['medida'] ?? 'Unidad',
                    'precio' => floatval($p['precio']),
                    'precio_mayor' => !empty($p['precio_mayor']) ? floatval($p['precio_mayor']) : null,
                    'precio_usado' => $precio_usado,
                    'cantidad' => $cantidad,
                    'subtotal' => $subtotal,
                    'estado_stock' => $p['estado_stock']
                ];
            }
        }
        
    } catch (Exception $e) {
        error_log("Error en calcularCarrito: " . $e->getMessage());
        return ['productos' => [], 'total' => 0];
    }
    
    return ['productos' => $productos, 'total' => $total];
}

// ============================================
// FUNCIONES DE UTILIDAD
// ============================================

function formatoMoneda($cantidad, $moneda = 'USD') {
    if ($moneda === 'USD') {
        return '$ ' . number_format($cantidad, 2, '.', ',');
    } else {
        return 'Bs ' . number_format($cantidad, 2, ',', '.');
    }
}

function formatoFecha($fecha, $formato = 'd/m/Y H:i') {
    return date($formato, strtotime($fecha));
}

?>