<?php
// Endpoint de renderizado de submissions en iframe.
// Inyecta el JavaScript en una etiqueta <script> en la página HTML en lugar de crear un archivo físico.

$submission_id = $_GET['id'] ?? '';
$token = $_GET['token'] ?? '';
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if (empty($submission_id)) {
    http_response_code(400);
    echo 'ID de envío requerido';
    exit;
}

if (empty($token) && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    $token = $matches[1];
}

if (empty($token)) {
    http_response_code(401);
    echo 'No autorizado';
    exit;
}

require_once __DIR__ . '/../config.php';

session_id($token);
session_start();

if (!isset($_SESSION['usuario_id'])) {
    session_destroy();
    http_response_code(401);
    echo 'No autorizado';
    exit;
}

$conexion = obtenerConexionBD();
$stmt = $conexion->prepare('SELECT rol FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !in_array($user['rol'], ['admin', 'moderator'])) {
    http_response_code(403);
    echo 'No tienes permisos';
    exit;
}

// decodificar meta.json
$submit_dir = __DIR__ . '/../../codeSubmits';
$submission_dir = $submit_dir . '/' . basename($submission_id);
$html_file = $submission_dir . '/index.html';
$js_file = $submission_dir . '/game.js';

if (!file_exists($html_file)) {
    http_response_code(404);
    echo 'Archivo no encontrado';
    exit;
}

$html_content = file_get_contents($html_file);
$js_content = file_exists($js_file) ? file_get_contents($js_file) : '';

$js_content = preg_replace('#<script.*?>#is', '', $js_content);
$js_content = preg_replace('#</script>#is', '', $js_content);

function ensureValidHtml($html)
{
    if (stripos($html, '<html') === false) {
        $html = "<html><body>" . $html;
    }

    if (stripos($html, '</body>') === false) {
        $html .= "</body>";
    }

    if (stripos($html, '</html>') === false) {
        $html .= "</html>";
    }

    return $html;
}

$html_content = ensureValidHtml($html_content);

$nonce = bin2hex(random_bytes(16));

// bloquear flechas de movimiento y scroll
$preventScrollScript = "<script nonce=\"$nonce\">\n";
$preventScrollScript .= "document.addEventListener('keydown', function(e) {\n";
$preventScrollScript .= "    if(['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', ' ', 'PageUp', 'PageDown', 'Home', 'End'].includes(e.key)) {\n";
$preventScrollScript .= "        e.preventDefault();\n";
$preventScrollScript .= "    }\n";
$preventScrollScript .= "}, false);\n";
$preventScrollScript .= "</script>\n";

if (stripos($html_content, '<body') !== false) {
    $html_content = preg_replace('#(<body[^>]*>)#i', '$1' . $preventScrollScript, $html_content, 1);
} else {
    $html_content = $preventScrollScript . $html_content;
}

$hasGameJsReference = preg_match('#<script[^>]*src=["\']game\.js["\']#i', $html_content);

if (!empty(trim($js_content))) {
    $scriptTag = "<script nonce=\"$nonce\">\n" . trim($js_content) . "\n</script>";

    if ($hasGameJsReference) {
        // reemplazar referencia a game.js
        $html_content = preg_replace(
            '#<script[^>]*src=["\']game\.js["\'][^>]*></script>#i',
            $scriptTag,
            $html_content
        );
    } else {
        // si tiene etiqueta body, inyectar antes de </body>, si no, al final del documento
        if (stripos($html_content, '</body>') !== false) {
            $html_content = str_ireplace('</body>', $scriptTag . '</body>', $html_content);
        } else {
            $html_content .= $scriptTag;
        }
    }
} else {
    // si no hay JS content pero hay referencia a game.js, quitar la referencia
    if ($hasGameJsReference) {
        $html_content = preg_replace(
            '#<script[^>]*src=["\']game\.js["\'][^>]*></script>#i',
            '',
            $html_content
        );
    }
}

// Content Security Policy para permitir scripts inyectados con el nonce generado.
// El sandbox del iframe se maneja en el atributo HTML del iframe en Vue, no aquí.
$csp = "default-src 'self'; script-src 'self' 'nonce-$nonce'";
$csp .= "; style-src 'self' 'unsafe-inline'; img-src 'self' data:;";

header('Content-Type: text/html; charset=UTF-8');
header("Content-Security-Policy: $csp");

$isFullDocument = preg_match('/<\s*(?:!doctype|html)\b/i', $html_content);

if (!$isFullDocument) {
    $output = "<!DOCTYPE html>\n<html>\n<head>\n    <meta charset='UTF-8'>\n    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n    <title>Vista previa - " . htmlspecialchars($submission_id) . "</title>\n</head>\n<body>\n    $html_content\n</body>\n</html>";
} else {
    $output = $html_content;
}

echo $output;
