<script lang="ts">
import { useUserStore } from "@/stores/user";
import type { User } from "@/stores/user";
import { defineComponent } from 'vue'

type Data = {
  user: User | null;
  loading: boolean;
  serverErrorMessage: string | null;
}

export default defineComponent<Data>({
  data(){
    return {
      user: null,
      loading: false,
      serverErrorMessage: null,
    }
  },
  async created() {
    this.user = await this.userStore.currentUser;

  },
  setup() {
    return {
      userStore: useUserStore()
    };
  },
  methods: {
    async save() {
      this.loading = true;
      try {
        const { successfully, message } = await this.userStore.saveCurrentUser(this.user);
        if (successfully) {
          this.$router.push("/");
        } else {
          this.serverErrorMessage = message;
          setTimeout(() => this.serverErrorMessage = null, 3000);
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
    v-if="!serverErrorMessage && user !== null"
    class="root"
    @submit.prevent="save"
  >
    <label>
      First Name
      <input
        required
        type="text"
        v-model="user.firstName"
        placeholder="Please enter your first name"
      />
    </label>
    <label>
      Last Name
      <input
        required
        type="text"
        v-model="user.lastName"
        placeholder="Please enter your last name"
      />
    </label>
    <button :disabled="loading || !user.firstName || !user.lastName ">Save</button>
  </form>
  <div v-if="serverErrorMessage">
    <h1>{{ serverErrorMessage }}</h1>
  </div>

</template>

<style lang="scss" scoped>
@import "src/assets/admin.scss";
</style>
