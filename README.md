# 
Trabajo de final de grado del curso 2024 - 2026 del IES Virgen del Carmen - Curso D.A.W


# TFG_RetroRoom_Alejandro_Martinez

## Guía de Ejecución - Docker

El proyecto esta preparado para ser iniciado por docker, usando un .yml automático

Solo hay que abrir la carpeta root del proyecto y en una terminal, usar "docker compose up"

La credencial de usuario admin por defecto es "admin" / "admin123"

## Ejecución alternativa - Local

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
