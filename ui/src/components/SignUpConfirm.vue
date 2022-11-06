<script lang="ts">
import { defineComponent } from 'vue'
import { useUserStore } from '@/stores/user'
import { useRoute } from 'vue-router'

export default defineComponent({
  data() {
    return {
      password: "",
      passwordCompared: "",
      loading: false,
      serverMessage: null as null | string
    }
  },
  setup() {
    return {
      userStore: useUserStore(),
      route: useRoute(),
    };
  },
  methods: {
    async confirm() {
      this.loading = true;
      try {
        const { successfully, message } = await this.userStore.signUpConfirm(
          this.password,
          this.route.query.regKey as any
        );
        this.serverMessage = message;
        if (successfully) {
          setTimeout(() => {
            this.$router.push("/admin");
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
    @submit.prevent="confirm"
  >
    <label>
      Password
      <input
        required
        type="password"
        v-model="password"
        placeholder="Please enter your password"
      />
    </label>
    <label>
      <input
        required
        type="password"
        v-model="passwordCompared"
        placeholder="Please enter password again"
      />
    </label>
    <button :disabled="this.loading || !this.password || (this.password !== this.passwordCompared)">
      Finish Sign Up
    </button>
  </form>
  <div v-if="serverMessage">
    <h1>{{ serverMessage }}</h1>
  </div>
</template>

<style lang="scss" scoped>
@import "src/assets/admin.scss";
</style>
