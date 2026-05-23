import './assets/main.css'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'sweetalert2/dist/sweetalert2.min.css'
import $ from 'jquery'

window.$ = $

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import enrutador from './router'
import * as alerts from './utils/alerts.js'

const aplicacion = createApp(App)

// funciones de alertas disponibles globalmente
window.showSuccess = alerts.showSuccess
window.showError = alerts.showError
window.showInfo = alerts.showInfo
window.showWarning = alerts.showWarning
window.showConfirm = alerts.showConfirm
window.showHtml = alerts.showHtml
window.showLoading = alerts.showLoading
window.hideLoading = alerts.hideLoading
window.showCodeFormat = alerts.showCodeFormat

aplicacion.use(createPinia())
aplicacion.use(enrutador)

aplicacion.mount('#app')
