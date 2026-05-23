<?php
/*
 * Este endpoint devuelve el perfil del usuario conectado.
 * Requiere token de autenticación en el encabezado Authorization (Bearer token).
 * GET /backend/api/get_profile.php
 */

require_once '../config.php';
require_once '../api_base.php';

apiBase('GET', true);

try {
    $conexion = obtenerConexionBD();

    // datos del usuario
    $consulta = $conexion->prepare("SELECT id, username, email, display_name, avatar_url, rol, created_at FROM usuarios WHERE id = ?");
    $consulta->bindParam(1, $_SESSION['usuario_id'], PDO::PARAM_INT);
    $consulta->execute();
    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }

    // GET stats del usuario
    $consultaStats = $conexion->prepare("SELECT COUNT(*) AS partidas, COALESCE(SUM(puntuacion), 0) AS total_puntos, COALESCE(MAX(puntuacion), 0) AS mejor_puntuacion FROM puntuaciones WHERE usuario_id = ?");
    $consultaStats->bindParam(1, $_SESSION['usuario_id'], PDO::PARAM_INT);
    $consultaStats->execute();
    $estadisticas = $consultaStats->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'exito' => true,
        'perfil' => [
            'id' => $usuario['id'],
            'username' => $usuario['username'],
            'nombre' => $usuario['display_name'] ?? $usuario['username'],
            'email' => $usuario['email'],
            'avatar_url' => $usuario['avatar_url'] ?? '',
            'rol' => $usuario['rol'] ?? 'user',
            'created_at' => $usuario['created_at'],
            'estadisticas' => [
                'partidas' => (int) $estadisticas['partidas'],
                'total_puntos' => (int) $estadisticas['total_puntos'],
                'mejor_puntuacion' => (int) $estadisticas['mejor_puntuacion']
            ]
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener el perfil: ' . $e->getMessage()]);
}
?>