import { cn } from "@/lib/utils";
import { Inbox } from "lucide-react";

interface Props {
  title?: string;
  description?: string;
  icon?: React.ReactNode;
  action?: React.ReactNode;
  className?: string;
}

export function EmptyState({ title = "No data", description, icon, action, className }: Props) {
  return (
    <div className={cn("flex flex-col items-center justify-center rounded-lg border border-dashed bg-card p-10 text-center", className)}>
      <div className="mb-3 text-muted-foreground">{icon ?? <Inbox className="h-8 w-8" />}</div>
      <p className="text-sm font-medium text-foreground">{title}</p>
      {description && <p className="mt-1 max-w-sm text-sm text-muted-foreground">{description}</p>}
      {action && <div className="mt-4">{action}</div>}
    </div>
  );
}
