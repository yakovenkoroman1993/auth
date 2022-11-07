<script lang="ts">
import type { ServerResponse, User } from "@/stores/user";
import { useUserStore } from "@/stores/user";
import { defineComponent } from "vue";

type Data = {
  loading: boolean;
  users: User[];
  serverErrorMessage: string | null;
}

export default defineComponent<Data>({
  data(){
    return {
      loading: false,
      users: [],
      serverErrorMessage: null,
      selectedUser: null,
    }
  },
  setup() {
    return {
      userStore: useUserStore()
    };
  },
  async created() {
    await this.load();
  },
  methods: {
    async handleAction(request: () => Promise<ServerResponse>) {
      this.loading = true;
      try {
        const {message, successfully} = await request();
        if (successfully) {
          await this.load();
          this.selectedUser = null;
          return;
        }
        this.serverErrorMessage = message;
        setTimeout(() => this.serverErrorMessage = null, 3000);
      }
      catch (err) {
        console.error(err);
        this.serverErrorMessage = "Server Error";
        setTimeout(() => this.serverErrorMessage = null, 3000);
      } finally {
        this.loading = false;
      }
    },
    cloneUser(userId: number) {
      this.handleAction(() => this.userStore.cloneUserById(userId));
    },
    deleteUser(userId: number) {
      this.handleAction(() => this.userStore.deleteUserById(userId));
    },
    saveSelectedUser() {
      this.handleAction(() => this.userStore.save(this.selectedUser));
    },
    async load() {
      this.users = await this.userStore.findAllUsers();
    },
    selectUser(u: User) {
      this.selectedUser = { ...u };
    },
    async signOut() {
      await this.userStore.signOut();
      this.$router.push("/");
    }
  }
})
</script>

<template>
  <div class="root">
    <div>
      <main v-if="users.length > 0">
        <h1>Users</h1>
        <table border="">
          <tr>
            <th>User ID</th>
            <th>Email</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Created At</th>
            <th>Enabled</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
          <tr v-for="u in users" @click="selectUser(u)">
            <td>{{u.id}}</td>
            <td>{{u.email}}</td>
            <td>{{u.firstName}}</td>
            <td>{{u.lastName}}</td>
            <td>{{u.createdAt}}</td>
            <td>{{u.enabled}}</td>
            <td>{{u.role}}</td>
            <td>
              <button :disabled="loading" @click="deleteUser(u.id)">Delete</button>
              <button :disabled="loading" @click="cloneUser(u.id)">Clone</button>
            </td>
          </tr>
        </table>
      </main>
      <main v-if="users.length === 0">
        <h1>Users not found</h1>
      </main>
      <h1 v-if="serverErrorMessage">
        {{serverErrorMessage}}
      </h1>
    </div>
    <form v-if="selectedUser" @submit.prevent="saveSelectedUser">
      <label>
        ID
        <input
          disabled
          type="text"
          v-model="selectedUser.id"
        />
      </label>
      <label>
        Email
        <input
          required
          type="email"
          v-model="selectedUser.email"
          placeholder="Please enter email"
        />
      </label>
      <label>
        First Name
        <input
          type="text"
          v-model="selectedUser.firstName"
          placeholder="Please enter first name"
        />
      </label>
      <label>
        Last Name
        <input
          type="text"
          v-model="selectedUser.lastName"
          placeholder="Please enter last name"
        />
      </label>
      <label>
        Role
        <select
          v-model="selectedUser.role"
        >
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>
      </label>
      <div>
        <span>Enabled&nbsp;&nbsp;&nbsp;</span>
        <input
          type="checkbox"
          v-model="selectedUser.enabled"
          :true-value="1"
          :false-value="0"
        />
      </div>
      <button :disabled="loading">Save</button>
    </form>
    <hr />
    <button @click="signOut">Sign Out</button>
  </div>
</template>

<style lang="scss" scoped>
@import "src/assets/admin.scss";
</style>
