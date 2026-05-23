<?php
/*
 * Este endpoint permite a los usuarios autenticados guardar su puntuación de un juego.
 * Requiere un token de autenticación en el encabezado Authorization (Bearer token).
 * Ejemplo de solicitud:
 *  POST /backend/api/save_score.php
 *  Headers: Authorization: Bearer <token>
 *  Body: { "juego": "tetris", "puntuacion": 1500 }
 * 
 * Respuestas:
 * - 200 OK: { "mensaje": "Puntuación guardada correctamente"
 * - 400 Bad Request: { "errores": ["El juego es obligatorio", "La puntuación debe ser un número positivo"] }
 * - 401 Unauthorized: { "error": "Usuario no autenticado" }
 * - 405 Method Not Allowed: { "error": "Metodo no permitido" }
 * - 500 Internal Server Error: { "error": "Error al guardar la puntuación: <mensaje de error>" }
 * 
 * Notas: 
 *  No hay mas comprobaciones que sinplemente los datos se envien, ni si existe el juego, ni si se ha obtenido
 *   la puntuacion legalmente... por tanto con una simple llamada a la api con postman, se podria crear un nuevo
 *   highscore, pero siendo realistas, para un proyecto de tfg no es necesario implementar un sistema para evitarlo
 *   porque las consecuencias serian un highscore, hay cosas mas requirientes como XSS o usurpacion de sesion
 */

require_once '../config.php';
require_once '../api_base.php';

apiBase('POST', true);

// Bloque de validaciones

$inputs = json_decode(file_get_contents('php://input'), true);

$juego = trim($inputs['juego'] ?? '');
$puntuacion = $inputs['puntuacion'] ?? '';

$errores = [];

if (empty($juego)) {
    $errores[] = 'El juego es obligatorio';
}

if (!is_numeric($puntuacion) || $puntuacion < 0) {
    $errores[] = 'La puntuación debe ser un número positivo';
}

if (!empty($errores)) {
    http_response_code(400);
    echo json_encode(['errores' => $errores]);
    exit;
}

try {
    $conexion = obtenerConexionBD();

    $consulta = $conexion->prepare("INSERT INTO puntuaciones (usuario_id, juego, puntuacion) VALUES (?, ?, ?)");
    $consulta->bindParam(1, $_SESSION['usuario_id']);
    $consulta->bindParam(2, $juego);
    $consulta->bindParam(3, $puntuacion);
    $consulta->execute();

    echo json_encode(['mensaje' => 'Puntuación guardada correctamente']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar la puntuación: ' . $e->getMessage()]);
}
?>