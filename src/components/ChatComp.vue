<template>
  <div class="chat-container d-flex flex-column">
    <div
      class="messages-area flex-grow-1 overflow-auto p-3"
      ref="messagesContainer"
    >
      <div
        v-for="mensaje in mensajes"
        :key="mensaje.id"
        class="message mb-3 d-flex"
      >
        <img
          :src="mensaje.avatar_url || defaultAvatar"
          alt="Avatar"
          class="rounded-circle me-3"
          width="40"
          height="40"
        />
        <div class="message-content">
          <div class="message-header d-flex align-items-center mb-1">
            <strong class="me-2">{{ mensaje.username }}</strong>
            <small class="text-muted">{{
              formatDate(mensaje.enviado_at)
            }}</small>
          </div>
          <div class="message-text">{{ mensaje.contenido }}</div>
        </div>
      </div>
      <div v-if="mensajes.length === 0" class="text-muted text-center">
        No hay mensajes.
      </div>
    </div>
    <div class="input-area p-3 border-top">
      <div class="input-group">
        <input
          v-model="nuevoMensaje"
          @keyup.enter="enviarMensaje"
          type="text"
          class="form-control"
          placeholder="Escribe un mensaje..."
          maxlength="500"
          :disabled="enviando"
        />
        <button
          @click="enviarMensaje"
          class="btn btn-primary w-100"
          :disabled="enviando || !nuevoMensaje.trim()"
        >
          <span
            v-if="enviando"
            class="spinner-border spinner-border-sm me-2"
          ></span>
          Enviar
        </button>
        <small class="text-muted d-block mt-3"
          >{{ nuevoMensaje.length }}/500 caracteres</small
        >
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from "vue";
import { useAuthStore } from "../stores/auth.js";

const authStore = useAuthStore();

const mensajes = ref([]);
const nuevoMensaje = ref("");
let pollingInterval = null;
let ultimoMensajeId = 0;
const enviando = ref(false);
const messagesContainer = ref(null);
const defaultAvatar = "https://cdn-icons-png.flaticon.com/512/149/149071.png";

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString();
};

const obtenerMensajes = () => {
  $.ajax({
    url: authStore.apiUrl(`get_messages.php?ultimo_id=${ultimoMensajeId}`),
    type: "GET",
    headers: {
      Authorization: `Bearer ${authStore.token}`,
    },
    success: function (datos) {
      if (datos.exito) {
        if (datos.mensajes.length > 0) {
          mensajes.value.push(...datos.mensajes);
          ultimoMensajeId = Math.max(...mensajes.value.map((m) => m.id));
          // scroll al recarga
          nextTick(() => {
            if (messagesContainer.value) {
              messagesContainer.value.scrollTop =
                messagesContainer.value.scrollHeight;
            }
          });
        }
      } else {
        // console.error('Error obteniendo mensajes:', datos.error)
      }
    },
    error: function (xhr, status, error) {
      // console.error('Error AJAX:', error)
    },
  });
};

const enviarMensaje = () => {
  if (!nuevoMensaje.value.trim() || enviando.value) return;

  enviando.value = true;

  $.ajax({
    url: authStore.apiUrl("send_message.php"),
    type: "POST",
    contentType: "application/json",
    headers: {
      Authorization: `Bearer ${authStore.token}`,
    },
    data: JSON.stringify({
      contenido: nuevoMensaje.value,
    }),
    success: function (datos) {
      if (datos.exito) {
        nuevoMensaje.value = "";
        obtenerMensajes();
      } else {
        // console.error("Error enviando mensaje:", datos.error);
      }
    },
    error: function (x, s, error) {
      // console.error("Error AJAX:", error);
    },
    complete: function () {
      enviando.value = false;
    },
  });
};

const cargaInicial = () => {
  mensajes.value = [];
  ultimoMensajeId = 0;

  $.ajax({
    url: authStore.apiUrl("get_messages.php"),
    type: "GET",
    headers: {
      Authorization: `Bearer ${authStore.token}`,
    },
    success: function (datos) {
      if (datos.exito) {
        mensajes.value = datos.mensajes;
        if (mensajes.value.length > 0) {
          ultimoMensajeId = Math.max(...mensajes.value.map((m) => m.id));
        }
        nextTick(() => {
          if (messagesContainer.value) {
            messagesContainer.value.scrollTop =
              messagesContainer.value.scrollHeight;
          }
        });
      } else {
        // console.error("Error obteniendo mensajes:", datos.error);
      }
    },
    error: function (xhr, status, error) {
      // console.error("Error AJAX:", error);
    },
  });
  pollingInterval = setInterval(obtenerMensajes, 3000);
};

const pararPoll = () => {
  if (pollingInterval) {
    clearInterval(pollingInterval);
    pollingInterval = null;
  }
};

onMounted(() => {
  cargaInicial();
});

onUnmounted(() => {
  pararPoll();
});
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes fadeOut {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
  }
}

.chat-container {
  width: 100%;
  height: 100vh;
  max-height: 98vh;
  display: flex;
  flex-direction: column;
  gap: 0;
  animation: fadeIn 0.4s ease-in-out forwards;
}

.chat-container.fade-out {
  animation: fadeOut 0.4s ease-in-out forwards;
}

.messages-area {
  border-radius: 8px;
  background: transparent;
  border: none;
  min-height: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.message {
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.04);
  border-radius: 8px;
  transition:
    transform 0.25s ease,
    background-color 0.25s ease;
}

.message:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateX(2px);
}

.message-header strong {
  color: var(--color-heading);
}

.message-text {
  color: var(--color-text);
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
  white-space: pre-wrap;
}

.messages-area .text-muted {
  color: var(--color-muted);
}

.input-area {
  border-top: 1px solid var(--color-border);
  flex-shrink: 0;
}
</style>
