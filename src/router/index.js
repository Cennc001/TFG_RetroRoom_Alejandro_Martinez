import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import VistaInicio from "../views/HomeView.vue";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "inicio",
      component: VistaInicio,
    },
    {
      path: "/registro",
      name: "registro",
      component: () => import("../views/RegisterView.vue"),
      meta: { requiresAuth: false },
    },
    {
      path: "/juego",
      name: "juego",
      component: () => import("../views/GameView.vue"),
      meta: { requiresAuth: false },
    },
    {
      path: "/login",
      name: "login",
      component: () => import("../views/LoginView.vue"),
      meta: { requiresAuth: false },
    },
    {
      path: "/perfil",
      name: "perfil",
      component: () => import("../views/ProfileView.vue"),
      meta: { requiresAuth: true },
    },
    {
      path: "/enviar-codigo",
      name: "enviar-codigo",
      component: () => import("../views/SubmitCodeView.vue"),
      meta: { requiresAuth: true },
    },
    {
      path: "/mis-envios",
      name: "mis-envios",
      component: () => import("../views/UserSubmissionsView.vue"),
      meta: { requiresAuth: true },
    },
    {
      path: "/admin/submissions",
      name: "admin-submissions",
      component: () => import("../views/AdminSubmissionsView.vue"),
      meta: { requiresAuth: true },
    },
    {
      path: "/admin/usuarios",
      name: "admin-usuarios",
      component: () => import("../views/AdminUsersView.vue"),
      meta: { requiresAuth: true },
    },
  ],
});

// auth de navegacion
router.beforeEach((to, from) => {
  const authStore = useAuthStore();
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return "/login"; // si no esta aut. va a a login
  }
});

export default router;
