<template>
  <div class="container-xl mx-auto py-4">
    <h2><i class="bi bi-journal-text me-2"></i>Mis envíos</h2>
    <p class="text-muted my-3">
      Revisa tus envíos y los metadatos asociados, sin mostrar rutas de
      servidor.
    </p>

    <div class="barra-busqueda p-4 rounded border mb-4">
      <div class="busqueda-grid">
        <div class="busqueda-field">
          <label class="form-label">Buscar envío</label>
          <input
            v-model="filters.search"
            type="text"
            class="form-control"
            placeholder="Buscar por nombre de juego o descripción"
          />
        </div>
        <div class="busqueda-field">
          <label class="form-label">Filtrar estado</label>
          <select
            v-model="filters.status"
            class="form-select"
            @change="loadMySubmissions"
          >
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendientes</option>
            <option value="aprobado">Aprobados</option>
            <option value="rechazado">Rechazados</option>
          </select>
        </div>
        <div class="busqueda-action">
          <button
            @click="loadMySubmissions"
            class="btn btn-primary w-100 busqueda-button"
          >
            <i class="bi bi-arrow-clockwise me-2"></i>Actualizar
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
    </div>

    <div v-else-if="filteredSubmissions.length === 0" class="alert alert-info">
      No hay envíos que mostrar.
    </div>

    <div v-else class="table-responsive">
      <table class="table envio-tabla align-middle rounded overflow-hidden">
        <thead>
          <tr>
            <th>Envío</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Revisión</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="submission in filteredSubmissions"
            :key="submission.submission_id"
            class="envio-row"
          >
            <td>
              <div class="envio-card">
                <div class="envio-title">{{ submission.nombre }}</div>
                <div class="envio-description">
                  {{ submission.descripcion || "Sin descripción" }}
                </div>
                <div class="envio-meta">
                  <span>ID:</span>
                  <strong>{{ submission.submission_id }}</strong>
                </div>
              </div>
            </td>
            <td>
              <div class="envio-date">
                {{ formatDate(submission.creado_at) }}
              </div>
              <div class="envio-meta small text-muted">
                {{ formatDateTime(submission.creado_at) }}
              </div>
            </td>
            <td>
              <div class="pill-estado" :class="statusClass(submission.status)">
                <i
                  :class="statusIcon(submission.status)"
                  aria-hidden="true"
                ></i>
                {{ statusLabel(submission.status) }}
              </div>
              <div
                v-if="submission.status === 'rechazado'"
                class="mt-2 div-reject"
              >
                {{ submission.motivo_rechazo || "Razón no especificada" }}
              </div>
            </td>
            <td>
              <div class="review-envio">
                <div class="fw-semibold">
                  {{ submission.revisado_at ? "Revisado" : "Pendiente" }}
                </div>
                <div class="envio-meta small text-muted">
                  {{
                    submission.revisado_at
                      ? formatDateTime(submission.revisado_at)
                      : "Aún no se ha revisado"
                  }}
                </div>
                <div v-if="submission.revisado_por" class="envio-meta small">
                  Revisado por ID {{ submission.revisado_por }}
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useAuthStore } from "@/stores/auth";
import alerts from "@/utils/alerts.js";

const authStore = useAuthStore();
const submissions = ref([]);
const loading = ref(true);

// filtros globales
const filters = reactive({
  status: "",
  search: "",
});

const loadMySubmissions = async () => {
  loading.value = true;
  try {
    const response = await fetch(
      authStore.apiUrl(`get_submissions.php?mine=1&status=${filters.status}`),
      {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
        },
      },
    );
    const data = await response.json();
    if (data.exito) {
      // cargar envios
      submissions.value = data.datos || [];
    } else {
      alerts.showError(
        "Error al cargar mis envíos",
        data.error || "Intenta de nuevo más tarde",
      );
    }
  } catch (error) {
    alerts.showError(
      "Error al cargar mis envíos",
      "No se pudo conectar con el servidor",
    );
  } finally {
    loading.value = false;
  }
};

// filtrar envios
const filteredSubmissions = computed(() => {
  const searchText = filters.search.toLowerCase().trim();
  if (!searchText) return submissions.value;
  return submissions.value.filter((submission) => {
    return (
      // si hay match
      submission.nombre.toLowerCase().includes(searchText) ||
      (submission.descripcion || "").toLowerCase().includes(searchText) ||
      submission.status.toLowerCase().includes(searchText)
    );
  });
});

// helpers para mostrar estado y fechas
const statusLabel = (status) => {
  return (
    {
      pendiente: "Pendiente",
      aprobado: "Aprobado",
      rechazado: "Rechazado",
    }[status] || "Desconocido"
  );
};

const statusIcon = (status) => {
  return (
    {
      pendiente: "bi bi-clock-fill",
      aprobado: "bi bi-check-circle-fill",
      rechazado: "bi bi-x-circle-fill",
    }[status] || "bi bi-question-circle-fill"
  );
};

const statusClass = (status) => {
  return (
    {
      pendiente: "status-pending",
      aprobado: "status-approved",
      rechazado: "status-rejected",
    }[status] || "status-unknown"
  );
};

// formatear fechas
const formatDate = (value) => {
  if (!value) return "-";
  return new Date(value).toLocaleDateString();
};

const formatDateTime = (value) => {
  if (!value) return "-";
  return new Date(value).toLocaleString();
};

onMounted(() => {
  loadMySubmissions();
});
</script>

<style scoped>
.barra-busqueda {
  background: #ffffff;
  border: 1px solid rgba(15, 23, 42, 0.12);
  box-shadow: 0 10px 35px rgba(15, 23, 42, 0.04);
}

.busqueda-grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: minmax(240px, 1.8fr) minmax(200px, 1.2fr) minmax(
      140px,
      1fr
    );
  align-items: end;
}

.busqueda-field,
.busqueda-action {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.barra-busqueda .form-label {
  margin-bottom: 0;
  color: #334155;
  font-weight: 600;
}

.barra-busqueda .form-control,
.barra-busqueda .form-select {
  min-height: 3.5rem;
  margin: 0;
}

.busqueda-button {
  width: 100%;
  min-height: 3.5rem;
}

body[data-bs-theme="dark"] .barra-busqueda {
  background: #111827;
  border-color: rgba(148, 163, 184, 0.22);
  box-shadow: 0 10px 35px rgba(0, 0, 0, 0.22);
  color: #e2e8f0;
}

body[data-bs-theme="dark"] .barra-busqueda .form-label {
  color: #cbd5e1;
}

body[data-bs-theme="dark"] .barra-busqueda .form-control,
body[data-bs-theme="dark"] .barra-busqueda .form-select {
  background: #1f2937;
  color: #e2e8f0;
  border-color: rgba(148, 163, 184, 0.28);
}

body[data-bs-theme="dark"] .barra-busqueda .form-control::placeholder {
  color: #94a3b8;
}

body[data-bs-theme="dark"] .busqueda-button,
body[data-bs-theme="dark"] .barra-busqueda .btn {
  box-shadow: none;
}

.envio-tabla {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background: #f8f9fe;
}

.envio-tabla thead th {
  background: #eef2ff;
  color: #0f172a;
  border-bottom: 1px solid rgba(15, 23, 42, 0.12);
  padding: 1rem 0.75rem;
}

.envio-tabla tbody tr {
  transition:
    background-color 0.25s ease,
    transform 0.25s ease;
  background: #ffffff;
}

.envio-tabla tbody tr:hover {
  background: #eff6ff;
  transform: translateY(-1px);
}

.envio-tabla td {
  border-top: 1px solid rgba(15, 23, 42, 0.08);
  vertical-align: top;
  padding: 1rem 0.75rem;
  color: #0f172a;
}

.envio-card {
  padding: 1rem;
  border-radius: 1rem;
  background: #f8fafc;
  border: 1px solid rgba(15, 23, 42, 0.08);
  color: #0f172a;
}

.envio-title {
  font-size: 1rem;
  font-weight: 700;
  margin-bottom: 0.35rem;
}

.envio-description {
  color: #475569;
  font-size: 0.92rem;
  margin-bottom: 0.65rem;
}

.envio-meta {
  color: #64748b;
  font-size: 0.83rem;
}

.envio-date {
  font-weight: 600;
  margin-bottom: 0.35rem;
  color: #0f172a;
}

.pill-estado {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.75rem 1.05rem;
  border-radius: 999px;
  font-weight: 700;
  font-size: 0.92rem;
  letter-spacing: 0.01em;
  min-width: 130px;
  justify-content: center;
  border: 1px solid transparent;
  box-shadow: 0 1px 12px rgba(15, 23, 42, 0.08);
}

.pill-estado.status-pending {
  background: #fef3c7;
  color: #92400e;
  border-color: rgba(245, 158, 11, 0.35);
  box-shadow: 0 6px 18px rgba(245, 158, 11, 0.16);
}

.pill-estado.status-approved {
  background: #dcfce7;
  color: #166534;
  border-color: rgba(16, 185, 129, 0.35);
  box-shadow: 0 6px 18px rgba(16, 185, 129, 0.16);
}

.pill-estado.status-rejected {
  background: #fee2e2;
  color: #991b1b;
  border-color: rgba(239, 68, 68, 0.35);
  box-shadow: 0 6px 18px rgba(239, 68, 68, 0.16);
}

body[data-bs-theme="dark"] .envio-tabla {
  background: #111827;
}

body[data-bs-theme="dark"] .envio-tabla thead th {
  background: #1f2937;
  color: #e2e8f0;
  border-bottom-color: rgba(148, 163, 184, 0.22);
}

body[data-bs-theme="dark"] .envio-tabla tbody tr {
  background: #111827;
}

body[data-bs-theme="dark"] .envio-tabla tbody tr:hover {
  background: rgba(147, 197, 253, 0.08);
}

body[data-bs-theme="dark"] .envio-tabla td {
  border-top-color: rgba(148, 163, 184, 0.18);
  color: #e2e8f0;
}

body[data-bs-theme="dark"] .envio-card {
  background: #1f2937;
  border-color: rgba(148, 163, 184, 0.18);
  color: #e2e8f0;
}

body[data-bs-theme="dark"] .envio-date,
body[data-bs-theme="dark"] .envio-card .envio-title {
  color: #e2e8f0;
}

body[data-bs-theme="dark"] .envio-card .envio-description,
body[data-bs-theme="dark"] .envio-card .envio-meta {
  color: #94a3b8;
}

body[data-bs-theme="dark"] .pill-estado {
  box-shadow: 0 1px 12px rgba(0, 0, 0, 0.15);
}

body[data-bs-theme="dark"] .pill-estado.status-pending {
  background-color: rgba(245, 158, 11, 0.22);
  color: #f59e0b;
  border-color: rgba(245, 158, 11, 0.45);
}

body[data-bs-theme="dark"] .pill-estado.status-approved {
  background-color: rgba(16, 185, 129, 0.18);
  color: #4ade80;
  border-color: rgba(16, 185, 129, 0.45);
}

body[data-bs-theme="dark"] .pill-estado.status-rejected {
  background-color: rgba(239, 68, 68, 0.2);
  color: #fecaca;
  border-color: rgba(239, 68, 68, 0.45);
}

.div-reject {
  margin-top: 0.9rem;
  font-size: 0.9rem;
  color: #991b1b;
  background: rgba(254, 226, 226, 0.75);
  border-radius: 0.85rem;
  padding: 0.8rem;
}

body[data-bs-theme="dark"] .div-reject {
  color: #fecaca;
  background: rgba(248, 113, 113, 0.14);
}

.review-envio {
  display: grid;
  gap: 0.4rem;
}

@media (max-width: 768px) {
  .envio-tabla thead {
    display: none;
  }

  .envio-tabla,
  .envio-tabla tbody,
  .envio-tabla tr,
  .envio-tabla td {
    display: block;
    width: 100%;
  }

  .envio-tabla tr {
    margin-bottom: 1rem;
  }

  .envio-tabla td {
    padding: 0.75rem;
  }
}
</style>
