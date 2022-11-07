<script lang="ts">
import { defineComponent } from 'vue'
import { useUserStore } from '@/stores/user'
import Cookies from "js-cookie";

export default defineComponent({
  data() {
    return {
      email: "",
      password: "",
      serverErrorMessage: null as string | null,
      loading: false,
    }
  },
  async created() {
    if (Cookies.get("token")) {
      this.$router.push("/");
    }
  },
  setup() {
    return {
      userStore: useUserStore()
    };
  },
  methods: {
    async signIn() {
      this.loading = true;
      try {
        const { successfully, message } = await this.userStore.signIn(this.email, this.password);
        if (!successfully) {
          this.serverErrorMessage = message;
          setTimeout(() => { this.serverErrorMessage = null }, 3000);
          return;
        }
        this.$router.push("/");
      } catch (error) {
        console.error(error);
      } finally {
        this.loading = false;
      }
    }
  }
})
</script>

<template>
  <form
    class="root"
    @submit.prevent="signIn"
  >
    <label>Email <input required v-model="email" type="email"></label>
    <label>Password <input required v-model="password" type="password"></label>
    <button :disabled="!email || !password || loading">Sign In</button>
    <span v-if="serverErrorMessage" class="error">{{ serverErrorMessage }}</span>
    <a href="/sign-up">Register a new user</a>
  </form>
</template>

<style lang="scss" scoped>
  @import "src/assets/admin.scss";
</style>
