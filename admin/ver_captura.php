<?php
$carpeta = $_SERVER['DOCUMENT_ROOT'] . '/gusticos/assets/uploads/capturas/';

echo "<h2>📸 Verificación de capturas</h2>";

if (file_exists($carpeta)) {
    echo "✅ Carpeta de capturas existe<br>";
    
    $archivos = scandir($carpeta);
    $archivos = array_diff($archivos, ['.', '..']);
    
    if (count($archivos) > 0) {
        echo "<h3>Capturas guardadas:</h3>";
        foreach ($archivos as $archivo) {
            $url = "/gusticos/assets/uploads/capturas/$archivo";
            echo "<div style='margin-bottom: 20px;'>";
            echo "<a href='$url' target='_blank'>$archivo</a><br>";
            echo "<img src='$url' style='max-width: 300px; max-height: 200px; border: 1px solid #ccc; margin-top: 5px;'>";
            echo "</div>";
        }
    } else {
        echo "❌ No hay capturas guardadas aún<br>";
    }
} else {
    echo "❌ La carpeta NO existe. Creándola...";
    mkdir($carpeta, 0777, true);
    echo "✅ Carpeta creada";
}
?>