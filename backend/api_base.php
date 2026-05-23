<?php

/*
 * Esta funcion base de Api se usa para lo siguiente:
 *   · Access-Control y CORS
 *   · Validaciones: token de auth y de métodos http
 */
function apiBase($method = 'GET', $authentication = true)
{

    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: ' . $method . ', OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Preflight CORS
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    require_once __DIR__ . '/config.php';

    // Metodo HTTP
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        http_response_code(405);
        echo json_encode(['error' => 'Metodo no permitido']);
        exit;
    }

    // limitar tamaño del body
    $maxSize = 1024 * 1024 * 10; // 10 MB

    $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;

    if ($contentLength > $maxSize) {
        http_response_code(413);
        echo json_encode([
            'error' => 'El payload es demasiado grande (máx 10MB permitido)'
        ]);
        exit;
    }


    // Validacion
    if ($authentication && !verificarToken()) {
        http_response_code(401);
        echo json_encode(['error' => 'No autenticado']);
        exit;
    }
}
?>