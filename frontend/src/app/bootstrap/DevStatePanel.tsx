import { useState } from "react";
import { Settings2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useBootstrap } from "@/app/bootstrap/BootstrapProvider";
import type { AppState } from "@/types";
import { cn } from "@/lib/utils";

const states: { value: AppState; label: string }[] = [
  { value: "no_clinic", label: "No clinic" },
  { value: "clinic_required", label: "Clinic required" },
  { value: "tenant_ready", label: "Tenant ready" },
  { value: "controlplane_ready", label: "Control plane" },
  { value: "suspended", label: "Suspended" },
  { value: "subscription_inactive", label: "Subscription inactive" },
];

/** Floating dev-only switcher to flip mock app_state. */
export function DevStatePanel() {
  const [open, setOpen] = useState(false);
  const { app_state, setAppState } = useBootstrap();

  return (
    <div className="fixed bottom-4 end-4 z-50">
      {open && (
        <div className="mb-2 w-56 rounded-lg border bg-card p-2 shadow-lg">
          <p className="px-2 py-1 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
            Dev: app_state
          </p>
          <div className="space-y-0.5">
            {states.map((s) => (
              <button
                key={s.value}
                onClick={() => setAppState(s.value)}
                className={cn(
                  "block w-full rounded px-2 py-1.5 text-start text-xs hover:bg-accent",
                  app_state === s.value && "bg-primary/10 text-primary font-medium"
                )}
              >
                {s.label}
              </button>
            ))}
          </div>
        </div>
      )}
      <Button size="sm" variant="outline" className="shadow-lg" onClick={() => setOpen((v) => !v)}>
        <Settings2 className="me-1 h-4 w-4" />
        Dev
      </Button>
    </div>
  );
}
