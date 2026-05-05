export type AppState =
  | "no_clinic"
  | "clinic_required"
  | "tenant_ready"
  | "controlplane_ready"
  | "suspended"
  | "subscription_inactive";

export type Locale = "en" | "ar";
export type Direction = "ltr" | "rtl";

export interface User {
  id: string;
  name: string;
  email: string;
  avatar_url?: string;
  is_platform_admin?: boolean;
}

export interface Clinic {
  id: string;
  name: string;
  city?: string;
  status: "active" | "suspended";
}

export interface BootstrapState {
  app_state: AppState;
  user: User | null;
  clinics: Clinic[];
  active_clinic: Clinic | null;
  locale: Locale;
  direction: Direction;
  permissions: string[];
  features: string[];
}

export interface ApiSuccess<T> {
  data: T;
  meta?: Record<string, unknown>;
}

export interface ApiError {
  error: {
    code: string;
    message: string;
    details?: Record<string, unknown>;
  };
  meta: { request_id: string };
}
