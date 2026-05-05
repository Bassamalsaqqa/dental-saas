import { Navigate, useLocation } from "react-router-dom";
import { useBootstrap } from "@/app/bootstrap/BootstrapProvider";
import { LoadingState } from "@/components/feedback/LoadingState";
import {
  NoClinicWall,
  SubscriptionInactiveWall,
  SuspendedWall,
} from "@/features/walls/Walls";
import type { ReactNode } from "react";

interface Props {
  children: ReactNode;
}

/** Gates tenant routes based on mock bootstrap state. */
export function TenantGuard({ children }: Props) {
  const { app_state, user, loading } = useBootstrap();
  const loc = useLocation();

  if (loading) return <LoadingState label="Loading..." />;
  if (!user) return <Navigate to="/app/login" state={{ from: loc }} replace />;

  switch (app_state) {
    case "no_clinic":
      return <NoClinicWall />;
    case "suspended":
      return <SuspendedWall />;
    case "subscription_inactive":
      return <SubscriptionInactiveWall />;
    case "clinic_required":
      return <Navigate to="/app/clinic-select" replace />;
    case "controlplane_ready":
      return <Navigate to="/app/controlplane/dashboard" replace />;
    case "tenant_ready":
    default:
      return <>{children}</>;
  }
}

export function ControlPlaneGuard({ children }: Props) {
  const { user, loading } = useBootstrap();
  if (loading) return <LoadingState />;
  if (!user) return <Navigate to="/app/login" replace />;
  return <>{children}</>;
}
