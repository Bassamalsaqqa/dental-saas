/**
 * App API client for /app-api/v1/*
 *
 * Auth: httpOnly session cookie issued by PHP backend (CodeIgniter 4).
 * - Do NOT store tokens in localStorage/sessionStorage.
 * - credentials: "include" so the browser sends the session cookie.
 * - CSRF token is fetched from GET /app-api/v1/csrf and sent as X-CSRF-TOKEN on unsafe methods.
 *
 * SESSION 1: This client is a portable scaffold. Real network calls are
 * intentionally NOT wired yet — see src/api/mockApi.ts. Codex will swap
 * mockApi usages for these methods during backend integration.
 */
import type { ApiError, ApiSuccess } from "@/types";

export const APP_API_BASE = "/app-api/v1";

let csrfToken: string | null = null;

export class AppApiError extends Error {
  code: string;
  details?: Record<string, unknown>;
  requestId?: string;
  status?: number;

  constructor(err: ApiError, status?: number) {
    super(err.error.message);
    this.code = err.error.code;
    this.details = err.error.details;
    this.requestId = err.meta?.request_id;
    this.status = status;
  }
}

type Method = "GET" | "POST" | "PATCH" | "PUT" | "DELETE";

interface RequestOptions {
  method?: Method;
  body?: unknown;
  signal?: AbortSignal;
  headers?: Record<string, string>;
}

const UNSAFE: Method[] = ["POST", "PATCH", "PUT", "DELETE"];

async function ensureCsrf(): Promise<string | null> {
  if (csrfToken) return csrfToken;
  try {
    const res = await fetch(`${APP_API_BASE}/csrf`, { credentials: "include" });
    if (!res.ok) return null;
    const json = (await res.json()) as ApiSuccess<{ token: string }>;
    csrfToken = json.data.token;
    return csrfToken;
  } catch {
    return null;
  }
}

export async function appApiRequest<T>(
  path: string,
  opts: RequestOptions = {}
): Promise<T> {
  const method = opts.method ?? "GET";
  const headers: Record<string, string> = {
    Accept: "application/json",
    ...(opts.headers ?? {}),
  };

  if (opts.body !== undefined) headers["Content-Type"] = "application/json";

  if (UNSAFE.includes(method)) {
    const token = await ensureCsrf();
    if (token) headers["X-CSRF-TOKEN"] = token;
  }

  const res = await fetch(`${APP_API_BASE}${path}`, {
    method,
    credentials: "include",
    headers,
    body: opts.body !== undefined ? JSON.stringify(opts.body) : undefined,
    signal: opts.signal,
  });

  let json: unknown = null;
  try {
    json = await res.json();
  } catch {
    /* empty */
  }

  if (!res.ok) {
    if (json && typeof json === "object" && "error" in json) {
      throw new AppApiError(json as ApiError, res.status);
    }
    throw new AppApiError(
      {
        error: { code: "http_error", message: `HTTP ${res.status}` },
        meta: { request_id: "" },
      },
      res.status
    );
  }

  const envelope = json as ApiSuccess<T>;
  return envelope.data;
}

export const appApiClient = {
  get: <T>(path: string, opts?: RequestOptions) =>
    appApiRequest<T>(path, { ...opts, method: "GET" }),
  post: <T>(path: string, body?: unknown, opts?: RequestOptions) =>
    appApiRequest<T>(path, { ...opts, method: "POST", body }),
  patch: <T>(path: string, body?: unknown, opts?: RequestOptions) =>
    appApiRequest<T>(path, { ...opts, method: "PATCH", body }),
  delete: <T>(path: string, opts?: RequestOptions) =>
    appApiRequest<T>(path, { ...opts, method: "DELETE" }),
  // Test helper
  _resetCsrf: () => {
    csrfToken = null;
  },
};
