<?php
// ============================================
// CONFIGURACIÓN DE LA BASE DE DATOS
// ============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gusticos_bd');
define('DB_CHARSET', 'utf8mb4');

// ============================================
// CONFIGURACIÓN GENERAL
// ============================================
define('SITE_NAME', 'Gustico\'s');
define('SITE_URL', 'http://localhost/gusticos');
define('UPLOAD_PATH', __DIR__ . '/assets/uploads/');
define('UPLOAD_URL', SITE_URL . '/assets/uploads/');

// ============================================
// CONEXIÓN PDO
// ============================================
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error de conexión a la base de datos. Por favor, intente más tarde.");
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>