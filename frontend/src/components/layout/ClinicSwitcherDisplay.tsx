import { Building2 } from "lucide-react";
import { useBootstrap } from "@/app/bootstrap/BootstrapProvider";

export function ClinicSwitcherDisplay() {
  const { active_clinic } = useBootstrap();
  if (!active_clinic) return null;
  return (
    <div className="flex items-center gap-2 rounded-md border bg-card px-3 py-1.5">
      <Building2 className="h-4 w-4 text-primary" />
      <div className="leading-tight">
        <p className="text-xs font-medium text-foreground">{active_clinic.name}</p>
        {active_clinic.city && (
          <p className="text-[10px] text-muted-foreground">{active_clinic.city}</p>
        )}
      </div>
    </div>
  );
}
