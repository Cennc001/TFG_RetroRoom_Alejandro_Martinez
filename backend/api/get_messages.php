<?php
/*
 * Este endpoint permite obtener mensajes del chat general.
 * Requiere un token de autenticación en el encabezado Authorization (Bearer token).
 * Ejemplo de solicitud:
 * GET /backend/api/get_messages.php?limite=50
 * Headers: Authorization: Bearer {token}
 */

require_once '../config.php';
require_once '../api_base.php';

apiBase('GET', true);

$usuario_id = $_SESSION['usuario_id'];

// @params por URL
$limite = intval($_GET['limite'] ?? 100);
$ultimo_id = intval($_GET['ultimo_id'] ?? 0);

// 100 mensajes maximo
if ($limite > 100 || $limite < 1)
    $limite = 100;

$conexion = obtenerConexionBD();

// ultimos mensajes
$query = "SELECT m.id, m.contenido, m.enviado_at, u.username, u.id as usuario_id, u.avatar_url FROM mensajes m JOIN usuarios u ON m.usuario_id = u.id";
$params = [];

if ($ultimo_id > 0) {
    $query .= " AND m.id > ?";
    $params[] = $ultimo_id;
}

$query .= " ORDER BY m.enviado_at DESC LIMIT " . $limite;

$consulta = $conexion->prepare($query);
for ($i = 1; $i <= count($params); $i++) {
    $consulta->bindValue($i, $params[$i - 1], PDO::PARAM_INT);
}
$consulta->execute();
$mensajes = $consulta->fetchAll(PDO::FETCH_ASSOC);

// orden inverso
$mensajes = array_reverse($mensajes);

echo json_encode([
    'exito' => true,
    'mensajes' => $mensajes
]);
?>