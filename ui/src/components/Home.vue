<script lang="ts">
import { useUserStore } from "@/stores/user";
import type { User } from "@/stores/user";
import { defineComponent } from 'vue'

type Data = {
  user: User | null;
  loading: boolean;
}

export default defineComponent<Data>({
  data(){
    return {
      user: null,
      loading: false,
    }
  },
  async created() {
    const currentUser: User = await this.userStore.currentUser;
    this.user = currentUser;
    if (!currentUser.firstName || !currentUser.lastName) {
      this.$router.push("/profile");
      return;
    }
    if (currentUser.role === "admin") {
      this.$router.push("admin/users");
      return;
    }

  },
  setup() {
    return {
      userStore: useUserStore()
    };
  },
  methods: {

  }
})
</script>

<template>
  <main v-if="user && user.firstName && user.lastName">
    Welcome to Auth App, {{user.firstName}} {{user.lastName}}
  </main>
</template>

<style lang="scss" scoped>
@import "src/assets/admin.scss";
</style>
