import { Card } from "@/components/ui/card";
import { PageHeader } from "@/components/layout/PageHeader";

export default function ControlPlanePage({
  title,
  description,
}: {
  title: string;
  description?: string;
}) {
  return (
    <div>
      <PageHeader title={title} description={description} />
      <Card className="border-controlplane-border bg-controlplane-accent/40 p-10 text-center">
        <p className="text-sm text-controlplane-foreground/80">
          Control plane shell placeholder. Real functionality not built in this phase.
        </p>
      </Card>
    </div>
  );
}
