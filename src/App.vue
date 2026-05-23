<template>
  <div class="app-container d-flex flex-column h-100" :data-bs-theme="theme">
    <!-- NavBar -->
    <NavBar />

    <div class="content-wrapper d-flex flex-grow-1 overflow-hidden">
      <!-- Chat Panel (33%) -->
      <Transition name="page-card" mode="in-out">
        <div
          v-if="authStore.isAuthenticated && chatStore.showChat"
          class="chat-panel d-flex flex-column flex-shrink-0 border-end"
        >
          <!-- Chat body -->
          <ChatComponent class="flex-grow-1" />
        </div>
      </Transition>

      <!-- Main -->
      <main class="main-content flex-grow-1 d-flex flex-column overflow-hidden">
        <div class="main-content-inner flex-grow-1 overflow-auto px-4 py-1">
          <router-view v-slot="{ Component }">
            <Transition name="page-card" mode="out-in">
              <component :is="Component" :key="$route.path" />
            </Transition>
          </router-view>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import NavBar from "./components/Navbar.vue";
import ChatComponent from "./components/ChatComp.vue";
import { useAuthStore } from "@/stores/auth";
import { useChatStore } from "@/stores/chat";
import { onMounted, onUnmounted, ref } from "vue";

const authStore = useAuthStore();
const chatStore = useChatStore();

const theme = ref(document.documentElement.dataset.bsTheme); // light o dark

let pollingInterval = null;
let usuariosInterval = null;

const cargarUsuariosOnline = () => {
  $.ajax({
    url: authStore.apiUrl("get_online_users.php"),
    type: "GET",
    success: function (datos) {
      if (datos.exito) {
        chatStore.actualizarUsuariosOnline(datos.usuarios);
      }
    },
    error: function (error) {
      // console.error('Error al cargar usuarios online:', error)
    },
  });
};

onMounted(() => {
  document.documentElement.dataset.bsTheme = theme.value;
  document.body.dataset.bsTheme = theme.value;
  authStore.initializeAuth();
  cargarUsuariosOnline();

  // Recargar usuarios online cada 30 segundos
  usuariosInterval = setInterval(cargarUsuariosOnline, 30000);
});

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval);
  if (usuariosInterval) clearInterval(usuariosInterval);
});
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.app-container {
  min-height: 100vh;
  background-color: var(--color-background);
  color: var(--color-text);
}

.content-wrapper {
  flex: 1;
  overflow: hidden;
  gap: 0;
}

.chat-panel {
  width: 33.333%;
  min-width: 320px;
  background: var(--color-background-soft);
  border-color: rgba(148, 163, 184, 0.12);
  overflow: hidden;
}

.chat-header {
  border-color: rgba(148, 163, 184, 0.12);
  background: linear-gradient(
    180deg,
    rgba(59, 130, 246, 0.08),
    transparent 70%
  );
}

.online-counter {
  margin-top: 0.5rem;
  padding-top: 0.5rem;
  border-top: 1px solid rgba(148, 163, 184, 0.12);
}

.main-content {
  width: 66.667%;
  background-color: var(--color-background-soft);
  border-radius: 24px;
  box-shadow: 0 28px 80px rgba(15, 23, 42, 0.12);
  margin: 1rem;
}

.main-content-inner {
  background-color: var(--color-background-soft);
  color: var(--color-text);
  border-radius: 24px;
}

@media (max-width: 1200px) {
  .chat-panel {
    width: 35%;
    min-width: 300px;
  }

  .main-content {
    width: 65%;
  }
}

@media (max-width: 768px) {
  .chat-panel {
    position: absolute;
    left: 0;
    top: 60px;
    width: 100%;
    height: calc(100% - 60px);
    z-index: 1000;
    border-right: none;
  }

  .main-content {
    width: 100%;
    margin: 0;
    border-radius: 0;
    box-shadow: none;
  }

  .content-wrapper {
    flex-direction: column;
  }
}

/* Chat fade transition */
.chat-fade-enter-active,
.chat-fade-leave-active {
  transition: opacity 0.4s ease-in-out;
}

.chat-fade-enter-from {
  opacity: 0;
}

.chat-fade-leave-to {
  opacity: 0;
}
</style>
