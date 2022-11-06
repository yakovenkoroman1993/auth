import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "home",
      component: () => import("@/components/Home.vue"),
    },
    {
      path: "/admin",
      name: "admin",
      component: () => import("@/components/SignIn.vue"),
    },
    {
      path: "/sign-up/confirm",
      name: "sign-up-confirm",
      component: () => import("@/components/SignUpConfirm.vue"),
    },
    {
      path: "/sign-up",
      name: "sign-up",
      component: () => import("@/components/SignUp.vue"),
    },
  ],
});

export default router;
