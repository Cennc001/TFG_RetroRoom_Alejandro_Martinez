<?php
/*
 * Este endpoint permite actualizar el perfil del usuario autenticado.
 * Requiere token de autenticación en el encabezado Authorization (Bearer token).
 * POST /backend/api/update_profile.php
 * Headers: Authorization: Bearer {token}
 * Body: { 
 *  "displayName": "Nuevo Nombre",
 *  "avatarUrl": "https://example.com/avatar.jpg",
 *  "email": "",
 *  "currentPassword": "contraseña_actual",
 *  "newPassword": "nueva_contraseña",
 *  "confirmPassword": "confirmar_contraseña"
 * }
 */

require_once '../config.php';
require_once '../api_base.php';

apiBase('POST', true);

$entrada = json_decode(file_get_contents('php://input'), true);
$displayName = trim($entrada['displayName'] ?? '');
$avatarUrl = trim($entrada['avatarUrl'] ?? '');
$email = trim($entrada['email'] ?? ($_SESSION['email'] ?? ''));
$currentPassword = $entrada['currentPassword'] ?? '';
$newPassword = $entrada['newPassword'] ?? '';
$confirmPassword = $entrada['confirmPassword'] ?? '';

$errores = [];

// validaciond de nombre y avatar
if (empty($displayName)) {
    $errores[] = 'El nombre es obligatorio';
} elseif (strlen($displayName) < 3) {
    $errores[] = 'El nombre debe tener al menos 3 caracteres';
}

// if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
//     $errores[] = 'El email no es valido';
// } // TEMP

// validacion de contraseña nueva
if (!empty($newPassword)) {
    if (empty($currentPassword)) {
        $errores[] = 'La contraseña actual es obligatoria para cambiar la contraseña';
    }
    if ($newPassword !== $confirmPassword) {
        $errores[] = 'Las nuevas contraseñas no coinciden';
    }
    if (strlen($newPassword) < 6) {
        $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres';
    }
}

if (!empty($errores)) {
    http_response_code(400);
    echo json_encode(['errores' => $errores]);
    exit;
}

try {
    $conexion = obtenerConexionBD();

    // cambiar el email, verificar que no este en uso por otro usuario
    if (!empty($email)) {
        $consultaEmail = $conexion->prepare("SELECT id FROM usuarios WHERE email = ? AND id <> ?");
        $consultaEmail->bindParam(1, $email);
        $consultaEmail->bindParam(2, $_SESSION['usuario_id'], PDO::PARAM_INT);
        $consultaEmail->execute();
        if ($consultaEmail->fetch()) {
            http_response_code(409);
            echo json_encode(['errores' => ['El email ya esta en uso']]);
            exit;
        }
    }

    // cambiar la contraseña, verificar la contraseña actual
    if (!empty($newPassword)) {
        $consultaPassword = $conexion->prepare("SELECT password FROM usuarios WHERE id = ?");
        $consultaPassword->bindParam(1, $_SESSION['usuario_id'], PDO::PARAM_INT);
        $consultaPassword->execute();
        $usuarioBD = $consultaPassword->fetch(PDO::FETCH_ASSOC);

        if (!$usuarioBD || !password_verify($currentPassword, $usuarioBD['password'])) {
            http_response_code(401);
            echo json_encode(['errores' => ['La contraseña actual no es correcta']]);
            exit;
        }
    }
    // aqui uso [] para generar los valores del update, o bien existentes o bien, si los hay 
    //  los nuevos. Para luego usarlas en un ptsmt
    $campos = [];
    $valores = [];

    $campos[] = 'display_name = ?';
    $valores[] = $displayName;

    $campos[] = 'avatar_url = ?';
    $valores[] = $avatarUrl;

    if (!empty($email)) {
        $campos[] = 'email = ?';
        $valores[] = $email;
    }

    // contraseña: hashearla y agregar al update
    if (!empty($newPassword)) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $campos[] = 'password = ?';
        $valores[] = $passwordHash;
    }

    $valores[] = $_SESSION['usuario_id'];
    $consulta = $conexion->prepare("UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?");
    $consulta->execute($valores);

    $_SESSION['display_name'] = $displayName;
    $_SESSION['avatar_url'] = $avatarUrl;
    if (!empty($email)) {
        $_SESSION['email'] = $email;
    }

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Perfil actualizado correctamente',
        'perfil' => [
            'id' => $_SESSION['usuario_id'],
            'username' => $_SESSION['usuario'],
            'nombre' => $displayName,
            'email' => $email,
            'avatar_url' => $avatarUrl
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar el perfil: ' . $e->getMessage()]);
}
?>