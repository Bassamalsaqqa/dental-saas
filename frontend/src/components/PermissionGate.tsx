import { useBootstrap } from "@/app/bootstrap/BootstrapProvider";
import { hasPermission } from "@/lib/permissions";
import type { ReactNode } from "react";

interface Props {
  permission: string;
  fallback?: ReactNode;
  children: ReactNode;
}

/**
 * Mock permission gate for UI hint only.
 * Backend remains the source of truth for authorization.
 */
export function PermissionGate({ permission, fallback = null, children }: Props) {
  const { permissions } = useBootstrap();
  if (!hasPermission(permissions, permission)) return <>{fallback}</>;
  return <>{children}</>;
}
