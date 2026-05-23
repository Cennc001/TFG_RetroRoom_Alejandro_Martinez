<?php
/*
 * Este endpoint permite a los usuarios iniciar sesión en la plataforma.
 * Ejemplo de solicitud:
 *  POST /backend/api/login.php
 *  Body: { "usuario": "periquito", "contrasena": "123" }
 */

require_once '../config.php';
require_once '../api_base.php';

apiBase('POST', false);

// VALIDACIONES

$inputs = json_decode(file_get_contents('php://input'), true);

$usuario = trim($inputs['usuario'] ?? ''); // username o email
$contrasena = $inputs['contrasena'] ?? '';

$errores = [];

if (empty($usuario)) {
    $errores[] = 'El usuario o email es obligatorio';
}

if (empty($contrasena)) {
    $errores[] = 'La contraseña es obligatoria';
}

if (!empty($errores)) {
    http_response_code(400);
    echo json_encode(['errores' => $errores]);
    exit;
}
$conexion = obtenerConexionBD();

// buscar por nombre o email
$consulta = $conexion->prepare("SELECT id, username, email, password, display_name, avatar_url, rol FROM usuarios WHERE username = ? OR email = ?");
$consulta->bindParam(1, $usuario);
$consulta->bindParam(2, $usuario);
$consulta->execute();
$resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);

if (count($resultado) === 0) {
    $errores[] = 'Usuario o contraseña incorrectos';
    http_response_code(401);
    echo json_encode(['errores' => $errores]);
    exit;
}

$usuarioBD = $resultado[0];

// contraseña
if (!password_verify($contrasena, $usuarioBD['password'])) {
    $errores[] = 'Usuario o contraseña incorrectos';
    http_response_code(401);
    echo json_encode(['errores' => $errores]);
    exit;
}

// SESION
session_start();

// regenerar la id de sesion para evitar session fixation
session_regenerate_id(true);

$_SESSION['usuario_id'] = $usuarioBD['id'];
$_SESSION['usuario'] = $usuarioBD['username'];
$_SESSION['email'] = $usuarioBD['email'];
$_SESSION['display_name'] = $usuarioBD['display_name'] ?? $usuarioBD['username'];
$_SESSION['avatar_url'] = $usuarioBD['avatar_url'] ?? '';
$_SESSION['rol'] = $usuarioBD['rol'] ?? 'user';

// respuesta 200 - ok
echo json_encode([
    'exito' => true,
    'mensaje' => 'Inicio de sesión exitoso',
    'usuario' => [
        'id' => $usuarioBD['id'],
        'usuario' => $usuarioBD['username'],
        'nombre' => $usuarioBD['display_name'] ?? $usuarioBD['username'],
        'email' => $usuarioBD['email'],
        'avatar_url' => $usuarioBD['avatar_url'] ?? '',
        'rol' => $usuarioBD['rol'] ?? 'user'
    ],
    'token' => session_id() // devolver el session_id como token
]);
?>