// config/production.php
<?php
// Activar caché de consultas
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

// Configurar sesiones seguras
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Solo con HTTPS
ini_set('session.gc_maxlifetime', 7200); // 2 horas