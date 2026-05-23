<?php
/*
 * Este endpoint permite obtener la lista de usuarios registrados en la plataforma.
 * Requiere un token de autenticación en el encabezado Authorization (Bearer token).
 * Ejemplo de solicitud:
 * GET /backend/api/get_users.php
 */

require_once '../config.php';
require_once '../api_base.php';

apiBase('GET', false);


$juego = trim($_GET['juego'] ?? '');

if (empty($juego)) {
    http_response_code(400);
    echo json_encode(['error' => 'El parametro juego es obligatorio']);
    exit;
}

try {
    $conexion = obtenerConexionBD();

    $consulta = $conexion->prepare("
        SELECT u.username, p.puntuacion, p.fecha, u.avatar_url
        FROM puntuaciones p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.juego = ?
        ORDER BY p.puntuacion DESC
        LIMIT 10
    ");
    $consulta->bindParam(1, $juego);
    $consulta->execute();
    $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultados);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener el ranking: ' . $e->getMessage()]);
}
?>