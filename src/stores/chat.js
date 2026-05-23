import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useChatStore = defineStore('chat', () => {
  const showChat = ref(false)
  const usuariosOnline = ref([])
  const cargandoUsuarios = ref(false)

  const toggleChat = () => {
    showChat.value = !showChat.value
  }

  const setShowChat = (value) => {
    showChat.value = value
  }

  const actualizarUsuariosOnline = (usuarios) => {
    usuariosOnline.value = usuarios
  }

  const totalUsuariosOnline = computed(() => usuariosOnline.value.length)

  return {
    showChat,
    usuariosOnline,
    cargandoUsuarios,
    totalUsuariosOnline,
    toggleChat,
    setShowChat,
    actualizarUsuariosOnline,
  }
})
