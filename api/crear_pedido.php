<?php
require_once '../includes/funciones.php';


header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'numero_pedido' => ''];

try {
    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Datos no válidos');
    }
    
    // Validar datos obligatorios
    if (empty($input['nombre']) || empty($input['telefono']) || empty($input['direccion'])) {
        throw new Exception('Faltan datos de entrega');
    }
    
    if (empty($input['productos'])) {
        throw new Exception('No hay productos en el carrito');
    }
    
    // Procesar captura de pago si existe
    $ruta_captura = '';
    if (!empty($input['datos_pago']['captura'])) {
        $ruta_captura = guardarCaptura($input['datos_pago']['captura'], $input['metodo_pago']);
    }
    
    // Generar número de pedido único
    $numero_pedido = 'GUS-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $pdo->beginTransaction();
    
    // Insertar pedido
    $sql = "INSERT INTO pedidos 
            (numero_pedido, id_cliente, nombre_cliente, telefono_cliente, direccion_entrega, 
             referencias, ubicacion_maps, subtotal, total, tasa_bcv, metodo_pago, datos_pago, 
             estado_pedido, estado_confirmacion, sucursal, ruta_captura) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'recibido', 'pendiente', ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    $id_cliente = $_SESSION['cliente_id'] ?? null;
    $sucursal_nombre = $input['sucursal']['nombre'] ?? 'No especificada';
    $datos_pago_json = json_encode($input['datos_pago'] ?? []);
    
    $stmt->execute([
        $numero_pedido,
        $id_cliente,
        $input['nombre'],
        $input['telefono'],
        $input['direccion'],
        $input['referencias'] ?? '',
        $input['ubicacion'] ?? '',
        $input['subtotal'],
        $input['total'],
        $input['tasa_bcv'] ?? null,
        $input['metodo_pago'],
        $datos_pago_json,
        $sucursal_nombre,
        $ruta_captura
    ]);
    
    $pedido_id = $pdo->lastInsertId();
    
    // Insertar detalles del pedido
    $stmt_detalle = $pdo->prepare("INSERT INTO detalle_pedido 
        (id_pedido, id_producto, nombre_producto, cantidad, precio_unitario, subtotal) 
        VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($input['productos'] as $p) {
        $precio_unitario = $p['precio_usado'] ?? $p['precio'];
        $stmt_detalle->execute([
            $pedido_id,
            $p['id'],
            $p['nombre'],
            $p['cantidad'],
            $precio_unitario,
            $p['subtotal']
        ]);
    }
    
    $pdo->commit();
    
    // ============================================
    // GENERAR MENSAJE DE WHATSAPP PARA LA TIENDA
    // ============================================
    $mensaje_tienda = "🍦 *NUEVO PEDIDO - GUSTICO'S* 🍦\n\n";
    $mensaje_tienda .= "══════════════════════\n";
    $mensaje_tienda .= "📋 *PEDIDO #{$numero_pedido}*\n";
    $mensaje_tienda .= "══════════════════════\n\n";
    
    $mensaje_tienda .= "👤 *CLIENTE*\n";
    $mensaje_tienda .= "Nombre: {$input['nombre']}\n";
    $mensaje_tienda .= "Teléfono: {$input['telefono']}\n";
    $mensaje_tienda .= "Dirección: {$input['direccion']}\n";
    if (!empty($input['referencias'])) {
        $mensaje_tienda .= "Referencias: {$input['referencias']}\n";
    }
    if (!empty($input['ubicacion'])) {
        $mensaje_tienda .= "📍 Ubicación: {$input['ubicacion']}\n";
    }
    
    $mensaje_tienda .= "\n🏬 *SUCURSAL*\n";
    $mensaje_tienda .= "{$sucursal_nombre}\n";
    
    $mensaje_tienda .= "\n📦 *PRODUCTOS*\n";
    foreach ($input['productos'] as $p) {
        $precio = $p['precio_usado'] ?? $p['precio'];
        $mensaje_tienda .= "• {$p['nombre']} x{$p['cantidad']} = $" . number_format($p['subtotal'], 2) . "\n";
    }
    
    $mensaje_tienda .= "\n💰 *TOTAL*\n";
    $mensaje_tienda .= "Subtotal: $" . number_format($input['subtotal'], 2) . "\n";
    $mensaje_tienda .= "Total USD: $" . number_format($input['total'], 2) . "\n";
    if (!empty($input['tasa_bcv'])) {
        $total_bs = $input['total'] * $input['tasa_bcv'];
        $mensaje_tienda .= "Total Bs: Bs " . number_format($total_bs, 2, ',', '.') . "\n";
    }
    
    $mensaje_tienda .= "\n💳 *MÉTODO DE PAGO*\n";
    $metodos = [
        'efectivo_bs' => 'Efectivo Bs',
        'efectivo_divisa' => 'Efectivo Divisa',
        'pago_movil' => 'Pago Móvil',
        'multipago' => 'Pago Múltiple'
    ];
    $mensaje_tienda .= $metodos[$input['metodo_pago']] ?? $input['metodo_pago'] . "\n";
    
    // Agregar detalles específicos según método de pago
    if ($input['metodo_pago'] === 'pago_movil') {
        $mensaje_tienda .= "📱 *Referencia:* {$input['datos_pago']['referencia']}\n";
        if ($ruta_captura) {
            $url_captura = "http://localhost" . $ruta_captura;
            $mensaje_tienda .= "📸 *Captura:* {$url_captura}\n";
        }
    }
    
    if ($input['metodo_pago'] === 'multipago' && !empty($input['datos_pago']['referencia'])) {
        $mensaje_tienda .= "📱 *Ref. Pago Móvil:* {$input['datos_pago']['referencia']}\n";
        $mensaje_tienda .= "💰 *Monto PM:* $" . number_format($input['datos_pago']['monto_pm'], 2) . "\n";
        if ($ruta_captura) {
            $url_captura = "http://localhost" . $ruta_captura;
            $mensaje_tienda .= "📸 *Captura:* {$url_captura}\n";
        }
        $resto = $input['total'] - $input['datos_pago']['monto_pm'];
        $mensaje_tienda .= "💵 *Resto efectivo:* $" . number_format($resto, 2) . "\n";
    }
    
    $mensaje_tienda .= "\n══════════════════════\n";
    $mensaje_tienda .= "✅ *PEDIDO PENDIENTE*\n";
    $mensaje_tienda .= "Ingresa al panel de administración para confirmar:\n";
    $mensaje_tienda .= "http://localhost/gusticos/admin/pedidos.php\n";
    
    // Número de WhatsApp de la tienda (sucursal)
    $whatsapp_tienda = $input['sucursal']['whatsapp'] ?? '584244179135';
    
    // URL para abrir WhatsApp
    $whatsapp_url = "https://wa.me/{$whatsapp_tienda}?text=" . urlencode($mensaje_tienda);
    
    $response['success'] = true;
    $response['numero_pedido'] = $numero_pedido;
    $response['whatsapp_url'] = $whatsapp_url;
    $response['message'] = 'Pedido creado correctamente';
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = $e->getMessage();
    error_log("Error en crear_pedido: " . $e->getMessage());
}

echo json_encode($response);
exit;

// ============================================
// FUNCIÓN PARA GUARDAR CAPTURA DE PAGO
// ============================================
function guardarCaptura($base64, $tipo) {
    if (empty($base64)) return '';
    
    // Extraer la parte de la imagen del base64
    if (strpos($base64, 'base64,') !== false) {
        $base64 = explode('base64,', $base64)[1];
    }
    
    // Decodificar
    $imagen = base64_decode($base64);
    if (!$imagen) return '';
    
    // Crear nombre de archivo
    $fecha = date('Ymd_His');
    $nombre = "captura_{$tipo}_{$fecha}.jpg";
    $ruta_completa = __DIR__ . "/../assets/uploads/capturas/{$nombre}";
    
    // Crear directorio si no existe
    $dir = dirname($ruta_completa);
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    
    // Guardar archivo
    if (file_put_contents($ruta_completa, $imagen)) {
        return "/../assets/uploads/capturas/{$nombre}";
    }
    
    return '';
}
?>