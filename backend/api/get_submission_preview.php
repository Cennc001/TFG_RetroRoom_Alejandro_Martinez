<?php
header('Content-Type: application/json');

require_once '../config.php';
require_once '../api_base.php';

apiBase('GET', true);

// Decodificar HTML entities
function decodeHtmlEnt($text)
{
    // varias veces, por si falla alguna
    $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    while ($decoded !== $text) {
        $text = $decoded;
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    return $decoded;
}
$usuario_id = $_SESSION['usuario_id'];

$conexion = obtenerConexionBD();

// rol de moderador/admin
$stmt = $conexion->prepare('SELECT rol FROM usuarios WHERE id = ?');
$stmt->bindParam(1, $usuario_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['rol'] !== 'admin' && $user['rol'] !== 'moderator') {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para ver vistas previas']);
    exit;
}

// submission_id
$submission_id = $_GET['submission_id'] ?? '';

if (empty($submission_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de envío requerido']);
    exit;
}

try {
    // verificar que el submission existe
    $stmt = $conexion->prepare('SELECT * FROM code_submissions WHERE submission_id = ?');
    $stmt->bindParam(1, $submission_id, PDO::PARAM_STR);
    $stmt->execute();
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$submission) {
        http_response_code(404);
        echo json_encode(['error' => 'Envío no encontrado']);
        exit;
    }

    // Rutas
    $submit_dir = __DIR__ . '/../../codeSubmits';
    $submission_dir = $submit_dir . '/' . $submission_id;

    if (!is_dir($submission_dir)) {
        http_response_code(404);
        echo json_encode(['error' => 'Archivos del envío no encontrados']);
        exit;
    }

    // archivos
    $html_file = $submission_dir . '/index.html';
    $js_file = $submission_dir . '/game.js';
    $meta_file = $submission_dir . '/meta.json';

    $html_content = '';
    $js_content = '';
    $meta_content = [];

    if (file_exists($html_file)) {
        $html_content = file_get_contents($html_file);
        $html_content = decodeHtmlEnt($html_content);
    }

    if (file_exists($js_file)) {
        $js_content = file_get_contents($js_file);
        $js_content = decodeHtmlEnt($js_content);
    }

    if (file_exists($meta_file)) {
        $meta_content = json_decode(file_get_contents($meta_file), true);
    }

    // frmatear JS usando npx prettier
    $formatted_js = $js_content;
    if (!empty($js_content)) {
        $temp_file = sys_get_temp_dir() . '/temp_js_' . uniqid() . '.js';
        file_put_contents($temp_file, $js_content);

        $output = [];
        $return_var = 0;
        exec("npx prettier --write \"$temp_file\" 2>&1", $output, $return_var);

        if ($return_var === 0) {
            $formatted_js = file_get_contents($temp_file);
        } else {
            $formatted_js = preg_replace('/;/', ";\n", $js_content);
            $formatted_js = preg_replace('/{/', "{\n    ", $formatted_js);
            $formatted_js = preg_replace('/}/', "\n}", $formatted_js);
            $formatted_js = preg_replace('/\n\s*\n/', "\n", $formatted_js);
        }

        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
    }

    echo json_encode([
        'exito' => true,
        'submission' => $submission,
        'preview' => [
            'html' => $html_content,
            'javascript' => $formatted_js,
            'meta' => $meta_content
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>