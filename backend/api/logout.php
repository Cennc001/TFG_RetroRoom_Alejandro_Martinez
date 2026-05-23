<?php
require_once '../config.php';

$conexion = obtenerConexionBD();

session_start();

if (isset($_SESSION['usuario_id'])) {
    // Marcar como offline actualizando last_seen a una fecha pasada
    $stmt = $conexion->prepare("UPDATE usuarios SET last_seen = DATE_SUB(NOW(), INTERVAL 10 MINUTE) WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
}

session_destroy();

echo json_encode(['exito' => true, 'mensaje' => 'Sesión cerrada exitosamente']);
?>