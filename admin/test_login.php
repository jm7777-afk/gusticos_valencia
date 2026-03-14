<?php
require_once '../includes/funciones.php';

echo "<h2>🔍 DIAGNÓSTICO DE LOGIN ADMIN</h2>";

// 1. Verificar conexión a BD
try {
    $pdo->query("SELECT 1");
    echo "✅ Conexión a BD: OK<br>";
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
}

// 2. Verificar tabla usuarios
$result = $pdo->query("SHOW TABLES LIKE 'usuarios'");
if ($result->rowCount() > 0) {
    echo "✅ Tabla 'usuarios' existe<br>";
} else {
    echo "❌ Tabla 'usuarios' NO existe<br>";
    exit;
}

// 3. Mostrar usuarios
$usuarios = $pdo->query("SELECT id_usuario, usuario, nombre, rol, activo FROM usuarios")->fetchAll();
echo "<h3>Usuarios en BD:</h3>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Activo</th></tr>";
foreach ($usuarios as $u) {
    echo "<tr>";
    echo "<td>{$u['id_usuario']}</td>";
    echo "<td>{$u['usuario']}</td>";
    echo "<td>{$u['nombre']}</td>";
    echo "<td>{$u['rol']}</td>";
    echo "<td>" . ($u['activo'] ? '✅' : '❌') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Probar contraseña admin123
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = 'admin'");
$stmt->execute();
$admin = $stmt->fetch();

if ($admin) {
    echo "<h3>Prueba de contraseña:</h3>";
    if (password_verify('admin123', $admin['contrasena'])) {
        echo "✅ La contraseña 'admin123' es CORRECTA<br>";
    } else {
        echo "❌ La contraseña 'admin123' es INCORRECTA<br>";
        echo "Hash actual: " . $admin['contrasena'] . "<br>";
        echo "Hash esperado: \$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi<br>";
    }
}

// 5. Botón para resetear
echo "<h3>¿Resetear contraseña?</h3>";
echo "<form method='POST'>";
echo "<button type='submit' name='reset' style='background: #7B1FA2; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>RESETEAR CONTRASEÑA A admin123</button>";
echo "</form>";

if (isset($_POST['reset'])) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE usuarios SET contrasena = ? WHERE usuario = 'admin'")->execute([$hash]);
    echo "<p style='color: green;'>✅ Contraseña reseteada correctamente a 'admin123'</p>";
    echo "<p>Nuevo hash: " . $hash . "</p>";
}
?>