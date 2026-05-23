<template>
  <div class="auth-container">
    <div class="card-wrapper">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Registro de Usuario</h3>
        </div>
        <div class="card-body">
          <form @submit.prevent="registrarUsuario">
            <div class="form-group">
              <label for="username" class="form-label"
                >Nombre de Usuario <span class="text-danger">*</span></label
              >
              <input
                type="text"
                class="form-control"
                id="username"
                v-model="formulario.username"
                required
              />
            </div>
            <div class="form-group">
              <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control"
                id="email"
                v-model="formulario.email"
                required
              />
            </div>
            <div class="form-group">
              <label for="displayName" class="form-label">Nombre</label>
              <input
                type="text"
                class="form-control"
                id="displayName"
                v-model="formulario.displayName"
                placeholder="Nombre visible (opcional)"
              />
            </div>
            <div class="form-group">
              <label for="avatarUrl" class="form-label">Avatar URL</label>
              <input
                type="text"
                class="form-control"
                id="avatarUrl"
                v-model="formulario.avatarUrl"
                placeholder="https://..."
              />
            </div>
            <div class="form-group">
              <label for="password" class="form-label"
                >Contraseña <span class="text-danger">*</span></label
              >
              <input
                type="password"
                class="form-control"
                id="password"
                v-model="formulario.password"
                required
              />
            </div>
            <div class="form-group">
              <label for="confirmPassword" class="form-label"
                >Confirmar Contraseña <span class="text-danger">*</span></label
              >
              <input
                type="password"
                class="form-control"
                id="confirmPassword"
                v-model="formulario.confirmPassword"
                required
              />
            </div>
            <button type="submit" class="btn btn-primary w-100" :disabled="cargando">
              <span
                v-if="cargando"
                class="spinner-border spinner-border-sm me-2"
                role="status"
              ></span>
              {{ cargando ? 'Registrando...' : 'Registrarse' }}
            </button>
          </form>
          <div class="mt-3">
            <p>
              ¿Ya tienes cuenta?
              <router-link to="/login" class="link-primary">Inicia sesión</router-link>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const formulario = ref({
  username: '',
  email: '',
  displayName: '',
  avatarUrl: '',
  password: '',
  confirmPassword: '',
})

const cargando = ref(false)

// POST registrar nuevo usuario
const registrarUsuario = () => {
  cargando.value = true

  // aunque se comprueba igual en el back, se envia por defecto el nombre visible como el nombre de usuario si no se especifica
  const payload = {
    ...formulario.value,
    displayName: formulario.value.displayName.trim() || formulario.value.username,
  }

  $.ajax({
    url: authStore.apiUrl('register.php'),
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify(payload),
    success: function (respuesta) {
      if (respuesta.exito) {
        // Mostrar modal y redirigir al login
        window.showSuccess(
          '¡Registro exitoso!',
          'Tu cuenta ha sido creada correctamente. Te redirigiremos al login.',
          () => {
            router.push('/login')
          },
        )
      }
    },
    error: function (xhr) {
      const respuesta = xhr.responseJSON || {}
      if (Array.isArray(respuesta.errores) && respuesta.errores.length) {
        window.showError('Error en el registro', respuesta.errores)
      } else {
        window.showError('Error', respuesta.error || 'Error en la comunicación con el servidor')
      }
    },
    complete: function () {
      cargando.value = false
    },
  })
}
</script>

<style scoped></style>
