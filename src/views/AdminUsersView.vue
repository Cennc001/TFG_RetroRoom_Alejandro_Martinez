<template>
  <div class="container-xl mx-auto py-4">
    <h2>
      <i class="bi bi-people-fill me-2"></i>Gestión de Usuarios
    </h2>
    <p class="text-muted my-4">
      Edita roles de usuarios y elimínalos de la base de datos
    </p>

    <!-- Filtros y búsqueda -->
    <div class="bg-secondary bg-opacity-10 p-4 rounded border mb-4">
      <div class="row g-3">
        <div class="col-md-6">
          <input
            v-model="filters.search"
            type="text"
            class="form-control"
            placeholder="Buscar por nombre de usuario, email o nombre..."
          />
        </div>
        <div class="col-md-3">
          <select
            v-model="filters.role"
            class="form-select"
            @change="applyFilters"
          >
            <option value="">Todos los roles</option>
            <option value="user">Usuario</option>
            <option value="moderator">Moderador</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
        <div class="col-md-3">
          <button @click="loadUsers" class="btn btn-primary w-100">
            <i class="bi bi-arrow-clockwise me-2"></i>Actualizar
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
    </div>

    <!-- Sin usuarios -->
    <div v-else-if="filteredUsers.length === 0" class="alert alert-info">
      No hay usuarios que mostrar.
    </div>

    <!-- Tabla de usuarios -->
    <div v-else class="table-responsive">
      <table class="table table-hover border">
        <thead class="table-header">
          <tr>
            <th style="width: 30%">Usuario</th>
            <th style="width: 30%">Email</th>
            <th style="width: 15%">Rol</th>
            <th style="width: 15%">Creado</th>
            <th style="width: 10%">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in filteredUsers" :key="user.id">
            <td>
              <div class="d-flex align-items-center">
                <img
                  v-if="user.avatar_url"
                  :src="user.avatar_url"
                  :alt="user.username"
                  class="rounded-circle me-2"
                  style="width: 32px; height: 32px; object-fit: cover"
                />
                <div v-else class="rounded-circle bg-secondary me-2" style="width: 32px; height: 32px"></div>
                <div>
                  <div class="fw-bold">
                    {{ user.username }}
                    <span v-if="user.username === currentUsername" class="badge bg-info ms-2">Tú</span>
                  </div>
                  <small class="text-muted">{{ user.display_name }}</small>
                </div>
              </div>
            </td>
            <td>
              <small>{{ user.email }}</small>
            </td>
            <td>
              <select
                :value="user.rol"
                @change="changeRole(user.id, $event)"
                :disabled="user.username === currentUsername || updatingUserId === user.id"
                class="form-select form-select-sm"
                :class="getRoleClass(user.rol)"
              >
                <option value="user">Usuario</option>
                <option value="moderator">Moderador</option>
                <option value="admin">Administrador</option>
              </select>
            </td>
            <td>
              <small class="text-muted">
                {{ formatDate(user.created_at) }}
              </small>
              <br>
              <small class="text-muted">
                <i class="bi bi-clock-history"></i> {{ getTimeAgo(user.last_seen) }}
              </small>
            </td>
            <td>
              <button
                v-if="user.username !== currentUsername"
                @click="deleteUser(user.id, user.username)"
                :disabled="deletingUserId === user.id"
                class="btn btn-sm btn-danger"
                title="Eliminar usuario"
              >
                <span v-if="deletingUserId === user.id" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-trash me-1"></i>
              </button>
              <span v-else class="text-muted small">-</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div
      v-if="showDeleteConfirm"
      class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
      style="background: rgba(0, 0, 0, 0.7); z-index: 1000"
      @click.self="showDeleteConfirm = false"
    >
      <div
        class="bg-body rounded-3 shadow-lg p-5"
        style="max-width: 400px"
      >
        <h5 class="mb-3">
          <i class="bi bi-exclamation-triangle text-danger me-2"></i>Confirmar eliminación
        </h5>
        <p class="mb-4">
          ¿Estás seguro de que quieres eliminar al usuario <strong>{{ deleteUsername }}</strong>?
          <br>
          <small class="text-danger">Esta acción es irreversible.</small>
        </p>
        <div class="d-flex gap-2">
          <button @click="showDeleteConfirm = false" class="btn btn-secondary flex-grow-1">
            Cancelar
          </button>
          <button
            @click="confirmDelete"
            :disabled="deletingUserId !== null"
            class="btn btn-danger flex-grow-1"
          >
            <span v-if="deletingUserId" class="spinner-border spinner-border-sm me-2"></span>
            Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth'
import { showSuccess, showError, showInfo } from '@/utils/alerts'

export default {
  name: 'AdminUsersView',
  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },
  data() {
    return {
      users: [],
      filteredUsers: [],
      loading: false,
      filters: {
        search: '',
        role: '',
      },
      updatingUserId: null,
      deletingUserId: null,
      showDeleteConfirm: false,
      deleteUserId: null,
      deleteUsername: null,
    }
  },
  computed: {
    currentUsername() {
      return this.authStore.user?.username || ''
    },
  },
  methods: {
    async loadUsers() {
      this.loading = true
      try {
        const response = await fetch(
          this.authStore.apiUrl('get_admin_users.php'),
          {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
              Authorization: `Bearer ${this.authStore.token}`,
            },
          }
        )

        const data = await response.json()

        if (response.ok && data.exito) {
          this.users = data.usuarios
          this.applyFilters()
        } else {
          showError('Error', data.error || 'Error desconocido')
        }
      } catch (error) {
        console.error('Error:', error)
        showError('Error', 'Error al conectar con el servidor')
      } finally {
        this.loading = false
      }
    },

    applyFilters() {
      let filtered = this.users

      // Filtro de búsqueda
      if (this.filters.search) {
        const search = this.filters.search.toLowerCase()
        filtered = filtered.filter(
          (user) =>
            user.username.toLowerCase().includes(search) ||
            user.email.toLowerCase().includes(search) ||
            (user.display_name && user.display_name.toLowerCase().includes(search))
        )
      }

      // Filtro de rol
      if (this.filters.role) {
        filtered = filtered.filter((user) => user.rol === this.filters.role)
      }

      this.filteredUsers = filtered
    },

    async changeRole(userId, event) {
      const newRole = event.target.value
      const user = this.users.find((u) => u.id === userId)
      const oldRole = user?.rol

      this.updatingUserId = userId

      try {
        const response = await fetch(
          this.authStore.apiUrl('update_user_role.php'),
          {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              Authorization: `Bearer ${this.authStore.token}`,
            },
            body: JSON.stringify({
              usuario_id: userId,
              nuevo_rol: newRole,
            }),
          }
        )

        const data = await response.json()

        if (response.ok && data.exito) {
          showSuccess('Éxito', 'Rol actualizado correctamente', () => this.loadUsers())
        } else {
          showError('Error', data.error || 'Error desconocido')
          // Revertir el cambio en la UI
          if (user) {
            user.rol = oldRole
          }
        }
      } catch (error) {
        console.error('Error:', error)
        showError('Error', 'Error al conectar con el servidor')
        // Revertir el cambio en la UI
        if (user) {
          user.rol = oldRole
        }
      } finally {
        this.updatingUserId = null
      }
    },

    deleteUser(userId, username) {
      this.showDeleteConfirm = true
      this.deleteUserId = userId
      this.deleteUsername = username
    },

    async confirmDelete() {
      if (!this.deleteUserId) return

      this.deletingUserId = this.deleteUserId

      try {
        const response = await fetch(
          this.authStore.apiUrl('delete_user.php'),
          {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              Authorization: `Bearer ${this.authStore.token}`,
            },
            body: JSON.stringify({
              usuario_id: this.deleteUserId,
            }),
          }
        )

        const data = await response.json()

        if (response.ok && data.exito) {
          showSuccess('Éxito', 'Usuario eliminado correctamente', () => this.loadUsers())
        } else {
          showError('Error', data.error || 'Error desconocido')
        }
      } catch (error) {
        console.error('Error:', error)
        showError('Error', 'Error al conectar con el servidor')
      } finally {
        this.showDeleteConfirm = false
        this.deleteUserId = null
        this.deleteUsername = null
        this.deletingUserId = null
      }
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      })
    },

    getTimeAgo(dateString) {
      const date = new Date(dateString)
      const now = new Date()
      const seconds = Math.floor((now - date) / 1000)

      if (seconds < 60) {
        return 'Hace unos segundos'
      }
      const minutes = Math.floor(seconds / 60)
      if (minutes < 60) {
        return `Hace ${minutes}m`
      }
      const hours = Math.floor(minutes / 60)
      if (hours < 24) {
        return `Hace ${hours}h`
      }
      const days = Math.floor(hours / 24)
      if (days < 7) {
        return `Hace ${days}d`
      }
      return `Hace ${Math.floor(days / 7)}w`
    },

    getRoleClass(role) {
      switch (role) {
        case 'admin':
          return 'border-danger'
        case 'moderator':
          return 'border-warning'
        default:
          return 'border-secondary'
      }
    },
  },

  mounted() {
    // Verificar permisos de admin
    if (this.authStore.user?.rol !== 'admin') {
      showInfo('Acceso denegado', 'No tienes permiso para acceder a esta sección', () => {
        this.$router.push('/')
      })
      return
    }

    this.loadUsers()
  },
}
</script>

<style scoped>
.table {
  vertical-align: middle;
}

.form-select-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
}

.table-header {
  background-color: #f8f9fa;
  color: #1e293b;
}

body[data-bs-theme="dark"] .table-header {
  background-color: #1a2332;
  color: #e2e8f0;
}

body[data-bs-theme="dark"] .table-header th {
  background-color: #1a2332;
  color: #e2e8f0;
  border-color: #2d3e52 !important;
}

.table-hover tbody tr:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
  border-width: 0.2em;
}
</style>
