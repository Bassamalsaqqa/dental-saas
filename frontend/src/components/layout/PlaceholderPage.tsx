import { PageHeader } from "@/components/layout/PageHeader";
import { useT } from "@/app/bootstrap/useT";

interface Props {
  title: string;
  description?: string;
}

export function PlaceholderPage({ title, description }: Props) {
  const t = useT();
  return (
    <div>
      <PageHeader title={title} description={description} />
      <div className="rounded-lg border border-dashed bg-card p-10 text-center">
        <p className="text-sm text-muted-foreground">{t("common.placeholder_module")}</p>
      </div>
    </div>
  );
}
