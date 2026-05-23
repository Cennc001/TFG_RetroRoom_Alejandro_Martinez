<?php
header('Content-Type: application/json');

require_once '../config.php';
require_once '../api_base.php';

apiBase('POST', true);

$usuario_id = $_SESSION['usuario_id'];

$conexion = obtenerConexionBD();

// inputs del request
$inputs = json_decode(file_get_contents('php://input'), true);

if (!$inputs) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

// validaciones de datos
$game_name = $inputs['game_name'] ?? '';
$game_description = $inputs['game_description'] ?? '';
$html_code = $inputs['html_code'] ?? '';
$js_code = $inputs['js_code'] ?? '';
$author_name = $inputs['author_name'] ?? '';

if (empty($game_name) || empty($game_description) || empty($html_code) || empty($js_code)) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos requeridos']);
    exit;
}

// validaciones de tamaños de archivos
if (strlen($html_code) < 50 || strlen($js_code) < 50) {
    http_response_code(400);
    echo json_encode(['error' => 'El código es demasiado corto']);
    exit;
}

if (strlen($html_code) > 500000 || strlen($js_code) > 500000) {
    http_response_code(413);
    echo json_encode(['error' => 'Código demasiado grande']);
    exit;
}

// sanitar metadatos de texto
$game_name = htmlspecialchars($game_name, ENT_QUOTES, 'UTF-8');
$game_description = htmlspecialchars($game_description, ENT_QUOTES, 'UTF-8');
$author_name = htmlspecialchars($author_name, ENT_QUOTES, 'UTF-8');

$html_code = str_replace("\r\n", "\n", $html_code);
$js_code = str_replace("\r\n", "\n", $js_code);

// carpeta codeSubmits si no existe
$submit_dir = __DIR__ . '/../../codeSubmits';
if (!is_dir($submit_dir)) {
    mkdir($submit_dir, 0755, true);
}

$submission_id = uniqid('submit_' . time() . '_', false);
$submission_dir = $submit_dir . '/' . $submission_id;

if (!mkdir($submission_dir, 0755, true) && !is_dir($submission_dir)) {
    throw new Exception('No se pudo crear directorio');
}

try {
    // Preparar HTML - Agregar enlace a game.js si no existe referencia a JS externo
    $final_html_code = $html_code;

    // verificar si el HTML tiene referencias a archivos JS externos
    $hasJsReference = preg_match('#<script[^>]*src=["\'].*\.js["\']#i', $final_html_code);

    if (!$hasJsReference) {
        if (stripos($final_html_code, '</body>') !== false) {
            $final_html_code = str_ireplace('</body>', '<script src="game.js"></script>' . "\n</body>", $final_html_code);
        } else {
            $final_html_code .= "\n<script src=\"game.js\"></script>";
        }
    }

    $final_js_code = $js_code;

    // guardar archivos
    $html_file = $submission_dir . '/index.html';
    $js_file = $submission_dir . '/game.js';
    $meta_file = $submission_dir . '/meta.json';

    // escribir archivos en disco
    if (file_put_contents($html_file, $final_html_code) === false) {
        throw new Exception('No se pudo guardar el archivo HTML');
    }

    if (file_put_contents($js_file, $final_js_code) === false) {
        throw new Exception('No se pudo guardar el archivo JavaScript');
    }

    // guardar metadatos
    $metadata = [
        'submission_id' => $submission_id,
        'game_name' => $game_name,
        'game_description' => $game_description,
        'author_name' => $author_name,
        'usuario_id' => $usuario_id,
        'fecha_envio' => date('Y-m-d H:i:s'),
        'status' => 'pendiente',
        'archivo_html' => basename($html_file),
        'archivo_js' => basename($js_file),
        'nota' => 'JavaScript se inyecta inline en render_submission.php'
    ];

    if (file_put_contents($meta_file, json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
        throw new Exception('No se pudo guardar los metadatos');
    }

    // guardar en BD
    try {
        $stmt = $conexion->prepare('INSERT INTO code_submissions (usuario_id, nombre, descripcion, status, archivo_html, archivo_js, submission_id, creado_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');

        $status = 'pendiente';
        $stmt->bindParam(1, $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $game_name, PDO::PARAM_STR);
        $stmt->bindParam(3, $game_description, PDO::PARAM_STR);
        $stmt->bindParam(4, $status, PDO::PARAM_STR);
        $stmt->bindParam(5, $html_file, PDO::PARAM_STR);
        $stmt->bindParam(6, $js_file, PDO::PARAM_STR);
        $stmt->bindParam(7, $submission_id, PDO::PARAM_STR);
        $stmt->execute();
    } catch (Exception $e) {
        error_log('Error al guardar en BD: ' . $e->getMessage());
    }

    http_response_code(200);
    echo json_encode([
        'exito' => true,
        'mensaje' => 'Código enviado exitosamente',
        'submission_id' => $submission_id
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>