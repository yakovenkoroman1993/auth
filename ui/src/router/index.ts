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
      path: "/admin/users",
      name: "admin-users",
      component: () => import("@/components/Users.vue"),
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
    {
      path: "/profile",
      name: "profile",
      component: () => import("@/components/Profile.vue"),
    },
  ],
});

export default router;
