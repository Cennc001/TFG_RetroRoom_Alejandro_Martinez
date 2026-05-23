<?php
/*
 * Este endpoint devuelve la lista de usuarios conectados (activos en los últimos 5 minutos).
 * Ejemplo de solicitud:
 * GET /backend/api/get_online_users.php
 */

require_once '../config.php';
require_once '../api_base.php';

apiBase('GET', false);

try {
    $conexion = obtenerConexionBD();

    // Usuarios activos en los ultimos 5 minutos
    $stmt = $conexion->prepare("
        SELECT id, username, display_name, avatar_url, last_seen
        FROM usuarios
        WHERE last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ORDER BY last_seen DESC
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'exito' => true,
        'usuarios' => $usuarios,
        'total' => count($usuarios)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'exito' => false,
        'error' => 'Error al obtener usuarios conectados: ' . $e->getMessage()
    ]);
}
?>