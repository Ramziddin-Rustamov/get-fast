/**
 * Tiny typed fetch client for the Qadam admin API.
 *
 * - Talks directly to the Laravel API (NEXT_PUBLIC_API_BASE_URL) from the browser.
 * - Attaches the JWT bearer token kept in localStorage.
 * - Normalizes errors into `ApiError` and broadcasts 401s so the auth layer can
 *   log the user out.
 */
import type { ApiErrorBody, Paginated } from "./types";

export const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_BASE_URL ?? "http://127.0.0.1:8000/api/v1";

export const TOKEN_STORAGE_KEY = "qadam_admin_token";
export const UNAUTHORIZED_EVENT = "qadam:unauthorized";

export class ApiError extends Error {
  status: number;
  errors?: Record<string, string[]>;

  constructor(status: number, body: ApiErrorBody) {
    super(body?.message || `Request failed (${status})`);
    this.name = "ApiError";
    this.status = status;
    this.errors = body?.errors;
  }

  /** First validation error for a field, if any. */
  fieldError(field: string): string | undefined {
    return this.errors?.[field]?.[0];
  }
}

export function getToken(): string | null {
  if (typeof window === "undefined") return null;
  return window.localStorage.getItem(TOKEN_STORAGE_KEY);
}

export function setToken(token: string | null) {
  if (typeof window === "undefined") return;
  if (token) window.localStorage.setItem(TOKEN_STORAGE_KEY, token);
  else window.localStorage.removeItem(TOKEN_STORAGE_KEY);
}

type Query = Record<string, string | number | boolean | undefined | null>;

interface RequestOptions {
  query?: Query;
  /** Override/extra headers. */
  headers?: Record<string, string>;
  signal?: AbortSignal;
}

function buildUrl(path: string, query?: Query): string {
  const url = new URL(
    path.startsWith("http")
      ? path
      : `${API_BASE_URL}${path.startsWith("/") ? "" : "/"}${path}`,
  );
  if (query) {
    for (const [key, value] of Object.entries(query)) {
      if (value === undefined || value === null || value === "") continue;
      url.searchParams.set(key, String(value));
    }
  }
  return url.toString();
}

async function request<T>(
  method: string,
  path: string,
  body?: unknown,
  options: RequestOptions = {},
): Promise<T> {
  const isForm = body instanceof FormData;
  const headers: Record<string, string> = {
    Accept: "application/json",
    ...(isForm ? {} : body !== undefined ? { "Content-Type": "application/json" } : {}),
    ...options.headers,
  };

  const token = getToken();
  if (token) headers.Authorization = `Bearer ${token}`;

  let res: Response;
  try {
    res = await fetch(buildUrl(path, options.query), {
      method,
      headers,
      body: isForm ? (body as FormData) : body !== undefined ? JSON.stringify(body) : undefined,
      signal: options.signal,
    });
  } catch (err) {
    if ((err as Error)?.name === "AbortError") throw err;
    throw new ApiError(0, {
      message: "Network error — is the API server running?",
    });
  }

  if (res.status === 401) {
    if (typeof window !== "undefined") {
      window.dispatchEvent(new CustomEvent(UNAUTHORIZED_EVENT));
    }
  }

  if (res.status === 204) return undefined as T;

  const contentType = res.headers.get("content-type") ?? "";
  if (!contentType.includes("application/json")) {
    if (res.ok) return undefined as T;
    throw new ApiError(res.status, { message: `Request failed (${res.status})` });
  }

  const json = await res.json();
  if (!res.ok) throw new ApiError(res.status, json as ApiErrorBody);
  return json as T;
}

export const api = {
  get: <T>(path: string, options?: RequestOptions) => request<T>("GET", path, undefined, options),
  post: <T>(path: string, body?: unknown, options?: RequestOptions) =>
    request<T>("POST", path, body, options),
  put: <T>(path: string, body?: unknown, options?: RequestOptions) =>
    request<T>("PUT", path, body, options),
  patch: <T>(path: string, body?: unknown, options?: RequestOptions) =>
    request<T>("PATCH", path, body, options),
  del: <T>(path: string, options?: RequestOptions) => request<T>("DELETE", path, undefined, options),
};

/** Convenience for list endpoints that return `{ data, meta }`. */
export type PaginatedResponse<T> = Paginated<T>;
