import { defineStore } from "pinia";
import Cookies from "js-cookie"
import { RequestService } from "@/services/RequestService";

type ServerResponse = {
  successfully: boolean;
  message: string;
}

export interface User {
  userId: number;
  email: string;
  firstName: string | null;
  lastName: string | null;
  accessToken: string;
  accessTokenExpiresAt: string;
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

      this.user = json.currentUser;
      Cookies.set("token", this.user.accessToken);
      Cookies.set(
        "tokenExpiresAt",
        new Date(this.user.accessTokenExpiresAt + " UTC").getTime().toString()
      );


      return {
        successfully: json.successfully,
        message: json.message,
      };
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
  }
});
