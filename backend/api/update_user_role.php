<?php
/*
 * Este endpoint permite actualizar el rol de un usuario.
 * Solo accesible para administradores.
 * Requiere un token de autenticación en el encabezado Authorization (Bearer token).
 * Ejemplo de solicitud:
 * POST /backend/api/update_user_role.php
 * Headers: Authorization: Bearer {token}
 * Body: { "usuario_id": 5, "nuevo_rol": "moderator" }
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
$nuevoRol = trim($inputs['nuevo_rol'] ?? '');

// Validaciones
$errores = [];

if (empty($usuarioId) || !is_numeric($usuarioId)) {
    $errores[] = 'El ID del usuario es inválido';
}

if (empty($nuevoRol)) {
    $errores[] = 'El nuevo rol es obligatorio';
} elseif (!in_array($nuevoRol, ['user', 'moderator', 'admin'])) {
    $errores[] = 'El rol debe ser: user, moderator o admin';
}

if (!empty($errores)) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'errores' => $errores
    ]);
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

    // Actualizar el rol
    $stmt = $conexion->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
    $stmt->execute([$nuevoRol, $usuarioId]);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Rol actualizado correctamente'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar el rol']);
}
?>