# Documentación del funcionamiento del proyecto RetroRoom

## 1. Visión general

Este proyecto es una aplicación web con frontend en Vue 3 y backend en PHP. Está diseñada como una plataforma de juegos retro con chat, administración de canales, envíos de código de juegos y ranking basado en puntuaciones.

### Componentes principales

- `src/main.js`: punto de entrada de la aplicación Vue.
- `src/App.vue`: contenedor principal de la app, barra de navegación, chat y router view.
- `src/router/index.js`: enrutador de Vue Router con rutas y protección de páginas que requieren autenticación.
- `src/stores/`: almacena datos globales usando Pinia.
- `src/components/`: componentes reutilizables, como `Navbar.vue`, `ChatComp.vue` y juegos.
- `backend/api/`: endpoints PHP REST que atienden las peticiones del frontend.
- `backend/config.php` y `backend/api_base.php`: lógica de conexión a base de datos, CORS y autenticación.
- `src/assets/`: estilos globales.

## 2. Flujo de arranque y estructura del frontend

### `src/main.js`

Este archivo realiza la configuración global de la aplicación:

- importa estilos globales (`main.css`, Bootstrap y SweetAlert2).
- importa `jquery` y lo expone como `window.$`.
- crea la aplicación Vue con `createApp(App)`.
- instala Pinia (`aplicacion.use(createPinia())`) para el estado global.
- instala el router (`aplicacion.use(enrutador)`).
- monta la app en el DOM con `aplicacion.mount('#app')`.
- exporta funciones de alerta de `src/utils/alerts.js` al objeto global `window` para que puedan usarse desde cualquier componente.

### `src/App.vue`

`App.vue` es el layout principal que:

- envuelve todo en un contenedor flex de altura completa.
- renderiza el componente `NavBar` en la parte superior.
- contiene la sección principal donde se muestran las vistas del router.
- muestra un panel de chat si el usuario está autenticado y `chatStore.showChat` es verdadero.
- usa `Transition` para animar los cambios de página.

#### Interacción con stores

`App.vue` importa y consume tres stores:

- `useAuthStore()` para saber si el usuario está autenticado y obtener el token.
- `useThemeStore()` para cambiar entre modo oscuro y claro.
- `useChatStore()` para manejar la visibilidad del chat, el canal actual y los canales disponibles.

#### Ciclo de vida

- `onMounted()` llama a `authStore.initializeAuth()` para reconstruir la sesión desde `localStorage`.
- luego carga los canales y la lista de usuarios online.
- también programa una recarga de usuarios online cada 30 segundos.
- el efecto `watchEffect()` aplica el tema al `document.body` según `themeStore.isDark`.

## 3. Stores: cómo funcionan y para qué sirven

### `src/stores/auth.js`

Este store define el estado de autenticación y ofrece lógica para login, logout e inicialización.

- `state()` contiene:
  - `user`: datos del usuario autenticado.
  - `isAuthenticated`: booleano que indica si el usuario está logueado.
  - `token`: token de sesión usado para autorizar peticiones al backend.
  - `urlServidor`: URL base del backend API.
- `getters`:
  - `apiUrl(endpoint)`: construye la URL completa de un endpoint del backend.
- `actions`:
  - `login(userData, token)`: guarda el usuario y token en el store y en `localStorage`.
  - `logout()`: limpia el estado y elimina datos de `localStorage`.
  - `initializeAuth()`: lee `localStorage` y reinicia el estado si ya existe sesión.

### `src/stores/theme.js`

Maneja solo el estado del tema:

- `isDark`: booleano.
- `toggleTheme()`: invierte el modo claro/oscuro.

### `src/stores/chat.js`

Es un store usando la API composable de Pinia. Mantiene:

- `showChat`: controla si el panel de chat se muestra.
- `canales`: lista de canales disponibles.
- `canalActual`: canal seleccionado.
- `usuariosOnline`: usuarios conectados.
- `cargandoCanales`, `cargandoUsuarios`: indicadores de carga.
- `toggleChat()`, `setShowChat()`, `setCanalActual()`: métodos para actualizar el estado.
- `actualizarCanales()` y `actualizarUsuariosOnline()`.
- `totalUsuariosOnline`: computed que devuelve la cantidad de usuarios en línea.

### Relación entre stores y componentes

- `Navbar.vue` utiliza `authStore`, `themeStore` y `chatStore` para mostrar el menú, el toggle del chat y el toggle del tema.
- `ChatComp.vue` usa `authStore` para construir las llamadas al backend y validar el token.
- Muchas vistas usan `authStore.apiUrl()` para construir URLs y `authStore.token` para autorizar.

## 4. Router y protección de rutas

### `src/router/index.js`

Define rutas como:

- `/`: `HomeView.vue`
- `/acerca`: `AboutView.vue`
- `/registro`: `RegisterView.vue`
- `/juego`: `GameView.vue`
- `/login`: `LoginView.vue`
- `/perfil`: `ProfileView.vue` (requiere auth)
- `/enviar-codigo`: `SubmitCodeView.vue` (requiere auth)
- `/admin/canales`: `AdminChannels.vue` (requiere auth)
- `/admin/submissions`: `AdminSubmissionsView.vue` (requiere auth)

La protección del router se hace con un `beforeEach()`:

- Si `to.meta.requiresAuth` es verdadero y el usuario no está autenticado, la ruta redirige a `/login`.

Esto significa que el frontend bloquea el acceso a ciertas vistas, pero el backend también valida token en cada API.

## 5. Cómo funciona el Chat

### `src/components/ChatComp.vue`

Es un componente de chat en tiempo casi real. Contiene:

- `props`: recibe `canalId` (el ID del canal activo).
- `mensajes`: lista de mensajes mostrados.
- `nuevoMensaje`: texto del mensaje en el input.
- `ultimoMensajeId`: se usa para pedir solo mensajes nuevos.
- `enviando`: indicador de envío.

#### Funciones principales

- `cargaInicial()`: carga los mensajes actuales del canal y arranca polling.
- `obtenerMensajes()`: hace `GET` a `get_messages.php` con query params `canal_id` y `ultimo_id`.
- `enviarMensaje()`: hace `POST` a `send_message.php` con JSON en el body.
- `watch(() => props.canalId, ...)`: recarga el chat cuando cambia de canal.
- `onMounted()` y `onUnmounted()` controlan el ciclo de vida del polling.

### `App.vue` y el panel de chat

`App.vue` carga la lista de canales y cambia el canal actual a través de `chatStore`.

- Si el usuario no está autenticado, el chat no aparece.
- `chatStore.canalActual` controla el canal seleccionado.
- `ChatComp` recibe ese canal y actualiza sus mensajes.

## 6. Vistas de usuario importantes

### `LoginView.vue`

- El usuario escribe credenciales.
- `iniciarSesion()` envía un `POST` a `authStore.apiUrl('login.php')`.
- Si la respuesta es exitosa, llama `authStore.login(respuesta.usuario, respuesta.token)` y redirige a `/`.
- Los errores del backend se muestran con `window.showError(...)`.

### `RegisterView.vue`

- Envía un `POST` a `register.php` con los datos del nuevo usuario.
- Si la respuesta indica éxito, muestra un modal y redirige al login.
- No guarda sesión automáticamente.

### `ProfileView.vue`

- Carga el perfil actual con `get_profile.php` usando el token.
- Permite editar nombre, avatar, email y contraseña.
- Envía los cambios con `update_profile.php`.
- Actualiza `authStore.user` y `localStorage` cuando el backend responde con la nueva información.

### `SubmitCodeView.vue`

- Permite enviar un juego hecho en HTML/JS.
- Valida el formulario en frontend antes de enviar.
- Envía la petición a `submit_code.php` con `fetch` y el token en el header `Authorization`.
- Muestra modales de éxito/error.
- Tiene botones para ver el formato aceptado y descargar una plantilla HTML.

### `AdminChannels.vue`

- Panel administrativo para gestionar canales.
- Consulta `get_channels.php` y `get_users.php`.
- Crea canales con `create_channel.php`.
- Administra miembros con `add_user_to_channel.php`, `remove_user_from_channel.php` y `get_channel_members.php`.
- Usa el token de `authStore` para todas las llamadas.

### `AdminSubmissionsView.vue`

- Carga envíos de código desde `get_submissions.php`.
- Muestra el detalle de un envío y obtiene vista previa con `get_submission_preview.php`.
- Permite aprobar o rechazar envíos mediante `review_submission.php`.
- Genera iframes con `render_submission.php` para previsualizar un envío.
- El `token` también se pasa en la URL del iframe para autorizar la renderización.

## 7. Juegos y guardado de puntuaciones

### `SnakeGame.vue`

- Implementa el juego Snake usando canvas y un game loop con `requestAnimationFrame`.
- Usa un array `snake` para las posiciones de la serpiente.
- Actualiza la posición, detecta colisiones y genera comida.
- Cuando termina el juego, envía la puntuación a `save_score.php`.
- El token se usa para autorizar el guardado de la puntuación.

### Otros juegos

- `TetrisGame.vue` y `StickHero.vue` existen en el proyecto, y también usan APIs con `authStore.apiUrl('save_score.php')`.
- `Ranking.vue` consulta `get_ranking.php?juego=...` para mostrar la tabla de puntuaciones.

## 8. CSS y apariencia

### `src/assets/base.css`

Define variables CSS para colores claros y oscuros con soporte de tema.

- Variables globales como `--color-background`, `--color-text`, `--color-border`, `--color-info`, etc.
- Cambia valores cuando `body[data-bs-theme='dark']`.
- Incluye estilos generales de tipografía, body y clases de juegos.
- El tema oscuro se activa desde `themeStore.isDark` en `App.vue`.

### `src/assets/main.css`

- Importa `base.css`.
- Contiene estilos específicos de layout, cabecera y chat.
- Define estilos de componentes como ventanas modales, botones, tarjetas, formularios y contenedores.
- Aplica `box-sizing`, `background-color` y colores usando variables CSS.

### Estilos scoped en componentes

Muchos componentes (`Navbar.vue`, `LoginView.vue`, `ChatComp.vue`, `SubmitCodeView.vue`, etc.) usan estilos `scoped` para:

- mantener estilos locales.
- mejorar la apariencia del formulario.
- personalizar botones y cards sin afectar al resto.

## 9. Backend PHP: cómo está organizado

### `backend/config.php`

- Define constantes de conexión a la base de datos.
- Proporciona `obtenerConexionBD()` que devuelve un objeto PDO.
- Define `verificarToken()`, que realiza:
  - lectura del header `Authorization`
  - extracción del token `Bearer`
  - usa `session_id($token)` y `session_start()` para reconstruir la sesión.
  - verifica que existe `$_SESSION['usuario_id']`.
  - actualiza `last_seen` del usuario en la base de datos.
- Define `GENERAL_CHANNEL_ID`.

### `backend/api_base.php`

- Establece cabeceras CORS (`Access-Control-Allow-Origin: *`, métodos permitidos y headers).
- Atiende peticiones `OPTIONS` para preflight.
- valida que el método HTTP sea el esperado.
- si la ruta requiere auth, llama `verificarToken()`.
- retorna errores `405` o `401` cuando corresponde.

### `backend/api/login.php`

- Incluye `api_base.php` y `config.php`.
- Valida `POST` sin autenticación.
- Lee JSON desde `php://input`.
- busca el usuario por `username` o `email`.
- verifica la contraseña con `password_verify()`.
- inicia sesión con `session_start()`.
- regenera el id de sesión con `session_regenerate_id(true)`.
- guarda datos del usuario en `$_SESSION`.
- devuelve `token: session_id()`.

### `backend/api/get_channels.php`

- Requiere autenticación.
- Conecta a la base de datos y obtiene la lista de canales junto con el creador.
- Devuelve JSON con `exito` y `canales`.

### Otros endpoints clave

Aunque el proyecto tiene muchos endpoints, la arquitectura general es:

- cada archivo PHP es un endpoint independiente.
- usan `apiBase()` para validar CORS, método y token.
- usan el token de sesión del header para reconstruir la sesión.
- devuelven JSON estructurado con `exito`, `error`, `mensaje` y datos relevantes.

## 10. Interconexión frontend-backend

### Peticiones autenticadas

- El frontend obtiene el token al iniciar sesión.
- El token se guarda en `authStore.token` y `localStorage`.
- Cada petición protegida añade el header `Authorization: Bearer ${authStore.token}`.
- El backend reconstruye la sesión a partir de ese token.

### Sincronización del estado

- `authStore` es la fuente de verdad para usuario, token y estado de autenticación.
- `chatStore` mantiene el canal activo y el estado del chat.
- `themeStore` controla el tema de toda la app, que se refleja en CSS variables.
- `App.vue` alimenta el layout con estos stores.

### Ejemplos de rutas y endpoints

- `LoginView.vue` -> `login.php` -> `authStore.login()`.
- `ProfileView.vue` -> `get_profile.php` y `update_profile.php`.
- `ChatComp.vue` -> `get_messages.php`, `send_message.php`.
- `AdminChannels.vue` -> `get_channels.php`, `create_channel.php`, `get_channel_members.php`, `add_user_to_channel.php`, `remove_user_from_channel.php`.
- `SubmitCodeView.vue` -> `submit_code.php`.
- `SnakeGame.vue` -> `save_score.php`.
- `AdminSubmissionsView.vue` -> `get_submissions.php`, `get_submission_preview.php`, `review_submission.php`, `render_submission.php`.
