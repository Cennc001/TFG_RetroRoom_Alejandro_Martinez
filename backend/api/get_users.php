<?php
/*
 * Este endpoint permite obtener la lista de usuarios registrados en la plataforma.
 * Requiere un token de autenticación en el encabezado Authorization (Bearer token).
 * Ejemplo de solicitud:
 * GET /backend/api/get_users.php
 * Headers: Authorization: Bearer {token}
 */

require_once '../config.php';
require_once '../api_base.php';

apiBase('GET', true);

$conexion = obtenerConexionBD();

$consulta = $conexion->prepare("SELECT id, username, email, display_name, avatar_url, created_at FROM usuarios ORDER BY username ASC");
$consulta->execute();
$usuarios = $consulta->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'exito' => true,
    'usuarios' => $usuarios
]);
?>