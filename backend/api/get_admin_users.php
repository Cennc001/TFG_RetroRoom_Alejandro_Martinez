<?php
/*
 * Este endpoint permite obtener la lista de usuarios con sus roles.
 * Solo accesible para administradores.
 * Requiere un token de autenticación en el encabezado Authorization (Bearer token).
 * Ejemplo de solicitud:
 * GET /backend/api/get_admin_users.php
 * Headers: Authorization: Bearer {token}
 */

require_once '../config.php';
require_once '../api_base.php';

apiBase('GET', true);

// Verificar que el usuario sea admin
if (!esAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permiso para acceder a este recurso']);
    exit;
}

$conexion = obtenerConexionBD();

$consulta = $conexion->prepare("
    SELECT 
        id, 
        username, 
        email, 
        display_name, 
        avatar_url, 
        rol, 
        last_seen,
        created_at 
    FROM usuarios 
    ORDER BY created_at DESC
");
$consulta->execute();
$usuarios = $consulta->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'exito' => true,
    'usuarios' => $usuarios
]);
?>