import { defineStore } from 'pinia'

// store global
export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null, // usuario
    isAuthenticated: false, // estado de auth
    token: null, // token de sesion
    urlServidor: 'http://localhost:8000/backend/api/'
  }),
  getters: {
    apiUrl: (state) => (endpoint) => `${state.urlServidor}${endpoint}`
  },
  actions: {
    login(userData, token) {
      // lo llama loginView
      this.user = userData
      this.isAuthenticated = true
      this.token = token
      localStorage.setItem('user', JSON.stringify(userData))
      localStorage.setItem('isAuthenticated', 'true')
      localStorage.setItem('token', token)
    },
    logout() {
      // al hacer logout, borra estado
      this.user = null
      this.isAuthenticated = false
      this.token = null
      localStorage.removeItem('user')
      localStorage.removeItem('isAuthenticated')
      localStorage.removeItem('token')
    },
    initializeAuth() {
      // al iniciar la app, revisa localStorage
      const user = localStorage.getItem('user')
      const isAuthenticated = localStorage.getItem('isAuthenticated')
      const token = localStorage.getItem('token')
      if (user && isAuthenticated === 'true' && token) {
        this.user = JSON.parse(user)
        this.isAuthenticated = true
        this.token = token
      }
    },
  },
})
