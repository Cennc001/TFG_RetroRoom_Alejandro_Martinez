<template>
  <div class="submit-code-container">
    <div class="code-header">
      <h1><i class="bi bi-send-fill me-2"></i>Envía Tu Juego</h1>
      <p>Comparte tu juego hecho en JavaScript y HTML. Será revisado por moderadores.</p>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <form @submit.prevent="submitCode" class="code-form">
          <!-- Nombre de juego -->
          <div class="form-group mb-3">
            <label for="gameName" class="form-label"
              >Nombre del Juego <span class="text-danger">*</span></label
            >
            <input
              id="gameName"
              v-model="form.gameName"
              type="text"
              class="form-control"
              placeholder="Ej: Mi juego"
              required
            />
          </div>

          <!-- Descripcion -->
          <div class="form-group mb-3">
            <label for="gameDescription" class="form-label"
              >Descripción <span class="text-danger">*</span></label
            >
            <textarea
              id="gameDescription"
              v-model="form.gameDescription"
              class="form-control"
              rows="3"
              placeholder="Describe brevemente tu juego, mecánicas principales, controles, etc..."
              required
            ></textarea>
          </div>

          <!-- Autor -->
          <div class="form-group mb-3">
            <label for="authorName" class="form-label">Créditos del Autor (Opcional)</label>
            <input
              id="authorName"
              v-model="form.authorName"
              type="text"
              class="form-control"
              placeholder="Tu nombre o pseudónimo"
            />
          </div>

          <!-- HTML -->
          <div class="form-group mb-3">
            <label for="htmlCode" class="form-label"
              >Código HTML <span class="text-danger">*</span></label
            >
            <div class="code-editor-wrapper">
              <textarea
                id="htmlCode"
                v-model="form.htmlCode"
                class="form-control code-editor"
                rows="10"
                placeholder="Pega aquí tu código HTML. Debe incluir un canvas o elemento principal."
                spellcheck="false"
                required
              ></textarea>
            </div>
            <small class="form-text">
              <i class="bi bi-info-circle me-1"></i>
              Mínimo 50 caracteres. Sin etiquetas &lt;script&gt;.
            </small>
          </div>

          <!-- JavaScript -->
          <div class="form-group mb-3">
            <label for="jsCode" class="form-label"
              >Código JavaScript <span class="text-danger">*</span></label
            >
            <div class="code-editor-wrapper">
              <textarea
                id="jsCode"
                v-model="form.jsCode"
                class="form-control code-editor"
                rows="15"
                placeholder="Pega aquí tu código JavaScript. Será inyectado en el HTML."
                spellcheck="false"
                required
              ></textarea>
            </div>
            <small class="form-text">
              <i class="bi bi-info-circle me-1"></i>
              Mínimo 50 caracteres. Evita usar eval() y contenido peligroso.
            </small>
          </div>

          <!-- Submit -->
          <button type="submit" class="btn btn-primary btn-lg w-100" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ loading ? 'Enviando...' : 'Enviar Juego' }}
          </button>
        </form>
      </div>

      <!-- Sidebar -->
      <div class="col-lg-4">
        <div class="info-box">
          <h5><i class="bi bi-lightbulb me-2"></i>Consejos</h5>
          <ul class="tips-list">
            <li>Asegúrate que tu código funcione correctamente</li>
            <li>Incluye instrucciones claras de uso</li>
            <li>Mantén el código limpio y comentado</li>
            <li>Prueba en diferentes navegadores</li>
            <li>Se revisará dentro de 24-48 horas</li>
          </ul>
        </div>

        <div class="info-box mt-3">
          <h5><i class="bi bi-file-earmark-code me-2"></i>Formato de Código</h5>
          <p>Consulta el formato correcto de código que aceptamos.</p>
          <button @click="showCodeFormatModal" class="btn btn-info w-100">
            Ver Formato Aceptado
          </button>
        </div>

        <div class="info-box mt-3">
          <h5><i class="bi bi-download me-2"></i>Plantilla</h5>
          <p>Descarga una plantilla básica para empezar.</p>
          <button @click="downloadTemplate" class="btn btn-outline-primary w-100">
            Descargar Template
          </button>
        </div>

        <div class="info-box mt-3">
          <h5><i class="bi bi-shield-check me-2"></i>Seguridad</h5>
          <p>Tu código será:</p>
          <ul class="security-list">
            <li><i class="bi bi-check2"></i> Escaneado en busca de malware</li>
            <li><i class="bi bi-check2"></i> Revisado por moderadores</li>
            <li><i class="bi bi-check2"></i> Protegido contra XSS</li>
            <li><i class="bi bi-check2"></i> Ejecutado en sandbox seguro</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '../stores/auth.js'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const form = reactive({
  gameName: '',
  gameDescription: '',
  htmlCode: '',
  jsCode: '',
  authorName: '',
})

const loading = ref(false)
const errors = ref([])

const sanitizeCode = (code) => {
  // se preserva el codigo original para revision, sin codificarlo en entidades HTML.
  return code.replace(/\u2028|\u2029/g, '')
}

const validateCode = () => {
  errors.value = []

  if (!form.gameName.trim()) {
    errors.value.push('El nombre del juego es requerido')
  }
  if (!form.gameDescription.trim()) {
    errors.value.push('La descripción es requerida')
  }
  if (!form.htmlCode.trim()) {
    errors.value.push('El código HTML es requerido')
  }
  if (!form.jsCode.trim()) {
    errors.value.push('El código JavaScript es requerido')
  }
  if (form.htmlCode.trim().length < 50) {
    errors.value.push('El código HTML parece muy corto')
  }
  if (form.jsCode.trim().length < 50) {
    errors.value.push('El código JavaScript parece muy corto')
  }

  // validar que no haya scripts peligrosos
  if (form.htmlCode.includes('<script>') || form.jsCode.includes('eval(')) {
    errors.value.push('Código con potencial XSS detectado. Por favor revisa tu código.')
  }

  return errors.value.length === 0
}

const submitCode = async () => {
  if (!validateCode()) {
    window.showError('Errores de validación', errors.value)
    return
  }

  loading.value = true

  try {
    const response = await fetch(authStore.apiUrl('submit_code.php'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${authStore.token}`,
      },
      body: JSON.stringify({
        game_name: form.gameName,
        game_description: form.gameDescription,
        html_code: sanitizeCode(form.htmlCode),
        js_code: sanitizeCode(form.jsCode),
        author_name: form.authorName,
      }),
    })

    const data = await response.json()

    if (data.exito) {
      // modal de exito
      window.showSuccess(
        '¡Código enviado!',
        'Tu juego ha sido enviado exitosamente.\nSe revisará dentro de 24-48 horas.',
        () => {
          // limpiar formulario
          form.gameName = ''
          form.gameDescription = ''
          form.htmlCode = ''
          form.jsCode = ''
          form.authorName = ''
          errors.value = []

          router.push('/')
        },
      )
    } else {
      window.showError('Error al enviar', data.error || 'Error al enviar el código')
    }
  } catch (error) {
    window.showError('Error de conexión', error.message)
  } finally {
    loading.value = false
  }
}

const showCodeFormatModal = () => {
  window.showCodeFormat()
}

const downloadTemplate = () => {
  const template = [
    '<!DOCTYPE html>',
    '<html lang="es">',
    '  <head>',
    '    <meta charset="UTF-8">',
    '    <meta name="viewport" content="width=device-width, initial-scale=1.0">',
    '    <title>Mi Juego</title>',
    '    <style>',
    '        body {',
    '            margin: 0;',
    '            padding: 20px;',
    '            font-family: Arial, sans-serif;',
    '            background: #222;',
    '            color: #fff;',
    '            display: flex;',
    '            justify-content: center;',
    '            align-items: center;',
    '            min-height: 100vh;',
    '        }',
    '        .game-container {',
    '            text-align: center;',
    '        }',
    '        canvas {',
    '            border: 2px solid #00ff00;',
    '            background: #000;',
    '        }',
    '    </style>',
    '  </head>',
    '  <body>',
    '    <div class="game-container">',
    '        <h1>Mi Juego</h1>',
    '        <canvas id="gameCanvas" width="400" height="400"></canvas>',
    '    </div>',
    '    <script><' + '/script>',
    '  </body>',
    '</html>',
  ].join('\n')

  // crear blob y descargarlo
  const blob = new Blob([template], { type: 'text/html' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'template.html'
  a.click()
}
</script>

<style scoped>
.submit-code-container {
  max-width: 1200px;
  margin: 0 auto;
}

.code-header {
  text-align: center;
  margin-bottom: 3rem;
}

.code-header h1 {
  font-size: 2.5rem;
  color: var(--color-heading);
  margin-bottom: 0.5rem;
}

.code-header p {
  font-size: 1.1rem;
  color: var(--color-text);
}

.code-editor-wrapper {
  position: relative;
  border-radius: 6px;
  overflow: hidden;
}

.code-editor {
  font-size: 0.9rem;
  line-height: 1.5;
  border-radius: 6px;
  padding: 1rem;
}

.form-text {
  color: var(--color-muted);
  display: block;
  margin-top: 0.5rem;
}

.info-box {
  background: rgba(59, 130, 246, 0.05);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 1.5rem;
}

.info-box h5 {
  color: var(--color-heading);
  margin-bottom: 1rem;
  font-weight: 600;
}

.tips-list,
.security-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.tips-list li,
.security-list li {
  color: var(--color-text);
  margin-bottom: 0.5rem;
  padding-left: 1.5rem;
  position: relative;
}

.tips-list li::before {
  content: '→';
  position: absolute;
  left: 0;
  color: #3b82f6;
  font-weight: bold;
}

.security-list li {
  padding-left: 0;
}

@media (max-width: 768px) {
  .submit-code-container {
    padding: 1rem;
  }

  .code-header h1 {
    font-size: 1.8rem;
  }

  .row {
    display: block;
  }

  .col-lg-8,
  .col-lg-4 {
    width: 100%;
    margin-bottom: 2rem;
  }

  .code-editor {
    font-size: 0.85rem;
    padding: 0.75rem;
  }
}
</style>
