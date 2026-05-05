import { AlertTriangle, Building2, ShieldOff, CreditCard, ShieldCheck } from "lucide-react";
import { Card } from "@/components/ui/card";
import { useT } from "@/app/bootstrap/useT";
import type { ReactNode } from "react";

function Wall({ icon, title, message }: { icon: ReactNode; title: string; message?: string }) {
  return (
    <div className="flex min-h-screen w-full items-center justify-center bg-background p-6">
      <Card className="max-w-md p-8 text-center">
        <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-muted text-muted-foreground">
          {icon}
        </div>
        <h2 className="text-lg font-semibold">{title}</h2>
        {message && <p className="mt-2 text-sm text-muted-foreground">{message}</p>}
      </Card>
    </div>
  );
}

export function NoClinicWall() {
  const t = useT();
  return <Wall icon={<Building2 className="h-6 w-6" />} title="No clinic assigned" message={t("wall.no_clinic")} />;
}

export function SuspendedWall() {
  const t = useT();
  return <Wall icon={<ShieldOff className="h-6 w-6 text-destructive" />} title="Clinic suspended" message={t("wall.suspended")} />;
}

export function SubscriptionInactiveWall() {
  const t = useT();
  return <Wall icon={<CreditCard className="h-6 w-6 text-warning" />} title="Subscription inactive" message={t("wall.subscription_inactive")} />;
}

export function ControlPlaneReadyPlaceholder() {
  const t = useT();
  return <Wall icon={<ShieldCheck className="h-6 w-6 text-primary" />} title="Control Plane" message={t("wall.controlplane")} />;
}

export function GenericWall({ message }: { message?: string }) {
  return <Wall icon={<AlertTriangle className="h-6 w-6" />} title="Unavailable" message={message} />;
}
