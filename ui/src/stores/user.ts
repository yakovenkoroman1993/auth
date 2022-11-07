import { defineStore } from "pinia";
import Cookies from "js-cookie"
import { RequestService } from "@/services/RequestService";

export type ServerResponse = {
  successfully: boolean;
  message: string;
}

type ServerResponseUsers = {
  users: User[];
}

export type User = {
  id: number;
  enabled: boolean;
  email: string;
  firstName: string | null;
  lastName: string | null;
  accessToken: string;
  accessTokenExpiresAt: string;
  createdAt: string;
  regKey: string | null;
  role: "user" | "admin";
}

type ServerResponseAuth = ServerResponse & {
  token: string;
  expiresAt: number;
  currentUser: User;
}

export const useUserStore = defineStore("user", {
  state: () => ({
    user: null as null | User
  }),
  getters: {
    async currentUser(state): Promise<null | User> {
      if (state.user) {
        return state.user;
      }

      const user = await RequestService.get<User>("/user");
      this.user = user;
      return user;
    },
  },
  actions: {
    async signIn(email: string, password: string): Promise<ServerResponse> {
      const json = await RequestService.post<ServerResponseAuth>(
        "/user/sign-in",
        { email, password },
        true
      );

      const { successfully } = json;
      if (successfully && json.currentUser) {
        this.user = json.currentUser;
        Cookies.set("token", this.user.accessToken);
        Cookies.set(
          "tokenExpiresAt",
          new Date(this.user.accessTokenExpiresAt + " UTC").getTime().toString()
        );
      }

      return {
        successfully,
        message: json.message,
      };
    },
    signOut() {
      return RequestService.get<ServerResponse>("/user/sign-out");
    },
    signUp(email: string): Promise<ServerResponse> {
      return RequestService.post<ServerResponseAuth>(
        "/user/sign-up",
        { email },
        true
      );
    },
    signUpConfirm(password: string, regKey: string): Promise<ServerResponse> {
      return RequestService.post<ServerResponseAuth>(
        "/user/sign-up/confirm",
        { password, regKey },
        true
      );
    },
    async saveCurrentUser(user: User): Promise<ServerResponse> {
      const json = await this.save(user);

      this.user = json.currentUser;

      return {
        successfully: json.successfully,
        message: json.message,
      };
    },
    save(user: User): Promise<ServerResponse & {currentUser: User}> {
      return RequestService.post<ServerResponse & {currentUser: User}>(
        "/user/save",
        { ...user }
      );
    },
    async findAllUsers(): Promise<User[]> {
      const json = await RequestService.get<ServerResponseUsers>("/user/all");

      return json.users;
    },
    deleteUserById(id: number): Promise<ServerResponse> {
      return RequestService.delete<ServerResponse>("/user/delete", { id });
    },
    cloneUserById(id: number): Promise<ServerResponse> {
      return RequestService.get<ServerResponse>("/user/clone", { id });
    },
  }
});
