import Swal from 'sweetalert2'

/**
 * Mostrar modal de éxito
 * @param {string} title - Título
 * @param {string} message - Mensaje
 * @param {function} onConfirm - Callback cuando se confirma
 */
export const showSuccess = (title, message, onConfirm = null) => {
  const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark'
  return Swal.fire({
    icon: 'success',
    title: title,
    text: message,
    confirmButtonText: 'Aceptar',
    confirmButtonColor: '#10b981',
    background: isDark
      ? 'linear-gradient(135deg, #1a2332 0%, #2d3e52 100%)'
      : 'linear-gradient(135deg, #ffffff 0%, #f8fafc 100%)',
    color: isDark ? '#e2e8f0' : '#1e293b',
    iconColor: '#10b981',
    customClass: {
      popup: 'swal2-custom-popup',
      title: 'swal2-custom-title',
      confirmButton: 'swal2-custom-confirm',
    },
    willClose: () => {
      if (onConfirm && typeof onConfirm === 'function') {
        onConfirm()
      }
    },
  })
}

/**
 * Mostrar modal de error
 * @param {string}
 * @param {string|array} message - Mensaje o array de errores
 */
export const showError = (title, message) => {
  const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark'
  let messageText = message
  if (Array.isArray(message)) {
    messageText = message.join('\n')
  }
  return Swal.fire({
    icon: 'error',
    title: title,
    html: messageText.replace(/\n/g, '<br>'),
    confirmButtonText: 'Entendido',
    confirmButtonColor: '#ef4444',
    background: isDark
      ? 'linear-gradient(135deg, #1a2332 0%, #2d3e52 100%)'
      : 'linear-gradient(135deg, #ffffff 0%, #fef2f2 100%)',
    color: isDark ? '#e2e8f0' : '#1e293b',
    iconColor: '#ef4444',
    customClass: {
      popup: 'swal2-custom-popup',
      title: 'swal2-custom-title',
      confirmButton: 'swal2-custom-confirm',
    },
  })
}

/**
 * Mostrar modal de información
 * @param {string} title
 * @param {string} message
 * @param {function} onConfirm
 */
export const showInfo = (title, message, onConfirm = null) => {
  const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark'
  return Swal.fire({
    icon: 'info',
    title: title,
    text: message,
    confirmButtonText: 'Aceptar',
    confirmButtonColor: '#3b82f6',
    background: isDark
      ? 'linear-gradient(135deg, #1a2332 0%, #2d3e52 100%)'
      : 'linear-gradient(135deg, #ffffff 0%, #eff6ff 100%)',
    color: isDark ? '#e2e8f0' : '#1e293b',
    iconColor: '#3b82f6',
    customClass: {
      popup: 'swal2-custom-popup',
      title: 'swal2-custom-title',
      confirmButton: 'swal2-custom-confirm',
    },
    willClose: () => {
      if (onConfirm && typeof onConfirm === 'function') {
        onConfirm()
      }
    },
  })
}

/**
 * Mostrar modal de advertencia
 * @param {string} title
 * @param {string} message
 */
export const showWarning = (title, message) => {
  const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark'
  return Swal.fire({
    icon: 'warning',
    title: title,
    text: message,
    confirmButtonText: 'Aceptar',
    confirmButtonColor: '#f59e0b',
    background: isDark
      ? 'linear-gradient(135deg, #1a2332 0%, #2d3e52 100%)'
      : 'linear-gradient(135deg, #ffffff 0%, #fffbeb 100%)',
    color: isDark ? '#e2e8f0' : '#1e293b',
    iconColor: '#f59e0b',
    customClass: {
      popup: 'swal2-custom-popup',
      title: 'swal2-custom-title',
      confirmButton: 'swal2-custom-confirm',
    },
  })
}

/**
 * Mostrar modal de confirmación (Sí/No)
 * @param {string} title
 * @param {string} message
 * @param {string} confirmText - Texto de confirmación (default: 'Sí')
 * @param {string} cancelText - Texto de cancelación (default: 'No')
 * @returns {Promise} - true si confirma, false si cancela
 */
export const showConfirm = (title, message, confirmText = 'Sí', cancelText = 'No') => {
  const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark'
  return Swal.fire({
    icon: 'question',
    title: title,
    text: message,
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText: cancelText,
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#6b7280',
    background: isDark
      ? 'linear-gradient(135deg, #1a2332 0%, #2d3e52 100%)'
      : 'linear-gradient(135deg, #ffffff 0%, #eff6ff 100%)',
    color: isDark ? '#e2e8f0' : '#1e293b',
    iconColor: '#3b82f6',
    customClass: {
      popup: 'swal2-custom-popup',
      title: 'swal2-custom-title',
      confirmButton: 'swal2-custom-confirm',
      cancelButton: 'swal2-custom-cancel',
    },
  }).then((result) => result.isConfirmed)
}

/**
 * Mostrar modal con contenido HTML personalizado
 * @param {string} title
 * @param {string} html - HTML personalizado
 * @param {function} onConfirm - Callback
 */
export const showHtml = (title, html, onConfirm = null) => {
  const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark'
  return Swal.fire({
    title: title,
    html: html,
    confirmButtonText: 'Aceptar',
    confirmButtonColor: '#3b82f6',
    background: isDark
      ? 'linear-gradient(135deg, #1a2332 0%, #2d3e52 100%)'
      : 'linear-gradient(135deg, #ffffff 0%, #f8fafc 100%)',
    color: isDark ? '#e2e8f0' : '#1e293b',
    customClass: {
      popup: 'swal2-custom-popup',
      title: 'swal2-custom-title',
      confirmButton: 'swal2-custom-confirm',
    },
    willClose: () => {
      if (onConfirm && typeof onConfirm === 'function') {
        onConfirm()
      }
    },
  })
}

/**
 * Mostrar modal de carga
 * @param {string} title
 * @param {string} message
 */
export const showLoading = (title, message = 'Por favor espera...') => {
  const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark'
  Swal.fire({
    title: title,
    html: message,
    allowOutsideClick: false,
    allowEscapeKey: false,
    background: isDark
      ? 'linear-gradient(135deg, #1a2332 0%, #2d3e52 100%)'
      : 'linear-gradient(135deg, #ffffff 0%, #f8fafc 100%)',
    color: isDark ? '#e2e8f0' : '#1e293b',
    customClass: {
      popup: 'swal2-custom-popup',
      title: 'swal2-custom-title',
    },
    didOpen: () => {
      Swal.showLoading()
    },
  })
}

/**
 * Cerrar modal de carga
 */
export const hideLoading = () => {
  Swal.close()
}

/**
 * Mostrar modal con HTML para el formato de código aceptado
 */
export const showCodeFormat = () => {
  const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark'
  const bgColor = isDark ? '#2d3e52' : '#f7fafc'
  const textColor = isDark ? '#e2e8f0' : '#2d3748'
  const htmlContent = `
        <div style="text-align: left; color: ${textColor};">
            <h4 style="color: ${textColor}; margin-bottom: 15px;"><i class="bi bi-file-earmark-code" style="margin-right: 8px;"></i>Formato de Código Aceptado</h4>
            
            <div style="background-color: ${bgColor}; padding: 15px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #10b981;">
                <h5 style="color: ${textColor}; margin-top: 0;"><i class="bi bi-check-circle" style="margin-right: 8px;"></i>Opciones Aceptadas:</h5>
                <ul style="margin: 10px 0; padding-left: 20px; color: ${textColor};">
                    <li><strong>Solo HTML:</strong> Con estilos CSS incluidos en &lt;style&gt;</li>
                    <li><strong>Solo JavaScript:</strong> Con lógica del juego</li>
                    <li><strong>Combinado:</strong> HTML + JavaScript separados en archivos diferentes</li>
                    <li><strong>Canvas:</strong> Se recomienda usar canvas para gráficos</li>
                </ul>
            </div>

            <div style="background-color: ${bgColor}; padding: 15px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #f59e0b;">
                <h5 style="color: ${textColor}; margin-top: 0;"><i class="bi bi-exclamation-triangle" style="margin-right: 8px;"></i>Requisitos Mínimos:</h5>
                <ul style="margin: 10px 0; padding-left: 20px; color: ${textColor};">
                    <li>Mínimo 50 caracteres en HTML</li>
                    <li>Mínimo 50 caracteres en JavaScript</li>
                    <li>Debe ser funcional e interactivo</li>
                    <li>No se permiten scripts maliciosos (eval, innerHTML inseguro, etc.)</li>
                </ul>
            </div>

            <div style="background-color: ${bgColor}; padding: 15px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #ef4444;">
                <h5 style="color: ${textColor}; margin-top: 0;"><i class="bi bi-x-circle" style="margin-right: 8px;"></i>No Permitido:</h5>
                <ul style="margin: 10px 0; padding-left: 20px; color: ${textColor};">
                    <li>Código con &lt;script&gt; tags en línea</li>
                    <li>Funciones eval()</li>
                    <li>Scripts externos no autorizados</li>
                    <li>Contenido ofensivo o inapropiado</li>
                </ul>
            </div>

            <div style="background-color: ${bgColor}; padding: 15px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #0284c7;">
                <h5 style="color: ${textColor}; margin-top: 0;"><i class="bi bi-file-earmark-text" style="margin-right: 8px;"></i>Estructura HTML Recomendada:</h5>
                <pre style="background-color: #1e293b; color: #e2e8f0; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 12px;">
                  &lt;!DOCTYPE html&gt;
                  &lt;html&gt;
                    &lt;head&gt;
                      &lt;meta charset="UTF-8"&gt;
                      &lt;title&gt;Mi Juego&lt;/title&gt;
                      &lt;style&gt;
                        body { margin: 0; padding: 20px; background: #222; color: #fff; }
                        canvas { border: 2px solid #00ff00; background: #000; }
                      &lt;/style&gt;
                    &lt;/head&gt;
                    &lt;body&gt;
                      &lt;canvas id="gameCanvas" width="400" height="400"&gt;&lt;/canvas&gt;
                      &lt;script src="game.js"&gt;&lt;/script&gt;
                    &lt;/body&gt;
                  &lt;/html&gt;
                </pre>
            </div>

            <div style="background-color: ${bgColor}; padding: 15px; border-radius: 6px; border-left: 4px solid #8b5cf6;">
              <h5 style="color: ${textColor}; margin-top: 0;"><i class="bi bi-code-slash" style="margin-right: 8px;"></i>Ejemplo JavaScript:</h5>
              <pre style="background-color: #1e293b; color: #e2e8f0; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 12px;">
                  const canvas = document.getElementById('gameCanvas');
                  const ctx = canvas.getContext('2d');

                  // Variables del juego
                  let x = canvas.width / 2;
                  let y = canvas.height / 2;
                  let dx = 2;
                  let dy = -2;

                  // Función principal del juego
                  function draw() {
                      ctx.clearRect(0, 0, canvas.width, canvas.height);
                      
                      // Dibujar círculo
                      ctx.beginPath();
                      ctx.arc(x, y, 10, 0, Math.PI * 2);
                      ctx.fillStyle = '#00ff00';
                      ctx.fill();
                      ctx.closePath();
                      
                      // Actualizar posición
                      x += dx;
                      y += dy;
                      
                      // Rebote en bordes
                      if (x + dx > canvas.width - 10 || x + dx < 10) {
                          dx = -dx;
                      }
                      if (y + dy > canvas.height - 10 || y + dy < 10) {
                          dy = -dy;
                      }
                  }

                  // Bucle del juego
                  setInterval(draw, 10);
                </pre>
            </div>
          </div>
    `

  return Swal.fire({
    title: '<i class="bi bi-clipboard-check" style="margin-right: 8px;"></i>Formato de Código',
    html: htmlContent,
    width: '700px',
    confirmButtonText: 'Entendido',
    confirmButtonColor: '#3b82f6',
    background: isDark
      ? 'linear-gradient(135deg, #1a2332 0%, #2d3e52 100%)'
      : 'linear-gradient(135deg, #ffffff 0%, #f8fafc 100%)',
    color: isDark ? '#e2e8f0' : '#1e293b',
    customClass: {
      popup: 'swal2-custom-popup',
      title: 'swal2-custom-title',
      confirmButton: 'swal2-custom-confirm',
    },
    scrollbarPadding: false,
  })
}

export default {
  showSuccess,
  showError,
  showInfo,
  showWarning,
  showConfirm,
  showHtml,
  showLoading,
  hideLoading,
  showCodeFormat,
}
