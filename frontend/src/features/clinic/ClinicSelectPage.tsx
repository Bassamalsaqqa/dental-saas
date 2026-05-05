import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { Building2, Check } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { LanguageSwitcher } from "@/components/layout/LanguageSwitcher";
import { useBootstrap } from "@/app/bootstrap/BootstrapProvider";
import { useT } from "@/app/bootstrap/useT";
import { mockApi } from "@/api/mockApi";
import { cn } from "@/lib/utils";

export default function ClinicSelectPage() {
  const t = useT();
  const { clinics, setActiveClinic } = useBootstrap();
  const [selected, setSelected] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);
  const navigate = useNavigate();

  const onContinue = async () => {
    if (!selected) return;
    setBusy(true);
    try {
      const { active_clinic } = await mockApi.switchClinic(selected);
      setActiveClinic(active_clinic);
      navigate("/app/dashboard");
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="relative flex min-h-screen items-center justify-center bg-gradient-to-br from-background to-accent/30 p-6">
      <div className="absolute end-4 top-4">
        <LanguageSwitcher />
      </div>
      <div className="w-full max-w-2xl">
        <div className="mb-6 text-center">
          <h1 className="text-2xl font-semibold tracking-tight">{t("clinic.select_title")}</h1>
          <p className="mt-1 text-sm text-muted-foreground">{t("clinic.select_subtitle")}</p>
        </div>

        {clinics.length === 0 ? (
          <Card className="p-8 text-center text-sm text-muted-foreground">{t("wall.no_clinic")}</Card>
        ) : (
          <div className="grid gap-3 sm:grid-cols-2">
            {clinics.map((c) => {
              const isSelected = selected === c.id;
              return (
                <button
                  key={c.id}
                  onClick={() => setSelected(c.id)}
                  className={cn(
                    "group relative rounded-lg border bg-card p-4 text-start transition-all hover:border-primary/50 hover:shadow-sm",
                    isSelected && "border-primary ring-2 ring-primary/20"
                  )}
                >
                  <div className="flex items-start gap-3">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
                      <Building2 className="h-5 w-5" />
                    </div>
                    <div className="flex-1">
                      <p className="text-sm font-medium">{c.name}</p>
                      {c.city && <p className="text-xs text-muted-foreground">{c.city}</p>}
                    </div>
                    {isSelected && (
                      <div className="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-primary-foreground">
                        <Check className="h-3 w-3" />
                      </div>
                    )}
                  </div>
                </button>
              );
            })}
          </div>
        )}

        <div className="mt-6 flex justify-end">
          <Button onClick={onContinue} disabled={!selected || busy}>
            {t("clinic.continue")}
          </Button>
        </div>
      </div>
    </div>
  );
}
