<?php
/*
 * Este endpoint permite a los usuarios registrarse en la plataforma.
 * Ejemplo de solicitud:
 * POST /backend/api/register.php
 * Body: { "username": "usuario1", "email": "periquito@delospalotes.com", "password": "123", "confirmPassword": "123" }
 */

require_once '../config.php';
require_once '../api_base.php';

apiBase('POST', false);

// Inputs del POST
$inputs = json_decode(file_get_contents('php://input'), true);

$nombreUsuario = trim($inputs['username'] ?? '');
$email = trim($inputs['email'] ?? '');
$contrasena = $inputs['password'] ?? '';
$confirmarContrasena = $inputs['confirmPassword'] ?? '';
$displayName = trim($inputs['displayName'] ?? $nombreUsuario);
$avatarUrl = trim($inputs['avatarUrl'] ?? '');

// Bloque de validaciones
$errores = [];

if (empty($nombreUsuario)) {
    $errores[] = 'El nombre de usuario es obligatorio';
} elseif (strlen($nombreUsuario) < 3) {
    $errores[] = 'El nombre de usuario debe tener al menos 3 caracteres';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $nombreUsuario)) {
    $errores[] = 'El nombre de usuario solo puede contener letras, números y guiones bajos';
}

//TEMP Validaciones de php

if (empty($email)) {
    $errores[] = 'El email es obligatorio';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El email no es valido';
}

if (empty($contrasena)) {
    $errores[] = 'La contraseña es obligatoria';
} elseif (strlen($contrasena) < 6) {
    $errores[] = 'La contraseña debe tener al menos 6 caracteres';
}

if ($contrasena !== $confirmarContrasena) {
    $errores[] = 'Las contraseñas no coinciden';
}

if (!empty($errores)) {
    http_response_code(400);
    echo json_encode(['errores' => $errores]);
    exit;
}

// BBDD
$conexion = obtenerConexionBD();

// verificar con bbdd
// para seguridad, consultas preparadas
$consulta = $conexion->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
$consulta->bindParam(1, $nombreUsuario);
$consulta->bindParam(2, $email);
$consulta->execute();
$resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);

// si ya existe el usuario o email
if (count($resultado) > 0) {
    $errores[] = 'El nombre de usuario o email ya esta registrado';
    http_response_code(409);
    echo json_encode(['errores' => $errores]);
    exit;
}

// TEMP - Asignacio nde rol de admin para testing
$rol = 'user'; // rol por defecto
if ($nombreUsuario === 'admin') {
    $rol = 'admin';
} elseif ($nombreUsuario === 'mod') {
    $rol = 'moderator';
}

// hasheado en BBDD de la contraseña, para seguridad
$contrasenaHasheada = password_hash($contrasena, PASSWORD_DEFAULT);

// usuario neuvo con contraseña hasheada
$consulta = $conexion->prepare("INSERT INTO usuarios (username, email, password, display_name, avatar_url, rol) VALUES (?, ?, ?, ?, ?, ?)");
$consulta->bindParam(1, $nombreUsuario);
$consulta->bindParam(2, $email);
$consulta->bindParam(3, $contrasenaHasheada);
$consulta->bindParam(4, $displayName);
$consulta->bindParam(5, $avatarUrl);
$consulta->bindParam(6, $rol);

if ($consulta->execute()) {
    echo json_encode(['exito' => true, 'mensaje' => 'Usuario registrado exitosamente']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar el usuario']);
}
?>