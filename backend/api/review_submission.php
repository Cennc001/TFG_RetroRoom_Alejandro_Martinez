<?php
header('Content-Type: application/json');

require_once '../config.php';
require_once '../api_base.php';

apiBase('POST', true);

// Obtener usuario_id de la sesión restaurada
$usuario_id = $_SESSION['usuario_id'];

// Obtener conexión a BD
$conexion = obtenerConexionBD();

// Verificar que sea moderador/admin
$stmt = $conexion->prepare('SELECT rol FROM usuarios WHERE id = ?');
$stmt->bindParam(1, $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['rol'] !== 'admin' && $user['rol'] !== 'moderator') {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para revisar envíos']);
    exit;
}

// Obtener datos del POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['submission_id']) || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$submission_id = $data['submission_id'];
$action = $data['action']; // 'approve' o 'reject'
$reason = $data['reason'] ?? '';

try {
    if ($action === 'approve') {
        $stmt = $conexion->prepare('
            UPDATE code_submissions 
            SET status = ?, revisado_por = ?, revisado_at = NOW()
            WHERE submission_id = ?
        ');

        $status = 'aprobado';
        $stmt->bindParam(1, $status, PDO::PARAM_STR);
        $stmt->bindParam(2, $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $submission_id, PDO::PARAM_STR);
        $stmt->execute();

        echo json_encode([
            'exito' => true,
            'mensaje' => 'Envío aprobado'
        ]);

    } else if ($action === 'reject') {
        $stmt = $conexion->prepare('
            UPDATE code_submissions 
            SET status = ?, motivo_rechazo = ?, revisado_por = ?, revisado_at = NOW()
            WHERE submission_id = ?
        ');

        $status = 'rechazado';
        $stmt->bindParam(1, $status, PDO::PARAM_STR);
        $stmt->bindParam(2, $reason, PDO::PARAM_STR);
        $stmt->bindParam(3, $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(4, $submission_id, PDO::PARAM_STR);
        $stmt->execute();

        echo json_encode([
            'exito' => true,
            'mensaje' => 'Envío rechazado'
        ]);

    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>