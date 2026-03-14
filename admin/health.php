// admin/health.php
<?php
$start = microtime(true);

// Verificar conexión BD
try {
    $pdo->query("SELECT 1");
    $db_status = "✅ OK";
} catch (Exception $e) {
    $db_status = "❌ ERROR: " . $e->getMessage();
}

// Verificar espacio en disco
$disk_free = disk_free_space("/");
$disk_total = disk_total_space("/");
$disk_used = $disk_total - $disk_free;
$disk_percent = round(($disk_used / $disk_total) * 100, 2);

// Tiempo de respuesta
$time = round((microtime(true) - $start) * 1000, 2);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Health Check - Gustico's</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        .ok { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>🔍 Health Check</h1>
    
    <h2>Base de datos</h2>
    <div class="<?php echo strpos($db_status, 'OK') ? 'ok' : 'error'; ?>">
        <?php echo $db_status; ?>
    </div>
    
    <h2>Disco</h2>
    <div>Usado: <?php echo round($disk_used / 1024 / 1024 / 1024, 2); ?> GB</div>
    <div>Libre: <?php echo round($disk_free / 1024 / 1024 / 1024, 2); ?> GB</div>
    <div>Ocupado: <?php echo $disk_percent; ?>%</div>
    
    <h2>Rendimiento</h2>
    <div>Tiempo de respuesta: <?php echo $time; ?> ms</div>
    
    <h2>PHP</h2>
    <div>Versión: <?php echo phpversion(); ?></div>
    <div>Memoria límite: <?php echo ini_get('memory_limit'); ?></div>
    <div>Upload max: <?php echo ini_get('upload_max_filesize'); ?></div>
</body>
</html>