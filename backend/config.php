<?php
// CONFIG 
// BBDD LOCAL
// define('DB_HOST', 'localhost');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_NAME', 'retroroom');

// BBDD DOCKER
define('DB_HOST', 'mysql');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'retroroom');

// OBTENER CONN
function obtenerConexionBD()
{
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $conexion = new PDO($dsn, DB_USER, DB_PASS);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion;
    } catch (PDOException $e) {
        throw $e; // TEMP - execpcione en vez de die
    }
}

// VERIFICAR TOKEN Y RESTAURAR SESION
function verificarToken()
{
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    // token de auth
    if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        return false;
    }

    $token = $matches[1];

    // restaurar sesion usando el token (session_id)
    session_id($token);
    session_start();

    if (!isset($_SESSION['usuario_id'])) {
        session_destroy();
        return false;
    }

    // actualizar last_seen del usuario
    try {
        $conexion = obtenerConexionBD();
        $stmt = $conexion->prepare("UPDATE usuarios SET last_seen = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
    } catch (Exception $e) {
    }

    return true;
}

// VERIFICAR SI EL USUARIO ACTUAL ES ADMIN
function esAdmin()
{
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }

    try {
        $conexion = obtenerConexionBD();
        $stmt = $conexion->prepare("SELECT rol FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario && $usuario['rol'] === 'admin';
    } catch (Exception $e) {
        return false;
    }
}

?>