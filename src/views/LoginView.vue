<template>
  <div class="auth-container">
    <div class="card-wrapper">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Iniciar Sesión</h3>
        </div>
        <div class="card-body">
          <form @submit.prevent="iniciarSesion">
            <div class="mb-3">
              <label for="usuario" class="form-label">Usuario o Email</label>
              <input
                type="text"
                class="form-control"
                id="usuario"
                v-model="formulario.usuario"
                required
              />
            </div>
            <div class="mb-3">
              <label for="contrasena" class="form-label">Contraseña</label>
              <input
                type="password"
                class="form-control"
                id="contrasena"
                v-model="formulario.contrasena"
                required
              />
            </div>
            <button
              type="submit"
              class="btn btn-primary w-100"
              :disabled="cargando"
            >
              <span
                v-if="cargando"
                class="spinner-border spinner-border-sm me-2"
                role="status"
              ></span>
              {{ cargando ? "Iniciando sesión..." : "Iniciar Sesión" }}
            </button>
          </form>
          <div class="mt-3">
            <p>
              ¿No tienes cuenta?
              <router-link to="/registro" class="link-primary"
                >Regístrate aquí</router-link
              >
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";

const authStore = useAuthStore();
const router = useRouter();

const formulario = ref({
  usuario: "",
  contrasena: "",
});

const cargando = ref(false);

// POST iniciar sesión
const iniciarSesion = () => {
  cargando.value = true;

  $.ajax({
    url: authStore.apiUrl("login.php"),
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify(formulario.value),
    success: function (respuesta) {
      if (respuesta.exito) {
        // modal de exito
        window.showSuccess(
          "¡Bienvenido!",
          `Sesión iniciada correctamente. ¡Hola ${respuesta.usuario.nombre}!`,
          () => {
            // guardar en el store y localStorage
            authStore.login(respuesta.usuario, respuesta.token);
            router.push("/");
          },
        );
      }
    },
    error: function (xhr) {
      const respuesta = xhr.responseJSON || {};
      if (Array.isArray(respuesta.errores) && respuesta.errores.length) {
        window.showError("Error al iniciar sesión", respuesta.errores);
      } else {
        window.showError(
          "Error al iniciar sesión",
          respuesta.error || "Error desconocido. Verifica tus credenciales.",
        );
      }
    },
    complete: function () {
      cargando.value = false;
    },
  });
};
</script>

<style scoped></style>
