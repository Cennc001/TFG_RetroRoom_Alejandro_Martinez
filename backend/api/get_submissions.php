<?php
header('Content-Type: application/json');

require_once '../config.php';
require_once '../api_base.php';

apiBase('GET', true);

$usuario_id = $_SESSION['usuario_id'];
// rol del usuario (admin, moderator, user)
$rol = $_SESSION['rol'] ?? 'user';
// si se pasan ?mine=true, solo se muestran los envíos del usuario (aunque sea admin)
$mine = isset($_GET['mine']) && filter_var($_GET['mine'], FILTER_VALIDATE_BOOLEAN);

$conexion = obtenerConexionBD();

// estado de filtro
$status = $_GET['status'] ?? '';

try {
    // si el usuario es admin o moderator, puede ver todos
    $isAdmin = in_array($rol, ['admin', 'moderator']);
    $useOwnOnly = !$isAdmin || $mine;

    if ($useOwnOnly) {
        $query = '
            SELECT cs.*, u.username as usuario_nombre, u.display_name 
            FROM code_submissions cs
            JOIN usuarios u ON cs.usuario_id = u.id
            WHERE cs.usuario_id = ?';

        if ($status) {
            $query .= ' AND cs.status = ?';
        }

        $query .= ' ORDER BY cs.creado_at DESC';
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(1, $usuario_id, PDO::PARAM_INT);

        if ($status) {
            $stmt->bindParam(2, $status, PDO::PARAM_STR);
        }

        // Si el usuario no es admin ni moderator, solo ve sus envíos
    } else {
        if ($status) {
            $stmt = $conexion->prepare('
                SELECT cs.*, u.username as usuario_nombre, u.display_name 
                FROM code_submissions cs
                JOIN usuarios u ON cs.usuario_id = u.id
                WHERE cs.status = ?
                ORDER BY cs.creado_at DESC
            ');
            $stmt->bindParam(1, $status, PDO::PARAM_STR);
        } else {
            $stmt = $conexion->prepare('
                SELECT cs.*, u.username as usuario_nombre, u.display_name 
                FROM code_submissions cs
                JOIN usuarios u ON cs.usuario_id = u.id
                ORDER BY cs.creado_at DESC
            ');
        }
    }

    $stmt->execute();
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'exito' => true,
        'datos' => $datos
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>