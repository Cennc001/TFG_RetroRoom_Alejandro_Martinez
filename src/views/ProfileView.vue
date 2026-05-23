<template>
  <main class="container mt-4">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card mb-4">
          <div class="card-header">
            <h2>Mi Perfil</h2>
          </div>
          <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-4">
              <img
                :src="perfil.avatar_url || defaultAvatar"
                alt="Avatar"
                class="rounded-circle"
                width="96"
                height="96"
              />
              <div>
                <h4 class="ps-1">{{ perfil.nombre || perfil.username }}</h4>
                <p class="mb-1 ps-3"><strong>Usuario:</strong> {{ perfil.username }}</p>
                <p class="mb-1"><strong>Email:</strong> {{ perfil.email }}</p>
                <!-- <p class="mb-2"><strong>Rol:</strong> {{ perfil.rol }}</p> -->
                <div class="mb-0">
                  <span v-if="perfil.rol === 'admin'" class="badge bg-danger">Admin</span>
                  <span v-else-if="perfil.rol === 'moderator'" class="badge bg-warning text-dark"
                    >Moderador</span
                  >
                  <span v-else class="badge bg-secondary ms-3">Usuario</span>
                </div>
              </div>
            </div>

            <div class="row mb-4">
              <div class="col-md-4">
                <div class="card stat-card p-3">
                  <h5>Total puntos</h5>
                  <p class="display-6 mb-0">{{ perfil.estadisticas.total_puntos }}</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card stat-card p-3">
                  <h5>Partidas</h5>
                  <p class="display-6 mb-0">{{ perfil.estadisticas.partidas }}</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card stat-card p-3">
                  <h5>Mejor puntuación</h5>
                  <p class="display-6 mb-0">{{ perfil.estadisticas.mejor_puntuacion }}</p>
                </div>
              </div>
            </div>

            <form>
              <div class="mb-3">
                <label for="displayName" class="form-label">Nombre</label>
                <input
                  id="displayName"
                  class="form-control"
                  v-model="formulario.displayName"
                  required
                />
              </div>
              <div class="mb-3">
                <label for="avatarUrl" class="form-label">Avatar URL</label>
                <input
                  id="avatarUrl"
                  class="form-control"
                  v-model="formulario.avatarUrl"
                  placeholder="https://..."
                />
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" class="form-control" v-model="formulario.email" />
              </div>
              <div class="mb-3">
                <label for="currentPassword" class="form-label">Contraseña actual</label>
                <input
                  id="currentPassword"
                  type="password"
                  class="form-control"
                  v-model="formulario.currentPassword"
                />
              </div>
              <div class="mb-3">
                <label for="newPassword" class="form-label">Nueva contraseña</label>
                <input
                  id="newPassword"
                  type="password"
                  class="form-control"
                  v-model="formulario.newPassword"
                />
              </div>
              <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirmar nueva contraseña</label>
                <input
                  id="confirmPassword"
                  type="password"
                  class="form-control"
                  v-model="formulario.confirmPassword"
                />
              </div>

              <button @click="actualizarPerfil" class="btn btn-primary" :disabled="cargando">
                <span
                  v-if="cargando"
                  class="spinner-border spinner-border-sm me-2"
                  role="status"
                ></span>
                Guardar cambios
              </button>
            </form>

            <div v-if="mensaje" class="mt-3 alert" :class="tipoMensaje">{{ mensaje }}</div>
            <ul v-if="errores.length" class="mt-3 alert alert-danger">
              <li v-for="error in errores" :key="error">{{ error }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

// estado del perfil y formulario de edicin
const perfil = reactive({
  id: null,
  username: '',
  nombre: '',
  email: '',
  avatar_url: '',
  rol: null,
  estadisticas: {
    partidas: 0,
    total_puntos: 0,
    mejor_puntuacion: 0,
  },
})

const formulario = reactive({
  displayName: '',
  avatarUrl: '',
  email: '',
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

const loading = ref(false)
const mensaje = ref('')
const tipoMensaje = ref('')
const errores = ref([])
const cargando = ref(false)
const defaultAvatar = 'https://cdn-icons-png.flaticon.com/512/149/149071.png'

// iniciar el perfil con los datos obtenidos del backend
const inicializarPerfil = (datos) => {
  perfil.id = datos.id
  perfil.username = datos.username
  perfil.nombre = datos.nombre
  perfil.email = datos.email
  perfil.avatar_url = datos.avatar_url || ''
  perfil.rol = datos.rol || 'user'
  perfil.estadisticas = datos.estadisticas || perfil.estadisticas

  formulario.displayName = datos.nombre || datos.username
  formulario.avatarUrl = datos.avatar_url || ''
  formulario.email = datos.email || ''
}

// GET obtener datos del perfil del usuario autenticado
const fetchPerfil = () => {
  if (!authStore.token) {
    router.push('/login')
    return
  }

  $.ajax({
    url: authStore.apiUrl('get_profile.php'),
    type: 'GET',
    headers: {
      Authorization: `Bearer ${authStore.token}`,
    },
    success(response) {
      if (response.exito) {
        inicializarPerfil(response.perfil)
      } else {
        mensaje.value = response.error || 'No se pudo obtener el perfil'
        tipoMensaje.value = 'alert-danger'
      }
    },
    error(xhr) {
      const res = xhr.responseJSON
      mensaje.value = res?.error || 'Error al cargar el perfil'
      tipoMensaje.value = 'alert-danger'
    },
  })
}

// POST actualizar perfil del usuario autenticado
const actualizarPerfil = () => {
  mensaje.value = ''
  tipoMensaje.value = ''
  errores.value = []
  cargando.value = true

  $.ajax({
    url: authStore.apiUrl('update_profile.php'),
    type: 'POST',
    contentType: 'application/json',
    headers: {
      Authorization: `Bearer ${authStore.token}`,
    },
    // enviar los campos editables del perfil como JSON en el cuerpo de la peticinon
    data: JSON.stringify({
      displayName: formulario.displayName,
      avatarUrl: formulario.avatarUrl,
      email: formulario.email,
      currentPassword: formulario.currentPassword,
      newPassword: formulario.newPassword,
      confirmPassword: formulario.confirmPassword,
    }),
    success(response) {
      if (response.exito) {
        // actualizar el perfil con los nuevos datos y modal success
        mensaje.value = response.mensaje
        tipoMensaje.value = 'alert-success'

        perfil.nombre = response.perfil.nombre
        perfil.email = response.perfil.email
        perfil.avatar_url = response.perfil.avatar_url || ''

        authStore.user.nombre = response.perfil.nombre
        authStore.user.email = response.perfil.email
        authStore.user.avatar_url = response.perfil.avatar_url || ''
        localStorage.setItem('user', JSON.stringify(authStore.user))

        formulario.currentPassword = ''
        formulario.newPassword = ''
        formulario.confirmPassword = ''
      }
    },
    error(xhr) {
      const res = xhr.responseJSON
      if (res?.errores) {
        errores.value = res.errores
      } else {
        mensaje.value = res?.error || 'Error al guardar el perfil'
        tipoMensaje.value = 'alert-danger'
      }
    },
    complete() {
      cargando.value = false
    },
  })
}

onMounted(() => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }

  fetchPerfil()
})
</script>

<style scoped>
.container {
  color: var(--color-text);
}

.stat-card {
  background-color: rgba(59, 130, 246, 0.05);
  border: 1px solid var(--color-border);
  border-radius: 0.75rem;
  color: var(--color-text);
}

.stat-card h5 {
  color: var(--color-heading);
  font-weight: 600;
  font-size: 0.875rem;
  text-transform: uppercase;
  margin-bottom: 0.5rem;
}

.stat-card .display-6 {
  color: #3b82f6;
  font-weight: 700;
}

.d-flex.gap-3 {
  color: var(--color-text);
}
</style>
