<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container-fluid">
      <router-link to="/" class="navbar-brand fw-bold">
        <img class="img-fluid img-navbar" src="/src/assets/logo.png" alt="Logo" />RetroRoom
      </router-link>

      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Navbar Links -->
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav ms-auto gap-2">
          <!-- NAVEGACION DROPDOWN -->
          <li class="nav-item dropdown">
            <button
              class="btn btn-outline-light nav-link dropdown-toggle"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="bi bi-compass me-1"></i>Navegación
            </button>
            <ul class="dropdown-menu dropdown-menu-dark">
              <li>
                <router-link to="/" class="dropdown-item">
                  <i class="bi bi-house-door me-2"></i>Inicio
                </router-link>
              </li>
              <li>
                <router-link to="/juego" class="dropdown-item">
                  <i class="bi bi-joystick me-2"></i>Juegos
                </router-link>
              </li>
              <li>
                <router-link to="/enviar-codigo" class="dropdown-item">
                  <i class="bi bi-cloud-upload me-2"></i>Enviar Código
                </router-link>
              </li>
              <li>
                <router-link to="/mis-envios" class="dropdown-item">
                  <i class="bi bi-journal-text me-2"></i>Mis envíos
                </router-link>
              </li>
            </ul>
          </li>

          <!-- CHAT BUTTON BADGE -->
          <li v-if="authStore.isAuthenticated" class="nav-item">
            <button
              @click="chatStore.toggleChat()"
              class="btn btn-outline-light nav-link toggle-chat-btn"
              :class="{ active: chatStore.showChat }"
            >
              <i class="bi bi-chat-dots me-1"></i>
              <span class="nav-label">{{
                chatStore.showChat ? 'Ocultar' : 'Mostrar'
              }}</span>
              <span v-if="chatStore.totalUsuariosOnline > 0" class="badge bg-success ms-1">
                {{ chatStore.totalUsuariosOnline }}
              </span>
            </button>
          </li>

          <!-- ADMIN DROPDOWN -->
          <li
            v-if="
              authStore.isAuthenticated &&
              (authStore.user?.rol === 'admin' || authStore.user?.rol === 'moderator')
            "
            class="nav-item dropdown"
          >
            <button
              class="btn btn-outline-warning nav-link dropdown-toggle"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="bi bi-shield-check me-1"></i>Admin
            </button>
            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
              <li>
                <router-link to="/admin/submissions" class="dropdown-item">
                  <i class="bi bi-file-earmark-check me-2"></i>Revisión de Código
                </router-link>
              </li>
              <li v-if="authStore.user?.rol === 'admin'">
                <router-link to="/admin/usuarios" class="dropdown-item">
                  <i class="bi bi-people-fill me-2"></i>Gestión de Usuarios
                </router-link>
              </li>
            </ul>
          </li>

          <!-- TOGGLE TEMA -->
          <li class="nav-item">
            <button @click="toggleTheme()" class="btn btn-outline-light nav-link">
              <i class="bi bi-palette me-1"></i>
              <span class="nav-label">Cambiar tema</span>
            </button>
          </li>

          <!-- OPCIONES DROPDOWN -->
          <li v-if="authStore.isAuthenticated" class="nav-item dropdown">
            <button
              class="btn btn-outline-light nav-link dropdown-toggle"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="bi bi-gear"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
              <li>
                <router-link to="/perfil" class="dropdown-item">
                  <i class="bi bi-person-circle me-2"></i>Mi Perfil
                </router-link>
              </li>
              <li>
                <hr class="dropdown-divider" />
              </li>
              <li>
                <button @click="logout" class="dropdown-item">
                  <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                </button>
              </li>
            </ul>
          </li>

          <!-- LOGIN / REGISTRO -->
          <li v-if="!authStore.isAuthenticated" class="nav-item">
            <router-link to="/login" class="nav-link btn btn-outline-light">
              <i class="bi bi-box-arrow-in-right me-1"></i><span class="nav-label">Login</span>
            </router-link>
          </li>
          <li v-if="!authStore.isAuthenticated" class="nav-item">
            <router-link to="/registro" class="nav-link btn btn-outline-light">
              <i class="bi bi-person-plus me-1"></i><span class="nav-label">Registro</span>
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useChatStore } from '@/stores/chat'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const chatStore = useChatStore()
const router = useRouter()

const logout = () => {
  authStore.logout()
  router.push('/login')
}

const toggleTheme = () => {
  const currentTheme = document.documentElement.dataset.bsTheme
  const newTheme = currentTheme === 'light' ? 'dark' : 'light'
  document.documentElement.dataset.bsTheme = newTheme
  document.body.dataset.bsTheme = newTheme
}
</script>

<style scoped>
.navbar {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(30, 41, 59, 0.96));
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.navbar-brand {
  font-size: 1.5rem;
  font-weight: 600;
  color: #fff !important;
}

.nav-link {
  color: rgba(255, 255, 255, 0.8) !important;
  transition:
    color 0.3s ease,
    background-color 0.25s ease;
  border-radius: 4px;
  padding: 0.5rem 0.75rem !important;
}

.nav-link:hover {
  color: #fff !important;
  background-color: rgba(255, 255, 255, 0.1);
}

.nav-link.router-link-exact-active {
  color: #fff !important;
  background-color: rgba(255, 255, 255, 0.2);
}

.dropdown-menu {
  background-color: rgba(15, 23, 42, 0.98);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.dropdown-item {
  color: rgba(255, 255, 255, 0.8);
  transition:
    color 0.3s ease,
    background-color 0.25s ease;
}

.dropdown-item:hover,
.dropdown-item:focus {
  background-color: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.toggle-chat-btn {
  transition: all 0.3s ease;
}

.toggle-chat-btn.active {
  background: rgba(59, 130, 246, 0.3) !important;
  color: #7dd3fc !important;
}

.badge {
  font-size: 0.7rem;
  padding: 0.25rem 0.5rem;
}

.img-navbar {
  height: 35px;
  width: auto;
  margin-right: 8px;
  border-radius: 20px;
}

.nav-label {
  display: inline;
  font-size: inherit;
}

@media (max-width: 768px) {
  .navbar-brand {
    font-size: 1.25rem;
  }

  .nav-link {
    padding: 0.35rem 0.5rem !important;
    font-size: 0.9rem;
  }

  .nav-label {
    font-size: 0.9rem;
  }
}

@media (max-width: 576px) {
  .navbar-brand {
    font-size: 1.1rem;
  }

  .nav-link {
    padding: 0.25rem 0.35rem !important;
    font-size: 0.75rem;
  }

  .nav-label {
    font-size: 0.65rem;
  }
}

  .img-navbar {
    height: 28px;
  }

@media (max-width: 991px) {
  .navbar-nav {
    justify-content: center;
    align-items: center;
  }
}
</style>
