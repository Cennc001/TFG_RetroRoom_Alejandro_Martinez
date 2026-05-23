<?php
/*
 * Este endpoint permite eliminar un usuario de la base de datos.
 * Solo accesible para administradores.
 * Requiere un token de autenticación en el encabezado Authorization (Bearer token).
 * Ejemplo de solicitud:
 * POST /backend/api/delete_user.php
 * Headers: Authorization: Bearer {token}
 * Body: { "usuario_id": 5 }
 */

require_once '../config.php';
require_once '../api_base.php';

apiBase('POST', true);

// Verificar que el usuario sea admin
if (!esAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permiso para acceder a este recurso']);
    exit;
}

// Inputs del POST
$inputs = json_decode(file_get_contents('php://input'), true);

$usuarioId = $inputs['usuario_id'] ?? null;

// Validaciones
if (empty($usuarioId) || !is_numeric($usuarioId)) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID del usuario es inválido']);
    exit;
}

// No permitir auto-eliminación
if ($usuarioId == $_SESSION['usuario_id']) {
    http_response_code(400);
    echo json_encode(['error' => 'No puedes eliminar tu propia cuenta']);
    exit;
}

try {
    $conexion = obtenerConexionBD();

    // Verificar que el usuario existe
    $checkStmt = $conexion->prepare("SELECT id FROM usuarios WHERE id = ?");
    $checkStmt->execute([$usuarioId]);

    if ($checkStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'El usuario no existe']);
        exit;
    }

    // Eliminar el usuario (cascada eliminará sus datos relacionados)
    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$usuarioId]);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Usuario eliminado correctamente'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al eliminar el usuario']);
}
?>