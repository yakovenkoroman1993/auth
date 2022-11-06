<script lang="ts">
import { defineComponent } from 'vue'
import { useUserStore } from '@/stores/user'

export default defineComponent({
  data() {
    return {
      email: "",
      loading: false,
      serverMessage: null as string | null,
    }
  },
  setup() {
    return {
      userStore: useUserStore()
    };
  },
  methods: {
    async signUp() {
      this.loading = true;
      try {
        const { successfully, message } = await this.userStore.signUp(this.email);
        this.serverMessage = message;
        if (!successfully) {
          setTimeout(() => {
            this.serverMessage = null;
          }, 3000);
        }
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
    v-if="!serverMessage"
    class="root"
    @submit.prevent="signUp"
  >
    <label>
      Email
      <input
        required
        type="email"
        v-model="email"
        placeholder="Please enter your email"
      />
    </label>
    <button :disabled="this.loading">Sign Up</button>
  </form>
  <div v-if="serverMessage">
    <h1>{{ serverMessage }}</h1>
  </div>
</template>

<style lang="scss" scoped>
@import "src/assets/admin.scss";
</style>
