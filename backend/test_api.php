<?php
/**
 * prueba para verificar la API del backend
 * y la configuracion si se descomenta la linea 7
 * http://localhost:8000/backend/test_api.php
 */
// phpinfo();
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test API RetroRoom</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 800px; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        h1 { color: #667eea; margin-bottom: 30px; }
        .test-item { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .test-item h4 { color: #333; margin-bottom: 10px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1><i class='bi bi-shield-check'></i> Test API RetroRoom</h1>";

// PHP funcionando
echo "<div class='test-item success'>
    <h4>✓ PHP esta funcionando</h4>
    <p><strong>Versión PHP:</strong> " . phpversion() . "</p>
</div>";

// configuración PHP
echo "<div class='test-item'>
    <h4>Configuración PHP</h4>
    <p><strong>Archivo de configuración cargado:</strong> " . php_ini_loaded_file() . "</p>
    <p><strong>Extensiones cargadas:</strong></p>
    <pre>" . implode(', ', get_loaded_extensions()) . "</pre>
</div>";

// conexión a bbdd
echo "<div class='test-item'>";
require_once './config.php';

try {
    $conexion = obtenerConexionBD();
    echo "<h4>✓ Conexión a la base de datos</h4>";
    echo "<p><strong>Base de datos:</strong> " . DB_NAME . "</p>";
    echo "<p><strong>Host:</strong> " . DB_HOST . "</p>";
} catch (Exception $e) {
    echo "<div class='test-item error'>
        <h4>✗ Error de conexión</h4>
        <p>" . $e->getMessage() . "</p>
    </div>";
}
echo "</div>";

// tablas
echo "<div class='test-item'>";
$consulta = $conexion->query("SHOW TABLES");
if ($consulta->rowCount() > 0) {
    echo "<h4>✓ Tablas encontradas:</h4>";
    echo "<ul>";
    while ($row = $consulta->fetch(PDO::FETCH_NUM)) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<h4>✗ No hay tablas</h4>";
    echo "<p>Ejecuta el archivo init.sql para crear las tablas</p>";
}
echo "</div>";

// API endpoints
echo "<div class='test-item'>
    <h4>Endpoints disponibles</h4>
    <ul>
        <li><strong>GET</strong> <a href='http://localhost:8000/backend/api/api.php'>/backend/api/api.php</a> - Prueba de API</li>
        <li><strong>POST</strong> <a href='http://localhost:8000/backend/api/login.php'>/backend/api/login.php</a> - Login</li>
        <li><strong>POST</strong> <a href='http://localhost:8000/backend/api/register.php'>/backend/api/register.php</a> - Registro</li>
        <li><strong>POST</strong> <a href='http://localhost:8000/backend/api/check_session.php'>/backend/api/check_session.php</a> - Verificar sesión</li>
        <li><strong>POST</strong> <a href='http://localhost:8000/backend/api/send_message.php'>/backend/api/send_message.php</a> - Enviar mensaje</li>        
        <li><strong>GET</strong> <a href='http://localhost:8000/backend/api/get_messages.php?limite=50'>/backend/api/get_messages.php?limite=50</a> - Obtener mensajes</li>    
    </ul>
</div>";

echo "    </div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>