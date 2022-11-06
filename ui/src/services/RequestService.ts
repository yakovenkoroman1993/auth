import Cookies from "js-cookie"

type Dict<T> = {
  [key: string]: T | undefined;
}

type Method = "POST" | "GET" | "PUT" | "DELETE";

const serverHostUrl = "/api"; // todo: in env

export class RequestService {
  private static get token(): string {
    const token = Cookies.get("token") as string | undefined;
    const tokenExpiresAt = +Number(Cookies.get("tokenExpiresAt")) as number;
    if (token && tokenExpiresAt) {
      const now = new Date().getTime();
      if (now >= tokenExpiresAt) {
        Cookies.remove("token");
        Cookies.remove("tokenExpiresAt");
      } else {
        return token;
      }
    }

    window.location.href = '/admin';
    throw new Error("Token not found. Authenticate");
  }

  static get<T>(path: string, params: Dict<any> = {}): Promise<T> {
    return RequestService.requestWithoutPayload<T>(path, params, "GET");
  }

  static delete<T>(path: string, params: Dict<any> = {}): Promise<T> {
    return RequestService.requestWithoutPayload<T>(path, params, "DELETE");
  }

  static post<T>(path: string, params: Dict<any> = {}, guest = false): Promise<T> {
    return RequestService.requestWithPayload<T>(path, params, "POST", guest);
  }

  static put<T>(path: string, params: Dict<any> = {}): Promise<T> {
    return RequestService.requestWithPayload<T>(path, params, "PUT");
  }

  private static async requestWithPayload<T>(
    path: string,
    params: Dict<any>,
    method: Method,
    guest = false,
  ): Promise<T> {
    let token = "";
    if (!guest) {
      token = RequestService.token;
    }

    const fd = RequestService.objectToFormData(params);
    const response = await fetch(`${serverHostUrl}${path}`, {
      method,
      cache: "no-cache",
      body: fd,
      ...(!guest && ({
        headers: {
          // "Content-Type": "application/json",
          Authorization: `Bearer ${token}`
        }
      }))
    });

    return this.getJsonFromResponseIfNoErrors<T>(response);
  }

  private static async requestWithoutPayload<T>(
    path: string,
    params: Dict<any>,
    method: Method
  ): Promise<T> {
    const { token } = RequestService;
    const qs = RequestService.objectToQueryString(params);

    const response = await fetch(`${serverHostUrl}${path}${qs ? `?${qs}` : ""}`, {
      method,
      cache: "no-cache",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });

    return this.getJsonFromResponseIfNoErrors<T>(response);
  }

  private static async getJsonFromResponseIfNoErrors<T>(
    response: Response
  ): Promise<T> {
    if (!response.ok) {
      throw new Error("Request failed. Response is not ok");
    }
    const json = await response.json();
    if (json.type === "Error") {
      throw new Error("Request failed. Type is Error");
    }

    return json;
  }

  private static objectToFormData(params: Dict<any> = {}): FormData {
    return Object
      .keys(params)
      .filter((key) => ![undefined, null, NaN].includes(params[key]))
      .reduce<FormData>((fd, key) => {
        fd.append(key, params[key]);
        return fd;
      }, new FormData())
  }

  private static objectToQueryString(params: Dict<any> = {}): string {
    return Object
      .keys(params)
      .filter((key) => ![undefined, null, NaN].includes(params[key]))
      .reduce<URLSearchParams>((sp, key) => {
        sp.append(key, params[key].toString());
        return sp;
      }, new URLSearchParams())
      .toString();
  }
}

