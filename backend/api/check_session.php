<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

/**
 * Este endpoint permite verificar si el usuario tiene una sesión activa.
 * Ejemplo de solicitud:
 * GET /backend/api/check_session.php
 * Headers: Authorization: Bearer {token}
 */

require_once '../config.php';

$autenticado = false;
$usuario = null;

if (verificarToken()) {
    // Si el token del cliente es valido, asume que la sesion es valida
    $autenticado = true;
    $usuario = [
        'id' => $_SESSION['usuario_id'],
        'usuario' => $_SESSION['usuario'],
        'nombre' => $_SESSION['display_name'] ?? $_SESSION['usuario'],
        'email' => $_SESSION['email'],
        'avatar_url' => $_SESSION['avatar_url'] ?? '',
        'rol' => $_SESSION['rol'] ?? 'user'
    ];
} else {
    session_start();
    // Si no hay token valido, comprueba si hay una sesion activa (cookie de sesion)
    if (isset($_SESSION['usuario_id'])) {
        $autenticado = true;
        $usuario = [
            'id' => $_SESSION['usuario_id'],
            'usuario' => $_SESSION['usuario'],
            'nombre' => $_SESSION['display_name'] ?? $_SESSION['usuario'],
            'email' => $_SESSION['email'],
            'avatar_url' => $_SESSION['avatar_url'] ?? '',
            'rol' => $_SESSION['rol'] ?? 'user'
        ];
    }
}

if ($autenticado) {
    echo json_encode([
        'autenticado' => true,
        'usuario' => $usuario
    ]);
} else {
    echo json_encode(['autenticado' => false]);
}
?>