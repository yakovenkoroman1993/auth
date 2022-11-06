<script lang="ts">
import { defineComponent } from 'vue'
import { useUserStore } from "@/stores/user";

export default defineComponent({
  data() {
    return {
      user: null as null | any, // todo: !
      loading: false,
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

  }
})
</script>

<template>
  <main>
    HOME
  </main>
</template>

<style lang="scss" scoped>
@import "src/assets/admin.scss";
</style>
