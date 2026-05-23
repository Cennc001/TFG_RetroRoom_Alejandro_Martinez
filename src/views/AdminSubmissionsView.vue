<template>
  <div class="container-xl mx-auto py-4">
    <h2>
      <i class="bi bi-file-earmark-check me-2"></i>Panel de Revisión de Código
    </h2>
    <p class="text-muted my-4">
      Revisa y aprueba/rechaza los envíos de código de los usuarios
    </p>

    <div class="bg-secondary bg-opacity-10 p-4 rounded border mb-4">
      <div class="row g-3">
        <div class="col-md-6">
          <input
            v-model="filters.search"
            type="text"
            class="form-control"
            placeholder="Buscar por nombre de juego o usuario..."
          />
        </div>
        <div class="col-md-3">
          <select
            v-model="filters.status"
            class="form-select"
            @change="loadSubmissions"
          >
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendientes</option>
            <option value="aprobado">Aprobados</option>
            <option value="rechazado">Rechazados</option>
          </select>
        </div>
        <div class="col-md-3">
          <button @click="loadSubmissions" class="btn btn-primary w-100">
            <i class="bi bi-arrow-clockwise me-2"></i>Actualizar
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
    </div>

    <div v-else-if="filteredSubmissions.length === 0" class="alert alert-info">
      No hay envíos que mostrar.
    </div>

    <div v-else class="d-grid gap-3">
      <div
        v-for="submission in filteredSubmissions"
        :key="submission.id"
        class="card p-3 border"
        @click="viewSubmission(submission)"
      >
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h5 class="text-primary mb-0">{{ submission.nombre }}</h5>
            <p class="text-muted small mb-0">
              <i class="bi bi-person me-1"></i>{{ submission.usuario_nombre }}
            </p>
          </div>
          <div class="text-end">
            <span :class="getStatusBadge(submission.status)">
              {{ submission.status }}
            </span>
            <small class="text-muted d-block">
              {{ new Date(submission.creado_at).toLocaleDateString() }}
            </small>
          </div>
        </div>

        <p class="text-muted mt-2 mb-3">{{ submission.descripcion }}</p>

        <div class="d-flex gap-2">
          <button
            @click="viewSubmission(submission)"
            class="btn btn-sm btn-primary"
          >
            <i class="bi bi-eye me-1"></i>Ver Detalles
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Detalles -->
    <div
      v-if="showDetail && selectedSubmission"
      class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
      style="background: rgba(0, 0, 0, 0.7); z-index: 1000"
      @click="closeDetail"
    >
      <div
        class="bg-white rounded-3 shadow-lg"
        style="max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto"
        @click.stop
      >
        <div
          class="d-flex justify-content-between align-items-center border-bottom p-4 detail-footer"
        >
          <h5 class="mb-0">{{ selectedSubmission.nombre }}</h5>
          <button @click="closeDetail" class="btn-close"></button>
        </div>

        <div class="p-4 card-body">
          <div>
            <div class="d-flex gap-2 mb-3 align-items-start">
              <strong class="text-nowrap">Usuario:</strong>
              <span>{{ selectedSubmission.usuario_nombre }}</span>
            </div>
            <div class="d-flex gap-2 mb-3 align-items-start">
              <strong class="text-nowrap">Descripción:</strong>
              <span>{{ selectedSubmission.descripcion }}</span>
            </div>
            <div class="d-flex gap-2 mb-3 align-items-start">
              <strong class="text-nowrap">Enviado:</strong>
              <span>{{
                new Date(selectedSubmission.creado_at).toLocaleString()
              }}</span>
            </div>

            <div
              class="bg-secondary bg-opacity-10 p-3 rounded border mt-4 mb-3"
            >
              <div
                class="d-flex justify-content-between align-items-center mb-2"
              >
                <h6 class="mb-0">
                  <i class="bi bi-eye me-2"></i>Vista Previa del Juego
                </h6>
              </div>
              <div class="bg-dark rounded">
                <iframe
                  sandbox="allow-scripts"
                  :src="
                    authStore.apiUrl(
                      `render_submission.php?id=${selectedSubmission.submission_id}&token=${authStore.token}`,
                    )
                  "
                  width="100%"
                  height="600"
                  frameborder="0"
                  style="
                    border: 1px solid var(--color-border);
                    border-radius: 4px;
                  "
                >
                </iframe>
              </div>
            </div>

            <div
              class="bg-secondary bg-opacity-10 p-3 rounded border mt-3 mb-3"
            >
              <div
                class="d-flex justify-content-between align-items-center mb-3"
              >
                <h6 class="mb-0">
                  <i class="bi bi-file-earmark-code me-2"></i>Código HTML
                </h6>
                <div
                  class="btn-group btn-group-sm"
                  role="group"
                  v-if="previewData"
                >
                  <button
                    @click="copyToClipboard(previewData.html, 'HTML')"
                    class="btn btn-outline-secondary"
                  >
                    <i class="bi bi-clipboard"></i> Copiar
                  </button>
                  <button
                    @click="
                      downloadCode(
                        previewData.html,
                        'html',
                        selectedSubmission.nombre,
                      )
                    "
                    class="btn btn-outline-primary"
                  >
                    <i class="bi bi-download"></i> Descargar
                  </button>
                </div>
              </div>
              <div v-if="previewLoading" class="text-center">
                <div
                  class="spinner-border spinner-border-sm"
                  role="status"
                ></div>
                <span class="ms-2">Cargando código...</span>
              </div>
              <div v-else-if="previewData" class="bg-dark rounded">
                <pre class="html-code"><code>{{ previewData.html }}</code></pre>
              </div>
              <div v-else class="alert alert-warning mb-0">
                No se pudo cargar el código HTML
              </div>
            </div>

            <div
              class="bg-secondary bg-opacity-10 p-3 rounded border mt-3 mb-3"
            >
              <div
                class="d-flex justify-content-between align-items-center mb-3"
              >
                <h6 class="mb-0">
                  <i class="bi bi-code-slash me-2"></i>Código JavaScript
                </h6>
                <div
                  class="btn-group btn-group-sm"
                  role="group"
                  v-if="previewData"
                >
                  <button
                    @click="
                      copyToClipboard(previewData.javascript, 'JavaScript')
                    "
                    class="btn btn-outline-secondary"
                  >
                    <i class="bi bi-clipboard"></i> Copiar
                  </button>
                  <button
                    @click="
                      downloadCode(
                        previewData.javascript,
                        'js',
                        selectedSubmission.nombre,
                      )
                    "
                    class="btn btn-outline-primary"
                  >
                    <i class="bi bi-download"></i> Descargar
                  </button>
                </div>
              </div>
              <div v-if="previewLoading" class="text-center">
                <div
                  class="spinner-border spinner-border-sm"
                  role="status"
                ></div>
                <span class="ms-2">Cargando código...</span>
              </div>
              <div v-else-if="previewData" class="bg-dark rounded">
                <pre
                  class="js-code"
                ><code>{{ previewData.javascript }}</code></pre>
              </div>
              <div v-else class="alert alert-warning mb-0">
                No se pudo cargar el código JavaScript
              </div>
            </div>

            <div
              v-if="selectedSubmission.status === 'rechazado'"
              class="alert alert-danger mt-4 mb-0"
            >
              <strong>Razón del rechazo:</strong>
              <p class="mb-0">{{ selectedSubmission.motivo_rechazo }}</p>
            </div>

            <div v-if="selectedSubmission.status === 'pendiente'" class="mt-4">
              <label for="rejectionReason" class="form-label">
                Razón para rechazar (si aplica):
              </label>
              <textarea
                id="rejectionReason"
                v-model="rejectionReason"
                class="form-control bg-secondary bg-opacity-10"
                rows="3"
                placeholder="Especifica por qué se rechaza..."
              ></textarea>
            </div>
          </div>
        </div>

        <div
          class="d-flex justify-content-end gap-2 border-top p-4 detail-footer"
          v-if="selectedSubmission.status === 'pendiente'"
        >
          <button @click="closeDetail" class="btn btn-secondary">
            Cancelar
          </button>
          <button @click="rejectSubmission" class="btn btn-danger">
            <i class="bi bi-x-circle me-1"></i>Rechazar
          </button>
          <button @click="approveSubmission" class="btn btn-success">
            <i class="bi bi-check-circle me-1"></i>Aprobar
          </button>
        </div>
        <div
          class="d-flex justify-content-end gap-2 border-top p-4 detail-footer"
          v-else
        >
          <button @click="closeDetail" class="btn btn-secondary">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useAuthStore } from "../stores/auth.js";
import alerts from "@/utils/alerts.js";

const authStore = useAuthStore();
const submissions = ref([]);
const loading = ref(true);
const selectedSubmission = ref(null);
const showDetail = ref(false);
const rejectionReason = ref("");
const previewData = ref(null);
const previewLoading = ref(false);

const filters = reactive({
  status: "pendiente",
  search: "",
});

const loadSubmissions = async () => {
  loading.value = true;
  try {
    const response = await fetch(
      authStore.apiUrl("get_submissions.php?status=" + filters.status),
      {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
        },
      },
    );

    const data = await response.json();
    if (data.exito) {
      submissions.value = data.datos || [];
    }
  } catch (error) {
    // console.error('Error al cargar envíos:', error)
  } finally {
    loading.value = false;
  }
};

const viewSubmission = (submission) => {
  selectedSubmission.value = submission;
  showDetail.value = true;
  loadPreview(submission.submission_id);
};

const loadPreview = async (submissionId) => {
  previewLoading.value = true;
  try {
    const response = await fetch(
      authStore.apiUrl(
        `get_submission_preview.php?submission_id=${submissionId}`,
      ),
      {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
        },
      },
    );

    const data = await response.json();
    if (data.exito) {
      previewData.value = data.preview;
    }
  } catch (error) {
    // console.error('Error al cargar vista previa:', error)
  } finally {
    previewLoading.value = false;
  }
};

const closeDetail = () => {
  showDetail.value = false;
  selectedSubmission.value = null;
  rejectionReason.value = "";
  previewData.value = null;
};

const approveSubmission = async () => {
  if (!selectedSubmission.value) return;

  try {
    const response = await fetch(authStore.apiUrl("review_submission.php"), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${authStore.token}`,
      },
      body: JSON.stringify({
        submission_id: selectedSubmission.value.submission_id,
        action: "approve",
      }),
    });

    const data = await response.json();
    if (data.exito) {
      closeDetail();
      loadSubmissions();
      alerts.showSuccess("Envío aprobado exitosamente");
    }
  } catch (error) {
    alerts.showError("Error al aprobar el envío");
    // console.error('Error:', error)
  }
};

const rejectSubmission = async () => {
  if (!selectedSubmission.value || !rejectionReason.value.trim()) {
    alerts.showWarning("Debes indicar una razón para rechazar");
    return;
  }

  try {
    const response = await fetch(authStore.apiUrl("review_submission.php"), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${authStore.token}`,
      },
      body: JSON.stringify({
        submission_id: selectedSubmission.value.submission_id,
        action: "reject",
        reason: rejectionReason.value,
      }),
    });

    const data = await response.json();
    if (data.exito) {
      closeDetail();
      loadSubmissions();
      alerts.showSuccess("Envío rechazado exitosamente");
    }
  } catch (error) {
    alerts.showError("Error al rechazar el envío");
    // console.error('Error:', error)
  }
};

const getStatusBadge = (status) => {
  const badges = {
    pendiente: "badge bg-warning text-dark",
    aprobado: "badge bg-success",
    rechazado: "badge bg-danger",
  };
  return badges[status] || "badge bg-secondary";
};

const copyToClipboard = async (content, type) => {
  try {
    await navigator.clipboard.writeText(content);
    // Mostrar notificación temporal
    const notification = document.createElement("div");
    notification.className =
      "alert alert-success position-fixed top-0 end-0 m-3";
    notification.style.zIndex = "10000";
    notification.innerHTML = `<i class="bi bi-check-circle me-2"></i>Código ${type} copiado al portapapeles`;
    document.body.appendChild(notification);
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 2000);
  } catch (error) {
    // console.error("Error al copiar:", error);
    alerts.showError("Error al copiar al portapapeles");
  }
};

// helper para descargar codigo como archivo
const downloadCode = (content, extension, filename) => {
  const blob = new Blob([content], { type: "text/plain" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `${filename}.${extension}`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
};

// filtrar envios para admin (nombre de juego o usuario)
const filteredSubmissions = computed(() => {
  return submissions.value.filter((submission) => {
    const searchText = filters.search.toLowerCase().trim();
    return (
      submission.nombre.toLowerCase().includes(searchText) ||
      submission.usuario_nombre.toLowerCase().includes(searchText)
    );
  });
});

onMounted(() => {
  loadSubmissions();
});
</script>

<style scoped>
.card {
  transition: all 0.3s ease;
}

.card:hover {
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
  border-color: #3b82f6 !important;
}

.bg-dark {
  overflow: hidden;
}

.html-code,
.js-code {
  margin: 0;
  padding: 1rem;
  font-family: "Fira Code", "Monaco", "Consolas", monospace;
  font-size: 0.85rem;
  line-height: 1.5;
  color: #e2e8f0;
  white-space: pre-wrap;
  word-wrap: break-word;
  max-height: 300px;
  overflow-y: auto;
}

.html-code code {
  color: #60a5fa;
}

.js-code code {
  color: #34d399;
}

.btn-group .btn {
  border-color: var(--color-border);
  color: var(--color-text);
}

.btn-group .btn:hover {
  background: var(--color-background-soft);
  border-color: var(--color-border);
  color: var(--color-text);
}

.detail-footer {
  background: var(--color-background-soft);
}

@media (max-width: 768px) {
  .d-flex {
    flex-direction: column;
    gap: 1rem;
  }
}
</style>
