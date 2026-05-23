import { defineStore } from 'pinia'

export const useThemeStore = defineStore('theme', {
  state: () => ({
    isDark: true,
  }),
  actions: {
    toggleTheme() {
      this.isDark = !this.isDark
    },
  },
})
