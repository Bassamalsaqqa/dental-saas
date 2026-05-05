import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from "react";
import { mockApi } from "@/api/mockApi";
import { applyDirection } from "@/lib/rtl";
import { directionFor } from "@/lib/i18n";
import type { AppState, BootstrapState, Clinic, Locale, User } from "@/types";

interface BootstrapContextValue extends BootstrapState {
  loading: boolean;
  setAppState: (s: AppState) => void;
  setLocale: (l: Locale) => void;
  setActiveClinic: (c: Clinic | null) => void;
  setUser: (u: User | null) => void;
  reload: () => Promise<void>;
}

const BootstrapContext = createContext<BootstrapContextValue | null>(null);

const initial: BootstrapState = {
  app_state: "clinic_required",
  user: null,
  clinics: [],
  active_clinic: null,
  locale: "en",
  direction: "ltr",
  permissions: [],
  features: [],
};

export function BootstrapProvider({ children }: { children: ReactNode }) {
  const [state, setState] = useState<BootstrapState>(initial);
  const [loading, setLoading] = useState(true);

  const reload = useCallback(async () => {
    setLoading(true);
    try {
      const data = await mockApi.bootstrap();
      setState((prev) => ({ ...data, locale: prev.locale, direction: prev.direction }));
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    void reload();
  }, [reload]);

  useEffect(() => {
    applyDirection(state.direction);
  }, [state.direction]);

  const value = useMemo<BootstrapContextValue>(
    () => ({
      ...state,
      loading,
      setAppState: (app_state) => setState((s) => ({ ...s, app_state })),
      setLocale: (locale) =>
        setState((s) => ({ ...s, locale, direction: directionFor(locale) })),
      setActiveClinic: (active_clinic) =>
        setState((s) => ({
          ...s,
          active_clinic,
          app_state: active_clinic ? "tenant_ready" : s.app_state,
        })),
      setUser: (user) => setState((s) => ({ ...s, user })),
      reload,
    }),
    [state, loading, reload]
  );

  return <BootstrapContext.Provider value={value}>{children}</BootstrapContext.Provider>;
}

export function useBootstrap() {
  const ctx = useContext(BootstrapContext);
  if (!ctx) throw new Error("useBootstrap must be used within BootstrapProvider");
  return ctx;
}
