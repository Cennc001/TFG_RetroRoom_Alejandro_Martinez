DROP DATABASE IF EXISTS retroroom;
CREATE DATABASE IF NOT EXISTS retroroom;

USE retroroom;

-- USUARIOS - usuarios registrados
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    display_name VARCHAR(100) DEFAULT NULL,
    avatar_url VARCHAR(255) DEFAULT NULL,
    rol ENUM('user', 'moderator', 'admin') DEFAULT 'user',
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CHAT - mensajes
CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    contenido TEXT NOT NULL,
    enviado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE -- fk - USUARIOS
);

-- CHAT - indices
CREATE INDEX idx_mensajes_usuario ON mensajes(usuario_id, enviado_at);

-- JUEGOS - puntuaciones
CREATE TABLE IF NOT EXISTS puntuaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    juego VARCHAR(50) NOT NULL,
    puntuacion INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- CODIGO - envios de codigo
CREATE TABLE IF NOT EXISTS code_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    submission_id VARCHAR(100) UNIQUE,
    archivo_html VARCHAR(255),
    archivo_js VARCHAR(255),
    status ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    motivo_rechazo TEXT,
    creado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    revisado_por INT,
    revisado_at TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (revisado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_usuario (usuario_id)
);

-- Datos iniciales de ejemplo
INSERT INTO usuarios (username, email, password, display_name, avatar_url, rol, created_at)
VALUES (
    'admin',
    'admin@example.com',
    '$2y$12$3GluHLDlo1pVzHvlObVPp.AZjjOXVN9I7lvWlgblq255BIKkKGdcO',
    'Administrador',
    '',
    'admin',
    NOW()
);

INSERT INTO code_submissions (usuario_id, nombre, descripcion, submission_id, archivo_html, archivo_js, creado_at)
VALUES (
    1,
    'Ejemplo de juego válido',
    'Ejemplo de código HTML y JS válido para inicializar el proyecto.',
    'submit_example_game_valid_20260522',
    'codeSubmits/submit_example_game_valid_20260522/index.html',
    'codeSubmits/submit_example_game_valid_20260522/game.js',
    NOW()
);

INSERT INTO code_submissions (usuario_id, nombre, descripcion, submission_id, archivo_html, archivo_js, creado_at)
VALUES (
    1,
    'Ejemplo de juego válido 2',
    'Ejemplo de código HTML y JS válido para inicializar el proyecto. - Copia',
    'submit_example_game_valid_20260522',
    'codeSubmits/submit_example_game_valid_20260522/index.html',
    'codeSubmits/submit_example_game_valid_20260522/game.js',
    NOW()
);


