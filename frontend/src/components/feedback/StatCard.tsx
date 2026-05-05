import { cn } from "@/lib/utils";
import { Card } from "@/components/ui/card";

interface Props {
  label: string;
  value: string | number;
  hint?: string;
  icon?: React.ReactNode;
  tone?: "neutral" | "positive" | "warning" | "danger";
  className?: string;
}

const toneCls: Record<NonNullable<Props["tone"]>, string> = {
  neutral: "text-foreground",
  positive: "text-success",
  warning: "text-warning",
  danger: "text-destructive",
};

export function StatCard({ label, value, hint, icon, tone = "neutral", className }: Props) {
  return (
    <Card className={cn("p-4", className)}>
      <div className="flex items-start justify-between">
        <div>
          <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">{label}</p>
          <p className={cn("mt-1.5 text-2xl font-semibold tabular-nums", toneCls[tone])}>{value}</p>
          {hint && <p className="mt-1 text-xs text-muted-foreground">{hint}</p>}
        </div>
        {icon && <div className="text-muted-foreground">{icon}</div>}
      </div>
    </Card>
  );
}
