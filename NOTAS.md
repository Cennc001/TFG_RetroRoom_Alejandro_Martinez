# RetroRoom - Guía de Ejecución Local

## Setup - Docker

El proyecto esta preparado para ser iniciado por docker, usando un .yml automático

Solo hay que abrir la carpeta root del proyecto y en una terminal, usar "docker compose up"

La credencial de usuario admin por defecto es "admin" / "admin123"

## Setup - Local

En Php config, configurar session.strict_mode para evitar session fixation, al no permitir al usuario
enviar al servidor una id de sesión no creada por el servidor.

```
session.use_strict_mode = 1
```

Modificar la config en archivos Php de [api_base.php, check_session.php, logout.php]

```
Access-Control-Allow-Origin: *
    a
Access-Control-Allow-Origin: https://frontEnd.com
```

```sh
npm install
```

## Construcción para Producción

```bash
npm run build
```

Los archivos se generaran en la carpeta `dist/`.

## Estructura General

```
root/
├── index.html
├── codeSubmits/
│   ├── submit_1778183144_69fcebe8bf971/
│   │   ├── game.js
│   │   ├── index.html
│   │   └── meta.json
│   └── submit_1778183508_69fced54d5ab8/
│       ├── game.js
│       ├── index.html
│       └── meta.json

```

## Archivos de config y deployment

```
├── docker/
│   └── php/
│       └── php.ini
├── backend/
│   └── database/
│       └── init.sql
├── Dockerfile.backend
├── Dockerfile.frontend
├── docker-compose.yml
```

## Estructura de Backend

```
├── backend/
│   ├── api_base.php
│   ├── config.php
│   ├── test_api.php
│   └── api/
│       ├── api.php
│       ├── check_session.php
│       ├── get_messages.php
│       ├── get_online_users.php
│       ├── get_profile.php
│       ├── get_ranking.php
│       ├── get_submission_preview.php
│       ├── get_submissions.php
│       ├── get_users.php
│       ├── login.php
│       ├── logout.php
│       ├── register.php
│       ├── render_submission.php
│       ├── review_submission.php
│       ├── save_score.php
│       ├── send_message.php
│       ├── submit_code.php
│       └── update_profile.php
```

## Estructura de Componentes

```
└── src/
    ├── App.vue
    ├── main.js
    ├── assets/
    │   ├── base.css
    │   └── main.css
    ├── components/
    │   ├── BarraLateral.vue
    │   ├── ChatComp.vue
    │   ├── Navbar.vue
    │   ├── Ranking.vue
    │   ├── SnakeGame.vue
    │   ├── StickHero.vue
    │   ├── TetrisGame.vue
    │   └── icons/
    ├── router/
    │   └── index.js
    ├── stores/
    │   ├── auth.js
    │   ├── chat.js
    │   └── theme.js
    └── views/
        ├── AboutView.vue
        ├── AdminSubmissionsView.vue
        ├── GameView.vue
        ├── HomeView.vue
        ├── LoginView.vue
        ├── ProfileView.vue
        ├── RegisterView.vue
        └── SubmitCodeView.vue
```

## Bibliografia

```
// Referencias para creacion de API de registro
- https://www.geeksforgeeks.org/php/creating-a-registration-and-login-system-with-php-and-mysql/

// Sistema de polling (votaciones) como base para polling de mensajes
- github.com/piesocket/realtime-polling-system-php
- https://codeshack.io/poll-voting-system-php-mysql/

// Primera idea de foto de perfil - subir a sql como blob, pero descartada por url (innecesario)
- https://github.com/RizwanHawwari/change-profile-picture-php/blob/main/profile.sql

// Juegos - Tutorial base de canvas y game / animation loop
- https://www.slingacademy.com/article/build-simple-games-using-the-canvas-api-in-javascript/
- https://www.w3schools.com/graphics/game_obstacles.asp

// Snake game 2013 (logica de cabeza)
- https://stackoverflow.com/questions/19614329/snake-game-in-javascript

//// Juegos externos al proyecto (relleno, no creados por el autor)
// stick hero
- https://www.freecodecamp.org/news/javascript-game-tutorial-stick-hero-with-html-canvas/

// Convencion de codigos de respuesta HTTP
- https://developer.mozilla.org/es/docs/Web/HTTP/Reference/Status

// Bearer token - API php authentication
- https://blog.postman.com/what-is-a-bearer-token/

//// CSP
// Que es
- https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Content-Security-Policy/sandbox

// Iframe :sandbox
- https://www.w3schools.com/tags/att_iframe_sandbox.asp

// Foros
- https://stackoverflow.com/questions/79261079/why-iframe-enforce-the-parent-pages-content-security-policy-instead-of-its-own
```

## Herramientas usadas

```
// Diagramas de uml (documentación)
- https://www.eraser.io/diagramgpt


```

## Ejemplo de código válido de juego

```
<!-- HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Atrapa el Cuadrado</title>

  <style>
    body {
      margin: 0;
      background: #0f172a;
      color: white;
      font-family: Arial, sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    h1 {
      margin-bottom: 10px;
    }

    #game {
      width: 600px;
      height: 400px;
      background: #1e293b;
      border: 3px solid #3b82f6;
      position: relative;
      overflow: hidden;
      border-radius: 10px;
    }

    #player {
      width: 40px;
      height: 40px;
      background: #22c55e;
      position: absolute;
      left: 280px;
      top: 180px;
      border-radius: 6px;
    }

    #target {
      width: 30px;
      height: 30px;
      background: #ef4444;
      position: absolute;
      border-radius: 6px;
    }

    #score {
      margin-top: 15px;
      font-size: 20px;
    }

    #instructions {
      margin-top: 10px;
      color: #94a3b8;
    }
  </style>
</head>
<body>

  <h1>Atrapa el Cuadrado</h1>

  <div id="game">
    <div id="player"></div>
    <div id="target"></div>
  </div>

  <div id="score">Puntos: 0</div>

  <div id="instructions">
    Usa las flechas del teclado para moverte
  </div>

  <script src="game.js"></script>
</body>
</html>
```

```
<!-- JS -->
const game = document.getElementById('game')
const player = document.getElementById('player')
const target = document.getElementById('target')
const scoreText = document.getElementById('score')

const gameWidth = 600
const gameHeight = 400

let playerX = 280
let playerY = 180

let targetX = 100
let targetY = 100

let score = 0
const speed = 10

function movePlayer() {
  player.style.left = playerX + 'px'
  player.style.top = playerY + 'px'
}

function moveTarget() {
  target.style.left = targetX + 'px'
  target.style.top = targetY + 'px'
}

function randomizeTarget() {
  targetX = Math.random() * (gameWidth - 30)
  targetY = Math.random() * (gameHeight - 30)

  moveTarget()
}

function checkCollision() {
  const playerRect = player.getBoundingClientRect()
  const targetRect = target.getBoundingClientRect()

  if (
    playerRect.left < targetRect.right &&
    playerRect.right > targetRect.left &&
    playerRect.top < targetRect.bottom &&
    playerRect.bottom > targetRect.top
  ) {
    score++
    scoreText.textContent = 'Puntos: ' + score
    randomizeTarget()
  }
}

document.addEventListener('keydown', (e) => {
  switch (e.key) {
    case 'ArrowUp':
      playerY -= speed
      break

    case 'ArrowDown':
      playerY += speed
      break

    case 'ArrowLeft':
      playerX -= speed
      break

    case 'ArrowRight':
      playerX += speed
      break
  }

  // límites
  playerX = Math.max(0, Math.min(gameWidth - 40, playerX))
  playerY = Math.max(0, Math.min(gameHeight - 40, playerY))

  movePlayer()
  checkCollision()
})

movePlayer()
moveTarget()
```
