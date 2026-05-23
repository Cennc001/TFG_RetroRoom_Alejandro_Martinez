<?php
/*
 * Este endpoint permite enviar un mensaje al chat general.
 * Requiere un token de autenticación en el encabezado Authorization (Bearer token).
 * Ejemplo de solicitud:
 * POST /backend/api/send_message.php
 * Headers: Authorization: Bearer {token}
 * Body: { "contenido": "Hola, ¿cómo estan?" }
 */

require_once '../config.php';
require_once '../api_base.php';

apiBase('POST', true);

$usuario_id = $_SESSION['usuario_id'];

// inputs
$inputs = json_decode(file_get_contents('php://input'), true);
$contenido = trim($inputs['contenido'] ?? '');

if (empty($contenido)) {
    http_response_code(400);
    echo json_encode(['error' => 'El contenido es obligatorio']);
    exit;
}

$conexion = obtenerConexionBD();

// envio de mensaje
$consulta = $conexion->prepare("INSERT INTO mensajes (usuario_id, contenido) VALUES (?, ?)");
$consulta->bindParam(1, $usuario_id);
$consulta->bindParam(2, $contenido);

if ($consulta->execute()) {
    // last insert id para actualizar el indice de mensajes en el cliente a partir del ultimo
    $mensaje_id = $conexion->lastInsertId();
    echo json_encode([
        'exito' => true,
        'mensaje' => 'Mensaje enviado exitosamente',
        'mensaje_id' => $mensaje_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al enviar el mensaje']);
}
?>