# Documentación del backend PHP de RetroRoom

## 1. Visión general

Este backend está construido en PHP puro, sin frameworks, y utiliza MySQL como base de datos. Proporciona una API RESTful para manejar autenticación, chat, juegos, perfiles de usuario y revisión de código. La arquitectura se basa en sesiones PHP para autenticación y PDO para interacciones con la base de datos.

### Componentes principales

- `backend/config.php`: configuración de base de datos y funciones de sesión.
- `backend/api_base.php`: base común para todos los endpoints (CORS, validación de método HTTP, autenticación).
- `backend/api/`: carpeta con todos los endpoints específicos (login.php, register.php, etc.).
- `backend/database/init.sql`: esquema de la base de datos.
- `backend/test_api.php`: archivo de prueba para endpoints.

## 2. Estructura de la base de datos

### Tablas principales

#### `usuarios`

- Almacena información de usuarios registrados.
- Campos: `id`, `username`, `email`, `password` (hasheada), `display_name`, `avatar_url`, `rol`, `last_seen`, `created_at`.
- Roles: `user`, `moderator`, `admin`.

#### `mensajes`

- Mensajes del chat.
- Campos: `id`, `canal_id`, `usuario_id`, `contenido`, `enviado_at`.
- Índices: `idx_mensajes_canal` para búsquedas eficientes.

#### `puntuaciones`

- Puntuaciones de juegos.
- Campos: `id`, `usuario_id`, `juego`, `puntuacion`, `fecha`.

#### `code_submissions`

- Envíos de código para revisión.
- Campos: `id`, `usuario_id`, `nombre`, `descripcion`, `submission_id`, `archivo_html`, `archivo_js`, `status`, `motivo_rechazo`, `creado_at`, `revisado_por`, `revisado_at`.

### Inicialización

El archivo `init.sql` crea todas las tablas con claves foráneas y índices. Inserta automáticamente el canal general.

## 3. Flujo de arranque y estructura del backend

### `config.php`

Este archivo es el corazón de la configuración:

- Define constantes de conexión a BD: `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`.
- `obtenerConexionBD()`: crea y devuelve una instancia PDO con manejo de excepciones.
- `verificarToken()`: función crítica que:
  - Lee el header `Authorization: Bearer {token}`.
  - Usa `session_id($token)` para restaurar la sesión PHP.
  - Verifica que `$_SESSION['usuario_id']` exista.
  - Actualiza `last_seen` del usuario en la BD.
  - Retorna `true` si la sesión es válida.

### `api_base.php`

Función `apiBase($method, $authentication)` que:

- Establece headers CORS: `Access-Control-Allow-Origin: *`, métodos permitidos, headers.
- Maneja preflight OPTIONS.
- Valida método HTTP.
- Si `$authentication` es true, llama `verificarToken()`.
- Retorna errores 405 o 401 si falla.

Todos los endpoints incluyen `require_once '../api_base.php'; apiBase('GET/POST', true/false);`.

## 4. Sistema de autenticación y sesiones

### Cómo funciona la autenticación

1. **Login**: `login.php` recibe usuario/contraseña, verifica con `password_verify()`, inicia sesión con `session_start()`, regenera ID de sesión, guarda datos en `$_SESSION`, devuelve `token: session_id()`.

2. **Verificación**: Cada endpoint protegido llama `verificarToken()`, que restaura la sesión desde el token.

3. **Logout**: `logout.php` destruye la sesión.

4. **Registro**: `register.php` valida datos, hashea contraseña con `password_hash()`, inserta usuario, lo agrega automáticamente al canal general.

### Sesiones PHP

- Las sesiones se almacenan en el servidor (no cookies del lado cliente).
- El token es el `session_id()`, enviado en header `Authorization: Bearer {token}`.
- `session_regenerate_id(true)` en login previene session fixation.
- `session_destroy()` en logout.

## 5. Endpoints principales

### Autenticación

#### `register.php` (POST, sin auth)

- Valida username, email, password.
- Verifica unicidad de username/email.
- Hashea password con `PASSWORD_DEFAULT`.
- Inserta usuario y lo agrega al canal general.
- Retorna JSON con éxito/error.

#### `login.php` (POST, sin auth)

- Valida credenciales.
- Verifica password con `password_verify()`.
- Inicia sesión, regenera ID, guarda en `$_SESSION`.
- Retorna usuario y token.

#### `check_session.php` (GET, con auth)

- Verifica si la sesión actual es válida.
- Retorna datos del usuario.

### Chat

#### `get_messages.php` (GET, con auth)

- Obtiene mensajes de un canal.
- Soporta paginación con `ultimo_id`.
- Verifica membresía del canal.

#### `send_message.php` (POST, con auth)

- Inserta mensaje en BD.
- Valida contenido.

#### `get_online_users.php` (GET, sin auth)

- Lista usuarios con `last_seen` reciente.

### Perfil y usuario

#### `get_profile.php` (GET, con auth)

- Obtiene datos del usuario actual.
- Incluye estadísticas de juegos (partidas, puntos, mejor puntuación).

#### `update_profile.php` (POST, con auth)

- Actualiza display_name, avatar_url, email, password.
- Valida contraseña actual para cambios de password.
- Hashea nueva password.

#### `get_users.php` (GET, con auth)

- Lista todos los usuarios (para admin).

### Juegos

#### `save_score.php` (POST, con auth)

- Inserta puntuación en `puntuaciones`.
- No valida si es legítima (por simplicidad).

#### `get_ranking.php` (GET, con auth)

- Obtiene top puntuaciones por juego.

### Envíos de código

#### `submit_code.php` (POST, con auth)

- Valida HTML/JS.
- Crea directorio único en `codeSubmits/`.
- Guarda `index.html`, `game.js`, `meta.json`.
- Inserta en `code_submissions`.

#### `get_submissions.php` (GET, con auth)

- Lista envíos con filtros (status, búsqueda).

#### `get_submission_preview.php` (GET, con auth)

- Lee archivos del envío.
- Formatea JS con prettier si disponible.
- Retorna HTML, JS y meta.

#### `review_submission.php` (POST, con auth)

- Actualiza status a aprobado/rechazado.
- Guarda motivo de rechazo.

#### `render_submission.php` (GET, con auth)

- Lee HTML/JS del envío.
- Inyecta JS inline con nonce CSP.
- Retorna página HTML completa para iframe.

## 6. Seguridad y validaciones

### Validaciones comunes

- **SQL Injection**: Todos los queries usan prepared statements con `PDO::prepare()` y `bindParam()`.
- **XSS**: Datos de entrada se sanitizan con `htmlspecialchars()` o `html_entity_decode()`.
- **Passwords**: Hasheadas con `password_hash()`, verificadas con `password_verify()`.
- **Sesiones**: Regeneración de ID en login, destrucción en logout.
- **CORS**: Headers apropiados en `api_base.php`.
- **Tamaño de archivos**: Límites en `submit_code.php` (500KB max).
- **Permisos**: Verificación de rol en endpoints administrativos.

### Content Security Policy (CSP)

En `render_submission.php`:

- `script-src 'self' 'nonce-{nonce}'` permite solo scripts con nonce generado.
- Evita ejecución de scripts externos o inline sin nonce.

### Sandbox en iframes

Los iframes en Vue tienen `sandbox="allow-scripts"` para aislar el contenido del envío.

## 7. Manejo de errores y respuestas

### Formato de respuestas

- **Éxito**: `{"exito": true, "mensaje": "...", "datos": {...}}`
- **Error**: `{"error": "..."}` o `{"errores": ["...", "..."]}`
- **Códigos HTTP**: 200 OK, 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found, 405 Method Not Allowed, 500 Internal Server Error.

### Manejo de excepciones

- `try/catch` en operaciones de BD.
- `PDOException` capturadas y retornadas como errores 500.
- Logs de errores con `error_log()`.

## 8. Tipos de respuestas de la API

### Formatos de respuesta estándar

#### Respuestas exitosas

Todas las respuestas exitosas incluyen `"exito": true` y un mensaje descriptivo:

```json
{
  "exito": true,
  "mensaje": "Operación completada correctamente",
  "datos_adicionales": {...}
}
```

#### Respuestas de error

- **Error simple**: `{"error": "Mensaje descriptivo del error"}`
- **Errores múltiples**: `{"errores": ["Error 1", "Error 2", "Error 3"]}`

### Códigos HTTP utilizados

- **200 OK**: Operación exitosa
- **400 Bad Request**: Datos inválidos, faltantes o malformados
- **401 Unauthorized**: Token faltante, inválido o sesión expirada
- **403 Forbidden**: Usuario sin permisos para la operación
- **404 Not Found**: Recurso solicitado no existe
- **405 Method Not Allowed**: Método HTTP incorrecto
- **409 Conflict**: Conflicto (ej: username/email ya existe)
- **413 Payload Too Large**: Archivo o datos demasiado grandes
- **500 Internal Server Error**: Error interno del servidor

### Respuestas específicas por tipo de endpoint

#### Autenticación

- **Login exitoso (200)**: `{"exito": true, "mensaje": "Inicio de sesión exitoso", "usuario": {...}, "token": "session_id"}`
- **Registro exitoso (200)**: `{"exito": true, "mensaje": "Usuario registrado exitosamente"}`
- **Credenciales inválidas (401)**: `{"errores": ["Usuario o contraseña incorrectos"]}`
- **Usuario/email ya existe (409)**: `{"errores": ["El nombre de usuario o email ya esta registrado"]}`

#### Chat

- **Lista de mensajes (200)**: `{"exito": true, "mensajes": [{"id": 1, "contenido": "...", "usuario_id": 1, ...}]}`
- **Mensaje enviado (200)**: `{"exito": true, "mensaje": "Mensaje enviado correctamente"}`
- **No miembro del canal (403)**: `{"error": "No eres miembro de este canal"}`
- **Canal no encontrado (404)**: `{"error": "Canal no encontrado"}`

#### Perfiles y usuarios

- **Perfil obtenido (200)**: `{"exito": true, "perfil": {"id": 1, "username": "...", "estadisticas": {...}}}`
- **Perfil actualizado (200)**: `{"exito": true, "mensaje": "Perfil actualizado correctamente", "perfil": {...}}`
- **Contraseña actual incorrecta (401)**: `{"errores": ["La contraseña actual no es correcta"]}`
- **Email ya en uso (409)**: `{"errores": ["El email ya esta en uso"]}`

#### Juegos y puntuaciones

- **Puntuación guardada (200)**: `{"mensaje": "Puntuación guardada correctamente"}`
- **Ranking obtenido (200)**: `{"exito": true, "ranking": [{"usuario": "...", "puntuacion": 1000, ...}]}`
- **Datos inválidos (400)**: `{"errores": ["La puntuación debe ser un número positivo"]}`

#### Envíos de código

- **Código enviado (200)**: `{"exito": true, "mensaje": "Código enviado exitosamente", "submission_id": "..."}`
- **Vista previa (200)**: `{"exito": true, "preview": {"html": "...", "javascript": "...", "meta": {...}}}`
- **Revisión completada (200)**: `{"exito": true, "mensaje": "Envío aprobado/rechazado"}`
- **Archivo no encontrado (404)**: `{"error": "Archivos del envío no encontrados"}`
- **Sin permisos (403)**: `{"error": "No tienes permisos para revisar envíos"}`
- **Código demasiado largo (413)**: `{"error": "Código demasiado grande"}`

#### Errores comunes

- **Token faltante/inválido (401)**: `{"error": "No autorizado"}`
- **Método HTTP incorrecto (405)**: `{"error": "Metodo no permitido"}`
- **Datos requeridos faltantes (400)**: `{"errores": ["El campo X es obligatorio"]}`
- **Error de base de datos (500)**: `{"error": "Error al guardar en BD: mensaje específico"}`

## 9. Interconexión con el frontend

### Comunicación

- Frontend envía JSON en body de POST.
- Headers: `Authorization: Bearer {token}`, `Content-Type: application/json`.
- Backend retorna JSON siempre.

### Ejemplos de flujo

1. **Login**:
   - Vue: `$.ajax({url: 'login.php', data: {usuario, contrasena}})`
   - PHP: valida, inicia sesión, retorna token.
   - Vue: guarda token en store/localStorage.

2. **Enviar mensaje**:
   - Vue: `fetch('send_message.php', {headers: {Authorization: 'Bearer '+token}, body: JSON})`
   - PHP: verifica token, inserta mensaje.
   - Vue: actualiza lista de mensajes.

3. **Renderizar juego**:
   - Vue: `<iframe src="render_submission.php?id=...&token=...">`
   - PHP: lee archivos, inyecta JS inline, retorna HTML con CSP.
