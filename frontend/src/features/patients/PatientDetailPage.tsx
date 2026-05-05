import { useNavigate, useParams } from "react-router-dom";
import { ArrowLeft } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { PageHeader } from "@/components/layout/PageHeader";
import { PatientAvatar } from "@/components/Avatars";
import { useT } from "@/app/bootstrap/useT";

export default function PatientDetailPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const t = useT();

  return (
    <div>
      <Button variant="ghost" size="sm" className="mb-3 gap-1" onClick={() => navigate("/app/patients")}>
        <ArrowLeft className="h-4 w-4 rtl-flip" />
        Back
      </Button>
      <PageHeader title={`Patient ${id}`} description="Placeholder profile shell." />
      <Card className="p-6">
        <div className="flex items-center gap-3">
          <PatientAvatar name={`P ${id}`} className="h-12 w-12" />
          <div>
            <p className="text-base font-semibold">Patient #{id}</p>
            <p className="text-xs text-muted-foreground">Mock profile</p>
          </div>
        </div>
        <div className="mt-6 rounded-md border border-dashed p-6 text-center text-sm text-muted-foreground">
          {t("common.placeholder_module")}
        </div>
      </Card>
    </div>
  );
}
